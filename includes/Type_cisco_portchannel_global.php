<?php
$cisco_portchannel_global_commands = array(
'asymmetric' => array ('asymmetric', array('true', 'false', 'default', '<integer>', )),
'bundle hash' => array ('bundle_hash', array('mac', 'iponly', 'ipvlan', 'port', 'l4port', 'ipgre', 'ip', 'ipl4portvlan', 'default', 'ipl4port', '<integer>', 'portonly', )),
'bundle select' => array ('bundle_select', array('dst', 'src', 'srcdst', 'default', )),
'concatenation' => array ('concatenation', array('true', 'false', 'default', '<integer>', )),
'hash distribution' => array ('hash_distribution', array('adaptive', 'fixed', 'default', '<integer>', )),
'hash poly' => array ('hash_poly', array('CRC10b', 'CRC10a', 'CRC10c', 'default', 'CRC10d', '<integer>', )),
'load defer' => array ('load_defer', array('default', '<integer>', )),
'resilient' => array ('resilient', array('true', 'false', 'default', '<integer>', )),
'rotate' => array ('rotate', array('default', '<integer>', )),
'symmetry' => array ('symmetry', array('true', 'false', 'default', '<integer>', )),
);
?>