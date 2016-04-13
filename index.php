<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!--Author: Joe James, Cisco Systems, Feb-2016 -->
<!--Description: Parses Nexus configuration code and generates from it a Puppet/Ruby manifest -->
<img src="img/logo.png" /><br /><br />
<LINK href="css/style.css" rel="stylesheet" type="text/css">
</head><body>

<?php 
/*********************************************************************************************
# Variable definitions:
# given a config file, 
# interface Ethernet3/12
#	access vlan 24
#	no ip redirects
# will be converted to 
# type_name='interface'; type='cisco_interface'; value='Ethernet3/12'; 
#	cmd_name='access vlan'; cmd='access_vlan'; value=24;
#	cmd_name='ip redirects'; cmd='ipv4_redirects'; value=false;
#
# note that type lines must not be indented, and non-type lines must be indented with at least one space or tab
*********************************************************************************************/

# import cisco_interface Type data
include("includes/Type_cisco_interface.php");

function show_form($in_string) {
	echo '<h4>Input Configuration:</h4>';
	echo '<form action="index.php" method="POST">';
	echo '<textarea name="in_string" ROWS=10 COLS=80>' . $in_string . '</textarea><br />';
	echo '<input type="submit" name="submit" value="Convert" />';
	echo '</form>';
}

function print_results() {
	global $manifests;
	global $instances;
	global $unrecognized;
	
	echo '<h4>Puppet Manifest:</h4><p class="code"><br />';
	foreach ($manifests as $key => $value) {
		echo $value . '&nbsp&nbsp}<br />}<br />';
	}
	echo '</p>';
	
	echo '<h4>Instances:</h4><p class="code"><br />';
	foreach ($instances as $key => $value) {
		echo $value . '}<br />';
	}
	echo '</p>';
	
	if ($unrecognized != "") {
		echo ('<h4>Unrecognized Lines:</h4><p>' . $unrecognized);
	}
}

function is_empty($line) {
	if (strlen(trim($line)) == 0) return true;
	else return false;
}

# actually, this function only checks if line is indented
# if so, returns true, returns false if not indented
function is_type($line) {
	if (ltrim($line) == $line) return true;
	else return false;
}

# test increasingly shorter command strings to find type
# returns longest match of the tokenized_line in all_types
# returns empty string if no match found
function get_type_name($tokenized_line, $all_types) {
	$type_name = "";
	for ($i = count($tokenized_line); $i >= 0; $i--) {
		$type_name = join(" ", array_slice($tokenized_line, 0, $i));
		if (array_key_exists($type_name, $all_types)) {
			return $type_name;
		}
	}
	if (strlen(join(" ", $tokenized_line)) > 2)
		return "command config";
	return "";
}

# read in Types list from Types text file
function get_all_types() {
	$all_types = array();
	$file = fopen("types.txt","r");
	while(! feof($file)) {
		$line = fgets($file);
		if (strlen($line) > 2) {
			$tokens = explode(";", $line);
			$all_types[trim($tokens[0])] = array(trim($tokens[1]), trim($tokens[2]), trim($tokens[3]));
		}
	}
	fclose($file);
	return $all_types;
}

# get list of all type_names in the query
function get_type_names_in_query($lines, $all_types) {
	$type_name = "";
	$type_names_in_query = array();
	
	foreach ($lines as $line) {
		if (is_empty($line)) { continue; }
		
		# line is a type
		else {
			$tokenized_line = array_map('trim', explode(" ", $line));
			
			$type_name = get_type_name($tokenized_line, $all_types);
			if ($type_name != "") {
				$type_names_in_query[] = $type_name;
			}
		}
	}
	return $type_names_in_query;
}

