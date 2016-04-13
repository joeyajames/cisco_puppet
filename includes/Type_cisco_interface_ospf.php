<?php
$cisco_interface_ospf_commands = array(
'ensure' => array ('ensure', array('absent', 'present', 'arenpresent', )),
'interface' => array ('interface', array('<string>', )),
'ospf' => array ('ospf', array('<string>', )),
'cost' => array ('cost', array('<integer>', )),
'hello interval' => array ('hello_interval', array('default', '<integer>', )),
'dead interval' => array ('dead_interval', array('default', '<integer>', )),
'passive interface' => array ('passive_interface', array('true', 'false', )),
'message digest' => array ('message_digest', array('true', 'false', )),
'message digest key id' => array ('message_digest_key_id', array('<integer>', )),
'message digest algorithm type' => array ('message_digest_algorithm_type', array('default', 'md5', )),
'message digest encryption type' => array ('message_digest_encryption_type', array('andndefault', '3des', 'cleartext', 'default', 'ciscotype7', )),
'message digest password' => array ('message_digest_password', array('<string>', )),
'area' => array ('area', array('<string>', '<integer>', )),
);
?>