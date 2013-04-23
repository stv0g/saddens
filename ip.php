<?php
/**
 * Query ip
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

if (IpV4::isValid($_SERVER['REMOTE_ADDR'])) {
	$ip = new IpV4($_SERVER['REMOTE_ADDR']);
}
else if (IpV6::isValid($_SERVER['REMOTE_ADDR'])) {
	$ip = new IpV6($_SERVER['REMOTE_ADDR']);
}
else {
	$output->add('can\'t determine remote addr', 'error', $_SERVER['REMOTE_ADDR']);
}

$output->add('your current internet ip address', 'notice', $ip);

$output->send();

?>
