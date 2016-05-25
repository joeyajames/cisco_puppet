import re
import parse_types as parse

ds_types = parse.Parse_types().types # sourcing the python file which creates the types dictionary
input_dict = {}
global temp_value, t_value
temp_value = ""

def get_all_types():   #reads data from types.txt
    type_dict = {}
    with open('types.txt') as f:
        lines = f.readlines()
        for line in lines:
            type_list = line.split(';')
            type_dict[type_list[0]] = type_list
    return type_dict

def get_attr_name(type):    
    name = type
    if name[:6] == 'Type: ':
        name = type[6:]
    if name[:6] == 'cisco_':
        name = name[6:]
    name = name.replace(" ", "_")
    name = name.replace("ip", "ipv4")
    return name
 
def validate_attr_value(search_type, attr, value, t_value): #validates the attribute values
    attr= parse.get_name(attr)
    find_int =re.search(r'(^-?[0-9]+$)',value)
    if find_int:
        value = int(value)
    if value in ds_types[search_type][attr] or type(value) in ds_types[search_type][attr]:
        input_dict[search_type][t_value][attr] = value
    
def type_check(type_dict, index, cmd_list, search_type, value):  #checks if type is valid
    input_string = search_type
    while (search_type):
        search_length = len(search_type.split())
        if search_type in type_dict.keys():
            type_name = type_dict[search_type][1]
            if not type_name in input_dict:
                input_dict[type_name] = {}
            if value in ds_types[type_name][search_type] or type(value) in ds_types[type_name][search_type]:
                input_dict[type_name][value]= {}
                return type_name, value
        else:
            if search_length > 1:
                value = search_type.rsplit(' ', 1)[1]
                search_type = search_type.rsplit(' ', 1)[0]
            else:
                type_name = 'cisco_command_config'
                value = input_string
                return type_name, value

def attr_check(type_dict, index, cmd_list, search_type, value, t_value):   #checks the attribute is valid
    global temp_value
    while(search_type):
        if search_type in type_dict.keys():
            break
        else:
            search_type = search_type.rsplit(' ', 1)[0]
    
    search_type = type_dict[search_type][1]     #Get the type 
    temp_cmd_list = cmd_list[index]
    cmd_list[index] = cmd_list[index].lstrip()
    tmp_cmd_list = cmd_list[index].split(' ')
    
    if tmp_cmd_list[0] == 'no':
        value = 'false'
        attr = cmd_list[index].split(' ', 1)[1]
        if attr in ds_types[search_type].keys():
            validate_attr_value(search_type, attr, value, t_value)
        else:
            temp_value = temp_value+temp_cmd_list+'\n'
                
    else:
        
        while (cmd_list[index]):
            cmd_length = len(cmd_list[index].split())
            if cmd_list[index] in ds_types[search_type].keys():
                if not value:
                    value = 'true'
                attr = cmd_list[index]
                if attr == "ip address":
                    ip_addr_match = re.search(r'(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\/(\d+)', value)
                    value = ip_addr_match.group(1)
                    validate_attr_value(search_type, cmd_list[index], value, t_value)
                    attr = "ipv4_netmask_length"
                    value = ip_addr_match.group(2)
                    validate_attr_value(search_type, attr, value, t_value)
                    break
                else :
                    validate_attr_value(search_type, cmd_list[index], value, t_value)
                    break
                
            else:
                if cmd_length > 1:
                    value = cmd_list[index].rsplit(' ', 1)[1]
                    cmd_list[index] = cmd_list[index].rsplit(' ', 1)[0]
                else:
                    temp_value = temp_value+temp_cmd_list+'\n'
                    break
          

def parse_data(data):
    global temp_value
    cmd_list = data.split('\n')
    l_cmd_list = len(cmd_list)
    for index in range(l_cmd_list):
        type_dict = get_all_types()
        
        value = ''
        if not cmd_list[index]:
            continue
        
        if cmd_list[index][0] != ' ':
            search_type = cmd_list[index]
            values = type_check(type_dict, index, cmd_list, search_type, value)
            p_type = values[0]
            t_value = values[1]
            
        else:
            if p_type != 'cisco_command_config':
                attr_check(type_dict, index, cmd_list, search_type, value, t_value)
        
    generate_manifest(type_dict, p_type,t_value)
    if p_type != "cisco_command_config":
        generate_instances(type_dict, p_type)
    
    if temp_value != "":
        print "Unrecognised lines\n"
        print '===================='
        print temp_value
        
def generate_manifest(type_dict, p_type, t_value):
    c_type = p_type.strip('cisco')
    c_type = c_type.strip('_')
    c_type = re.sub(r'_', ' ',c_type)
    x = ' '
    print '\n'
    print 'Puppet Manifest:'
    print '==============='
    #manifests = " "
    manifests = type_dict[c_type][2]
    manifests = manifests+'\n'
    manifest_line = (2*x)+p_type+' { $'+c_type+':\n'
    manifests = manifests+manifest_line
    if c_type == "command config":
        manifest = manifests+t_value+'\n'
    else:
        key = input_dict.keys()
        inputs = {}
        inputs = input_dict.values()
        inputs = inputs.pop(0)
        keys = inputs.keys()
        p_keys = list()
        for key in keys:
            p_values = inputs[key]
            p_keys.extend(p_values.keys())

        for p_key in p_keys:
            manifests = manifests+(4*x)+p_key+',=> {$value['+p_key+']},\n'
    manifests = manifests+(2*x)+'}\n'
    manifests = manifests+'}\n'
    
    print manifests

def generate_instances(type_dict, p_type):
    c_type = p_type.strip('cisco')
    c_type = c_type.strip('_')
    x = ' '
    print '\n'
    print 'Instances:'
    print '=========='
    instance_name = type_dict[c_type][3]
    
    inputs = input_dict.values()
    inputs = inputs.pop(0)
    keys = inputs.keys()
    p_keys = list()
    p_values = list()
    for key in keys:
        p_values = inputs[key]
        p_keys.append(p_values.keys())
    
    instances = instance_name
    
    for key in keys:
        instances = instances+(2*x)+'"'+key+'"=> {'
        for p_key in inputs[key].keys():
            
            instance = get_attr_name(p_key)+'=>'+str(inputs[key][p_key])+','
            instances = instances+instance
        instances = instances+'},\n'
    instances = instances +'}'
    
    print instances
    
    if temp_value != "":
        unrecog_instances = '$cisco_command_config_instances = {\n"command0"=>{\n'
        unrecog_instances = unrecog_instances+temp_value+'},\n}'
        print unrecog_instances

def Convert(input_data):
    print 'User input'
    print '=========='
    print input_data
    parse_data(input_data)
    return True

input_data = input_string = 'interface vlan14\n  mtu 9100\n  ip address 124.14.1.1/24\ninterface eth1/1\n  shutdown\n  no ip redirects'
Convert(input_data)
