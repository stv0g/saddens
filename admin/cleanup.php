<?php

$output = Output::start();

require_once dirname(__FILE__) . '/../include/init.php';

if (empty($_REQUEST['zone']) || $_REQUEST['zone'] == 'all') {
	$zones = $config['sddns']['zones'];
}
elseif (isset($config['sddns']['zones'][$_REQUEST['zone']])) {
	$zones = array($config['sddns']['zones'][$_REQUEST['zone']]);
}
else {
	$output->add('no such zone', 'error', $_REQUEST['zone']);
}

foreach ($zones as $zone) {
	$output->add('cleaning zone', 'notice', $zone);
	$zone->cleanup($db);
}

Output::send();

?>
