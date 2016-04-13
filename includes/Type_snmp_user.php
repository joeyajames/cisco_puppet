<?php
$snmp_user_commands = array(
'ensure' => array ('ensure', array('absent', 'present', )),
'name' => array ('name', array('<string>', )),
'engine id' => array ('engine_id', array('<string>', )),
'roles' => array ('roles', array('<string>', )),
'auth' => array ('auth', array('md5', 'sha', )),
'password' => array ('password', array('<string>', )),
'privacy' => array ('privacy', array('des', 'aes128', )),
'private key' => array ('private_key', array('<string>', )),
'localized key' => array ('localized_key', array('true', 'false', )),
);
?>