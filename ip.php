<?php

require_once 'include/init.php';
$output = Output::start();

if (IpV4::isValid($_SERVER['REMOTE_ADDR'])) {
	$ip = new IpV4($_SERVER['REMOTE_ADDR']);
}
else if (IpV6::isValid($_SERVER['REMOTE_ADDR'])) {
	$ip = new IpV6($_SERVER['REMOTE_ADDR']);
}
else {
	$output->add('can\'t determine remote addr', 'error', $_SERVER['REMOTE_ADDR']);
}

$output->add('your current internet ip address', 'notice', $ip);

$output->send();

?>
