-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 12, 2012 at 11:55 AM
-- Server version: 5.5.28
-- PHP Version: 5.3.10-1ubuntu3.4

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `lms`
--
DROP SCHEMA IF EXISTS `lms` ;
CREATE SCHEMA IF NOT EXISTS `lms` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `lms`;

-- --------------------------------------------------------

--
-- Table structure for table `album`
--

DROP TABLE IF EXISTS `album`;
CREATE TABLE IF NOT EXISTS `album` (
	`albumID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`albumTitle` varchar(255) NOT NULL,
	`albumType` varchar(255) NOT NULL,
	`albumCover` longtext,
	`albumStorageFK` int(10) unsigned NOT NULL,
	`albumLoanFK` int(10) unsigned DEFAULT NULL,
	`albumDate` datetime NOT NULL,
	PRIMARY KEY (`albumID`),
	UNIQUE KEY `AlbumUniqueIDX` (`albumTitle`,`albumType`),
	KEY `AlbumTitleIDX` (`albumTitle`),
	KEY `AlbumDateIDX` (`albumDate`),
	KEY `FK_Albums_Storages` (`albumStorageFK`),
	KEY `FK_Albums_Loans` (`albumLoanFK`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='informations sur les albums' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `albums_bands`
--

DROP TABLE IF EXISTS `albums_bands`;
CREATE TABLE IF NOT EXISTS `albums_bands` (
	`albumFK` int(10) unsigned NOT NULL,
	`bandFK` int(10) unsigned NOT NULL,
	PRIMARY KEY (`albumFK`,`bandFK`),
	KEY `FK_Bands` (`bandFK`),
	KEY `FK_Albums` (`albumFK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='liaison entre les albums et les groupes';

-- --------------------------------------------------------

--
-- Stand-in structure for view `albums_view`
--
DROP VIEW IF EXISTS `albums_view`;
CREATE TABLE IF NOT EXISTS `albums_view` (
	`albumID` int(10) unsigned
	,`albumTitle` varchar(255)
	,`albumType` varchar(255)
	,`albumCover` longtext
	,`albumDate` datetime
	,`storageID` int(10) unsigned
	,`storageRoom` varchar(255)
	,`storageType` varchar(255)
	,`storageColumn` char(1)
	,`storageLine` tinyint(3) unsigned
	,`loanID` int(10) unsigned
	,`loanHolder` varchar(255)
	,`loanDate` datetime
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `album_bands_view`
--
DROP VIEW IF EXISTS `album_bands_view`;
CREATE TABLE IF NOT EXISTS `album_bands_view` (
	`albumFK` int(10) unsigned
	,`bandID` int(10) unsigned
	,`bandName` varchar(255)
	,`bandGenre` varchar(255)
	,`bandWebSite` varchar(255)
	,`bandLastCheckDate` datetime
);
-- --------------------------------------------------------

--
-- Table structure for table `alcohol`
--

DROP TABLE IF EXISTS `alcohol`;
CREATE TABLE IF NOT EXISTS `alcohol` (
	`alcoholID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`alcoholName` varchar(255) NOT NULL,
	`alcoholType` varchar(255) NOT NULL,
	`alcoholYear` year(4) NOT NULL,
	`alcoholCover` longtext,
	`alcoholStorageFK` int(10) unsigned NOT NULL,
	`alcoholRating` tinyint(3) unsigned DEFAULT NULL,
	`alcoholDate` datetime NOT NULL,
	PRIMARY KEY (`alcoholID`),
	UNIQUE KEY `AlcoholUniqueIDX` (`alcoholName`,`alcoholType`),
	KEY `AlcoholTitleIDX` (`alcoholName`),
	KEY `AlcoholDateIDX` (`alcoholDate`),
	KEY `FK_Alcohols_Storages` (`alcoholStorageFK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='informations sur les alcools' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `alcohols_makers`
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
-- Stand-in structure for view `alcohols_view`
--
DROP VIEW IF EXISTS `alcohols_view`;
CREATE TABLE IF NOT EXISTS `alcohols_view` (
	`alcoholID` int(10) unsigned
	,`alcoholName` varchar(255)
	,`alcoholType` varchar(255)
	,`alcoholYear` year(4)
	,`alcoholCover` longtext
	,`alcoholDate` datetime
	,`storageID` int(10) unsigned
	,`storageRoom` varchar(255)
	,`storageType` varchar(255)
	,`storageColumn` char(1)
	,`storageLine` tinyint(3) unsigned
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `alcohol_makers_view`
--
DROP VIEW IF EXISTS `alcohol_makers_view`;
CREATE TABLE IF NOT EXISTS `alcohol_makers_view` (
	`alcoholFK` int(10) unsigned
	,`makerID` int(10) unsigned
	,`makerName` varchar(255)
);
-- --------------------------------------------------------

--
-- Table structure for table `artist`
--

DROP TABLE IF EXISTS `artist`;
CREATE TABLE IF NOT EXISTS `artist` (
	`artistID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`artistFirstName` varchar(255) NOT NULL,
	`artistLastName` varchar(255) NOT NULL,
	`artistPhoto` longtext,
	PRIMARY KEY (`artistID`),
	UNIQUE KEY `ArtistNameIDX` (`artistFirstName`,`artistLastName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='informations sur les artistes' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `author`
--

DROP TABLE IF EXISTS `author`;
CREATE TABLE IF NOT EXISTS `author` (
	`authorID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`authorFirstName` varchar(255) NOT NULL,
	`authorLastName` varchar(255) NOT NULL,
	`authorWebSite` varchar(255) DEFAULT NULL,
	`authorSearchURL` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`authorID`),
	UNIQUE KEY `AuthorNameIDX` (`authorFirstName`,`authorLastName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='informations sur les auteurs' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `band`
--

DROP TABLE IF EXISTS `band`;
CREATE TABLE IF NOT EXISTS `band` (
	`bandID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`bandName` varchar(255) NOT NULL,
	`bandGenre` varchar(255) NOT NULL,
	`bandWebSite` varchar(255) NOT NULL,
	`bandLastCheckDate` datetime DEFAULT NULL,
	PRIMARY KEY (`bandID`),
	UNIQUE KEY `BandNameIDX` (`bandName`,`bandGenre`),
	KEY `bandLastCheckDateIDX` (`bandLastCheckDate`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='informations sur les groupes' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `book`
--

DROP TABLE IF EXISTS `book`;
CREATE TABLE IF NOT EXISTS `book` (
	`bookID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`bookTitle` varchar(255) NOT NULL,
	`bookCover` longtext,
	`bookSize` varchar(255) NOT NULL,
	`bookSagaFK` int(10) unsigned DEFAULT NULL,
	`bookSagaPosition` tinyint(3) unsigned DEFAULT NULL,
	`bookStorageFK` int(10) unsigned NOT NULL,
	`bookLoanFK` int(10) unsigned DEFAULT NULL,
	`bookDate` datetime NOT NULL,
	PRIMARY KEY (`bookID`),
	UNIQUE KEY `BookUniqueIDX` (`bookTitle`,`bookSize`,`bookSagaFK`),
	KEY `BookTitleIDX` (`bookTitle`),
	KEY `BookSizeIDX` (`bookSize`),
	KEY `BookDateIDX` (`bookDate`),
	KEY `FK_Books_Storages` (`bookStorageFK`),
	KEY `FK_Books_Sagas` (`bookSagaFK`),
	KEY `FK_Books_Loans` (`bookLoanFK`),
	KEY `BookSagaIDX` (`bookSagaFK`,`bookSagaPosition`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='informations sur les livres' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `books_authors`
--

DROP TABLE IF EXISTS `books_authors`;
CREATE TABLE IF NOT EXISTS `books_authors` (
	`bookFK` int(10) unsigned NOT NULL,
	`authorFK` int(10) unsigned NOT NULL,
	PRIMARY KEY (`bookFK`,`authorFK`),
	KEY `FK_Authors` (`authorFK`),
	KEY `FK_Books` (`bookFK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='liaison entre livres et auteurs';

-- --------------------------------------------------------

--
-- Stand-in structure for view `books_view`
--
DROP VIEW IF EXISTS `books_view`;
CREATE TABLE IF NOT EXISTS `books_view` (
	`bookID` int(10) unsigned
	,`bookTitle` varchar(255)
	,`bookSize` varchar(255)
	,`bookCover` longtext
	,`bookDate` datetime
	,`sagaID` int(10) unsigned
	,`sagaTitle` varchar(255)
	,`bookSagaPosition` tinyint(3) unsigned
	,`bookSagaSize` bigint(21)
	,`sagaSearchURL` varchar(255)
	,`storageID` int(10) unsigned
	,`storageRoom` varchar(255)
	,`storageType` varchar(255)
	,`storageColumn` char(1)
	,`storageLine` tinyint(3) unsigned
	,`loanID` int(10) unsigned
	,`loanHolder` varchar(255)
	,`loanDate` datetime
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `book_authors_view`
--
DROP VIEW IF EXISTS `book_authors_view`;
CREATE TABLE IF NOT EXISTS `book_authors_view` (
	`bookFK` int(10) unsigned
	,`authorID` int(10) unsigned
	,`authorFirstName` varchar(255)
	,`authorLastName` varchar(255)
	,`authorWebSite` varchar(255)
	,`authorSearchURL` varchar(255)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `book_saga_count_view`
--
DROP VIEW IF EXISTS `book_saga_count_view`;
CREATE TABLE IF NOT EXISTS `book_saga_count_view` (
	`bookSagaFK` int(10) unsigned
	,`bookSagaSize` bigint(21)
);
-- --------------------------------------------------------

--
-- Table structure for table `loan`
--

DROP TABLE IF EXISTS `loan`;
CREATE TABLE IF NOT EXISTS `loan` (
	`loanID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`loanHolder` varchar(255) NOT NULL,
	`loanDate` datetime NOT NULL,
	PRIMARY KEY (`loanID`),
	KEY `LoanHolderIDX` (`loanHolder`),
	KEY `LoanDateIDX` (`loanDate`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='gestion des prêts' AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='informations sur les producteurs' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `movie`
--

DROP TABLE IF EXISTS `movie`;
CREATE TABLE IF NOT EXISTS `movie` (
	`movieID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`movieTitle` varchar(255) NOT NULL,
	`movieGenre` varchar(255) NOT NULL,
	`movieCover` longtext,
	`movieMediaType` varchar(255) NOT NULL,
	`movieLength` smallint(6) NOT NULL,
	`movieSagaFK` int(10) unsigned DEFAULT NULL,
	`movieSagaPosition` tinyint(3) unsigned DEFAULT NULL,
	`movieStorageFK` int(10) unsigned NOT NULL,
	`movieLoanFK` int(10) unsigned DEFAULT NULL,
	`movieDate` datetime NOT NULL,
	PRIMARY KEY (`movieID`),
	UNIQUE KEY `MovieUniqueIDX` (`movieTitle`,`movieMediaType`,`movieSagaFK`),
	KEY `MovieDateIDX` (`movieDate`),
	KEY `MovieMediaTypeIDX` (`movieMediaType`),
	KEY `MovieGenreIDX` (`movieGenre`),
	KEY `FK_Movies_Storages` (`movieStorageFK`),
	KEY `FK_Movies_Sagas` (`movieSagaFK`),
	KEY `FK_Movies_Loans` (`movieLoanFK`),
	KEY `MovieSagaIDX` (`movieSagaFK`,`movieSagaPosition`),
	KEY `MovieTitleIDX` (`movieTitle`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='informations sur les films' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `movies_artists`
--

DROP TABLE IF EXISTS `movies_artists`;
CREATE TABLE IF NOT EXISTS `movies_artists` (
	`movieFK` int(10) unsigned NOT NULL,
	`artistFK` int(10) unsigned NOT NULL,
	PRIMARY KEY (`movieFK`,`artistFK`),
	KEY `FK_Artists` (`artistFK`),
	KEY `FK_Movies` (`movieFK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='liaison entre films et artistes';

-- --------------------------------------------------------

--
-- Stand-in structure for view `movies_view`
--
DROP VIEW IF EXISTS `movies_view`;
CREATE TABLE IF NOT EXISTS `movies_view` (
	`movieID` int(10) unsigned
	,`movieTitle` varchar(255)
	,`movieCover` longtext
	,`movieMediaType` varchar(255)
	,`movieLength` smallint(6)
	,`movieGenre` varchar(255)
	,`movieDate` datetime
	,`sagaID` int(10) unsigned
	,`sagaTitle` varchar(255)
	,`movieSagaPosition` tinyint(3) unsigned
	,`movieSagaSize` bigint(21)
	,`sagaSearchURL` varchar(255)
	,`storageID` int(10) unsigned
	,`storageRoom` varchar(255)
	,`storageType` varchar(255)
	,`storageColumn` char(1)
	,`storageLine` tinyint(3) unsigned
	,`loanID` int(10) unsigned
	,`loanHolder` varchar(255)
	,`loanDate` datetime
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `movie_artists_view`
--
DROP VIEW IF EXISTS `movie_artists_view`;
CREATE TABLE IF NOT EXISTS `movie_artists_view` (
	`movieFK` int(10) unsigned
	,`artistID` int(10) unsigned
	,`artistFirstName` varchar(255)
	,`artistLastName` varchar(255)
	,`artistPhoto` longtext
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `movie_saga_count_view`
--
DROP VIEW IF EXISTS `movie_saga_count_view`;
CREATE TABLE IF NOT EXISTS `movie_saga_count_view` (
	`movieSagaFK` int(10) unsigned
	,`movieSagaSize` bigint(21)
);
-- --------------------------------------------------------

--
-- Table structure for table `saga`
--

DROP TABLE IF EXISTS `saga`;
CREATE TABLE IF NOT EXISTS `saga` (
	`sagaID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`sagaTitle` varchar(255) NOT NULL,
	`sagaSearchURL` varchar(255) DEFAULT NULL,
	`sagaLastCheckDate` datetime DEFAULT NULL,
	`sagaRating` tinyint(3) unsigned DEFAULT NULL,
	PRIMARY KEY (`sagaID`),
	UNIQUE KEY `SagaTitleIDX` (`sagaTitle`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='informations sur les sagas' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `storage`
--

DROP TABLE IF EXISTS `storage`;
CREATE TABLE IF NOT EXISTS `storage` (
	`storageID` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`storageRoom` varchar(255) NOT NULL,
	`storageType` varchar(255) NOT NULL,
	`storageColumn` char(1) DEFAULT NULL,
	`storageLine` tinyint(3) unsigned DEFAULT NULL,
	PRIMARY KEY (`storageID`),
	UNIQUE KEY `storageUniqueIDX` (`storageRoom`,`storageType`,`storageColumn`,`storageLine`),
	KEY `storageIDX` (`storageRoom`,`storageType`),
	KEY `storageCodeIDX` (`storageColumn`,`storageLine`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='lieux de rangement' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure for view `albums_view`
--
DROP TABLE IF EXISTS `albums_view`;

CREATE VIEW `albums_view` AS select `a`.`albumID` AS `albumID`,`a`.`albumTitle` AS `albumTitle`,`a`.`albumType` AS `albumType`,`a`.`albumCover` AS `albumCover`,`a`.`albumDate` AS `albumDate`,`st`.`storageID` AS `storageID`,`st`.`storageRoom` AS `storageRoom`,`st`.`storageType` AS `storageType`,`st`.`storageColumn` AS `storageColumn`,`st`.`storageLine` AS `storageLine`,`l`.`loanID` AS `loanID`,`l`.`loanHolder` AS `loanHolder`,`l`.`loanDate` AS `loanDate` from ((`album` `a` join `storage` `st` on((`a`.`albumStorageFK` = `st`.`storageID`))) left join `loan` `l` on((`a`.`albumLoanFK` = `l`.`loanID`)));

-- --------------------------------------------------------

--
-- Structure for view `album_bands_view`
--
DROP TABLE IF EXISTS `album_bands_view`;

CREATE VIEW `album_bands_view` AS select distinct `ab`.`albumFK` AS `albumFK`,`b`.`bandID` AS `bandID`,`b`.`bandName` AS `bandName`,`b`.`bandGenre` AS `bandGenre`,`b`.`bandWebSite` AS `bandWebSite`,`b`.`bandLastCheckDate` AS `bandLastCheckDate` from (`albums_bands` `ab` join `band` `b` on((`ab`.`bandFK` = `b`.`bandID`)));

-- --------------------------------------------------------

--
-- Structure for view `alcohols_view`
--
DROP TABLE IF EXISTS `alcohols_view`;

CREATE VIEW `alcohols_view` AS select `a`.`alcoholID` AS `alcoholID`,`a`.`alcoholName` AS `alcoholName`,`a`.`alcoholType` AS `alcoholType`,`a`.`alcoholYear` AS `alcoholYear`,`a`.`alcoholCover` AS `alcoholCover`,`a`.`alcoholDate` AS `alcoholDate`,`st`.`storageID` AS `storageID`,`st`.`storageRoom` AS `storageRoom`,`st`.`storageType` AS `storageType`,`st`.`storageColumn` AS `storageColumn`,`st`.`storageLine` AS `storageLine` from (`alcohol` `a` join `storage` `st` on((`a`.`alcoholStorageFK` = `st`.`storageID`)));

-- --------------------------------------------------------

--
-- Structure for view `alcohol_makers_view`
--
DROP TABLE IF EXISTS `alcohol_makers_view`;

CREATE VIEW `alcohol_makers_view` AS select distinct `ab`.`alcoholFK` AS `alcoholFK`,`b`.`makerID` AS `makerID`,`b`.`makerName` AS `makerName` from (`alcohols_makers` `ab` join `maker` `b` on((`ab`.`makerFK` = `b`.`makerID`)));

-- --------------------------------------------------------

--
-- Structure for view `books_view`
--
DROP TABLE IF EXISTS `books_view`;

CREATE VIEW `books_view` AS select `b`.`bookID` AS `bookID`,`b`.`bookTitle` AS `bookTitle`,`b`.`bookSize` AS `bookSize`,`b`.`bookCover` AS `bookCover`,`b`.`bookDate` AS `bookDate`,`s`.`sagaID` AS `sagaID`,`s`.`sagaTitle` AS `sagaTitle`,`b`.`bookSagaPosition` AS `bookSagaPosition`,`bscv`.`bookSagaSize` AS `bookSagaSize`,`s`.`sagaSearchURL` AS `sagaSearchURL`,`st`.`storageID` AS `storageID`,`st`.`storageRoom` AS `storageRoom`,`st`.`storageType` AS `storageType`,`st`.`storageColumn` AS `storageColumn`,`st`.`storageLine` AS `storageLine`,`l`.`loanID` AS `loanID`,`l`.`loanHolder` AS `loanHolder`,`l`.`loanDate` AS `loanDate` from ((((`book` `b` join `storage` `st` on((`b`.`bookStorageFK` = `st`.`storageID`))) left join `loan` `l` on((`b`.`bookLoanFK` = `l`.`loanID`))) left join `saga` `s` on((`b`.`bookSagaFK` = `s`.`sagaID`))) left join `book_saga_count_view` `bscv` on((`bscv`.`bookSagaFK` = `b`.`bookSagaFK`)));

-- --------------------------------------------------------

--
-- Structure for view `book_authors_view`
--
DROP TABLE IF EXISTS `book_authors_view`;

CREATE VIEW `book_authors_view` AS select distinct `ba`.`bookFK` AS `bookFK`,`a`.`authorID` AS `authorID`,`a`.`authorFirstName` AS `authorFirstName`,`a`.`authorLastName` AS `authorLastName`,`a`.`authorWebSite` AS `authorWebSite`,`a`.`authorSearchURL` AS `authorSearchURL` from (`books_authors` `ba` join `author` `a` on((`ba`.`authorFK` = `a`.`authorID`)));

-- --------------------------------------------------------

--
-- Structure for view `book_saga_count_view`
--
DROP TABLE IF EXISTS `book_saga_count_view`;

CREATE VIEW `book_saga_count_view` AS select distinct `book`.`bookSagaFK` AS `bookSagaFK`,count(`book`.`bookSagaFK`) AS `bookSagaSize` from `book` group by `book`.`bookSagaFK`;

-- --------------------------------------------------------

--
-- Structure for view `movies_view`
--
DROP TABLE IF EXISTS `movies_view`;

CREATE VIEW `movies_view` AS select `m`.`movieID` AS `movieID`,`m`.`movieTitle` AS `movieTitle`,`m`.`movieCover` AS `movieCover`,`m`.`movieMediaType` AS `movieMediaType`,`m`.`movieLength` AS `movieLength`,`m`.`movieGenre` AS `movieGenre`,`m`.`movieDate` AS `movieDate`,`s`.`sagaID` AS `sagaID`,`s`.`sagaTitle` AS `sagaTitle`,`m`.`movieSagaPosition` AS `movieSagaPosition`,`mscv`.`movieSagaSize` AS `movieSagaSize`,`s`.`sagaSearchURL` AS `sagaSearchURL`,`st`.`storageID` AS `storageID`,`st`.`storageRoom` AS `storageRoom`,`st`.`storageType` AS `storageType`,`st`.`storageColumn` AS `storageColumn`,`st`.`storageLine` AS `storageLine`,`p`.`loanID` AS `loanID`,`p`.`loanHolder` AS `loanHolder`,`p`.`loanDate` AS `loanDate` from ((((`movie` `m` join `storage` `st` on((`m`.`movieStorageFK` = `st`.`storageID`))) left join `loan` `p` on((`m`.`movieLoanFK` = `p`.`loanID`))) left join `saga` `s` on((`m`.`movieSagaFK` = `s`.`sagaID`))) left join `movie_saga_count_view` `mscv` on((`mscv`.`movieSagaFK` = `m`.`movieSagaFK`)));

-- --------------------------------------------------------

--
-- Structure for view `movie_artists_view`
--
DROP TABLE IF EXISTS `movie_artists_view`;

CREATE VIEW `movie_artists_view` AS select distinct `fa`.`movieFK` AS `movieFK`,`a`.`artistID` AS `artistID`,`a`.`artistFirstName` AS `artistFirstName`,`a`.`artistLastName` AS `artistLastName`,`a`.`artistPhoto` AS `artistPhoto` from (`movies_artists` `fa` join `artist` `a` on((`fa`.`artistFK` = `a`.`artistID`)));

-- --------------------------------------------------------

--
-- Structure for view `movie_saga_count_view`
--
DROP TABLE IF EXISTS `movie_saga_count_view`;

CREATE VIEW `movie_saga_count_view` AS select distinct `movie`.`movieSagaFK` AS `movieSagaFK`,count(`movie`.`movieSagaFK`) AS `movieSagaSize` from `movie` group by `movie`.`movieSagaFK`;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `album`
--
ALTER TABLE `album`
ADD CONSTRAINT `FK_Albums_Loans` FOREIGN KEY (`albumLoanFK`) REFERENCES `loan` (`loanID`) ON DELETE SET NULL ON UPDATE NO ACTION,
ADD CONSTRAINT `FK_Albums_Storages` FOREIGN KEY (`albumStorageFK`) REFERENCES `storage` (`storageID`) ON UPDATE NO ACTION;

--
-- Constraints for table `albums_bands`
--
ALTER TABLE `albums_bands`
ADD CONSTRAINT `FK_Albums` FOREIGN KEY (`albumFK`) REFERENCES `album` (`albumID`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `FK_Bands` FOREIGN KEY (`bandFK`) REFERENCES `band` (`bandID`) ON DELETE CASCADE ON UPDATE NO ACTION;

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

--
-- Constraints for table `book`
--
ALTER TABLE `book`
ADD CONSTRAINT `FK_Books_Loans` FOREIGN KEY (`bookLoanFK`) REFERENCES `loan` (`loanID`) ON DELETE SET NULL ON UPDATE NO ACTION,
ADD CONSTRAINT `FK_Books_Sagas` FOREIGN KEY (`bookSagaFK`) REFERENCES `saga` (`sagaID`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `FK_Books_Storages` FOREIGN KEY (`bookStorageFK`) REFERENCES `storage` (`storageID`) ON UPDATE NO ACTION;

--
-- Constraints for table `books_authors`
--
ALTER TABLE `books_authors`
ADD CONSTRAINT `FK_Authors` FOREIGN KEY (`authorFK`) REFERENCES `author` (`authorID`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `FK_Books` FOREIGN KEY (`bookFK`) REFERENCES `book` (`bookID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `movie`
--
ALTER TABLE `movie`
ADD CONSTRAINT `FK_Movies_Loans` FOREIGN KEY (`movieLoanFK`) REFERENCES `loan` (`loanID`) ON DELETE SET NULL ON UPDATE NO ACTION,
ADD CONSTRAINT `FK_Movies_Sagas` FOREIGN KEY (`movieSagaFK`) REFERENCES `saga` (`sagaID`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `FK_Movies_Storages` FOREIGN KEY (`movieStorageFK`) REFERENCES `storage` (`storageID`) ON UPDATE NO ACTION;

--
-- Constraints for table `movies_artists`
--
ALTER TABLE `movies_artists`
ADD CONSTRAINT `FK_Artists` FOREIGN KEY (`artistFK`) REFERENCES `artist` (`artistID`) ON DELETE CASCADE ON UPDATE NO ACTION,
ADD CONSTRAINT `FK_Movies` FOREIGN KEY (`movieFK`) REFERENCES `movie` (`movieID`) ON DELETE CASCADE ON UPDATE NO ACTION;
SET FOREIGN_KEY_CHECKS=1;
