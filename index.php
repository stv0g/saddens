<?php

require_once 'include/init.php';

$filter = array('host' => $_REQUEST['host'], 'zone' => $_REQUEST['zone']);
$uris = DBUri::get($db, $filter);

if (count($uris) == 1) {
	$uri = $uris[0];

	$uri->accessed++;
	$uri->lastAccessed = time();
	$uri->update();

	if ($uri->frame) {
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head><title>/dev/nulll - Frame</title></head>
	<body style="margin: 0; padding: 0; overflow: hidden;">
		<iframe style="height: 100%; width: 100%; position: absolute; top: 0; left: 0;" height="100%" width="100%" frameborder="0" marginheight="0" marginwidth="0" src="' . $uri->uri . '"></iframe>
	</body>
</html>';

	}
	else {
		header('Location: ' . $uri->uri);
	}
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
