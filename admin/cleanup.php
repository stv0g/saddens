<?php
/**
 * Cleanup database
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

require_once dirname(__FILE__) . '/../include/init.php';
$output = Output::start();

if (empty($_REQUEST['zone']) || $_REQUEST['zone'] == 'all') {
	$zones = $config['sddns']['zones'];
}
elseif (isset($config['sddns']['zones'][$_REQUEST['zone']])) {
	$zones = array($_REQUEST['zone'] => $config['sddns']['zones'][$_REQUEST['zone']]);
}
else {
	$output->add('no such zone', 'error', $_REQUEST['zone']);
	$output->send();
}

foreach ($zones as $name => $zone) {
	$output->add('cleaning zone', 'notice', $zone);
	$zone->cleanup($db);
}
