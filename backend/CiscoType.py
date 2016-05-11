# NX-OS to Puppet Converter
# Author: Joe James, Cisco Systems, Apr-2016
# Description: stores Puppet Types, their Attributes and valid values
'''
EXAMPLE
interface Ethernet1/3
  ip address 168.175.0.5/24
  
CiscoType 
    typeName: interface
    ciscoType: cisco_interface
    value:    Ethernet1/3
    puppetHeader: $ciscopuppet::l3_interface_cfg_data::l3_interface_instances.each |$interface, $value| {
Attribute
    attributeName: ip address
    attribute: ipv4_address
    valid values: ['default', '<string>']
'''

class CiscoType:
    def __init__(self):
        self.types = {}
        
    def add_attribute(self, cmd_type, attr):
        if not isinstance(attr, Attribute):
            raise TypeError('Provide proper Attribute object')
        self.types[cmd_type] = attr.get_attributes()
        
    def get_attributes(self):
        return self.types
    
        
class Attribute:
    def __init__(self):
        self.attributes = {}
    
    def get_attributes(self):
        return self.attributes
   
    def add_attributes(self, attr, val):
        self.attributes[attr] = val
                    
    
