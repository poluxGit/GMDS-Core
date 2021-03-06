-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema {$TargetSchema}
-- -----------------------------------------------------
-- {$TargetSchema} - Generic Data Management
DROP SCHEMA IF EXISTS `{$TargetSchema}` ;

-- -----------------------------------------------------
-- Schema {$TargetSchema}
--
-- {$TargetSchema} - Generic Data Management
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `{$TargetSchema}` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin ;
USE `{$TargetSchema}` ;

-- -----------------------------------------------------
-- Table `a0000_template`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `a0000_template` ;

CREATE TABLE IF NOT EXISTS `a0000_template` (
  `uid` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT,
  `bid` VARCHAR(150) UNICODE NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `comment` TEXT NULL DEFAULT NULL,
  `jsondata` JSON NULL DEFAULT NULL,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cuser` INT(10) ZEROFILL NOT NULL,
  `udate` TIMESTAMP NULL DEFAULT NULL,
  `uuser` INT(10) ZEROFILL NULL,
  `deleted` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  UNIQUE INDEX `bid_UNIQUE` (`bid` ASC) VISIBLE)
ENGINE = InnoDB
COMMENT = 'Définition d\'objet - OBJD';


-- -----------------------------------------------------
-- Table `z0000_users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `z0000_users` ;

CREATE TABLE IF NOT EXISTS `z0000_users` (
  `uid` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT,
  `bid` VARCHAR(150) UNICODE NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE INDEX `bid_UNIQUE` (`bid` ASC) VISIBLE)
ENGINE = InnoDB
COMMENT = 'Utilisateurs - USR';


-- -----------------------------------------------------
-- Table `a0000_def_models`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `a0000_def_models` ;

CREATE TABLE IF NOT EXISTS `a0000_def_models` (
  `uid` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT,
  `bid` VARCHAR(150) UNICODE NOT NULL,
  `version` VARCHAR(5) NOT NULL DEFAULT 'DEV',
  `label` VARCHAR(100) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `bid_objd_prefix` VARCHAR(5) NOT NULL,
  `bid_table_prefix` VARCHAR(5) NOT NULL,
  `comment` TEXT NULL DEFAULT NULL,
  `jsondata` JSON NULL DEFAULT NULL,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cuser` INT(10) ZEROFILL NOT NULL,
  `udate` TIMESTAMP NULL DEFAULT NULL,
  `uuser` INT(10) ZEROFILL NULL,
  `deleted` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  UNIQUE INDEX `bid_UNIQUE` (`bid` ASC, `version` ASC) INVISIBLE,
  INDEX `FK_MDLUSR_CREATE_idx` (`cuser` ASC) VISIBLE,
  INDEX `FK_MDLUSR_UPDATE_idx` (`uuser` ASC) VISIBLE,
  CONSTRAINT `FK_MDLUSR_CREATE`
    FOREIGN KEY (`cuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_MDLUSR_UPDATE`
    FOREIGN KEY (`uuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Définition des Modèles de données - MDL';


-- -----------------------------------------------------
-- Table `a0000_def_objects`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `a0000_def_objects` ;

CREATE TABLE IF NOT EXISTS `a0000_def_objects` (
  `uid` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT,
  `uid_mdl` INT(10) ZEROFILL NOT NULL,
  `bid` VARCHAR(150) UNICODE NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `bid_obj_pattern` VARCHAR(100) NULL DEFAULT NULL,
  `table_name` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Nom de la table des informations de l\'objet',
  `view_name` VARCHAR(100) NULL DEFAULT NULL,
  `view_selectSQL` TEXT NULL DEFAULT NULL,
  `comment` TEXT NULL DEFAULT NULL,
  `jsondata` JSON NULL DEFAULT NULL,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cuser` INT(10) ZEROFILL NOT NULL,
  `udate` TIMESTAMP NULL DEFAULT NULL,
  `uuser` INT(10) ZEROFILL NULL,
  `deleted` TINYINT NOT NULL DEFAULT 0,
  `isSystem` TINYINT NOT NULL DEFAULT 0,
  `isVersionable` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`, `uid_mdl`),
  UNIQUE INDEX `bid_UNIQUE` (`bid` ASC) VISIBLE,
  INDEX `FK_OBJD_USR_CREATE_idx` (`cuser` ASC) VISIBLE,
  INDEX `FK_OBJD_USR_UPDATE_idx` (`uuser` ASC) VISIBLE,
  INDEX `FK_OBJD_MDL_idx` (`uid_mdl` ASC) VISIBLE,
  CONSTRAINT `FK_OBJD_USR_CREATE`
    FOREIGN KEY (`cuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_OBJD_USR_UPDATE`
    FOREIGN KEY (`uuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_ODJD_MDL`
    FOREIGN KEY (`uid_mdl`)
    REFERENCES `a0000_def_models` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Définition d\'objet - OBJD';


-- -----------------------------------------------------
-- Table `a0000_def_objects_meta`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `a0000_def_objects_meta` ;

CREATE TABLE IF NOT EXISTS `a0000_def_objects_meta` (
  `uid` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT,
  `uid_objd` INT(10) ZEROFILL NOT NULL,
  `uid_mdl` INT(10) ZEROFILL NOT NULL,
  `bid` VARCHAR(150) UNICODE NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `data_title` VARCHAR(25) NOT NULL,
  `data_type` ENUM('Text', 'Integer', 'Double', 'File','JSON','ObjectReference', 'LinkReference', 'ObjectDefReference','LinkDefReference', 'Other') NOT NULL,
  `data_pattern` VARCHAR(100) NULL DEFAULT NULL,
  `data_options` JSON NULL DEFAULT NULL,
  `data_sqlname` VARCHAR(20) NULL DEFAULT NULL,
  `data_sqlorder` INT NOT NULL DEFAULT 1,
  `comment` TEXT NULL DEFAULT NULL,
  `jsondata` JSON NULL DEFAULT NULL,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cuser` INT(10) ZEROFILL NOT NULL,
  `udate` TIMESTAMP NULL DEFAULT NULL,
  `uuser` INT(10) ZEROFILL NULL,
  `deleted` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`, `uid_objd`, `uid_mdl`),
  UNIQUE INDEX `bid_UNIQUE` (`bid` ASC) VISIBLE,
  INDEX `FK_MOBDUSR_CREATE_idx` (`cuser` ASC) VISIBLE,
  INDEX `FK_MOBDUSR_UPDATE_idx` (`uuser` ASC) VISIBLE,
  INDEX `fk_a000_def_objects_meta_a000_def_objects1_idx` (`uid_objd` ASC, `uid_mdl` ASC) VISIBLE,
  CONSTRAINT `FK_MOBDUSR_CREATE`
    FOREIGN KEY (`cuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_MOBDUSR_UPDATE`
    FOREIGN KEY (`uuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_a000_def_objects_meta_a000_def_objects1`
    FOREIGN KEY (`uid_objd` , `uid_mdl`)
    REFERENCES `a0000_def_objects` (`uid` , `uid_mdl`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Définition des metadonnées sur les objets - MOBD ';


-- -----------------------------------------------------
-- Table `a0000_def_links`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `a0000_def_links` ;

CREATE TABLE IF NOT EXISTS `a0000_def_links` (
  `uid` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT,
  `bid` VARCHAR(150) UNICODE NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `uid_objd_source` INT(10) ZEROFILL NOT NULL,
  `uid_mdl_source` INT(10) ZEROFILL NOT NULL,
  `uid_objd_target` INT(10) ZEROFILL NOT NULL,
  `uid_mdl_target` INT(10) ZEROFILL NOT NULL,
  `link_type` ENUM('Static','Dynamic') NOT NULL,
  `link_mandatory` TINYINT NOT NULL DEFAULT 1 COMMENT 'Lien Obligatoire.',
  `link_multiple` TINYINT NOT NULL DEFAULT 0,
  `view_name` VARCHAR(100) NULL DEFAULT NULL,
  `view_select` VARCHAR(4000) NULL DEFAULT NULL,
  `comment` TEXT NULL DEFAULT NULL,
  `jsondata` JSON NULL DEFAULT NULL,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cuser` INT(10) ZEROFILL NOT NULL,
  `udate` TIMESTAMP NULL DEFAULT NULL,
  `uuser` INT(10) ZEROFILL NULL,
  `deleted` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  UNIQUE INDEX `bid_UNIQUE` (`bid` ASC) VISIBLE,
  INDEX `FK_LNK_OBD_SRC_idx` (`uid_objd_source` ASC, `uid_mdl_source` ASC) VISIBLE,
  INDEX `FK_LNK_OBD_TAR_idx` (`uid_objd_target` ASC, `uid_mdl_target` ASC) VISIBLE,
  INDEX `FK_LNKD_USR_CREATE_idx` (`cuser` ASC) VISIBLE,
  INDEX `FK_LNKD_USR_UPDATE_idx` (`uuser` ASC) VISIBLE,
  CONSTRAINT `FK_LNKD_OBD_SRC`
    FOREIGN KEY (`uid_objd_source` , `uid_mdl_source`)
    REFERENCES `a0000_def_objects` (`uid` , `uid_mdl`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_LNKD_OBD_TAR`
    FOREIGN KEY (`uid_objd_target` , `uid_mdl_target`)
    REFERENCES `a0000_def_objects` (`uid` , `uid_mdl`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_LNKD_USR_CREATE`
    FOREIGN KEY (`cuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_LNKD_USR_UPDATE`
    FOREIGN KEY (`uuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Définition des liens entre objet - LNKD';


-- -----------------------------------------------------
-- Table `a0000_def_links_meta`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `a0000_def_links_meta` ;

CREATE TABLE IF NOT EXISTS `a0000_def_links_meta` (
  `uid` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT,
  `bid` VARCHAR(150) UNICODE NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `data_title` VARCHAR(25) NOT NULL,
  `data_type` ENUM('Text', 'Integer', 'Double', 'File','JSON','ObjectReference', 'LinkReference', 'ObjectDefReference','LinkDefReference', 'Other') NOT NULL,
  `data_pattern` VARCHAR(100) NULL DEFAULT NULL,
  `data_options` JSON NULL DEFAULT NULL,
  `data_sqlname` VARCHAR(20) NULL DEFAULT NULL,
  `data_sqlorder` INT NOT NULL DEFAULT 1,
  `comment` TEXT NULL DEFAULT NULL,
  `jsondata` JSON NULL DEFAULT NULL,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cuser` INT(10) ZEROFILL NOT NULL,
  `udate` TIMESTAMP NULL DEFAULT NULL,
  `uuser` INT(10) ZEROFILL NULL,
  `deleted` TINYINT NOT NULL DEFAULT 0,
  `uid_lnkd` INT(10) ZEROFILL NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE INDEX `bid_UNIQUE` (`bid` ASC) VISIBLE,
  INDEX `FK_MLNDUSR_UPDATE_idx` (`uuser` ASC) VISIBLE,
  INDEX `FK_MLNDUSR_CREATE_idx` (`cuser` ASC) VISIBLE,
  INDEX `FK_MLNDLNKD_UID_idx` (`uid_lnkd` ASC) VISIBLE,
  CONSTRAINT `FK_MLNDUSR_CREATE`
    FOREIGN KEY (`cuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_MLNDUSR_UPDATE`
    FOREIGN KEY (`uuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_MLNDLNKD_UID`
    FOREIGN KEY (`uid_lnkd`)
    REFERENCES `a0000_def_links` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Définition des metadonnées sur les liens entre objets - MLND';


-- -----------------------------------------------------
-- Table `z0000_log_data`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `z0000_log_data` ;

CREATE TABLE IF NOT EXISTS `z0000_log_data` (
  `uid` INT(10) ZEROFILL NOT NULL,
  `bid` VARCHAR(150) UNICODE NOT NULL,
  `log_action_type` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE INDEX `bid_UNIQUE` (`bid` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `a1000_objects`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `a1000_objects` ;

CREATE TABLE IF NOT EXISTS `a1000_objects` (
  `uid` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT,
  `uid_objd` INT(10) ZEROFILL NOT NULL,
  `uid_mdl` INT(10) ZEROFILL NOT NULL,
  `uid_obj` INT(10) ZEROFILL NULL,
  `bid_obj` VARCHAR(150) UNICODE NOT NULL,
  `ver_obj` VARCHAR(10) NOT NULL,
  `rev_obj` INT(3) ZEROFILL NOT NULL,
  PRIMARY KEY (`uid`),
  INDEX `FK_OBJ_OBJD_UID_idx` (`uid_objd` ASC, `uid_mdl` ASC) VISIBLE,
  CONSTRAINT `FK_OBJ_OBJD_UID`
    FOREIGN KEY (`uid_objd` , `uid_mdl`)
    REFERENCES `a0000_def_objects` (`uid` , `uid_mdl`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Objets';


-- -----------------------------------------------------
-- Table `a1000_links`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `a1000_links` ;

CREATE TABLE IF NOT EXISTS `a1000_links` (
  `uid` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT,
  `uid_lnkd` INT(10) ZEROFILL NOT NULL,
  `bid_lnk` VARCHAR(150) UNICODE NOT NULL,
  `uid_obj_source` INT(10) ZEROFILL NOT NULL,
  `uid_obj_target` INT(10) ZEROFILL NOT NULL,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cuser` INT(10) ZEROFILL NOT NULL,
  `udate` TIMESTAMP NULL DEFAULT NULL,
  `uuser` INT(10) ZEROFILL NULL,
  `deleted` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  INDEX `FK_LNK_LNKD_UID_idx` (`uid_lnkd` ASC) VISIBLE,
  INDEX `FK_LNK_OBJ_UIDSRC_idx` (`uid_obj_source` ASC) VISIBLE,
  INDEX `FK_LNK_OBJ_UIDTRG_idx` (`uid_obj_target` ASC) VISIBLE,
  INDEX `FK_LNK_USR_CREATE_idx` (`cuser` ASC) VISIBLE,
  INDEX `FK_LNK_USR_UPDATE_idx` (`uuser` ASC) VISIBLE,
  CONSTRAINT `FK_LNK_LNKD_UID`
    FOREIGN KEY (`uid_lnkd`)
    REFERENCES `a0000_def_links` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_LNK_OBJ_UIDSRC`
    FOREIGN KEY (`uid_obj_source`)
    REFERENCES `a1000_objects` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_LNK_OBJ_UIDTRG`
    FOREIGN KEY (`uid_obj_target`)
    REFERENCES `a1000_objects` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_LNK_USR_CREATE`
    FOREIGN KEY (`cuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_LNK_USR_UPDATE`
    FOREIGN KEY (`uuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `a1000_objects_meta`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `a1000_objects_meta` ;

CREATE TABLE IF NOT EXISTS `a1000_objects_meta` (
  `uid` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT,
  `uid_mobd` INT(10) ZEROFILL NOT NULL,
  `uid_obj` INT(10) ZEROFILL NULL,
  `value` JSON NULL DEFAULT NULL,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cuser` INT(10) ZEROFILL NOT NULL,
  `udate` TIMESTAMP NULL DEFAULT NULL,
  `uuser` INT(10) ZEROFILL NULL,
  `deleted` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  INDEX `FK_MOBJ_MOBD_UID_idx` (`uid_mobd` ASC) VISIBLE,
  INDEX `FK_MOBJ_OBJ_UID_idx` (`uid_obj` ASC) VISIBLE,
  INDEX `FK_MOBJ_USR_CREATE_idx` (`cuser` ASC) VISIBLE,
  INDEX `FK_MOBJ_USR_UPDATE_idx` (`uuser` ASC) VISIBLE,
  CONSTRAINT `FK_MOBJ_MOBD_UID`
    FOREIGN KEY (`uid_mobd`)
    REFERENCES `a0000_def_objects_meta` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_MOBJ_OBJ_UID`
    FOREIGN KEY (`uid_obj`)
    REFERENCES `a1000_objects` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_MOBJ_USR_CREATE`
    FOREIGN KEY (`cuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_MOBJ_USR_UPDATE`
    FOREIGN KEY (`uuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `a1000_links_meta`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `a1000_links_meta` ;

CREATE TABLE IF NOT EXISTS `a1000_links_meta` (
  `uid` INT(10) ZEROFILL NOT NULL AUTO_INCREMENT,
  `uid_lnkd` INT(10) ZEROFILL NOT NULL,
  `uid_lnk` INT(10) ZEROFILL NOT NULL,
  `value` JSON NULL DEFAULT NULL,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cuser` INT(10) ZEROFILL NOT NULL,
  `udate` TIMESTAMP NULL DEFAULT NULL,
  `uuser` INT(10) ZEROFILL NULL,
  `deleted` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  INDEX `FK_MLNK_USR_CREATE_idx` (`cuser` ASC) VISIBLE,
  INDEX `FK_MLNK_USR_UPDATE_idx` (`uuser` ASC) VISIBLE,
  INDEX `FK_MLNK_MLNKD_UID_idx` (`uid_lnkd` ASC) VISIBLE,
  INDEX `FK_MLNK_LNK_UID_idx` (`uid_lnk` ASC) VISIBLE,
  CONSTRAINT `FK_MLNK_MLNKD_UID`
    FOREIGN KEY (`uid_lnkd`)
    REFERENCES `a0000_def_links_meta` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_MLNK_LNK_UID`
    FOREIGN KEY (`uid_lnk`)
    REFERENCES `a1000_links` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_MLNK_USR_CREATE`
    FOREIGN KEY (`cuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `FK_MLNK_USR_UPDATE`
    FOREIGN KEY (`uuser`)
    REFERENCES `z0000_users` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------------------------------
-- Comptes Utilisateur Système et DBA
-- -----------------------------------------------------------------------------
INSERT INTO `z0000_users`(`bid`,`label`,`name`) VALUES ('admin@%', 'Administrateur', 'Compte Administrateur du système');
INSERT INTO `z0000_users`(`bid`,`label`,`name`) VALUES ('root@%', 'Super Administrateur - DBA', 'Compte DBA du SGBD');
