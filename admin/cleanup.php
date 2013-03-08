<?php

require_once dirname(__FILE__) . '/../include/init.php';
$output = Output::start();

if (empty($_REQUEST['zone']) || $_REQUEST['zone'] == 'all') {
	$zones = $config['sddns']['zones'];
}
elseif (isset($config['sddns']['zones'][$_REQUEST['zone']])) {
	$zones = array($_REQUEST['zone'] => $config['sddns']['zones'][$_REQUEST['zone']]);
}
else {
	$output->add('no such zone', 'error', $_REQUEST['zone']);
	$output->send();
}

foreach ($zones as $name => $zone) {
	$output->add('cleaning zone', 'notice', $zone);
	$zone->cleanup($db);
}

$output->send();

?>
