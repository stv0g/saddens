SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `hosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(255) NOT NULL,
  `zone` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `generated` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2149 ;

CREATE TABLE IF NOT EXISTS `logs` (
  `host` varchar(32) DEFAULT NULL,
  `facility` varchar(10) DEFAULT NULL,
  `priority` varchar(10) DEFAULT NULL,
  `level` varchar(10) DEFAULT NULL,
  `tag` varchar(10) DEFAULT NULL,
  `logged` datetime DEFAULT NULL,
  `program` varchar(15) DEFAULT NULL,
  `message` text,
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `host` (`host`),
  KEY `program` (`program`),
  KEY `datetime` (`logged`),
  KEY `priority` (`priority`),
  KEY `facility` (`facility`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18653 ;

CREATE TABLE IF NOT EXISTS `queries` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `port` int(5) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `class` varchar(12) NOT NULL,
  `type` varchar(12) NOT NULL,
  `options` varchar(12) NOT NULL,
  `queried` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11801589 ;

CREATE TABLE IF NOT EXISTS `records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  `lifetime` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `host_id` int(11) NOT NULL,
  `ttl` int(11) NOT NULL,
  `class` varchar(16) NOT NULL,
  `type` varchar(16) NOT NULL,
  `rdata` varchar(255) NOT NULL,
  `last_accessed` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=866 ;

CREATE TABLE IF NOT EXISTS `uris` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL,
  `uri` text NOT NULL,
  `frame` tinyint(1) NOT NULL,
  `accessed` int(11) NOT NULL,
  `last_accessed` datetime NOT NULL,
  `lifetime` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1104 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
