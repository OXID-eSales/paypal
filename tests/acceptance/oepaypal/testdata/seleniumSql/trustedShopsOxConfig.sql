-- phpMyAdmin SQL Dump
-- version 3.3.2deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 10, 2013 at 12:05 PM
-- Server version: 5.1.41
-- PHP Version: 5.3.2-1ubuntu4.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `oxid_fe_ee`
--

-- --------------------------------------------------------

--
-- Table structure for table `oxconfig`
--

CREATE TABLE IF NOT EXISTS `oxconfig` (
  `OXID` char(32) COLLATE latin1_general_ci NOT NULL COMMENT 'Config id',
  `OXSHOPID` int(11) NOT NULL DEFAULT '1' COMMENT 'Shop id (oxshops)',
  `OXMODULE` varchar(32) COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'Module or theme specific config (theme:themename, module:modulename)',
  `OXVARNAME` char(32) COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'Variable name',
  `OXVARTYPE` varchar(16) COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'Variable type',
  `OXVARVALUE` blob NOT NULL COMMENT 'Variable value',
  `OXTIMESTAMP` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp',
  PRIMARY KEY (`OXID`),
  KEY `OXVARNAME` (`OXVARNAME`),
  KEY `listall` (`OXSHOPID`,`OXMODULE`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Shop configuration values';

--
-- Dumping data for table `oxconfig`
--

REPLACE INTO `oxconfig` (`OXID`, `OXSHOPID`, `OXMODULE`, `OXVARNAME`, `OXVARTYPE`, `OXVARVALUE`) VALUES
('018f7ded2f677f00fd8dddce3a9a4b36', 1, '', 'aTsPassword', 'aarr', 0x4dba832f74e74df4cdd5af13525304e6162426d7a51f8819264ae8fad845a69ff40a268f518990e206ce1b7cb277df72c5bef4f48713),
('4a6256bd85c3fa0cd85c254a35b5275e', 1, '', 'aTsUser', 'aarr', 0x4dba832f74e74df4cdd5afa2e05304e6162426e54dfdc0efc3dc893bfadd0568e7e45f363aa7df938068e0ccfe8c9afa16011bfe6ef6c2f8babb448f634050616c8c28f5fbc3f958),
('52286a9a6e5ac6af62c03fafabffc78d', 1, '', 'iShopID_TrustedShops', 'aarr', 0x4dba832f74e74df4cdd5afa4565007251f7572173ad7657255132ce4df38ee321c4256f63cc88dc1e641b5e80949e62c312d99ae2153cc8ff58603dd8d2a),
('e14424d7b4be7de3388e9bb01ab35f06', 1, '', 'tsSealActive', 'bool', 0x07),
('5645a733ec7d4ac5d527560404fb1d4e', 1, '', 'tsSealType', 'aarr', 0x4dba322c77e44ef7ced6ac1039520541a886bd1da395e2c071aefd94),
('9c738e41aedcdb36b1c874b9a18df2eb', 1, '', 'tsTestMode', 'bool', 0x07);
