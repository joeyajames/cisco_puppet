<?php
$cisco_vlan_commands = array(
'vlan' => array ('vlan', array('<integer>', )),
'ensure' => array ('ensure', array('absent', 'present', )),
'vlan name' => array ('vlan_name', array('default', '<string>', )),
'state' => array ('state', array('suspend', 'active', 'default', )),
'shutdown' => array ('shutdown', array('true', 'false', 'default', )),
);
?>