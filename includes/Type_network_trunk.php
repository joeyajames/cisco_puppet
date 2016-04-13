<?php
$network_trunk_commands = array(
'name' => array ('name', array('<string>', )),
'encapsulation' => array ('encapsulation', array('isl', 'negotiate', 'dot1q', 'none', )),
'mode' => array ('mode', array('dynamicauto', 'access', 'dynamicdesirable', 'trunk', )),
'untagged vlan' => array ('untagged_vlan', array()),
'tagged vlans' => array ('tagged_vlans', array('<string>', )),
'pruned vlans' => array ('pruned_vlans', array('<string>', '<integer>', )),
);
?>