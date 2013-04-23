<?php
/**
 * Registry class
 *
 * provides a global key value registry
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

/**
 * Registry class to pass global variables between classes.
 */
abstract class Registry {
	/**
	 * Object registry provides storage for shared objects
	 *
	 * @var array
	 */
	private static $registry = array();

	/**
	 * Adds a new variable to the Registry.
	 *
	 * @param string $key Name of the variable
	 * @param mixed $value Value of the variable
	 * @throws Exception
	 * @return bool
	 */
	public static function set($key, $value) {
		if (!isset(self::$registry[$key])) {
			self::$registry[$key] = $value;
			return true;
		} else {
			throw new Exception('Unable to set variable `' . $key . '`. It was already set.');
		}
	}

	/**
	 * Returns the value of the specified $key in the Registry.
	 *
	 * @param string $key Name of the variable
	 * @return mixed Value of the specified $key
	 */
	public static function get($key)
	{
		if (isset(self::$registry[$key])) {
			return self::$registry[$key];
		}
		return null;
	}

	/**
	 * Returns the whole Registry as an array.
	 *
	 * @return array Whole Registry
	 */
	public static function getAll()
	{
		return self::$registry;
	}

	/**
	 * Removes a variable from the Registry.
	 *
	 * @param string $key Name of the variable
	 * @return bool
	 */
	public static function remove($key)
	{
		if (isset(self::$registry[$key])) {
			unset(self::$registry[$key]);
			return true;
		}
		return false;
	}

	/**
	 * Removes all variables from the Registry.
	 *
	 * @return void
	 */
	public static function removeAll()
	{
		self::$registry = array();
		return;
	}
}

?>
