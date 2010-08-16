<?php

require_once 'include/init.php';

// http://www.dyndns.com/developers/specs/syntax.html
/*$user = $_SERVER['PHP_AUTH_USER'];	// unused!
$mx = $_REQUEST['mx'];			// unused!
$backmx = $_REQUEST['backmx'];		// unused!
$wildcard = $_REQUEST['wildcard'];	// unused!
$ua = $_SERVER['HTTP_USER_AGENT'];	// unused!
$offline = $_REQUEST['offline'];	// unused!*/

// password
if (!empty($_REQUEST['pw'])) {
	$pw = $_REQUEST['pw'];
}
elseif (!empty($_SERVER['PHP_AUTH_PW'])) {
	$pw = $_SERVER['PHP_AUTH_PW'];
}

// host & zone
if (!empty($_REQUEST['hostname'])) {
	foreach ($config['sddns']['zones'] as $z) {
		if (substr($_REQUEST['hostname'], -strlen($z->name)) === $z->name) {
			$zone = $z;
			$host = reset(DBHost::get($db, array('host' => substr($_REQUEST['hostname'], 0, -(strlen($zone->name)+1)), 'zone' => $zone)));
		}
	}
}
elseif (!empty($_REQUEST['host'])) {
	if (array_key_exists($_REQUEST['zone'], $config['sddns']['zones'])) {
		$zone = $config['sddns']['zones'][$_REQUEST['zone']];
		$host = reset(DBHost::get($db, array('host' => $_REQUEST['host'], 'zone' => $zone)));
	}
}

// class
if (!empty($_REQUEST['class']) && in_array($_REQUEST['class'], $config['sddns']['classes']))
	$class = $_REQUEST['class'];

// type, rdata and ip
if (!empty($_REQUEST['type']) && in_array($_REQUEST['type'], $config['sddns']['types'])) {
	$type = $_REQUEST['type'];
}

// ip
if (!empty($_REQUEST['myip'])) {
	$rdata = $_REQUEST['myip'];
}
elseif (!empty($_REQUEST['ip'])) {
	$rdata = $_REQUEST['ip'];
}
elseif (!empty($_REQUEST['rdata'])) {
		$rdata = $_REQUEST['rdata'];
}
else {
	$rdata = $_SERVER['REMOTE_ADDR'];
}

if (!empty($zone)) {
	if (!empty($host)) {
		$records = DBRecord::get($db, array('host' => $host, 'zone' => $zone, 'class' => @$class, 'type' => @$type));
		if (count($records) > 0) {
			$output->add('found host', 'success', $host);
			if ($host->checkPassword($pw) || isAuthentificated()) {
				$records[0]->setRData($rdata);
				$records[0]->lastAccessed = time();
				$records[0]->update();
				$output->add('record updated in db', 'success', $records[0]);
				
				for ($i = 1; $i < count($records); $i++) {
					$records[$i]->delete();
					$output->add('record deleted from db', 'warning', $records[$i]);
				}
				
				$zone->cleanup($db);
				$zone->sync($db);
			}
			else {
				$output->add('not authentificated for host', 'error', $host);
			}
		}
		else {
			$output->add('no records found to update', 'warning');
		}
	}
	else {
		$output->add('host not found', 'error', @$_REQUEST['host'], @$_REQUEST['hostname']);
	}
}
else {
	$output->add('zone not found', 'error', $_REQUEST['host'], $_REQUEST['zone']);
}











?>
