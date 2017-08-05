<?php
/**
 * Update action
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
$ttl = (!empty($_REQUEST['ttl'])) ? $_REQUEST['ttl'] : $config['sddns']['std']['ttl'];
$class = (!empty($_REQUEST['class'])) ? $_REQUEST['class'] : $config['sddns']['std']['class'];
$rdata = (!empty($_REQUEST['rdata'])) ? $_REQUEST['rdata'] : $_SERVER['REMOTE_ADDR'];

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
if (!empty($_REQUEST['type'])) {
	if (in_array($_REQUEST['type'], $config['sddns']['types'])) {
		$type = $_REQUEST['type'];
        }
	else {
		throw new UserException('invalid type');
	}
}
else if (IpV4::isValid($rdata)) {
	$type = 'A';
}
else if (IpV6::isValid($rdata)) {
	$type = 'AAAA';
}
else {
	throw new UserException('missing type');
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
	// search entries
	if ($type == 'URL') {
		$entries = DBUri::get($db, array('host' => $host, 'zone' => $zone));
	}
	else {
		$entries = DBRecord::get($db, array('host' => $host, 'zone' => $zone, 'class' => $class, 'type' => $type));
	}

	if (empty($entries)) {
		throw new UserException('no records found to update');
	}

	$entry = array_shift($entries);

	if ($type == 'URL') {
		$entry->frame = (isset($_REQUEST['frame']) && $_REQUEST['frame']) ? 1 : 0;
		$entry->setUri($rdata);
	}
	else {
		$entry->setTtl($ttl);
		$entry->setRData($rdata);
	}

	$entry->lastAccessed = time();
	$entry->update();

	$output->add('entry updated in db', 'success', $entry);

	// delete other entries
	foreach ($entries as $entry) {
		$entry->delete();
		$output->add('record deleted from db', 'warning', $entry);
	}

	$zone->cleanup($db);
	$zone->sync($db);
}
else {
	throw new AuthentificationException('not authentificated for host', $host);
}
