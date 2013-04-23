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
$pw = @$_REQUEST['pw'];

if (array_key_exists($_REQUEST['zone'], $config['sddns']['zones'])) {
	$zone = $config['sddns']['zones'][$_REQUEST['zone']];

	if (!empty($_REQUEST['host'])) {
		if (list($host) = DBHost::get($db, array('host' => $_REQUEST['host'], 'zone' => $zone))) {
			if ($host->checkPassword($pw)  || isAuthentificated()) {
				if (isset($_REQUEST['class']) && in_array($_REQUEST['class'], $config['sddns']['classes']))
					$class = $_REQUEST['class'];

				if (isset($_REQUEST['type']) && in_array($_REQUEST['type'], $config['sddns']['types'])) {
					$type = $_REQUEST['type'];

					if (isset($_REQUEST['rdata']) && Record::isRData($_REQUEST['rdata'], $type))
						$rdata = $_REQUEST['rdata'];
				}

				if (@$type == 'URL' || empty($type)) {
					$uris = DBUri::get($db, array('zone' => $zone, 'host' => $host));
					foreach ($uris as $uri) {
						$uri->delete();
						$output->add('uri deleted from db', 'success', $uri);
					}
				}

				if (@$type != 'URL' || empty($type)) {
					$records = DBRecord::get($db, array('zone' => $zone, 'host' => $host, 'type' => @$type, 'class' => @$class, 'rdata' => @$rdata));
					foreach ($records as $record) {
						$record->delete();
						$output->add('record deleted from db', 'success', $record);
					}
				}

				$zone->cleanup($db);
				$zone->sync($db);
			}
			else {
				$output->add('not authentificated for host', 'error', $host);
			}
		}
		else {
			$output->add('host not found', 'error', $_REQUEST['host']);
		}
	}
	else {
		$output->add('no host specified', 'error');
	}
}
else {
	$output->add('zone not found', 'error', $_REQUEST['zone']);
}

$output->send();

?>
