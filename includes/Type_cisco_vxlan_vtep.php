<?php
$cisco_vxlan_vtep_commands = array(
'ensure' => array ('ensure', array('absent', 'present', )),
'description' => array ('description', array('default', '<string>', )),
'host reachability' => array ('host_reachability', array('flood', 'evpn', 'default', )),
'shutdown' => array ('shutdown', array('true', 'false', 'default', )),
'source interface' => array ('source_interface', array('default', '<string>', )),
);
?>