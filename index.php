<?php

require_once 'include/init.php';

$filter = array('host' => $_REQUEST['host'], 'zone' => $_REQUEST['zone']);
$uris = DBUri::get($db, $filter);

if (count($uris) == 1) {
	$uri = $uris[0];

	$uri->accessed++;
	$uri->lastAccessed = time();
	$uri->update();

	header('Location: ' . $uri->uri);
}
else {
	if (!empty($_SERVER['QUERY_STRING'])) {
		$qs = '?' . $_SERVER['QUERY_STRING'];
	}

	if (!isAuthentificated()) {
		header('Location: simple' . $qs);
	}
	else {
		header('Location: expert' . $qs);
	}
}

?>
