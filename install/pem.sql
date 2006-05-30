-- MySQL dump 10.9
--
-- Host: localhost    Database: pem
-- ------------------------------------------------------
-- Server version	4.1.12-Debian_1ubuntu3.4-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `pem_categories`
--

DROP TABLE IF EXISTS `pem_categories`;
CREATE TABLE `pem_categories` (
  `id_category` int(11) NOT NULL auto_increment,
  `idx_parent` int(11) default NULL,
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  PRIMARY KEY  (`id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `pem_extensions`
--

DROP TABLE IF EXISTS `pem_extensions`;
CREATE TABLE `pem_extensions` (
  `id_extension` int(11) NOT NULL auto_increment,
  `idx_user` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  PRIMARY KEY  (`id_extension`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `pem_extensions_categories`
--

DROP TABLE IF EXISTS `pem_extensions_categories`;
CREATE TABLE `pem_extensions_categories` (
  `idx_category` int(11) NOT NULL default '0',
  `idx_extension` int(11) NOT NULL default '0',
  PRIMARY KEY  (`idx_category`,`idx_extension`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `pem_links`
--

DROP TABLE IF EXISTS `pem_links`;
CREATE TABLE `pem_links` (
  `id_link` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `description` text,
  `rank` int(10) unsigned NOT NULL default '0',
  `idx_extension` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_link`),
  KEY `idx_extension` (`idx_extension`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `pem_revisions`
--

DROP TABLE IF EXISTS `pem_revisions`;
CREATE TABLE `pem_revisions` (
  `id_revision` int(11) NOT NULL auto_increment,
  `idx_extension` int(11) NOT NULL default '0',
  `date` int(11) NOT NULL default '0',
  `url` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `version` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`id_revision`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `pem_revisions_compatibilities`
--

DROP TABLE IF EXISTS `pem_revisions_compatibilities`;
CREATE TABLE `pem_revisions_compatibilities` (
  `idx_revision` int(11) NOT NULL default '0',
  `idx_version` int(11) NOT NULL default '0',
  PRIMARY KEY  (`idx_revision`,`idx_version`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `pem_user_infos`
--

DROP TABLE IF EXISTS `pem_user_infos`;
CREATE TABLE `pem_user_infos` (
  `idx_user` smallint(5) NOT NULL default '0',
  `language` varchar(50) NOT NULL default '',
  `registration_date` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `user_infos_ui1` (`idx_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `pem_users`
--

DROP TABLE IF EXISTS `pem_users`;
CREATE TABLE `pem_users` (
  `id_user` smallint(5) NOT NULL auto_increment,
  `username` varchar(20) character set latin1 collate latin1_bin NOT NULL default '',
  `password` varchar(32) default NULL,
  `email` varchar(255) default NULL,
  PRIMARY KEY  (`id_user`),
  UNIQUE KEY `users_ui1` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `pem_versions`
--

DROP TABLE IF EXISTS `pem_versions`;
CREATE TABLE `pem_versions` (
  `id_version` int(11) NOT NULL auto_increment,
  `version` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`id_version`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

