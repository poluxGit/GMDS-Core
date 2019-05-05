

DROP TABLE IF EXISTS `{$ObjectDefinitionTableName}`;

CREATE TABLE `{$ObjectDefinitionTableName}` (
  `uid` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `uid_obj` int(10) unsigned zerofill DEFAULT NULL,
  `bid` varchar(150) NOT NULL,
  `vers` varchar(10) DEFAULT 'UNIQ',
  `rev` int(3) unsigned zerofill DEFAULT 0,
  `label` varchar(100)  DEFAULT 'LabelNotDefined',
  `name` varchar(200)  DEFAULT 'NameNotDefined',
  `comment` text ,
  `jsondata` json DEFAULT NULL,
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cuser` int(10) unsigned zerofill NOT NULL,
  `udate` timestamp NULL DEFAULT NULL,
  `uuser` int(10) unsigned zerofill DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `{$ObjectDefinitionTableName}_bid_UNIQUE` (`bid`,`vers`,`rev`) /*!80000 INVISIBLE */,
  KEY `FK_{$ObjectDefinitionCode}_OBJ_UID_idx` (`uid_obj`),
  KEY `FK_{$ObjectDefinitionCode}_USR_CREATE_idx` (`cuser`),
  KEY `FK_{$ObjectDefinitionCode}_USR_UPDATE_idx` (`uuser`),
  CONSTRAINT `FK_{$ObjectDefinitionCode}_OBJ_UID` FOREIGN KEY (`uid_obj`) REFERENCES `a1000_objects` (`uid`),
  CONSTRAINT `FK_{$ObjectDefinitionCode}_USR_CREATE` FOREIGN KEY (`cuser`) REFERENCES `z0000_users` (`uid`),
  CONSTRAINT `FK_{$ObjectDefinitionCode}_USR_UPDATE` FOREIGN KEY (`uuser`) REFERENCES `z0000_users` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TRIGGER `{$ObjectDefinitionTableName}_BEFORE_INSERT` BEFORE INSERT ON `{$ObjectDefinitionTableName}` FOR EACH ROW
BEGIN
  SET NEW.vers = 'UNIQ';
  SET NEW.rev = 0;
END;

CREATE TRIGGER `{$ObjectDefinitionTableName}_BEFORE_UPDATE` BEFORE UPDATE ON `{$ObjectDefinitionTableName}` FOR EACH ROW
BEGIN
  SET NEW.udate = CURRENT_TIMESTAMP;
END;
