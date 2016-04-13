<?php
$snmp_notification_receiver_commands = array(
'ensure' => array ('ensure', array('absent', 'present', )),
'name' => array ('name', array('<string>', )),
'port' => array ('port', array('<integer>', )),
'username' => array ('username', array('<string>', )),
'version' => array ('version', array('v2', 'v1', 'v3', )),
'type' => array ('type', array('informs', 'traps', )),
'security' => array ('security', array('auto', 'noauth', 'priv', )),
'vrf' => array ('vrf', array('<string>', )),
'source interface' => array ('source_interface', array('<string>', )),
);
?>