<?php

require_once dirname(__FILE__) . '/../include/init.php';
$output = Output::start();

$sql = 'SELECT *
		FROM logs
		WHERE
			program = \'named\'
		ORDER BY logged ASC
		LIMIT 10000';

$result = $db->query($sql);

$pattern = '/^queries: info: client ([.\d]+)#(\d+): query: ([+.-\w]+) ([A-Z]+) ([0-9A-Z]+) ([-+A-Z]+) \(([.\d]+)\)$/';
$queries = array();
$update = array();
$delete = array();
foreach ($result as $log) {
	if (preg_match($pattern, $log['message'], $matches)) {
		$query = array('ip' => new IpV4($matches[1]),
				'port' => (int) $matches[2],
				'hostname' => $matches[3],
				'class' => $matches[4],
				'type' => $matches[5],
				'options' => $matches[6],
				'log_id' => $log['id'],
				'queried' => strtotime($log['logged']));

		$db->execute('INSERT IGNORE INTO queries (ip, port, hostname, class, type, options, queried, log_id) VALUES (\'' . $query['ip'] . '\', ' . $query['port'] . ', \'' . $query['hostname'] . '\', \'' . $query['class'] . '\', \'' . $query['type'] . '\', \'' . $query['options'] . '\', \'' . date('Y-m-d H:i:s', $query['queried']) . '\', ' . $query['log_id'] . ')');
		$output->add('query parsed', 'debug', 3, $log['logged'], $log['message']);
		array_push($delete, $query['log_id']);

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
$output->add('renewed records', 'success', $updated);

$output->send();

?>
