import string
from bs4 import BeautifulSoup
  
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
def get_valid_values(s):
	values = list()
	if 'integer' in s or 'Integer' in s:
		values.append('<integer>')
	if 'number' in s or 'Number' in s:
		values.append('<integer>')
	if 'port' in s or 'Port' in s:
		values.append('<integer>')
		
	if 'string' in s or 'String' in s:
		values.append('<string>')
	if 'interface' in s or 'Interface' in s:
		values.append('<string>')
	if 'array' in s or 'Array' in s:
		values.append('<string>')
		
	if 'true' in s or 'True' in s:
		values.append('true')
	if 'false' in s or 'False' in s:
		values.append('false')
	if 'present' in s or 'Present' in s:
		values.append('present')
	if 'absent' in s or 'Absent' in s:
		values.append('absent')
	if 'default' in s or 'Default' in s:
		values.append('default')
		
	s = s.replace('\n', ' ')	# for some reason this does not seem to work. still get a few odd results from linebreaks.
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
					values.append('<integer>')
				else:
					values.append(val)
	values = list(set(values))
	
	return values
	
def main():
	# first have to fix encoding problems with the html page
	with open("cisco_puppet_module_webpage.txt", 'r') as myfile:
		data = myfile.read()# .replace('&#x2713;', '*')
		data = str(data.encode('ascii', 'ignore'))
	with open("cisco_puppet_module_webpage2.txt", 'w') as outfile:
		outfile.write(data)
	  
	html_doc = open ("cisco_puppet_module_webpage2.txt")
	soup = BeautifulSoup(html_doc, 'html.parser')

	bstypes = get_bstypes(soup)
	
	tags = soup.find_all()
	for i in range(len(tags)):
		tag = str(tags[i])
		if tags[i] in bstypes.values():
			type = tags[i].text
			type_name = get_name(type)
			print(type_name, ' -> ', type[6:])
			
			out_filename = 'Type_' + type[6:] + '.php'
			with open ('..\\includes\\' + out_filename, 'w') as fout:
				fout.write('<?php\n')
				fout.write('$' + type[6:] + '_commands = array(\n')
				
				j = i + 1
				tag = str(tags[j])
				param = ''
				
				while '<h3>' not in tag:
					if ('<h5>' in tag or '<h6>' in tag) and '<code>' in tag:
						param = tags[j].text

						if not param[0].isupper() and param != "":
							fout.write("'" + get_name(param) + "' => array ('" + param + "', array(")
							print('    ', get_name(param), ' -> ', param)
						
					if '<p>' in tag and param != "":
						valid_values = tags[j].text
						if "Valid" in valid_values:
							valid_values = "Valid" + valid_values.split("Valid")[1]
						valid_values_list = get_valid_values(valid_values)
						for valid_value in valid_values_list:
							fout.write("'" + valid_value + "', ")
						print('        ', valid_values_list)
						if param != "":
							fout.write(")),\n")
							param = ''
						
					j += 1
					if  j >= len(tags):
						break
					tag = str(tags[j])
				fout.write(");\n?>")
						 
			
main()


'''	
for i in range(len(tags)):
	try:
		print (tags[i])
	except:
		print ("ERROR CODE J001")
	# if "Type: " in tags[i]:
		# type = tag[i].text
		# print (tags[i])
'''