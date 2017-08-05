<?php
/**
 * Add action
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

// zone
if (!empty($_REQUEST['zone'])) {
	if (array_key_exists($_REQUEST['zone'], $config['sddns']['zones'])) {
		$zone = $config['sddns']['zones'][$_REQUEST['zone']];
	}
	else {
			throw new UserException('invalid zone');
	}
}
else {
	throw new UserException('missing zone');
}

$host = (!empty($_REQUEST['host'])) ? new Host($_REQUEST['host'], $zone) : Host::unique($zone, $db);
$pw = (!empty($_REQUEST['pw'])) ? $_REQUEST['pw'] : randomString(8);
$ttl = (!empty($_REQUEST['ttl'])) ? (int) $_REQUEST['ttl'] : $config['sddns']['std']['ttl'];
$class = (!empty($_REQUEST['class'])) ? $_REQUEST['class'] : $config['sddns']['std']['class'];
$rdata = (!empty($_REQUEST['rdata'])) ? $_REQUEST['rdata'] : $_SERVER['REMOTE_ADDR'];

// type
if (isset($_REQUEST['type'])) {
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

// lifetime
$lifetime = (isset($_REQUEST['lifetime']) && is_numeric($_REQUEST['lifetime'])) ? (int) $_REQUEST['lifetime'] : $config['sddns']['std']['lifetime'];
if ($lifetime < 0) {
	throw new UserException('invalid lifetime', $lifetime);
}
else if (($lifetime > $config['sddns']['max_lifetime'] || $lifetime == 0) && !isAuthentificated()) {
	throw new UserException('lifetime exceeds limit');
}

// host
if ($host->isRegistred($db)) {
	if ($type == 'URL') {
		throw new UserException('hosts is already registred', $host);
	}

	$host = new DBHost($host->isRegistred($db), $db);
	$output->add('found existing host' ,'notice', $host);

	if (!$host->checkPassword($pw) && !isAuthentificated()) {
		throw new AuthentificationException('not authentificated for host', $host);
	}
}
else {
	$host = $host->add($pw, $db);	// returns new DBHost
	$output->add('host added to db' ,'notice', $host);

	if (empty($_REQUEST['pw'])) {
		$output->add('generated password' ,'notice', $pw);
	}
}

if ($type == 'URL') {	// pseudo type to create url redirection
	$uri = new Uri($rdata, $host);
	$uri->frame = (isset($_REQUEST['frame']) && $_REQUEST['frame']) ? 1 : 0;

	$uri = $uri->add($db, $lifetime);
	$output->add('uri redirection added to db', 'success', $uri);
}
else {
	$record = new Record($host, $ttl, $class, $type, $rdata);

	if ($record->isRegistred($db)) {
		throw new UserException('record already exists in db', $record);
	}

	$record = $record->add($db, $lifetime);
	$output->add('record added to db', 'success', $record);

	$zone->cleanup($db);
	$zone->sync($db);
}
