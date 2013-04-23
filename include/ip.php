<?php
/**
 * IP classes
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

class IpV4 implements Object {
	public $tuples = array();

	public function __construct($ipString) {
		if (self::isValid($ipString)) {
			$this->tuples = explode('.', $ipString);
		}
		else {
			throw new ValidationException('Invalid IP: ', $ipString);
		}
	}

	static function isValid($ipString) {
                return preg_match('/^((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])$/', $ipString);
        }

	public function __toString() {
		return implode('.', $this->tuples);
        }

        public function toHtml() {
		return '<a href="http://www.dnsstuff.com/tools/ipall/?tool_id=67&ip=' . $this . '">' . $this . '</a>';
        }

	public function toXml(DOMDocument $doc) {
                $xmlIpV4 = $doc->createElement('ip', $this);
		$xmlIpV4->setAttribute('version', 4);

                return $xmlIpV4;
        }
}

class IpV6 implements Object {
	private $ip;

	public function __construct($ipString) {
		if (self::isValid($ipString)) {
                        $this->ip = $ipString;
                }
                else {
                        throw new ValidationException('Invalid IP: ', $ipString);
                }
	}

        static function isValid($ipString) {
                return preg_match('/^((([0-9A-Fa-f]{1,4}:){7}(([0-9A-Fa-f]{1,4})|:))|(([0-9A-Fa-f]{1,4}:){6}(:|((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})|(:[0-9A-Fa-f]{1,4})))|(([0-9A-Fa-f]{1,4}:){5}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){4}(:[0-9A-Fa-f]{1,4}){0,1}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){3}(:[0-9A-Fa-f]{1,4}){0,2}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){2}(:[0-9A-Fa-f]{1,4}){0,3}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)(:[0-9A-Fa-f]{1,4}){0,4}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(:(:[0-9A-Fa-f]{1,4}){0,5}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})))(%.+)?$/', $ipString);
        }

	public function __toString() {
                return $this->ip;
        }

        public function toHtml() {
                return '<a href="http://www.dnsstuff.com/tools/ipall/?tool_id=67&ip=' . $this . '">' . $this . '</a>';
        }

        public function toXml(DOMDocument $doc) {
                $xmlIpV6 = $doc->createElement('ip', $this);
                $xmlIpV6->setAttribute('version', 6);

                return $xmlIpV6;
        }
}

?>
