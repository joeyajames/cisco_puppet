<?php
$cisco_snmp_user_commands = array(
'ensure' => array ('ensure', array('absent', 'present', )),
'user' => array ('user', array('<string>', )),
'engine id' => array ('engine_id', array('<string>', )),
'groups' => array ('groups', array('<string>', )),
'auth protocol' => array ('auth_protocol', array('shanand', 'md5', 'none', )),
'auth password' => array ('auth_password', array('<string>', )),
'priv protocol' => array ('priv_protocol', array('des', 'andnnone', 'aes128', )),
'priv password' => array ('priv_password', array('<string>', )),
'localized key' => array ('localized_key', array('true', 'false', )),
);
?>