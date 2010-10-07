<?php

require_once dirname(__FILE__) . '/../include/init.php';
$output = Output::start();

$sql = 'SELECT *
		FROM logs
		WHERE
			program = \'named\'
		LIMIT 10000';

$result = $db->query($sql);

$pattern = '/^named\[(\d+)\]: queries: info: client ([.\d]+)#(\d+): query: ([+.-\w]+) ([A-Z]+) ([A-Z]+) (.*)$/';
$sqlDelete = 'DELETE FROM logs WHERE id IN (';
$queries = array();
$c = 0; $u = 0;

foreach ($result as $log) {
	if (preg_match($pattern, $log['message'], $matches)) {
		$query = array('ip' => new IpV4($matches[2]),
				'port' => (int) $matches[3],
				'hostname' => $matches[4],
				'class' => $matches[5],
				'type' => $matches[6],
				'options' => $matches[7],
				'log_id' => $log['id'],
				'queried' => strtotime($log['logged']));

		$db->execute('INSERT IGNORE INTO queries (ip, port, hostname, class, type, options, queried, log_id) VALUES (\'' . $query['ip'] . '\', ' . $query['port'] . ', \'' . $query['hostname'] . '\', \'' . $query['class'] . '\', \'' . $query['type'] . '\', \'' . $query['options'] . '\', \'' . date('Y-m-d H:i:s', $query['queried']) . '\', ' . $query['log_id'] . ')');
		$c++;
		$sqlDelete .= $query['log_id'] . ', ';
		$output->add('query parsed', 'debug', 3, $query);

		foreach ($config['sddns']['zones'] as $zone) {
			if (substr($query['hostname'], -strlen($zone->name)) == $zone->name && strlen($query['hostname']) > strlen($zone->name)) {

				$filter = array('class' => $query['class'],
							'type' => $query['type'],
							'host' => substr($query['hostname'], 0, -(strlen($zone->name) + 1)),
							'zone' => $zone);

				$records = DBRecord::get($db, $filter);

				foreach ($records as $record) {
					$record->lastAccessed = $query['queried'];
					$record->update();
					$output->add('record renewed', 'debug', 1, $record);
				}
			}
		}
	}
}

if ($c > 0) {
	$db->execute(substr($sqlDelete, 0, -2) . ')');
	$output->add('parsed queries', 'success', $c);
}
else {
	$output->add('no queries to parse', 'debug', 1);
}

$output->send();

?>
