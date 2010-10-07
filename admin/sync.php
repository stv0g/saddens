<?php

require_once dirname(__FILE__) . '/../include/init.php';
$output = Output::start();

if (empty($_REQUEST['zone']) || $_REQUEST['zone'] == 'all') {
	$zones = $config['sddns']['zones'];
}
else {
	$zones = array($config['sddns']['zones'][$_REQUEST['zone']]);
}

foreach ($zones as $zone) {
	$output->add('syncing zone', 'notice', $zone);
	$zone->sync($db);
}

Output::send();

?>
