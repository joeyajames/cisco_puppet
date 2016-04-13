<?php
$cisco_interface_portchannel_commands = array(
'ensure' => array ('ensure', array('absent', 'present', )),
'lacp graceful convergence' => array ('lacp_graceful_convergence', array('true', 'false', 'default', )),
'lacp max bundle' => array ('lacp_max_bundle', array('default', '<integer>', )),
'lacp min links' => array ('lacp_min_links', array('default', '<integer>', )),
'lacp suspend individual' => array ('lacp_suspend_individual', array('true', 'false', 'default', )),
'port hash distribution' => array ('port_hash_distribution', array('adaptive', 'fixed', 'default', '<integer>', )),
'port load defer' => array ('port_load_defer', array('true', 'false', 'default', '<integer>', )),
);
?>