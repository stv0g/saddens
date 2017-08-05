<?php
/**
 * Dispatcher
 *
 * @copyright	2013 Steffen Vogel
 * @license	http://www.gnu.org/licenses/gpl.txt GNU Public License
 * @author	Steffen Vogel <post@steffenvogel.de>
 * @link	http://www.steffenvogel.de
 */
/*
 * This file is part of sddns
 *
 * sddns is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * sddns is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with sddns. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'include/init.php';

$host = $_REQUEST['host'];
$zone = $_REQUEST['zone'];

$uris = DBUri::get($db, array('host' => $host, 'zone' => $zone));

if (count($uris) == 1) {
	$uri = array_pop($uris);

	$uri->accessed++;
	$uri->lastAccessed = time();
	$uri->update();

	$fullUri = $uri->uri;

	$realHost = substr($_SERVER['HTTP_HOST'], 0 , -(strlen($zone)+1));
	if (!in_array($realHost, array('s', 't')) && $_SERVER['REQUEST_URI'] != '/') {
		$fullUri .= $_SERVER['REQUEST_URI'];
	}

	if ($uri->frame) {
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head><title>/dev/nulll - Frame</title></head>
	<body style="margin: 0; padding: 0; overflow: hidden;">
		<iframe style="height: 100%; width: 100%; position: absolute; top: 0; left: 0;" height="100%" width="100%" frameborder="0" marginheight="0" marginwidth="0" src="' . $fullUri . '"></iframe>
	</body>
</html>';

	}
	else {
		header('Location: ' . $fullUri);
	}
}
else {
	if (!empty($_SERVER['QUERY_STRING'])) {
		$qs = '?' . $_SERVER['QUERY_STRING'];
	}

	if (isAuthentificated()) {
		header('Location: /expert' . $qs);
	}
	else {
		header('Location: /simple' . $qs);
	}
}
