<?php

function isAuthentificated() {
	$config = Registry::get('config');
	
	$combi = @$_SERVER['PHP_AUTH_USER'] . ':{SHA}' . base64_encode(sha1(@$_SERVER['PHP_AUTH_PW'], TRUE));
	$htpasswd = file('/var/www/nulll/sddns/../.htpasswd');
	
	foreach ($htpasswd as $user) {
		if ($combi == trim($user)) {
			return true;
		}
	}
	
	return false;
}

function randomString($length, $characters='abcdefghijklmnopqrstuvwxyz0123456789') {
	$random_string = '';
	$characters_length = strlen($characters);
	for($i = 0; $i<$length; $i++) {
		$random_string .= $characters[mt_rand(0, $characters_length - 1)];
	}
	return $random_string;
}


function backtrace2xml($traces, DomDocument $doc) {
	$xmlTraces = $doc->createElement('backtrace');
	
	foreach ($traces as $step => $trace) {
		$xmlTrace = $doc->createElement('trace');
		$xmlTraces->appendChild($xmlTrace);
		$xmlTrace->setAttribute('step', $step);
		
		foreach ($trace as $key => $value) {
			switch ($key) {
				case 'function':
				case 'line':
				case 'file':
				case 'class':
				case 'type':
					$xmlTrace->appendChild($doc->createElement($key, $value));
					break;
				case 'args':
					$xmlArgs = $doc->createElement($key);
					$xmlTrace->appendChild($xmlArgs);
					foreach ($value as $arg) {
						$xmlArgs->appendChild($doc->createElement('arg', $value));
					}
					break;
			}
		}
	}
	
	return $xmlTraces;
}

function backtrace2html($traces) {
	$trace = '';

	foreach(debug_backtrace() as $i=>$l){
		$trace .= '[' . $i . '] in function <b>' . $l['class'] . $l['type'] . $l['function'] . '</b>';
		if($l['file'])
			$trace .= ' in <b>' . $l['file'] . '</b>';
		if($l['line'])
			$trace .= ' on line <b>' . $l['line'] . '</b>';
	}
	
	return $trace;
}

?>