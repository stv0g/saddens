<?php
/**
 * Delete action
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

$output = Output::start();

// default arguments
$rdata = @$_REQUEST['rdata'];
$class = @$_REQUEST['class'];
$type = @$_REQUEST['type'];
$ttl = @$_REQUEST['ttl'];

// zone
if (!empty($_REQUEST['zone'])) {
	if (array_key_exists($_REQUEST['zone'], $config['sddns']['zones'])) {
		$zone = $config['sddns']['zones'][$_REQUEST['zone']];
	}
	else {
		throw new UserException('invalid zone', $_REQUEST['zone']);
	}
}
else {
	throw new UserException('missing zone');
}

// password
if (!empty($_REQUEST['pw'])) {
	$pw = $_REQUEST['pw'];
}
else if (!empty($_SERVER['PHP_AUTH_PW'])) {
	$pw = $_SERVER['PHP_AUTH_PW'];
}
else {
	throw new AuthentificationException('missing password');
}

// type
if (!empty($type) && !in_array($type, $config['sddns']['types'])) {
	throw new UserException('invalid type');
}
else if (IpV4::isValid($rdata)) {
	$type = 'A';
}
else if (IpV6::isValid($rdata)) {
	$type = 'AAAA';
}

if (!empty($rdata) && !Record::isRdata($rdata, $type)) {
	throw new UserException('invalid rdata', $rdata);
}

// search host
if (!empty($_REQUEST['host'])) {
	$host = new Host($_REQUEST['host'], $zone);

	if ($host->isRegistred($db)) {
	        $host = new DBHost($host->isRegistred($db), $db);
		$output->add('found existing host', 'success', $host);
	}
	else {
		throw new UserException('host not found', $_REQUEST['host']);
	}
}
else {
	throw new UserException('missing host');
}

if ($host->checkPassword($pw) || isAuthentificated()) {
	// search
	$uris = DBUri::get($db, array('zone' => $zone, 'host' => $host));
	$records = DBRecord::get($db, array('zone' => $zone, 'host' => $host, 'type' => $type, 'class' => $class, 'rdata' => $rdata, 'ttl' => $ttl));

	if (empty($type)) {
		$entries = array_merge($uris, $records);
	}
	else if ($type == 'URL') {
		$entries = $uris;
	}
	else {
		$entries = $records;
	}

	if (empty($entries)) {
		$output->add('no records found to delete', 'warning');
	}
	else {
		foreach ($entries as $entry) {
			$entry->delete();
			$output->add('entry deleted from db', 'success', $entry);
		}

		$zone->cleanup($db);
		$zone->sync($db);
	}
}
else {
	throw new AuthentificationException('not authentificated for host', $host);
}
