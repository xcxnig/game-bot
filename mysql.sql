-- Adminer 4.2.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `game`;
CREATE TABLE `game` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(20) DEFAULT NULL,
  `project` varchar(20) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `data` float DEFAULT NULL,
  `result` varchar(20) DEFAULT NULL,
  `expect` varchar(20) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 2019-08-01 05:26:14
