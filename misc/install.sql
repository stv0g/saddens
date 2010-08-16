-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5+lenny3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 03. Dezember 2009 um 15:17
-- Server Version: 5.0.51
-- PHP-Version: 5.2.11-0.dotdeb.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `st_sddns`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hosts`
--

CREATE TABLE IF NOT EXISTS `hosts` (
  `id` int(11) NOT NULL auto_increment,
  `hostname` varchar(255) NOT NULL,
  `zone` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `generated` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `hosts`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `records`
--

CREATE TABLE IF NOT EXISTS `records` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime NOT NULL,
  `expires` datetime NOT NULL,
  `ip` varchar(15) NOT NULL,
  `host_id` int(11) NOT NULL,
  `zone` varchar(255) NOT NULL,
  `ttl` int(11) NOT NULL,
  `class` varchar(16) NOT NULL,
  `type` varchar(16) NOT NULL,
  `rdata` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `records`
--

