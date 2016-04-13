<?php
$cisco_acl_commands = array(
'ensure' => array ('ensure', array('absent', 'present', )),
'afi' => array ('afi', array()),
'acl name' => array ('acl_name', array('<string>', )),
'stats per entry' => array ('stats_per_entry', array('true', 'false', 'default', )),
'fragments' => array ('fragments', array('permitall', 'denyall', )),
);
?>