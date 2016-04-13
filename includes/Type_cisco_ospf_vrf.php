<?php
$cisco_ospf_vrf_commands = array(
'ensure' => array ('ensure', array('absent', 'present', )),
'vrf' => array ('vrf', array('present', 'default', '<string>', )),
'ospf' => array ('ospf', array('<string>', )),
'router id' => array ('router_id', array('default', '<string>', )),
'default metric' => array ('default_metric', array('default', 'keywordndefault', '<integer>', )),
'log adjacency' => array ('log_adjacency', array('none', 'default', 'detail', 'log', )),
'timer throttle lsa start' => array ('timer_throttle_lsa_start', array('default', '<integer>', )),
'timer throttle lsa hold' => array ('timer_throttle_lsa_hold', array('default', '<integer>', )),
'timer throttle lsa max' => array ('timer_throttle_lsa_max', array('default', '<integer>', )),
'timer throttle spf start' => array ('timer_throttle_spf_start', array('default', '<integer>', )),
'timer throttle spf hold' => array ('timer_throttle_spf_hold', array('default', '<integer>', )),
'timer throttle spf max' => array ('timer_throttle_spf_max', array('default', '<integer>', )),
'auto cost' => array ('auto_cost', array('default', '<integer>', )),
);
?>