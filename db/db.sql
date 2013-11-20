-- MySQL dump 10.13  Distrib 5.1.66, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: dianms2
-- ------------------------------------------------------
-- Server version	5.1.66-0+squeeze1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Alerts`
--

DROP TABLE IF EXISTS `Alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Alerts` (
  `IdAlert` int(4) NOT NULL AUTO_INCREMENT,
  `IdDiagram` int(4) NOT NULL,
  `IdObject` varchar(4) COLLATE utf8_spanish_ci NOT NULL,
  `object` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `host` int(50) unsigned NOT NULL,
  `status` varchar(20) COLLATE utf8_spanish_ci NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `count` int(4) NOT NULL,
  PRIMARY KEY (`IdAlert`),
  KEY `IdObject` (`IdObject`),
  KEY `ts` (`ts`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Diagrams`
--

DROP TABLE IF EXISTS `Diagrams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Diagrams` (
  `IdDiagram` int(4) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `content` longblob NOT NULL,
  `active` varchar(4) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `idUser` int(4) NOT NULL,
  `idDiagramGroup` int(4) NOT NULL,
  `idDiagramStatus` int(4) NOT NULL,
  `period` int(4) NOT NULL,
  PRIMARY KEY (`IdDiagram`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `DiagramsGraph`
--

DROP TABLE IF EXISTS `DiagramsGraph`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DiagramsGraph` (
  `IdDiagramGraph` int(4) NOT NULL AUTO_INCREMENT,
  `idDiagram` int(4) NOT NULL,
  `idObject` varchar(4) COLLATE utf8_spanish_ci DEFAULT NULL,
  `idGraph` int(4) NOT NULL,
  `host` int(50) unsigned NOT NULL,
  `instance` int(10) NOT NULL,
  `label` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  `community` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`IdDiagramGraph`),
  UNIQUE KEY `index` (`idDiagram`,`idGraph`,`host`,`instance`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `DiagramsGroups`
--

DROP TABLE IF EXISTS `DiagramsGroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DiagramsGroups` (
  `IdDiagramGroup` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`IdDiagramGroup`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `DiagramsStatus`
--

DROP TABLE IF EXISTS `DiagramsStatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DiagramsStatus` (
  `IdDiagramStatus` int(4) NOT NULL,
  `description` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `level` varchar(10) COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`IdDiagramStatus`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `GraphAvailable`
--

DROP TABLE IF EXISTS `GraphAvailable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GraphAvailable` (
  `idGraph` int(4) NOT NULL,
  `idObjectType` int(4) NOT NULL,
  UNIQUE KEY `idGraph` (`idGraph`,`idObjectType`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Groups`
--

DROP TABLE IF EXISTS `Groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Groups` (
  `IdGroup` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`IdGroup`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `History`
--

DROP TABLE IF EXISTS `History`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `History` (
  `IdHistory` int(4) NOT NULL AUTO_INCREMENT,
  `IdDiagram` int(4) NOT NULL,
  `content` longblob NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`IdHistory`,`ts`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Settings`
--

DROP TABLE IF EXISTS `Settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Settings` (
  `IdSetting` int(4) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  `value` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`IdSetting`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Users` (
  `IdUser` int(4) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) COLLATE utf8_spanish_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `idGroup` int(4) NOT NULL,
  PRIMARY KEY (`IdUser`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Table structure for table `Graph`
--

DROP TABLE IF EXISTS `Graph`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Graph` (
  `IdGraph` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`IdGraph`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Graph`
--

LOCK TABLES `Graph` WRITE;
INSERT INTO `Graph` VALUES (1,'Trafico'),(2,'Cpu'),(6,'CCQ'),(4,'Ping'),(5,'Stations');
UNLOCK TABLES;

--
-- Table structure for table `ObjectType`
--

DROP TABLE IF EXISTS `ObjectType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ObjectType` (
  `IdObjectType` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `period` int(4) NOT NULL,
  PRIMARY KEY (`IdObjectType`)
) ENGINE=MyISAM AUTO_INCREMENT=1002 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ObjectType`
--

LOCK TABLES `ObjectType` WRITE;
/*!40000 ALTER TABLE `ObjectType` DISABLE KEYS */;
INSERT INTO `ObjectType` VALUES (1000,'DIANMS-IfStatus',60),(1,'DIANMS-snmp-server',300),(2,'DIANMS-PING-Rectangle',60),(3,'DIANMS-PING-Wireless',60),(4,'DIANMS-ubiquiti-stations',600),(5,'DIANMS-PING-Router',60);
/*!40000 ALTER TABLE `ObjectType` ENABLE KEYS */;
UNLOCK TABLES;
