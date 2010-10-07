<?php

require_once 'include/init.php';
$output = Output::start()

$ip = new IpV4($_SERVER['REMOTE_ADDR']);

$output->add('your current internet ip address', 'notice', $ip);

$output->send();

?>
