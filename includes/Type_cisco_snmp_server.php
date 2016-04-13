<?php
$cisco_snmp_server_commands = array(
'name' => array ('name', array('default', )),
'location' => array ('location', array('default', '<string>', )),
'contact' => array ('contact', array('default', '<string>', 'keywordndefault', )),
'aaa user cache timeout' => array ('aaa_user_cache_timeout', array('default', '<integer>', )),
'packet size' => array ('packet_size', array('default', '<integer>', )),
'global enforce priv' => array ('global_enforce_priv', array('true', 'false', 'default', 'truenfalse', )),
'protocol' => array ('protocol', array('true', 'false', 'default', )),
'tcp session auth' => array ('tcp_session_auth', array('true', 'false', 'default', )),
);
?>