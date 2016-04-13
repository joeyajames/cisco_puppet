<?php
$cisco_vxlan_global_commands = array(
'ensure' => array ('ensure', array('absent', 'present', )),
'name' => array ('name', array('default', )),
'anycast gateway mac' => array ('anycast_gateway_mac', array()),
'dup host ip addr detection host moves' => array ('dup_host_ip_addr_detection_host_moves', array('default', '<integer>', )),
'dup host ip addr detection timeout' => array ('dup_host_ip_addr_detection_timeout', array('default', '<integer>', )),
'dup host mac detection host moves' => array ('dup_host_mac_detection_host_moves', array('default', '<integer>', )),
'dup host mac detection timeout' => array ('dup_host_mac_detection_timeout', array('default', '<integer>', )),
);
?>