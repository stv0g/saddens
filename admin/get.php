<?php
/**
 * Query database
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

require_once '../include/init.php';

$output = Output::start();

$dataTables = array(
	'hosts' => null,	// table name => date field
	'records' => 'created',
	'queries' => 'queried',
	'logs' => 'logged',
	'uris' => 'created'
);

$perModes = array(
	'hour' => false,	// mySQL funtion => is timestamp
	'day' => false,
	'date' => true,
	'week' => false,
	'month' => false,
	'weekday' => false
);

$colors = array(
	'records' => 'blue',
	'queries' => 'red',
	'logs' => 'orange',
	'uris' => 'black'
);

$get = array();
if (isset($_REQUEST['data'])) {
	foreach (explode(',', $_REQUEST['data']) as $tmp) {
		if (in_array($tmp, array_keys($dataTables)) && !($output instanceof GraphOutput && !$dataTables[$tmp])) {
			$get[] = trim($tmp);
		}
		else {
			$output->add('invalid data', 'error', $tmp);
			$output->send();
			die();
		}
	}
}
else {
	$get = array('records');
}

if ($output instanceof GraphOutput) {
	require_once $site['path']['server'] . '/include/jpgraph/jpgraph_line.php';
	require_once $site['path']['server'] . '/include/jpgraph/jpgraph_date.php';

	if (isset($_REQUEST['per'])) {
	        if (in_array($_REQUEST['per'], array_keys($perModes))) {
	                $per = $_REQUEST['per'];
		}
		else {
			$output->add('unknown grouping mode', 'error', $_REQUEST['per']);
			$output->send();
			die();
		}
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
		$graph->xaxis->SetLabelFormatCallback(function($label) use ($per) {
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

			$data = DBUri::get($db, $filter, array('last_accessed' => 'DESC'));
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

			$data = DBRecord::get($db, $filter, array('last_accessed' => 'DESC'));
			$dateField = 'created';
	}

	foreach ($data as $row) {
		switch ($get[0]) {
			case 'uris':
				$params = 'host=' . $row->host->toPunycode() . '&zone=' . $row->host->zone->name . '&type=URL&rdata=' . $row->uri;
				$actions = '<a href="../delete.php?' . $params . '"><img alt="delete" src="../images/delete.png" /></a>';
				$actions .= '<a href="../expert.php?' . $params . '&command=update"><img alt="edit" src="../images/edit.png" /></a>';

				if ($output instanceof HtmlOutput) $output->add(get_class($row), 'data', $row->host, $row, $actions);
				else $output->add(get_class($row), 'data', $row->host, $row);
				break;
			case 'hosts':
				$params = 'host=' . $row->toPunycode() . '&zone=' . $row->zone->name;
				$actions = '<a href="../delete.php?' . $params . '"><img alt="delete" src="../images/delete.png" /></a>';
				$actions .= '<a href="../expert.php?' . $params . '&command=update"><img alt="edit" src="../images/edit.png" /></a>';

				if ($output instanceof HtmlOutput) $output->add(get_class($row), 'data', $row, $actions);
				else $output->add(get_class($row), 'data', $row);
				break;
			case 'records':
				$params = 'host=' . $row->host->toPunycode() . '&zone=' . $row->host->zone->name . '&type=' . $row->type . '&class=' . $row->class . '&rdata=' . $row->rdata;
				$actions = '<a href="../delete.php?' . $params . '"><img alt="delete" src="../images/delete.png" /></a>';
				$actions .= '<a href="../expert.php?' . $params . '&command=update"><img alt="edit" src="../images/edit.png" /></a>';

				if ($output instanceof HtmlOutput) $output->add(get_class($row), 'data', $row, $actions);
				else $output->add(get_class($row), 'data', $row);
				break;
			case 'logs':
			case 'queries':
			default:
				$output->add('data', 'data', $row);
		}
	}
}
