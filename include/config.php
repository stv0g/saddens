<?php
/**
 * Configuration
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

$config['htpasswd'] = '/var/www/nulll/.htpasswd';
$config['path']['web'] = '';

$config['db']['host'] = 'localhost';
$config['db']['user'] = 'user';
$config['db']['pw'] = 'password';
$config['db']['db'] = 'sddns';
$config['db']['tbl']['prefix'] = '';
$config['db']['tbl']['records'] = $config['db']['tbl']['prefix'] . 'records';
$config['db']['tbl']['hosts'] = $config['db']['tbl']['prefix'] . 'hosts';
$config['db']['tbl']['uris'] = $config['db']['tbl']['prefix'] . 'uris';

$config['sddns']['htpasswd'] = $site['path']['server'] . '/../.htpasswd';

$key = array('hmac' => 'gDlXSZtESw78I47O68UEigpPofn0XbpSpo5Vba+9IY38EYagPO/2C2Ch lZL+AvtN/ozRdra+p3+wLOKvVvqdrA==', 'name' => 'info.steffenvogel.de.');
$zones = array('0l.de');

$config['sddns']['ns']['hostname'] = 'localhost';
$config['sddns']['ns']['port'] = 53;

foreach ($zones as $zone) {
	$config['sddns']['zones'][$zone] = new Zone($config['sddns']['ns']['hostname'], $zone, $key, $config['sddns']['ns']['port']);
}

$config['sddns']['max_lifetime'] = 6 * 30 * 24 * 60 * 60;	// in seconds; 6 months
$config['sddns']['max_ttl'] = 60 * 60;	// in seconds; 1 hour
$config['sddns']['classes'] = array('IN', 'CH', 'HS');
$config['sddns']['types'] = array('A', 'AAAA', 'NS', 'TXT', 'MX', 'SRV', 'CNAME', 'LOC', 'HINFO', 'URL' /* pseudo type for url redirection */);

$config['sddns']['std']['class'] = 'IN';
$config['sddns']['std']['type'] = 'A';
$config['sddns']['std']['ttl'] = 2 * 60;	// in seconds; 2 minutes; < max_ttl!
$config['sddns']['std']['lifetime'] = 1 * 30 * 24 * 60 * 60;	// in seconds; 1 month; < max_lifetime!

$config['sddns']['cmds'] = array('add', 'delete', 'update');	// available cmds
$config['sddns']['formats'] = array('html', 'xml', 'gif', 'txt', 'csv', 'png', 'json');	// available formats (keep in sync with .htaccess!)
$config['sddns']['blacklist'] = array_merge($zones, array('steffenvogel.de', 'griesm.de', 'vogel.cc', 'icann.org', 'isc.org'));
