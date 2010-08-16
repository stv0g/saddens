<?php
require_once 'include/init.php';

if (array_key_exists($_REQUEST['zone'], $config['sddns']['zones'])) {
	$zone = $config['sddns']['zones'][$_REQUEST['zone']];

	$host = (empty($_REQUEST['host'])) ? Host::unique($zone, $db) : new Host($_REQUEST['host'], $zone);
	$pw = (empty($_REQUEST['pw'])) ? randomString(8) : $_REQUEST['pw'];
	
	if (empty($_REQUEST['lifetime']) || !is_int($_REQUEST['lifetime'])) {
		$lifetime = $config['sddns']['std']['lifetime'];
	}
	else {
		$lifetime = (int) $_REQUEST['lifetime'];
	}
	
	if (($lifetime > $config['sddns']['max_lifetime'] && !isAuthentificated()) || $lifetime < 0) {
		$output->add('invalid lifetime', 'error', $lifetime);
	}
	
	if ($host->isRegistred($db)) {
		$host = new DBHost($host->isRegistred($db), $db);
		$output->add('found existing host' ,'notice', $host);
		
		if (!$host->checkPassword($pw)) {
			$output->add('not authentificated for host', 'error', $host);
			die();
		}
	}
	else {
		$host = $host->add($pw, $db);	// returns new DBHost
		$output->add('host added to db' ,'notice', $host);
				
		if (empty($_REQUEST['pw']))
			$output->add('generated password' ,'notice', $pw);
	}
	
	$type = (empty($_REQUEST['type'])) ? $config['sddns']['std']['type'] : $_REQUEST['type'];
	$rdata = (empty($_REQUEST['rdata']) && $type = 'A') ? $_SERVER['REMOTE_ADDR'] : $_REQUEST['rdata'];
	
	if ($type != 'URL') {	// pseudo type to create url redirection
		$ttl = (empty($_REQUEST['ttl'])) ? $config['sddns']['std']['ttl'] : (int) $_REQUEST['ttl'];
		$class = (empty($_REQUEST['class'])) ? $config['sddns']['std']['class'] : $_REQUEST['class'];
		
		$record = new Record($host, $ttl, $class, $type, $rdata);
		
		if (!$record->isRegistred($db)) {
			$record = $record->add($db, $lifetime);
			$output->add('record added to db', 'success', $record);
			
			$zone->cleanup($db);
			$zone->sync($db);
		}
		else {
			$output->add('record already exists in db', 'error', $record);
			die();
		}
	}
	else {
		$uri = new Uri($rdata, $host);
		$uri = $uri->add($db, $lifetime);
		$output->add('uri redirection added to db', 'success', $uri);
	}
}
else {
	$output->add('zone not found', 'error', $_REQUEST['zone']);
}

?>
