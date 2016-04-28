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
# cType ='interface'; pType='cisco_interface'; value='Ethernet3/12'; 
#	cAttribute='access vlan'; pAttribute='access_vlan'; value=24;
#	cmd_name='ip redirects'; pAttribute='ipv4_redirects'; value=false;
#
# note that type lines must not be indented, and non-type lines must be indented with at least one space or tab
*********************************************************************************************/

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
		echo $value . '}<br />}<br />';
	}
	// echo '}</p>';
	
	if ($unrecognized != "") {
		echo ('<h4>Unrecognized Lines:</h4><p>' . $unrecognized);
	}
}

function is_empty($line) {
	return strlen(trim($line)) == 0;
}

# test increasingly shorter command strings to find type
# returns longest match of the tokenized_line in all_types
# returns empty string if no match found
function get_cType($tokenized_line, $all_types, $indents) {
	$cType  = "";
	
	for ($i = count($tokenized_line); $i >= 0; $i--) {
		$cType  = join(" ", array_slice($tokenized_line, 0, $i));
		if (array_key_exists($cType , $all_types)) {
			return $cType ;
		}
	}
	if (strlen(join(" ", $tokenized_line)) > 2 && $indents == 0)
		return "command config";
	return "";
}

# read in Types list from Types text file
function get_all_types() {
	$all_types = array();
	$file = fopen("types2.txt","r");
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

# get number of INDENTs for a given line
# assumes INDENTs are composed of &nbsp characters
function get_indents($line) {
	$num_spaces = strlen($line) - strlen(ltrim($line, ' '));
	$num_indents = $num_spaces / (strlen(INDENT) / 5);
	// echo ($line . '(' . $num_indents . ')');
	return $num_indents;
}

function append_cmd_config_instance($pType, $lines, $cmd_config_mode, $indents) {
	global $instances;
	global $cmd_config_count;
	global $line_num;
	global $cTypes_stack;
	global $cTypes_lines_stack;
	
	# a line inside an unrecognized type
	if ($pType == 'cisco_command_config') {
		echo ' ' . $lines[$line_num-1] . ' ' . $indents;
		if (array_key_exists('cisco_command_config', $instances)) {
			for ($i = 0; $i < $indents; $i++) {
				$instances['cisco_command_config'] .= INDENT;
			}
			$instances['cisco_command_config'] .= $lines[$line_num-1] . '<br />';
		}
		else {
			$instances['cisco_command_config'] = INDENT . ltrim($lines[$line_num-1]) . '<br />';
		}
		//$cmd_config_count += 1;
	}
	
	# unrecognized parameter inside a recognized type => add to cmd_config
	else {
		if ($cmd_config_count == 0 && ! $cmd_config_mode) {
			$instances['cisco_command_config'] = '$cisco_command_config_instances = {<br />';
		}
		if (! $cmd_config_mode) {
			$instances['cisco_command_config'] .= INDENT . '"command' . $cmd_config_count . '"=>{<br>' .  $cTypes_lines_stack[0] . '<br />'; // $lines[$line_num-1] . '<br />';
		}
		for ($i = 0; $i < $indents; $i++) {
			$instances['cisco_command_config'] .= INDENT;
		}
		$instances['cisco_command_config'] .= ltrim($lines[$line_num-1]) . '<br />';
		//$cmd_config_count += 1;
	}
}

function convert($in_string) {
	global $manifests;
	global $instances;
	global $unrecognized;
	global $line_num;
	global $cmd_config_count;
	global $cTypes_stack;
	global $cTypes_lines_stack;
	global $cmd_config_mode;

	# read in all Types, get input query and convert to list of lines, get list of types in the query
	$all_types = get_all_types();
	$lines = explode("\n", $in_string);
	
	// import every file in includes folder 
	foreach (glob("includes/*.php") as $filename)
		include $filename;
		
	echo "Types: ";
	
	foreach ($lines as $line) {
		$pType = "";
		$cType = "";
		$value = "";
		$line_num += 1;
		$indents = get_indents($line);
			
		if (is_empty($line)) { continue; }
		
		$tokenized_line = array_map('trim', explode(" ", trim($line)));
		
		$cType = get_cType($tokenized_line, $all_types, $indents);
		
		// line is Type
		if ($cType != "") {
			// push to Types stack, get puppet type, get value
			$pType = trim($all_types[$cType][0]);
			// echo 'cType=' . $cType . ' pType=' . $pType . ' <br />';
			if (count($tokenized_line) > 1) { $value = trim(ltrim($line, $cType)); }
			if ($indents == 0) {
				array_pop($cTypes_stack);
				array_pop($cTypes_lines_stack);
				array_push($cTypes_stack, $cType);
				array_push($cTypes_lines_stack, $line);
				if ($cmd_config_mode) {
					$instances['cisco_command_config'] .= INDENT . '}<br />';
					$cmd_config_mode = false;
					$cmd_config_count += 1;
				}
			}
			
			// command_config type
			if ($cType == 'command config'){ 
				// echo $cType . ' ' . $pType . ' ' . $line;
				$value = 'command' . $cmd_config_count;
				$cmd_config_count += 1;

				// append_cmd_config_instance($pType, $lines, False, $indents);
				if ($cmd_config_count == 1) { 
					$instances[$pType] = $all_types[$cType][2];
				}
				if ($cmd_config_mode) {
				$instances[$pType] .= '&nbsp&nbsp}<br />';
				}
				$instances[$pType] .= '&nbsp&nbsp"' . $value . '"=>{<br />';
				$instances[$pType] .= $line . '<br />';
				continue;
			}
			
			if (array_key_exists($pType, $manifests)) {
				$instances[$pType] .= '}<br />&nbsp&nbsp"' . $value . '"=>{';
			}
			
			else {
				echo $pType."; ";
				$manifests[$pType] = $all_types[$cType][1] . '<br />&nbsp&nbsp' . $pType . ' { $' . ltrim(ltrim($pType, 'cisco'), '_') . ':<br />';
				$instances[$pType] = $all_types[$cType][2] . '<br />&nbsp&nbsp"' . $value . '"=>{';
			}
		}
		
		// line is not a Type; get type off top of type stack and call function
		elseif (! empty($cTypes_stack)) {
			
			$pType = $all_types[array_pop((array_slice($cTypes_stack, -1)))][0]; # php should have a peek() function
			$attributes = $pType . "_commands";
			// echo '|' . $line . '|Line#'.$line_num." ";
			parse_attribute($line, $lines, $line_num, $pType, $$attributes);
			
		}
	}
}

# parse_attribute parses a single line that is not a Type
function parse_attribute($line, $lines, $line_num, $pType, $attributes) {
	global $manifests;
	global $instances;
	global $unrecognized;
	global $cmd_config_count;
	global $cTypes_stack;
	global $cTypes_lines_stack;
	global $cmd_config_mode;
	// echo "<br />Line#".$line_num . " " . $lines[$line_num] . " pType=".$pType;

	$indents = get_indents($line);
	$line = trim($line);
	$tokenized_line = array_map('trim', explode(" ", trim($line)));
		
	# find attr_name
	$pAttribute = "";
	$cAttribute = "";
	$index = 0;
	if ($tokenized_line[0] != "no") {
		for ($j = count($tokenized_line); $j > 0; $j--) {
			$attribute = join(" ", array_slice($tokenized_line, 0, $j));
			if (array_key_exists($attribute, $attributes)) {
				$cAttribute = $attribute;
				$pAttribute = $attributes[$cAttribute][0];
				$index = $j;
				break;
			}
		} 
		
		if ($cAttribute != "") {
			if ($index < count($tokenized_line)) {
				# join remaining tokens in tokenized_line into value
				$value = join(" ", array_slice($tokenized_line, $index, count($tokenized_line)));
				$values = $attributes[$cAttribute][1];
				
				# check if value is a valid option
				if (is_numeric($value) && strpos($value, '.') === false) { 
					if (in_array('<integer>', $values)) {
						$value2 = '{$value[' . $pAttribute . ']}';
						if (strpos($manifests[$pType], $pAttribute) == false)
							$manifests[$pType] .= INDENT . INDENT . $pAttribute . " => " . $value2 . ",<br />";
						$instances[$pType] .= $pAttribute . '=>"' . $value . '",';
					}
				}
				
				elseif (in_array($value, $values)) {
					$value2 = '{$value[' . $pAttribute . ']}';
					if (strpos($manifests[$pType], $pAttribute) == false)
						$manifests[$pType] .= INDENT . INDENT . $pAttribute . " => " . $value2 . ",<br />";
					$instances[$pType] .= $pAttribute . '=>"' . $value . '",';
				}
				
				elseif (in_array('<string>', $values)) {
					$slash_index = strpos($value, '/');
					if ($pAttribute != "ipv4_address" or $slash_index === false) {
						$value2 = '{$value[' . $pAttribute . ']}';
						if (strpos($manifests[$pType], $pAttribute) == false)
							$manifests[$pType] .= INDENT . INDENT . $pAttribute . " => " . $value2 . ",<br />";
						$instances[$pType] .= $pAttribute . '=>"' . $value . '",';
					}
					
					# split ipv4 address and ipv4 mask into 2 commands
					else {
						$ipv4_addr = substr($value, 0, $slash_index);
						$ipv4_mask = substr($value, $slash_index+1);
						$value2 = '{$value[' . $pAttribute . ']}';
						if (strpos($manifests[$pType], $pAttribute) == false)
							$manifests[$pType] .= INDENT . INDENT . $pAttribute . " => " . $value2 . ",<br />";
						$instances[$pType] .= $pAttribute . '=>"' . $ipv4_addr . '",';
						
						$value2 = '{$value[ipv4_netmask_length]}';
						if (strpos($manifests[$pType], "ipv4_netmask_length") == false)
							$manifests[$pType] .= INDENT . INDENT . "ipv4_netmask_length" . " => " . $value2 . ",<br />";
						$instances[$pType] .= 'ipv4_netmask_length' . '=>"' . $ipv4_mask . '",';
					}
				}
				
				# value not found - invalid line
				else {
					$unrecognized .= '<span class="line_num">' . $line_num . '</span>&nbsp&nbsp&nbsp' . trim($line) . '<br />';
					append_cmd_config_instance($pType, $lines, $cmd_config_mode, $indents);
					$cmd_config_mode = true;
				}
			}
			# error - no parameters given
			else {
				$unrecognized .= '<span class="line_num">' . $line_num . '</span>&nbsp&nbsp&nbsp' . trim($line) . '<br />';
				append_cmd_config_instance($pType, $lines, $cmd_config_mode, $indents);
				$cmd_config_mode = true;
			}
		}
		elseif (trim($line) != "") {
			$unrecognized .= '<span class="line_num">' . $line_num . '</span>&nbsp&nbsp&nbsp' . trim($line) . '<br />';
			append_cmd_config_instance($pType, $lines, $cmd_config_mode, $indents);
			$cmd_config_mode = true;
		}
	}
	# handle 'no' keyword
	else {
		for ($j = count($tokenized_line); $j > 1; $j--) {
			$attribute = join(" ", array_slice($tokenized_line, 1, $j));
			if (array_key_exists($attribute, $attributes)) {
				$cAttribute = $attribute;
				break;
			}
		} 
		
		if ($cAttribute != "") {
			$value2 = '{$value[' . $cAttribute . ']}';
			$value = '"false"';
			if (strpos($manifests[$pType], $attributes[$cAttribute][0]) == false)
				$manifests[$pType] .= "&nbsp&nbsp&nbsp&nbsp" . $attributes[$cAttribute][0] . " => " . $value2 . ",<br />";
			$instances[$pType] .= $attributes[$cAttribute][0] . '=>' . $value . ',';
		}
		else {
			$unrecognized .= '<span class="line_num">' . $line_num . '</span>&nbsp&nbsp&nbsp' . trim($line) . '<br />';
			append_cmd_config_instance($pType, $lines, $cmd_config_mode, $indents);
			$cmd_config_mode = true;
		}
	}
	return $cmd_config_mode;
}

$in_string = "";
if(isset($_POST['submit'])) {
	define('INDENT', '&nbsp&nbsp');
	$in_string = $_POST['in_string'];
	show_form($in_string);
	$manifests = array();
	$instances = array();
	$cTypes_stack = array();
	$cTypes_lines_stack = array();
	$unrecognized = "";
	$line_num = 0;
	$cmd_config_count = 0;
	$cmd_config_mode = false;
	convert($in_string);
	// echo (count($manifests));
	print_results();
}
else {
	show_form($in_string);
}
?>
</body></html>