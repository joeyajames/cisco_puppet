<?php
$cisco_tacacs_server_host_commands = array(
'ensure' => array ('ensure', array('absent', 'present', )),
'host' => array ('host', array('<string>', )),
'port' => array ('port', array('default', '<integer>', )),
'timeout' => array ('timeout', array('default', '<integer>', )),
'encryption type' => array ('encryption_type', array('clear', 'default', 'encryptednnone', )),
'encryption password' => array ('encryption_password', array('<string>', )),
);
?>