# get list of all types in the query
function get_types_in_query($lines, $all_types) {
	$type_name = "";
	$types_in_query = array();
	
	foreach ($lines as $line) {
		if (is_empty($line)) { continue; }
		
		# line is a type
		else {
			$tokenized_line = array_map('trim', explode(" ", $line));
			
			$type_name = get_type_name($tokenized_line, $all_types);
			if ($type_name != "") {
				$types_in_query[] = $all_types[$type_name][0];
			}
		}
	}
	return $types_in_query;
}

# get number of INDENTs for a given line
# assumes INDENTs are composed of &nbsp characters
function get_indents($line) {
	$num_spaces = strlen($line) - strlen(ltrim($line, ' '));
	$num_indents = $num_spaces / (strlen(INDENT) / 5);
	// echo ($line . $num_indents);
	return $num_indents;
}

function append_cmd_config_instance($type, $lines, $line_number, $cmd_config_mode, $indents) {
	global $instances;
	global $cmd_config_count;
	global $line_num;
	
	# a line inside an unrecognized type
	if ($type == 'cisco_command_config') {
		if (array_key_exists('cisco_command_config', $instances)) {
			for ($i = 0; $i < $indents; $i++) {
				$instances['cisco_command_config'] .= INDENT;
			}
			$instances['cisco_command_config'] .= $lines[$line_number] . '<br />';
		}
		else {
			$instances['cisco_command_config'] = INDENT . ltrim($lines[$line_number]) . '<br />';
		}
	}
	
	# unrecognized parameter inside a recognized type => add to cmd_config
	else {
		if ($cmd_config_count == 0 && ! $cmd_config_mode) {
			$instances['cisco_command_config'] = '$cisco_command_config_instances = {<br />';
		}
		if (! $cmd_config_mode) {
			$instances['cisco_command_config'] .= INDENT . '"command' . $cmd_config_count . '"=>{<br>' . $lines[$line_num-1] . '<br />';
		}
		for ($i = 0; $i < $indents; $i++) {
			$instances['cisco_command_config'] .= INDENT;
		}
		$instances['cisco_command_config'] .= ltrim($lines[$line_number]) . '<br />';
	}
}

function convert($in_string) {
	global $manifests;
	global $instances;
	global $unrecognized;
	global $line_num;
	global $cmd_config_count;

	# read in all Types, get input query and convert to list of lines, get list of types in the query
	$all_types = get_all_types();
	$lines = explode("\n", $in_string);
	$types_in_query = get_types_in_query($lines, $all_types);
	// $type_names_in_query = get_type_names_in_query($lines, $all_types);
	// var_dump($types_in_query);
	// var_dump($all_types);
	
	# import Type files only for types in query
	echo 'Types: ';
	foreach (glob("includes/*.php") as $filename) {
		$mytype = substr(substr($filename, 14), 0, -4);
		if (in_array($mytype, $types_in_query)) {  # only need to include Type files that are part of the query
			include $filename;
			echo $mytype . ', ';
		}
	}
		
	foreach ($lines as $line) {
		$type = "";
		$type_name = "";
		$value = "";
		$line_num += 1;
		$indents = get_indents($line);
			
		if (is_empty($line)) { continue; }
		
		# line is a type (ie. has no indent, so Should be a type)
		elseif (is_type($line)) {
			$tokenized_line = array_map('trim', explode(" ", $line));
			
			$type_name = get_type_name($tokenized_line, $all_types);
			if ($type_name == "") {
				$unrecognized .= '<span class="line_num">' . $line_num . '</span>&nbsp&nbsp&nbsp' . trim($line) . '<br />';
				continue;
			}
			$type = $all_types[$type_name][0];
			// echo "|".$type . "-- " . $line . "|";
			if (strlen($type) == 0) { # should never reach this since unrecognized types use command_config type
				$unrecognized .= '<span class="line_num">' . $line_num . '</span>&nbsp&nbsp&nbsp' . trim($line) . '<br />';
				continue;
			}
			
			// if (count($tokenized_line) > 1) { $value = $tokenized_line[1]; }
			if (count($tokenized_line) > 1) { $value = trim(ltrim($line, $type_name)); }
			if ($type == 'cisco_command_config') { 
				$value = 'command' . $cmd_config_count; 
				$cmd_config_count += 1;
			}
			
			if (array_key_exists($type, $manifests)) {
				// $manifests[$type] .= '&nbsp&nbsp}<br />&nbsp&nbsp' . $type . ' { $' . ltrim(ltrim($type, 'cisco'), '_') . '":<br />';
				$instances[$type] .= '&nbsp&nbsp"' . $value . '"=>{';
			}
			
			elseif ($type != 'cisco_command_config') {
				$manifests[$type] = $all_types[$type_name][1] . '<br />&nbsp&nbsp' . $type . ' { $' . ltrim(ltrim($type, 'cisco'), '_') . ':<br />';
				$instances[$type] = $all_types[$type_name][2] . '<br />&nbsp&nbsp"' . $value . '"=>{';
			}
			
			# command_config type
			elseif ($cmd_config_count == 1) { 
				$instances[$type] = $all_types[$type_name][2] . '&nbsp&nbsp"' . $value . '"=>{<br />';
			}
			else {
				$instances[$type] .= '&nbsp&nbsp"' . $value . '"=>{<br />';
			}
			
			if ($type == 'cisco_command_config') { $instances[$type] .= $line . '<br />'; }
			
			# call parse_type() function to parse indented lines
			$type_commands = $type . "_commands";
			parse_type($lines, $line_num, $type, $$type_commands);
			$instances[$type] .= '},<br />';
		}
	}
}

