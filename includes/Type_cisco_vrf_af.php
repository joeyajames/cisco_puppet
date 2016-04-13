<?php
$cisco_vrf_af_commands = array(
'ensure' => array ('ensure', array('absent', 'present', 'default', )),
'name' => array ('name', array('<string>', )),
'afi' => array ('afi', array('ipv4', 'ipv6', )),
'safi' => array ('safi', array('<integer>', )),
'route target both auto' => array ('route target both auto', array('true', 'false', 'default', )),
'route target both auto evpn' => array ('route target both auto evpn', array('true', 'false', 'default', )),
'route target import' => array ('route_target_import', array('default', '<string>', )),
'route target import evpn' => array ('route_target_import_evpn', array('default', '<string>', )),
'route target export' => array ('route_target_export', array('default', '<string>', )),
'route target export evpn' => array ('route_target_export_evpn', array('default', '<string>', )),
);
?>