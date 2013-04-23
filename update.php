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

// password
if (!empty($_REQUEST['pw'])) {
	$pw = $_REQUEST['pw'];
}
else {
	$pw = false;
}

// host & zone
if (!empty($_REQUEST['hostname'])) {
	foreach ($config['sddns']['zones'] as $z) {
		if (substr($_REQUEST['hostname'], -strlen($z->name)) === $z->name) {
			$zone = $z;
			list($host) = DBHost::get($db, array('host' => substr($_REQUEST['hostname'], 0, -(strlen($zone->name)+1)), 'zone' => $zone));
		}
	}
}
elseif (!empty($_REQUEST['host'])) {
	if (array_key_exists($_REQUEST['zone'], $config['sddns']['zones'])) {
		$zone = $config['sddns']['zones'][$_REQUEST['zone']];
		list($host) = DBHost::get($db, array('host' => $_REQUEST['host'], 'zone' => $zone));
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
		if ($type == 'URL') {
			$entries = DBUri::get($db, array('host' => $host, 'zone' => $zone));
		}
		else {
			$entries = DBRecord::get($db, array('host' => $host, 'zone' => $zone, 'class' => @$class, 'type' => @$type));
		}

		if (count($entries) > 0) {
			$output->add('found host', 'success', $host);

			if (isAuthentificated() || $host->checkPassword($pw)) {
				if ($type == 'URL') {
					if (isset($_REQUEST['frame'])) $entries[0]->frame = $_REQUEST['frame'];

					$entries[0]->setUri($rdata);
				}
				else {
					$entries[0]->setRData($rdata);
				}
				$entries[0]->lastAccessed = time();
				$entries[0]->update();

				$output->add('entry updated in db', 'success', $entries[0]);

				for ($i = 1; $i < count($entries); $i++) {
					$records[$i]->delete();
					$output->add('record deleted from db', 'warning', $entries[$i]);
				}

				$zone->cleanup($db);
				$zone->sync($db);
			}
			else {
				$output->add('not authentificated for host', 'error', $host);
			}
		}
		else {
			$output->add('nothing found to update', 'warning');
		}
	}
	else {
		$output->add('host not found', 'error', @$_REQUEST['host'], @$_REQUEST['hostname']);
	}
}
else {
	$output->add('zone not found', 'error', $_REQUEST['host'], $_REQUEST['zone']);
}

$output->send();
?>
