<?php
require_once '../include/init.php';

$output = Output::start();

$dataTables = array(
			'hosts' => null,	// table name => date field
			'records' => 'created',
			'queries' => 'queried',
			'logs' => 'logged',
			'uris' => 'created'
		);

$colors = array(
			'records' => 'midnightblue',
			'queries' => 'red',
			'logs' => 'orange',
			'uris' => 'black'
		);

if (@!empty($_REQUEST['data'])) {
	$tmps = explode(',', trim($_REQUEST['data'], ' ,'));

	foreach ($tmps as $tmp) {
		if (in_array(trim($tmp), array_keys($dataTables)) && !($output instanceof GraphOutput && !$dataTables[trim($tmp)]))
			$get[] = trim($tmp);
	}
}
else {
	$get = array('queries');
}

if ($output instanceof GraphOutput) {
	require_once $site['path']['server'] . '/include/jpgraph/jpgraph_line.php';
	require_once $site['path']['server'] . '/include/jpgraph/jpgraph_date.php';

	$perModes = array(
				'hour' => false,	// mySQL funtion => is timestamp
				'day' => false,
				'date' => true,
				'week' => false,
				'month' => false,
				'weekday' => false
			);

	if (@isset($_REQUEST['per'])) {
	        if (in_array($_REQUEST['per'], array_keys($perModes)))
	                $per = $_REQUEST['per'];
	}
	else {
	        $per = 'date';
	}

	$graph = $output->getGraph(700, 300);
	$graph->img->SetAntiAliasing();
	
	if ($perModes[$per]) {
		$graph->SetScale('datint');
		$graph->xaxis->scale->SetTimeAlign(HOURADJ_1);
		$graph->xaxis->scale->SetDateFormat( 'M y' );
		$graph->xaxis->SetLabelAngle(45);
	}
	else {
		$graph->SetScale('intint');
		$graph->xaxis->SetLabelFormatCallback(function($label) {
			global $per;

			switch ($per) {
				case 'month':
					return date('M', mktime(0, 0, 0, $label));
					break;
				case 'weekday':
					return date('l', mktime(0, 0, 0, 3, $label + 1, 2010));
					break;
				default:
					return $label;
			}
		});
	}


	$graph->SetMargin(80,60,30,30);

	$graph->title->Set(implode(', ', $get) . '/' . $per);
	$graph->legend->SetAbsPos(10, 10, 'right', 'top');

	$graph->title->SetFont(FF_VERDANA, FS_BOLD, 14);
	$graph->yaxis->SetFont(FF_VERDANA, FS_NORMAL, 10);
	$graph->xaxis->SetFont(FF_VERDANA, FS_NORMAL, 10);
	$graph->yaxis->title->SetFont(FF_VERDANA, FS_NORMAL, 12);
	$graph->xaxis->title->SetFont(FF_VERDANA, FS_NORMAL, 12);
	$graph->yaxis->title->SetOrientation(90);
	$graph->SetBackgroundGradient('white', 'white');

	$graph->xaxis->title->Set('date');

	$graph->yaxis->title->Set(implode(',', $get) . '/' . $per);
	$graph->yaxis->SetLabelFormatCallback(function($label) { return ($label > 1000) ? round($label / 1000) . 'k' : $label; });
	$graph->yaxis->SetTitleMargin(50);
	$graph->xaxis->SetTitleMargin(17);

	// fetch data & create plot
	foreach ($get as $table) {
		unset($plotData);

		$result = $db->query('SELECT ' . strtoupper($per) . '(' . $dataTables[$table] . ') AS day, COUNT(*) AS count FROM ' . $table . ' GROUP BY day');

		foreach ($result as $date) {
		       	$plotData['x'][] = $date['day'];
	        	$plotData['y'][] = $date['count'];
		}

		if ($perModes[$per])
			array_walk($plotData['x'], function(&$value) { $value = strtotime($value); });

		$plot = new LinePlot($plotData['y'], $plotData['x']);
		$plot->SetColor($colors[$table]); 
		$plot->SetLegend($table); 
		$plot->SetLineWeight(2);

		$graph->Add($plot);
	}

	$output->showGraph();
}
else {
	switch ($get[0]) {
		case 'hosts':
			$filter = array();
			if (array_key_exists($_REQUEST['zone'], $config['sddns']['zones'])) {
				$filter['zone'] = $config['sddns']['zones'][$_REQUEST['zone']];
				
				if (!empty($_REQUEST['host'])) {
					$filter['host'] = $_REQUEST['host'];
				}
			}
		
			$data = DBHost::get($db, $filter);
			$dateField = null;
			break;
		
		case 'logs':
			$data = $db->query('SELECT logged, id, program, message FROM logs ORDER BY logged DESC', 1000);
			$dateField = 'logged';
			break;
		
		case 'queries':
			$data = $db->query('SELECT queried, id, ip, port, hostname, class, type, options FROM queries ORDER BY queried DESC', 1000);
			$dateField = 'queried';
			break;
		
		case 'uris':
			$filter = array();
			if (array_key_exists($_REQUEST['zone'], $config['sddns']['zones'])) {
				$filter['zone'] = $config['sddns']['zones'][$_REQUEST['zone']];
		
				if (!empty($_REQUEST['host'])) {
					$filter['host'] = $_REQUEST['host'];
				}
			}
		
			$data = DBUri::get($db, $filter);
			$dateField = 'created';
			break;
	
		case 'records':
		default:
			$filter = array();
			if (array_key_exists($_REQUEST['zone'], $config['sddns']['zones'])) {
				$filter['zone'] = $config['sddns']['zones'][$_REQUEST['zone']];
		
				if (!empty($_REQUEST['host'])) {
					$filter['host'] = $_REQUEST['host'];
				}
			}
		
			if (!empty($_REQUEST['class']) && in_array($_REQUEST['class'], $config['sddns']['classes']))
				$filter['class'] = $_REQUEST['class'];
			if (!empty($_REQUEST['ttl']))
				$filter['ttl'] = (int) $_REQUEST['ttl'];
			if (!empty($_REQUEST['type']) && in_array($_REQUEST['type'], $config['sddns']['types'])) {
				$filter['type'] = $_REQUEST['type'];
				if (!empty($_REQUEST['rdata']) && Record::isRData($_REQUEST['rdata'], $filter['type']))
					$filter['rdata'] = $_REQUEST['rdata'];
			}
			
			$data = DBRecord::get($db, $filter);
			$dateField = 'created';
	}

	foreach ($data as $row) {
		switch (@$_REQUEST['data']) {
			case 'uris':
				$output->add('', 'data', $row->host, $row);
				break;
			case 'hosts':
			case 'logs':
			case 'queries':
			case 'records':
			default:
				$output->add('', 'data', $row);
		}
	}
}

Output::send();

?>
