import string

def get_values(s):
	values = list()
	slist = s.split(" ")
	for word in slist:
		if "\'" in word:
			values.append(''.join(c for c in word if c not in string.punctuation))
		if '[' in word and ']' in word:
			brackets = word.split('|')
			for item in brackets:
				val = (''.join(c for c in item if c not in string.punctuation))
				if val.isdigit():
					values.append('<integer>')
				else:
					values.append(val)
	print (values)
			
st = "My dog is \'brown\' and my cat is \'yellow\'. Valid values [big|small|medium] and [0-7]."		
get_values(st)

