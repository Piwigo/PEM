DROP TABLE IF EXISTS `pem_authors`;
CREATE TABLE `pem_authors` (
  `idx_extension` int(11) NOT NULL default '0',
  `idx_user` int(11) NOT NULL default '0',
  PRIMARY KEY  (`idx_extension`,`idx_user`)
)  DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `pem_categories`;
CREATE TABLE `pem_categories` (
  `id_category` int(11) NOT NULL auto_increment,
  `idx_parent` int(11) default NULL,
  `name` varchar(255) default NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id_category`)
)   DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `pem_download_log`;
CREATE TABLE `pem_download_log` (
  `IP` varchar(15) NOT NULL default '',
  `year` smallint(4) NOT NULL default '0',
  `month` tinyint(2) default NULL,
  `day` tinyint(2) default NULL,
  `idx_revision` int(11) NOT NULL default '0',
  KEY `download_log_i1` (`year`),
  KEY `download_log_i2` (`month`),
  KEY `download_log_i3` (`day`),
  KEY `download_log_i4` (`idx_revision`)
)  DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `pem_extensions`;
CREATE TABLE `pem_extensions` (
  `id_extension` int(11) NOT NULL auto_increment,
  `idx_user` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  PRIMARY KEY  (`id_extension`)
)   DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `pem_extensions_categories`;
CREATE TABLE `pem_extensions_categories` (
  `idx_category` int(11) NOT NULL default '0',
  `idx_extension` int(11) NOT NULL default '0',
  PRIMARY KEY  (`idx_category`,`idx_extension`)
)  DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `pem_links`;
CREATE TABLE `pem_links` (
  `id_link` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `description` text,
  `rank` int(10) unsigned NOT NULL default '0',
  `idx_extension` int(10) unsigned NOT NULL default '0',
  `lang` char(2) default NULL,
  PRIMARY KEY  (`id_link`),
  KEY `idx_extension` (`idx_extension`)
)   DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `pem_revisions`;
CREATE TABLE `pem_revisions` (
  `id_revision` int(11) NOT NULL auto_increment,
  `idx_extension` int(11) NOT NULL default '0',
  `date` int(11) NOT NULL default '0',
  `url` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `version` varchar(25) NOT NULL default '',
  `accept_agreement` enum('true','false') default NULL,
  `author` int(11) NULL default NULL,
  PRIMARY KEY  (`id_revision`)
)   DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `pem_revisions_compatibilities`;
CREATE TABLE `pem_revisions_compatibilities` (
  `idx_revision` int(11) NOT NULL default '0',
  `idx_version` int(11) NOT NULL default '0',
  PRIMARY KEY  (`idx_revision`,`idx_version`),
  KEY `idx_version_only` (`idx_version`)
)  DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `pem_user_infos`;
CREATE TABLE `pem_user_infos` (
  `idx_user` smallint(5) NOT NULL default '0',
  `language` varchar(50) NOT NULL default '',
  `registration_date` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `user_infos_ui1` (`idx_user`)
)  DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `pem_users`;
CREATE TABLE `pem_users` (
  `id_user` smallint(5) NOT NULL auto_increment,
  `username` varchar(20) NOT NULL default '',
  `password` varchar(32) default NULL,
  `email` varchar(255) default NULL,
  PRIMARY KEY  (`id_user`),
  UNIQUE KEY `users_ui1` (`username`)
)  DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `pem_versions`;
CREATE TABLE `pem_versions` (
  `id_version` int(11) NOT NULL auto_increment,
  `version` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`id_version`),
  KEY `version_only` (`version`)
)   DEFAULT CHARSET=utf8;
