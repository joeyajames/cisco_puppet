<?php
$cisco_bgp_neighbor_commands = array(
'ensure' => array ('ensure', array('absent', 'present', )),
'asn' => array ('asn', array('<string>', '<integer>', )),
'vrf' => array ('vrf', array('present', 'default', '<string>', )),
'neighbor' => array ('neighbor', array('<string>', )),
'description' => array ('description', array('<string>', )),
'connected check' => array ('connected_check', array('true', 'false', )),
'capability negotiation' => array ('capability_negotiation', array('true', 'false', )),
'dynamic capability' => array ('dynamic_capability', array('true', 'false', )),
'ebgp multihop' => array ('ebgp_multihop', array('default', '<integer>', )),
'local as' => array ('local_as', array('default', '<string>', '<integer>', )),
'log neighbor changes' => array ('log_neighbor_changes', array('disable', 'enable', 'inherit', )),
'low memory exempt' => array ('low_memory_exempt', array('true', 'false', 'default', )),
'maximum peers' => array ('maximum_peers', array('default', 'ipprefix', )),
'password' => array ('password', array('<string>', )),
'password type' => array ('password_type', array('3des', 'cleartext', 'default', 'ciscotype7', 'defaultwhich', )),
'remote as' => array ('remote_as', array('default', '<string>', '<integer>', )),
'remove private as' => array ('remove_private_as', array('replaceas', 'all', 'disable', 'enable', '<integer>', )),
'shutdown' => array ('shutdown', array('true', 'false', )),
'suppress 4 byte as' => array ('suppress_4_byte_as', array('true', 'false', 'default', )),
'timers keepalive' => array ('timers_keepalive', array('default', '<integer>', )),
'timers holdtime' => array ('timers_holdtime', array('default', '<integer>', )),
'transport passive only' => array ('transport_passive_only', array('true', 'false', 'default', 'ip', )),
'update source' => array ('update_source', array('<string>', )),
);
?>