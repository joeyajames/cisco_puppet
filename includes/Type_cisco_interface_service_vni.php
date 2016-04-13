<?php
$cisco_interface_service_vni_commands = array(
'ensure' => array ('ensure', array('absent', 'present', )),
'interface' => array ('interface', array('<string>', )),
'sid' => array ('sid', array('<integer>', )),
'encapsulation profile vni' => array ('encapsulation_profile_vni', array('default', '<string>', )),
'shutdown' => array ('shutdown', array('true', 'false', 'default', )),
);
?>