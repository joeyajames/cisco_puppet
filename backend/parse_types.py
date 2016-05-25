import string
from bs4 import BeautifulSoup
import urllib2
import re
import CiscoType as cis_type
import sys  

reload(sys)  
sys.setdefaultencoding('utf8')

def get_type(tag):
    soup = BeautifulSoup(tag, 'html.parser')
    h3 = soup.find_all('h3')
    return h3.text #tag[i].text[:5] == "Type:":
    
def get_bstypes(soup):
    h3 = (soup.find_all('h3'))
    bstypes = dict()
    for tag in h3:
        if tag.text[:6] == 'Type: ':
            bstypes[tag.text[6:]] = tag
    return bstypes

def get_types(soup):
    h3 = (soup.find_all('h3'))
    types = list()
    for tag in h3:
        if tag.text[:6] == 'Type: ':
            types.append(tag.text[6:])
            
    h5 = (soup.find_all('h5'))
    params = list()
    for tag in h5:
        params.append(tag.text[:])
    
    h6 = (soup.find_all('h6'))
    for tag in h6:
        params.append(tag.text[:])
        # print (tag.text[:])
    
    p = (soup.find_all('p'))
    values = list()
    for tag in p:
        if "Valid " in tag.text[:]:
            values.append(tag.text[:])

    print ("TYPES\n", types)
    print ("PARAMS\n", params)
    print ("VALUES\n", values)
    return types, params, values

def get_name(type):
    name = type
    if name[:6] == 'Type: ':
        name = type[6:]
    if name[:6] == 'cisco_':
        name = name[6:]
    name = name.replace("_", " ")
    name = name.replace("ipv4", "ip")
    return name

# takes a string like "Valid values are ..."
# and adds all valid values to a list, then returns that list
def match_values(match, values):
    first = match.group(1)
    last = match.group(2)
    for i in range(int(first), int(last)+1):
        values.append(i)
                
def get_valid_values(s, values):
    if 'integer' in s or 'Integer' in s:
        match = re.search (r'in the range ([0-9]+)\-([0-9]+)', s)
        match1 = re.search (r'range ([0-9]+)..([0-9]+)', s)
        match2 = re.search (r'between ([0-9]+) and ([0-9]+)', s)
        match3 = re.search (r'from ([0-9]+)..([0-9]+)', s)
        if match:
            first = match.group(1)
            last = match.group(2)
            match5 = re.search(r'multiple of ([0-9]+)', s)
            if match5:    
                multiples = match5.group(1)
                values = range(int(first), int(last)+1, int(multiples))
            else:                    
                values = range(int(first), int(last)+1)
                
        elif match1:
            match_values(match1, values)
            
        elif match2:
            match_values(match2, values)
            
        elif match3:
            match_values(match3, values)
            
        else:
            values.append(type(0))  #integer
        
    if 'Speed' in s:
        s = s.split(".")[1]
        match = re.search(r'Valid values are (.*)', s)
        if match:
            match1 = match.group(1).split(',')
            for value in match1:
                match2 = re.search(r'([0-9gm]+)', value)
                if match2:
                    values.append(match2.group(1))    
        
    if 'number' in s or 'Number' in s:
        values.append(type(0))      #integer
    if ' port' in s or ' Port' in s:
        values.append(type(0))      #integer
        
    if 'string' in s or 'String' in s:
        values.append(type(''))     #string
    if 'interface' in s or 'Interface' in s and 'Speed' not in s:
        values.append(type(''))     #string
    if 'array' in s or 'Array' in s:
        values.append(type(''))     #string
        
    if 'true' in s or 'True' in s:
        values.append('true')
    if 'false' in s or 'False' in s:
        values.append('false')
    if ' present' in s or ' Present' in s:
        values.append('present')
    if 'absent' in s or 'Absent' in s:
        values.append('absent')
    if "default" in s:
        values.append('default')
    if 'permit or deny' in s:
        values.extend(['permit', 'deny'])
        
    
    s = s.replace('\n', ' ')    # for some reason this does not seem to work. still get a few odd results from linebreaks.
    slist = s.split(" ")
    for word in slist:
        # check for \'value\'
        if "\'" in word:
            values.append(''.join(c for c in word if c not in string.punctuation))
        
        # check for [value1|value2|value3]
        if '[' in word and ']' in word:
            brackets = word.split('|')
            for item in brackets:
                val = (''.join(c for c in item if c not in string.punctuation))
                if val.isdigit():
                    values.append(type(0))  #integer
                else:
                    values.append(val)
    values = list(set(values))
    
    return values
    
def Parse_types():

    url = urllib2.urlopen('https://github.com/cisco/cisco-network-puppet-module')
    html_doc = url.read()
    url.close()
    soup = BeautifulSoup(html_doc, 'html.parser')
 
    bstypes = get_bstypes(soup)
    tags = soup.find_all()
    feature_list = []
    
    feature = cis_type.CiscoType()
    for i in range(len(tags)):
        tag = str(tags[i])
        if tags[i] in bstypes.values():
            type = tags[i].text
            type_name = get_name(type)
            
            j = i + 1
            tag = str(tags[j])
            
            attr = cis_type.Attribute()
            param = ''
            while '<h3>' not in tag:
                
                if ('<h5>' in tag or '<h6>' in tag) and '<code>' in tag:
                    param = tags[j].text
                    valid_list = []
                                 
                if '<p>' in tag and param != "":
                    valid_values = tags[j].text
                    if 'Speed' not in valid_values:
                        if "Valid" in valid_values:
                            valid_values = "Valid" + valid_values.split("Valid")[1]
                    valid_values_list = get_valid_values(valid_values, valid_list)
                    attr.add_attributes(get_name(param), valid_values_list)
                    #attr.add_attributes(param, valid_values_list)
                    feature.add_attribute(type[6:], attr)
                j += 1
                if  j >= len(tags):
                    break
                tag = str(tags[j])
    
    return feature 

