<?php
/**
 * Parse log entries from database
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

$sql = 'SELECT *
		FROM logs
		WHERE
			program = \'named\'
		ORDER BY logged ASC
		LIMIT 10000';

$result = $db->query($sql);

$pattern = '/^queries: info: client ([:.0-9a-f]+)#(\d+):(?: view \w+:)? query: ([^ ]+) (IN|CH|HS) ([A-Z]+) ([-+A-Z]+) \(([:.0-9a-f]+)\)$/';
$queries = array();
$update = array();
$delete = array();

foreach ($result as $log) {
	if (preg_match($pattern, $log['message'], $matches)) {
		if (IpV4::isValid($matches[1])) {
			$ip = new IpV4($matches[1]);
		}
		else if (IpV6::isValid($matches[1])) {
			$ip = new IpV6($matches[1]);
		}

		$query = array('ip' => $ip,
				'port' => (int) $matches[2],
				'hostname' => $matches[3],
				'class' => $matches[4],
				'type' => $matches[5],
				'options' => $matches[6],
				'queried' => strtotime($log['logged']));

		$db->execute('INSERT IGNORE INTO queries (ip, port, hostname, class, type, options, queried) VALUES (\'' . $query['ip'] . '\', ' . $query['port'] . ', \'' . $query['hostname'] . '\', \'' . $query['class'] . '\', \'' . $query['type'] . '\', \'' . $query['options'] . '\', \'' . date('Y-m-d H:i:s', $query['queried']) . '\')');
		$output->add('query parsed', 'debug', 3, $log['logged'], $log['message']);
		array_push($delete, $log['id']);

		foreach ($config['sddns']['zones'] as $zone) {
			if (substr($query['hostname'], -strlen($zone->name)) == $zone->name && strlen($query['hostname']) > strlen($zone->name)) {
				$filter = array('class' => $query['class'],
							'type' => $query['type'],
							'host' => substr($query['hostname'], 0, -(strlen($zone->name) + 1)),
							'zone' => $zone);

				$records = DBRecord::get($db, $filter);

				foreach ($records as $record) {
					// remove older update requests
					foreach ($update as $index => $updateRecord) {
						if ($updateRecord->id == $record->id) {
							unset($update[$index]);
						}
					}

					$record->lastAccessed = $query['queried'];
					array_push($update, $record);
				}
			}
		}
	}
	else {
		$output->add('unmatching log entry', 'debug', 5, $log);
	}
}

if (count($delete)) {
	$sqlDelete = 'DELETE FROM logs WHERE id IN (' .  implode($delete, ', ') . ');';

	$db->execute($sqlDelete);
	$deleted = $db->affectedRows();

	$output->add('parsed queries', 'success', $deleted);
}

$updated = 0;
foreach ($update as $record) {
	$record->update();
	$updated++;

	$output->add('record renewed', 'success', $record, date('Y-m-d H:m:s', $record->lastAccessed));
}

if ($updated > 0) {
	$output->add('renewed records', 'success', $updated);
}
else {
	$output->add('no records updated', 'warning');
}
