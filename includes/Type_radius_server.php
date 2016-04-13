<?php
$radius_server_commands = array(
'ensure' => array ('ensure', array('absent', 'present', )),
'name' => array ('name', array('<string>', )),
'auth port' => array ('auth_port', array('<integer>', )),
'acct port' => array ('acct_port', array('<integer>', )),
'timeout' => array ('timeout', array('<integer>', )),
'retransmit count' => array ('retransmit_count', array('<integer>', )),
'accouting only' => array ('accouting_only', array('true', 'false', )),
'authentication only' => array ('authentication_only', array('true', 'false', )),
'key' => array ('key', array('<string>', )),
'key format' => array ('key_format', array('<integer>', )),
);
?>