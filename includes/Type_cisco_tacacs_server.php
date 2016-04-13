<?php
$cisco_tacacs_server_commands = array(
'name' => array ('name', array('default', )),
'timeout' => array ('timeout', array('default', '<integer>', )),
'directed request' => array ('directed_request', array('true', 'false', )),
'deadtime' => array ('deadtime', array('default', '<integer>', )),
'encryption type' => array ('encryption_type', array('clear', 'encrypted', 'default', 'none', )),
'encryption password' => array ('encryption_password', array('default', '<string>', )),
'source interface' => array ('source_interface', array('default', '<string>', )),
);
?>