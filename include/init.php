<?php

error_reporting(E_ALL);

$site['path']['server'] = dirname(dirname(__FILE__));

require_once $site['path']['server'] . '/include/functions.php';
require_once $site['path']['server'] . '/include/exceptions.php';
require_once $site['path']['server'] . '/include/registry.php';
require_once $site['path']['server'] . '/include/mysql.php';
require_once $site['path']['server'] . '/include/output.php';

require_once $site['path']['server'] . '/include/object.php';
require_once $site['path']['server'] . '/include/dbobject.php';

require_once $site['path']['server'] . '/include/host.php';
require_once $site['path']['server'] . '/include/record.php';
require_once $site['path']['server'] . '/include/uri.php';
require_once $site['path']['server'] . '/include/ip.php';

require_once $site['path']['server'] . '/include/dbhost.php';
require_once $site['path']['server'] . '/include/dbrecord.php';
require_once $site['path']['server'] . '/include/dburi.php';

require_once $site['path']['server'] . '/include/nameserver.php';
require_once $site['path']['server'] . '/include/zone.php';

require_once $site['path']['server'] . '/include/config.php';
Registry::set('config', $config);

// get relevant runtime information
$site['hostname'] = @$_SERVER[ 'SERVER_NAME'];
$site['path']['web'] = $config['path']['web'];
$site['url'] = 'http://' . $site['hostname'] . $site['path']['web'];

// debug mode
if (@isset($_REQUEST['debug'])) {
	$site['debug'] = (int) $_REQUEST['debug'];
}
else {
	if (isAuthentificated()) {
		$site['debug'] = 3;
	}
	else {
		$site['debug'] = 0;
	}
}

// simple hit counting
$file = $site['path']['server'] . '/include/hits.txt';
$handle = fopen($file, 'r+') ;
$data = fread($handle, 512) ;
$site['hits'] = $data + 1;
fseek($handle, 0);
fwrite($handle, $site['hits']) ;
fclose($handle);

Registry::set('site', $site);

// set locale
setlocale(LC_TIME, 'de_DE.UTF8');

// set runtime configuration
ini_set('idn.default_charset', 'UTF-8');

// set timezone
date_default_timezone_set('Europe/Berlin');

// database
$db = new MySql($config['db']['host'], $config['db']['user'], $config['db']['pw'], $config['db']['db']);
Registry::set('db', $db);

?>
