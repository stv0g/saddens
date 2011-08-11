<?php

$config['path']['web'] = '';

$config['db']['host'] = 'localhost';
$config['db']['user'] = 'sddns';
$config['db']['pw'] = 'RjRXDa68hnS5A8mX';
$config['db']['db'] = 'st_sddns';
$config['db']['tbl']['prefix'] = '';
$config['db']['tbl']['records'] = $config['db']['tbl']['prefix'] . 'records';
$config['db']['tbl']['hosts'] = $config['db']['tbl']['prefix'] . 'hosts';
$config['db']['tbl']['uris'] = $config['db']['tbl']['prefix'] . 'uris';

$config['sddns']['htpasswd'] = $site['path']['server'] . '/../.htpasswd';

$key = array('hmac' => 'gDlXSZtESw78I47O68UEigpPofn0XbpSpo5Vba+9IY38EYagPO/2C2Ch lZL+AvtN/ozRdra+p3+wLOKvVvqdrA==', 'name' => 'info.steffenvogel.de.');
$zones = array('0l.de', 'd.eta.li');	// , 'griesm.de', 'dynamic.steffenvogel.de');
$config['sddns']['ns']['hostname'] = 'ns0.0l.de';
$config['sddns']['ns']['port'] = 53;

foreach ($zones as $zone) {
	$config['sddns']['zones'][$zone] = new Zone($config['sddns']['ns']['hostname'], $zone, $key, $config['sddns']['ns']['port']);
}

$config['sddns']['max_lifetime'] = 6 * 30 * 24 * 60 * 60;	// in seconds; 6 months
$config['sddns']['max_ttl'] = 60 * 60;	// in seconds; 1 hour
$config['sddns']['classes'] = array('IN', 'CH', 'HS');
$config['sddns']['types'] =  array('A', 'AAAA', 'NS', 'TXT', 'MX', 'SRV', 'CNAME', 'LOC', 'HINFO', 'URL' /* pseudo type for url redirection */);

$config['sddns']['std']['class'] = 'IN';
$config['sddns']['std']['type'] = 'A';
$config['sddns']['std']['ttl'] = 2 * 60;	// in seconds; 2 minutes; < max_ttl!
$config['sddns']['std']['lifetime'] = 1 * 30 * 24 * 60 * 60;	// in seconds; 1 month; < max_lifetime!

$config['sddns']['cmds'] = array('add', 'delete', 'update');	// available cmds
$config['sddns']['formats'] = array('html', 'xml', 'gif', 'txt', 'csv');	// available formats (keep in sync with .htaccess!)

?>
