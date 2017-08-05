<?php
/**
 * Authentificator
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
$output = Output::start('html');


if (!isAuthentificated()) {
	header('WWW-Authenticate: Basic realm="Administration area"');
	header('HTTP/1.0 401 Unauthorized');

	echo '<h1>Authorization Required</h1>
<p>This server could not verify that you
are authorized to access the document
requested.  Either you supplied the wrong
credentials (e.g., bad password), or your
browser doesn\'t understand how to supply
the credentials required.</p>
<hr>
<address>' . $_SERVER['SERVER_SIGNATURE'] . '</address>';

	throw new AuthentificationException('authentification failed', @$_SERVER['PHP_AUTH_USER']);
} else {
	echo '<script language="javascript">window.setTimeout(function(){ window.location="/expert"; }, 1500);</script>';

	$output->add('authentificated as', 'notice', $_SERVER['PHP_AUTH_USER']);
}
