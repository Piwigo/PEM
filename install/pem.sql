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
  `idx_language` int(11) NOT NULL,
  PRIMARY KEY  (`id_category`)
)   DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pem_categories_translations`;
CREATE TABLE `pem_categories_translations` (
  `idx_category` int(11) NOT NULL,
  `idx_language` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text default NULL,
  PRIMARY KEY  (`idx_category`, `idx_language`)
) DEFAULT CHARSET=utf8;

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
  `idx_language` int(11) NOT NULL,
  `svn_url` varchar(255) NULL default NULL,
  `archive_root_dir` varchar(255) NULL default NULL,
  `archive_name` varchar(255) NULL default NULL,
  PRIMARY KEY  (`id_extension`)
)   DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pem_extensions_categories`;
CREATE TABLE `pem_extensions_categories` (
  `idx_category` int(11) NOT NULL default '0',
  `idx_extension` int(11) NOT NULL default '0',
  PRIMARY KEY  (`idx_category`,`idx_extension`)
)  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pem_extensions_tags`;
CREATE TABLE `pem_extensions_tags` (
  `idx_extension` int(11) NOT NULL default '0',
  `idx_tag` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY (`idx_extension`,`idx_tag`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pem_extensions_translations`;
CREATE TABLE `pem_extensions_translations` (
  `idx_extension` int(11) NOT NULL,
  `idx_language` int(11) NOT NULL,
  `description` TEXT NOT NULL,
  PRIMARY KEY  (`idx_extension`, `idx_language`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pem_languages`;
CREATE TABLE IF NOT EXISTS `pem_languages` (
  `id_language` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(5) NOT NULL,
  `name` varchar(50) NOT NULL,
  `interface` enum('true','false') NOT NULL default 'false',
  `extensions` enum('true','false') NOT NULL default 'false',
  PRIMARY KEY (`id_language`),
  KEY `languages_i2` (`interface`),
  KEY `languages_i3` (`extensions`)
)  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pem_links`;
CREATE TABLE `pem_links` (
  `id_link` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `description` text,
  `rank` int(10) unsigned NOT NULL default '0',
  `idx_extension` int(10) unsigned NOT NULL default '0',
  `idx_language` int(11) default NULL,
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
  `idx_language` int(11) NOT NULL,
  `version` varchar(25) NOT NULL default '',
  `accept_agreement` enum('true','false') default NULL,
  `author` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_revision`)
)   DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pem_revisions_compatibilities`;
CREATE TABLE `pem_revisions_compatibilities` (
  `idx_revision` int(11) NOT NULL default '0',
  `idx_version` int(11) NOT NULL default '0',
  PRIMARY KEY  (`idx_revision`,`idx_version`),
  KEY `idx_version_only` (`idx_version`)
)  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pem_revisions_languages`;
CREATE TABLE `pem_revisions_languages` (
  `idx_revision` int(11) NOT NULL default '0',
  `idx_language` int(11) NOT NULL default '0',
  PRIMARY KEY  (`idx_revision`,`idx_language`)
)  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pem_revisions_translations`;
CREATE TABLE `pem_revisions_translations` (
  `idx_revision` int(11) NOT NULL,
  `idx_language` int(11) NOT NULL,
  `description` TEXT NOT NULL,
  PRIMARY KEY  (`idx_revision`,`idx_language`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pem_tags`;
CREATE TABLE `pem_tags` (
  `id_tag` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_tag`)
) DEFAULT CHARSET=utf8;

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