# after a Type is found, parse_type parses all the sub-commands inside the Type (ie. indented lines)
function parse_type($lines, $line_num, $type, $type_commands) {
	global $manifests;
	global $instances;
	global $unrecognized;
	global $cmd_config_count;
	$line_number = $line_num;
	$cmd_config_mode = false;

	if ($line_number < count($lines)) {
		$line = $lines[$line_number];
		$indents = get_indents($line);
		while (! is_type($line) && $line_number < count($lines)) {
			$line = trim($line);
			$tokenized_line = array_map('trim', explode(" ", $line));
				
			# find cmd_name
			$cmd = "";
			$cmd_name = "";
			$index = 0;
			if ($tokenized_line[0] != "no") {
				for ($j = count($tokenized_line); $j > 0; $j--) {
					$command = join(" ", array_slice($tokenized_line, 0, $j));
					if (array_key_exists($command, $type_commands)) {
						$cmd_name = $command;
						$cmd = $type_commands[$cmd_name][0];
						$index = $j;
						break;
					}
				} 
				
				if ($cmd_name != "") {
					if ($index < count($tokenized_line)) {
						# join remaining tokens in tokenized_line into value
						$value = join(" ", array_slice($tokenized_line, $index, count($tokenized_line)));
						$values = $type_commands[$cmd_name][1];
						
						# check if value is a valid option
						if (is_numeric($value) && strpos($value, '.') === false) { 
							if (in_array('<integer>', $values)) {
								$value2 = '{$value[' . $cmd . ']}';
								if (strpos($manifests[$type], $cmd) == false)
									$manifests[$type] .= INDENT . INDENT . $cmd . " => " . $value2 . ",<br />";
								$instances[$type] .= $cmd . '=>"' . $value . '",';
							}
						}
						
						elseif (in_array($value, $values)) {
							$value2 = '{$value[' . $cmd . ']}';
							if (strpos($manifests[$type], $cmd) == false)
								$manifests[$type] .= INDENT . INDENT . $cmd . " => " . $value2 . ",<br />";
							$instances[$type] .= $cmd . '=>"' . $value . '",';
						}
						
						elseif (in_array('<string>', $values)) {
							$slash_index = strpos($value, '/');
							if ($cmd != "ipv4_address" or $slash_index === false) {
								$value2 = '{$value[' . $cmd . ']}';
								if (strpos($manifests[$type], $cmd) == false)
									$manifests[$type] .= INDENT . INDENT . $cmd . " => " . $value2 . ",<br />";
								$instances[$type] .= $cmd . '=>"' . $value . '",';
							}
							
							# split ipv4 address and ipv4 mask into 2 commands
							else {
								$ipv4_addr = substr($value, 0, $slash_index);
								$ipv4_mask = substr($value, $slash_index+1);
								$value2 = '{$value[' . $cmd . ']}';
								if (strpos($manifests[$type], $cmd) == false)
									$manifests[$type] .= INDENT . INDENT . $cmd . " => " . $value2 . ",<br />";
								$instances[$type] .= $cmd . '=>"' . $ipv4_addr . '",';
								
								$value2 = '{$value[ipv4_netmask_length]}';
								if (strpos($manifests[$type], $cmd) == false)
									$manifests[$type] .= INDENT . INDENT . "ipv4_netmask_length" . " => " . $value2 . ",<br />";
								$instances[$type] .= 'ipv4_netmask_length' . '=>"' . $ipv4_mask . '",';
							}
						}
						
						# value not found - invalid line
						else {
							$unrecognized .= '<span class="line_num">' . $line_number . '</span>&nbsp&nbsp&nbsp' . trim($line) . '<br />';
							append_cmd_config_instance($type, $lines, $line_number, $cmd_config_mode, $indents);
							$cmd_config_mode = true;
						}
					}
					# error - no parameters given
					else {
						$unrecognized .= '<span class="line_num">' . $line_number . '</span>&nbsp&nbsp&nbsp' . trim($line) . '<br />';
						append_cmd_config_instance($type, $lines, $line_number, $cmd_config_mode, $indents);
						$cmd_config_mode = true;
					}
				}
				else {
					if (trim($line) != "") {
						$unrecognized .= '<span class="line_num">' . $line_number . '</span>&nbsp&nbsp&nbsp' . trim($line) . '<br />';
						append_cmd_config_instance($type, $lines, $line_number, $cmd_config_mode, $indents);
						$cmd_config_mode = true;
					}
				}
			}
			# handle 'no' keyword
			else {
				for ($j = count($tokenized_line); $j > 1; $j--) {
					$command = join(" ", array_slice($tokenized_line, 1, $j));
					if (array_key_exists($command, $type_commands)) {
						$cmd_name = $command;
						break;
					}
				} 
				
				if ($cmd_name != "") {
					$value2 = '{$value[' . $cmd_name . ']}';
					$value = '"false"';
					if (strpos($manifests[$type], $type_commands[$cmd_name][0]) == false)
						$manifests[$type] .= "&nbsp&nbsp&nbsp&nbsp" . $type_commands[$cmd_name][0] . " => " . $value2 . ",<br />";
					$instances[$type] .= $type_commands[$cmd_name][0] . '=>' . $value . ',';
				}
				else {
					$unrecognized .= '<span class="line_num">' . $line_number . '</span>&nbsp&nbsp&nbsp' . trim($line) . '<br />';
					append_cmd_config_instance($type, $lines, $line_number, $cmd_config_mode, $indents);
					$cmd_config_mode = true;
				}
			}
			$line_number += 1;
			if ($line_number < count($lines)) {
				$line = $lines[$line_number];
				$indents = get_indents($line);
			}
		}
		# close current command_config command
		if ($cmd_config_mode && trim($line) != '') { 
			$instances['cisco_command_config'] .= '},<br />';
			$cmd_config_count += 1;
		}
	}
}

$in_string = "";
if(isset($_POST['submit'])) {
	define('INDENT', '&nbsp&nbsp');
	$in_string = $_POST['in_string'];
	show_form($in_string);
	$manifests = array();
	$instances = array();
	$unrecognized = "";
	$line_num = 0;
	$cmd_config_count = 0;
	convert($in_string);
	// echo (count($manifests));
	print_results();
}
else {
	show_form($in_string);
}
?>
</body></html>