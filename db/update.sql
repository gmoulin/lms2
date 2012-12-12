SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';
USE `lms` ;

--
-- Table structure for table `alcohol`
--

DROP TABLE IF EXISTS `alcohol`;
CREATE TABLE IF NOT EXISTS `alcohol` (
	`alcoholID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`alcoholName` varchar(255) NOT NULL,
	`alcoholType` varchar(255) NOT NULL,
	`alcoholYear` year NOT NULL,
	`alcoholCover` longtext,
	`alcoholStorageFK` int(10) unsigned NOT NULL,
	`alcoholRating` tinyint(3) unsigned DEFAULT NULL,
	`alcoholOfferedBy` varchar(255) DEFAULT NULL,
	`alcoholDate` datetime NOT NULL,
	PRIMARY KEY (`alcoholID`),
	UNIQUE KEY `AlcoholUniqueIDX` (`alcoholName`,`alcoholType`),
	KEY `AlcoholTitleIDX` (`alcoholName`),
	KEY `AlcoholDateIDX` (`alcoholDate`),
	KEY `AlcoholYearIDX` (`alcoholYear`),
	KEY `alcoholOfferedByIDX` (`alcoholOfferedBy`),
	KEY `FK_Alcohols_Storages` (`alcoholStorageFK`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='informations sur les alcools' AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Table structure for table `alcohols_bands`
--

DROP TABLE IF EXISTS `alcohols_makers`;
CREATE TABLE IF NOT EXISTS `alcohols_makers` (
	`alcoholFK` int(10) unsigned NOT NULL,
	`makerFK` int(10) unsigned NOT NULL,
	PRIMARY KEY (`alcoholFK`,`makerFK`),
	KEY `FK_Makers` (`makerFK`),
	KEY `FK_Alcohols` (`alcoholFK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='liaison entre les alcools et les producteurs';

-- --------------------------------------------------------

--
-- Table structure for table `maker`
--

DROP TABLE IF EXISTS `maker`;
CREATE TABLE IF NOT EXISTS `maker` (
	`makerID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`makerName` varchar(255) NOT NULL,
	PRIMARY KEY (`makerID`),
	UNIQUE KEY `makerNameIDX` (`makerName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='informations sur les producteurs' AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Structure for view `alcohols_view`
--
DROP VIEW IF EXISTS `alcohols_view`;
CREATE  VIEW `alcohols_view` AS select `a`.`alcoholID` AS `alcoholID`,`a`.`alcoholName` AS `alcoholName`,`a`.`alcoholType` AS `alcoholType`,`a`.`alcoholYear` AS `alcoholYear`,`a`.`alcoholRating` AS `alcoholRating`,`a`.`alcoholOfferedBy` AS `alcoholOfferedBy`,`a`.`alcoholCover` AS `alcoholCover`,`a`.`alcoholDate` AS `alcoholDate`,`st`.`storageID` AS `storageID`,`st`.`storageRoom` AS `storageRoom`,`st`.`storageType` AS `storageType`,`st`.`storageColumn` AS `storageColumn`,`st`.`storageLine` AS `storageLine` from ((`alcohol` `a` join `storage` `st` on((`a`.`alcoholStorageFK` = `st`.`storageID`))));

-- --------------------------------------------------------

--
-- Structure for view `alcohol_makers_view`
--
DROP VIEW IF EXISTS `alcohol_makers_view`;
CREATE  VIEW `alcohol_makers_view` AS select distinct `ab`.`alcoholFK` AS `alcoholFK`,`b`.`makerID` AS `makerID`,`b`.`makerName` AS `makerName` from (`alcohols_makers` `ab` join `maker` `b` on((`ab`.`makerFK` = `b`.`makerID`)));

--
-- Constraints for table `alcohol`
--
ALTER TABLE `alcohol`
ADD CONSTRAINT `FK_Alcohols_Storages` FOREIGN KEY (`alcoholStorageFK`) REFERENCES `storage` (`storageID`) ON UPDATE NO ACTION;

--
-- Constraints for table `alcohols_makers`
--
ALTER TABLE `alcohols_makers`
ADD CONSTRAINT `FK_Alcohols` FOREIGN KEY (`alcoholFK`) REFERENCES `alcohol` (`alcoholID`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `FK_Makers` FOREIGN KEY (`makerFK`) REFERENCES `maker` (`makerID`) ON DELETE CASCADE ON UPDATE NO ACTION;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

