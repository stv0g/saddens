<?php
require_once '../include/init.php';

$ns = new NameServer($config['sddns']['ns']['hostname'], $config['sddns']['ns']['port']);

if (empty($_REQUEST['query'])) {
	//throw new UserException('missing query hostname!');
}

$type = (empty($_REQUEST['type'])) ? 'A' : $_REQUEST['type'];
$class = (empty($_REQUEST['class'])) ? 'IN' : $_REQUEST['class'];

//$results = $ns->query($_REQUEST['query'], $type, $class);
$zone = $config['sddns']['zones']['0l.de'];
$results = $zone->getRecordsFromNS();

foreach ($results as $result) {
	$output->add('', 'data', $result);
}

?>
