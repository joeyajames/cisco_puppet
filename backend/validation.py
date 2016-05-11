import re
import parse_types as parse

def get_all_types():
    type_dict = {}
    with open('types.txt') as f:
        lines = f.readlines()
        for line in lines:
            type_list = line.split(';')
            type_dict[type_list[0]] = type_list
    return type_dict
 
def validate_attr_value(ds_types, search_type, attr, value):
    attr= parse.get_name(attr)
    find_int =re.search(r'(^-?[0-9]+$)',value)
    if find_int:
        value = int(value)
    print ds_types[search_type][attr]
    if value in ds_types[search_type][attr] or type(value) in ds_types[search_type][attr]:
        print "Provided value of the attribute is validated now, need to prepare manifest"
    
def type_check(type_dict, ds_types, index, cmd_list, search_type, value):
    while (search_type):
        if search_type in type_dict.keys():
            print "Type:", search_type, "Value:", value
            type_name = type_dict[search_type][1]
            print ds_types[type_name][search_type]
            if value in ds_types[type_name][search_type] or type(value) in ds_types[type_name][search_type]:
                print "Provided value is validated now, need to prepare manifest"
                break
        else:
            value = search_type.rsplit(' ', 1)[1]
            search_type = search_type.rsplit(' ', 1)[0]

def attr_check(type_dict, ds_types, index, cmd_list, search_type, value):  
    while(search_type):
        if search_type in type_dict.keys():
            break
        else:
            search_type = search_type.rsplit(' ', 1)[0]
    
    search_type = type_dict[search_type][1]     #Get the type 
    
    cmd_list[index] = cmd_list[index].lstrip()
    tmp_cmd_list = cmd_list[index].split(' ')
    
    if tmp_cmd_list[0] == 'no':
        value = 'false'
        attr = cmd_list[index].split(' ', 1)[1]
        if attr in ds_types[search_type].keys():
            print "Attribute: ", attr, "Value: ", value
            validate_attr_value(ds_types, search_type, attr, value)
        
    else:
        while (cmd_list[index]):
            if cmd_list[index] in ds_types[search_type].keys():
                if not value:
                    value = 'true'
                print "Attribute: ", cmd_list[index], "value: ", value
                validate_attr_value(ds_types, search_type, cmd_list[index], value)
                break
                
            else:
                value = cmd_list[index].rsplit(' ', 1)[1]
                cmd_list[index] = cmd_list[index].rsplit(' ', 1)[0]
          

def parse_data(data):
    cmd_list = data.split('\n')
    l_cmd_list = len(cmd_list)
    for index in range(l_cmd_list):
        type_dict = get_all_types()
        ds_types = parse.Parse_types().types
        value = ''
        
        if not cmd_list[index]:
            continue
        
        if cmd_list[index][0] != ' ':
            search_type = cmd_list[index]
            print "%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%\n", index, cmd_list[index]
            type_check(type_dict, ds_types, index, cmd_list, search_type, value)
            
        else:
            print "$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$\n", index, cmd_list[index]
            attr_check(type_dict, ds_types, index, cmd_list, search_type, value)
        
                

def Convert(input_data):
    parse_data(input_data)
    return True

input_data = input_string = 'interface vlan14\n  mtu 9100\n  ip address 124.14.1.1/24\ninterface eth1/1\n  shutdown\n  no ip redirects'
Convert(input_data)
