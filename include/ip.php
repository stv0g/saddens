<?php

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
