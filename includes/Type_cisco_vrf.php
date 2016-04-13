<?php
$cisco_vrf_commands = array(
'ensure' => array ('ensure', array('absent', 'present', 'default', )),
'name' => array ('name', array('<string>', )),
'description' => array ('description', array('<string>', )),
'route distinguisher' => array ('route_distinguisher', array('auto', 'default', '<string>', )),
'shutdown' => array ('shutdown', array('true', 'false', )),
'vni' => array ('vni', array('default', '<integer>', )),
);
?>