-- Aesthetica — complete database (structure + demo data)
-- Import this file into an EMPTY database called `aesthetica`
-- phpMyAdmin → Import → choose this file → Go

/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: aesthetica
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `adverse_events`
--

DROP TABLE IF EXISTS `adverse_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `adverse_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` int(10) unsigned NOT NULL,
  `treatment_record_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(100) NOT NULL COMMENT 'bruising, swelling, nodule, vascular occlusion…',
  `severity` enum('mild','moderate','severe') NOT NULL DEFAULT 'mild',
  `description` text DEFAULT NULL,
  `action_taken` text DEFAULT NULL,
  `resolved` tinyint(1) NOT NULL DEFAULT 0,
  `reported_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reported_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_ae_patient` (`patient_id`),
  KEY `fk_ae_record` (`treatment_record_id`),
  KEY `fk_ae_user` (`reported_by`),
  CONSTRAINT `fk_ae_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ae_record` FOREIGN KEY (`treatment_record_id`) REFERENCES `treatment_records` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ae_user` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adverse_events`
--

LOCK TABLES `adverse_events` WRITE;
/*!40000 ALTER TABLE `adverse_events` DISABLE KEYS */;
INSERT INTO `adverse_events` VALUES
(1,4,NULL,'Bruising','mild','Bruising at left nasolabial injection point, noted at 48 hours.','Reassurance, arnica advised. Resolved by day 7.',1,'2026-09-06 05:43:51',2),
(2,8,NULL,'Nodule','moderate','Palpable nodule upper lip, 2 weeks post filler.','Massage demonstrated; reviewed at 4 weeks. Hyaluronidase discussed, not required.',0,'2026-10-03 05:43:51',1);
/*!40000 ALTER TABLE `adverse_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aftercare_templates`
--

DROP TABLE IF EXISTS `aftercare_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `aftercare_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(140) NOT NULL,
  `body` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aftercare_templates`
--

LOCK TABLES `aftercare_templates` WRITE;
/*!40000 ALTER TABLE `aftercare_templates` DISABLE KEYS */;
INSERT INTO `aftercare_templates` VALUES
(1,'Botulinum toxin — aftercare','Stay upright for 4 hours.\nNo rubbing or massaging the treated area for 24 hours.\nAvoid exercise, saunas and alcohol for 24 hours.\nFull effect appears at 10–14 days.\nAttend your two-week review so any asymmetry can be corrected.'),
(2,'Dermal filler — aftercare','Expect swelling and possible bruising for 2–5 days.\nApply cool compresses, not ice directly.\nNo makeup on the area for 12 hours.\nAvoid heat, exercise and alcohol for 24 hours.\nContact the clinic immediately if you notice blanching, severe pain or skin discolouration.'),
(3,'PDO threads — aftercare','Sleep on your back, elevated, for 5 nights.\nNo wide mouth opening or dental work for 2 weeks.\nNo facial massage or treatments for 4 weeks.\nMild tightness and puckering is normal early on.'),
(4,'Skin booster — aftercare','Small bumps settle within 24–48 hours.\nNo makeup for 12 hours.\nAvoid sun, sauna and strenuous exercise for 48 hours.\nSecond session is due in 4 weeks.');
/*!40000 ALTER TABLE `aftercare_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` int(10) unsigned NOT NULL,
  `practitioner_id` int(10) unsigned NOT NULL,
  `treatment_id` int(10) unsigned NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `status` enum('booked','confirmed','arrived','in_room','completed','no_show','cancelled') NOT NULL DEFAULT 'booked',
  `deposit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` varchar(500) DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ix_appt_day` (`starts_at`),
  KEY `ix_appt_prac` (`practitioner_id`,`starts_at`),
  KEY `ix_appt_patient` (`patient_id`),
  KEY `fk_appt_treatment` (`treatment_id`),
  KEY `fk_appt_user` (`created_by`),
  CONSTRAINT `fk_appt_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_appt_prac` FOREIGN KEY (`practitioner_id`) REFERENCES `practitioners` (`id`),
  CONSTRAINT `fk_appt_treatment` FOREIGN KEY (`treatment_id`) REFERENCES `treatments` (`id`),
  CONSTRAINT `fk_appt_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES
(1,1,1,2,'2026-07-30 09:00:00','2026-07-30 09:30:00','completed',0.00,NULL,5,'2026-07-27 09:00:00','2026-10-14 08:43:51'),
(2,2,2,3,'2026-08-01 10:30:00','2026-08-01 11:00:00','completed',0.00,NULL,5,'2026-07-29 10:30:00','2026-10-14 08:43:51'),
(3,3,3,4,'2026-08-02 11:00:00','2026-08-02 11:25:00','completed',0.00,NULL,5,'2026-07-30 11:00:00','2026-10-14 08:43:51'),
(4,4,1,5,'2026-08-04 12:30:00','2026-08-04 13:00:00','completed',0.00,NULL,5,'2026-08-01 12:30:00','2026-10-14 08:43:51'),
(5,5,2,6,'2026-08-06 13:00:00','2026-08-06 13:45:00','completed',0.00,NULL,5,'2026-08-03 13:00:00','2026-10-14 08:43:51'),
(6,6,3,7,'2026-08-08 14:30:00','2026-08-08 15:15:00','completed',0.00,NULL,5,'2026-08-05 14:30:00','2026-10-14 08:43:51'),
(7,7,1,8,'2026-08-09 15:00:00','2026-08-09 15:40:00','completed',0.00,NULL,5,'2026-08-06 15:00:00','2026-10-14 08:43:51'),
(8,8,2,9,'2026-08-11 16:30:00','2026-08-11 17:30:00','completed',0.00,NULL,5,'2026-08-08 16:30:00','2026-10-14 08:43:51'),
(9,9,3,10,'2026-08-13 09:00:00','2026-08-13 09:30:00','completed',0.00,NULL,5,'2026-08-10 09:00:00','2026-10-14 08:43:51'),
(10,10,1,2,'2026-08-15 10:30:00','2026-08-15 11:00:00','completed',0.00,NULL,5,'2026-08-12 10:30:00','2026-10-14 08:43:51'),
(11,11,2,3,'2026-08-16 11:00:00','2026-08-16 11:30:00','completed',0.00,NULL,5,'2026-08-13 11:00:00','2026-10-14 08:43:51'),
(12,12,3,4,'2026-08-18 12:30:00','2026-08-18 12:55:00','completed',0.00,NULL,5,'2026-08-15 12:30:00','2026-10-14 08:43:51'),
(13,13,1,5,'2026-08-20 13:00:00','2026-08-20 13:30:00','completed',0.00,NULL,5,'2026-08-17 13:00:00','2026-10-14 08:43:51'),
(14,14,2,6,'2026-08-21 14:30:00','2026-08-21 15:15:00','completed',0.00,NULL,5,'2026-08-18 14:30:00','2026-10-14 08:43:51'),
(15,1,3,7,'2026-08-23 15:00:00','2026-08-23 15:45:00','completed',0.00,NULL,5,'2026-08-20 15:00:00','2026-10-14 08:43:51'),
(16,2,1,8,'2026-08-25 16:30:00','2026-08-25 17:10:00','completed',0.00,NULL,5,'2026-08-22 16:30:00','2026-10-14 08:43:51'),
(17,3,2,9,'2026-08-27 09:00:00','2026-08-27 10:00:00','completed',0.00,NULL,5,'2026-08-24 09:00:00','2026-10-14 08:43:51'),
(18,4,3,10,'2026-08-28 10:30:00','2026-08-28 11:00:00','completed',0.00,NULL,5,'2026-08-25 10:30:00','2026-10-14 08:43:51'),
(19,5,1,2,'2026-08-30 11:00:00','2026-08-30 11:30:00','completed',0.00,NULL,5,'2026-08-27 11:00:00','2026-10-14 08:43:51'),
(20,6,2,3,'2026-09-01 12:30:00','2026-09-01 13:00:00','completed',0.00,NULL,5,'2026-08-29 12:30:00','2026-10-14 08:43:51'),
(21,7,3,4,'2026-09-03 13:00:00','2026-09-03 13:25:00','completed',0.00,NULL,5,'2026-08-31 13:00:00','2026-10-14 08:43:51'),
(22,8,1,5,'2026-09-04 14:30:00','2026-09-04 15:00:00','completed',0.00,NULL,5,'2026-09-01 14:30:00','2026-10-14 08:43:51'),
(23,9,2,6,'2026-09-06 15:00:00','2026-09-06 15:45:00','completed',0.00,NULL,5,'2026-09-03 15:00:00','2026-10-14 08:43:51'),
(24,10,3,7,'2026-09-08 16:30:00','2026-09-08 17:15:00','completed',0.00,NULL,5,'2026-09-05 16:30:00','2026-10-14 08:43:51'),
(25,11,1,8,'2026-09-10 09:00:00','2026-09-10 09:40:00','completed',0.00,NULL,5,'2026-09-07 09:00:00','2026-10-14 08:43:51'),
(26,12,2,9,'2026-09-11 10:30:00','2026-09-11 11:30:00','completed',0.00,NULL,5,'2026-09-08 10:30:00','2026-10-14 08:43:51'),
(27,13,3,10,'2026-09-13 11:00:00','2026-09-13 11:30:00','completed',0.00,NULL,5,'2026-09-10 11:00:00','2026-10-14 08:43:51'),
(28,14,1,2,'2026-09-15 12:30:00','2026-09-15 13:00:00','completed',0.00,NULL,5,'2026-09-12 12:30:00','2026-10-14 08:43:51'),
(29,1,2,3,'2026-09-16 13:00:00','2026-09-16 13:30:00','completed',0.00,NULL,5,'2026-09-13 13:00:00','2026-10-14 08:43:51'),
(30,2,3,4,'2026-09-18 14:30:00','2026-09-18 14:55:00','completed',0.00,NULL,5,'2026-09-15 14:30:00','2026-10-14 08:43:51'),
(31,3,1,5,'2026-09-20 15:00:00','2026-09-20 15:30:00','completed',0.00,NULL,5,'2026-09-17 15:00:00','2026-10-14 08:43:51'),
(32,4,2,6,'2026-09-22 16:30:00','2026-09-22 17:15:00','completed',0.00,NULL,5,'2026-09-19 16:30:00','2026-10-14 08:43:51'),
(33,5,3,7,'2026-09-23 09:00:00','2026-09-23 09:45:00','completed',0.00,NULL,5,'2026-09-20 09:00:00','2026-10-14 08:43:51'),
(34,6,1,8,'2026-09-25 10:30:00','2026-09-25 11:10:00','completed',0.00,NULL,5,'2026-09-22 10:30:00','2026-10-14 08:43:51'),
(35,7,2,9,'2026-09-27 11:00:00','2026-09-27 12:00:00','completed',0.00,NULL,5,'2026-09-24 11:00:00','2026-10-14 08:43:51'),
(36,8,3,10,'2026-09-29 12:30:00','2026-09-29 13:00:00','completed',0.00,NULL,5,'2026-09-26 12:30:00','2026-10-14 08:43:51'),
(37,9,1,2,'2026-09-30 13:00:00','2026-09-30 13:30:00','completed',0.00,NULL,5,'2026-09-27 13:00:00','2026-10-14 08:43:51'),
(38,10,2,3,'2026-10-02 14:30:00','2026-10-02 15:00:00','completed',0.00,NULL,5,'2026-09-29 14:30:00','2026-10-14 08:43:51'),
(39,11,3,4,'2026-10-04 15:00:00','2026-10-04 15:25:00','completed',0.00,NULL,5,'2026-10-01 15:00:00','2026-10-14 08:43:51'),
(40,12,1,5,'2026-10-05 16:30:00','2026-10-05 17:00:00','completed',0.00,NULL,5,'2026-10-02 16:30:00','2026-10-14 08:43:51'),
(41,13,2,6,'2026-10-07 09:00:00','2026-10-07 09:45:00','completed',0.00,NULL,5,'2026-10-04 09:00:00','2026-10-14 08:43:51'),
(42,14,3,7,'2026-10-09 10:30:00','2026-10-09 11:15:00','completed',0.00,NULL,5,'2026-10-06 10:30:00','2026-10-14 08:43:51'),
(43,1,1,8,'2026-10-11 11:00:00','2026-10-11 11:40:00','completed',0.00,NULL,5,'2026-10-08 11:00:00','2026-10-14 08:43:51'),
(44,2,2,9,'2026-10-12 12:30:00','2026-10-12 13:30:00','completed',0.00,NULL,5,'2026-10-09 12:30:00','2026-10-14 08:43:51'),
(45,3,3,10,'2026-10-14 13:00:00','2026-10-14 13:30:00','completed',0.00,NULL,5,'2026-10-11 13:00:00','2026-10-14 08:43:51'),
(46,1,1,3,'2026-10-14 09:30:00','2026-10-14 10:00:00','completed',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(47,2,1,1,'2026-10-14 10:15:00','2026-10-14 10:45:00','in_room',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(48,3,2,6,'2026-10-14 11:00:00','2026-10-14 11:45:00','confirmed',100.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(49,4,3,9,'2026-10-14 13:30:00','2026-10-14 14:30:00','confirmed',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(50,5,1,10,'2026-10-14 15:00:00','2026-10-14 15:30:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(51,6,2,4,'2026-10-14 16:30:00','2026-10-14 16:55:00','confirmed',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(52,10,3,7,'2026-10-14 17:15:00','2026-10-14 18:00:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(53,4,2,3,'2026-10-15 10:00:00','2026-10-15 10:30:00','confirmed',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(54,5,3,6,'2026-10-15 11:30:00','2026-10-15 12:15:00','confirmed',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(55,6,1,7,'2026-10-15 12:00:00','2026-10-15 12:45:00','confirmed',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(56,7,3,6,'2026-10-16 11:00:00','2026-10-16 11:45:00','confirmed',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(57,8,1,7,'2026-10-16 12:30:00','2026-10-16 13:15:00','confirmed',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(58,9,2,9,'2026-10-16 13:00:00','2026-10-16 14:00:00','confirmed',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(59,10,3,10,'2026-10-16 14:30:00','2026-10-16 15:00:00','confirmed',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(60,10,1,7,'2026-10-17 12:00:00','2026-10-17 12:45:00','confirmed',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(61,11,2,9,'2026-10-17 13:30:00','2026-10-17 14:30:00','confirmed',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(62,2,3,10,'2026-10-19 14:00:00','2026-10-19 14:30:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(63,3,1,1,'2026-10-19 15:30:00','2026-10-19 16:00:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(64,4,2,2,'2026-10-19 09:00:00','2026-10-19 09:30:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(65,5,3,3,'2026-10-19 10:30:00','2026-10-19 11:00:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(66,5,1,1,'2026-10-20 15:00:00','2026-10-20 15:30:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(67,6,2,2,'2026-10-20 09:30:00','2026-10-20 10:00:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(68,8,2,2,'2026-10-21 09:00:00','2026-10-21 09:30:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(69,9,3,3,'2026-10-21 10:30:00','2026-10-21 11:00:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(70,10,1,6,'2026-10-21 11:00:00','2026-10-21 11:45:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(71,11,3,3,'2026-10-22 10:00:00','2026-10-22 10:30:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(72,12,1,6,'2026-10-22 11:30:00','2026-10-22 12:15:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(73,13,2,7,'2026-10-22 12:00:00','2026-10-22 12:45:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(74,14,3,9,'2026-10-22 13:30:00','2026-10-22 14:30:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(75,14,1,6,'2026-10-23 11:00:00','2026-10-23 11:45:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(76,1,2,7,'2026-10-23 12:30:00','2026-10-23 13:15:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(77,3,2,7,'2026-10-24 12:00:00','2026-10-24 12:45:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(78,4,3,9,'2026-10-24 13:30:00','2026-10-24 14:30:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(79,5,1,10,'2026-10-24 14:00:00','2026-10-24 14:30:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(80,9,1,10,'2026-10-26 14:00:00','2026-10-26 14:30:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(81,10,2,1,'2026-10-26 15:30:00','2026-10-26 16:00:00','booked',0.00,NULL,5,'2026-10-14 08:43:51','2026-10-14 08:43:51');
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `entity` varchar(60) NOT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `detail` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ix_audit_user` (`user_id`,`created_at`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `batches`
--

DROP TABLE IF EXISTS `batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `batches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `supplier_id` int(10) unsigned DEFAULT NULL,
  `lot_number` varchar(60) NOT NULL,
  `expiry_date` date NOT NULL,
  `qty_received` decimal(10,2) NOT NULL,
  `qty_remaining` decimal(10,2) NOT NULL,
  `received_at` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_batches_product` (`product_id`,`expiry_date`),
  KEY `ix_batches_lot` (`lot_number`),
  KEY `fk_batches_supplier` (`supplier_id`),
  CONSTRAINT `fk_batches_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_batches_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `batches`
--

LOCK TABLES `batches` WRITE;
/*!40000 ALTER TABLE `batches` DISABLE KEYS */;
INSERT INTO `batches` VALUES
(1,1,1,'BX-4471-A','2027-12-14',1000.00,760.00,'2026-09-24'),
(2,1,2,'BX-5120-C','2026-11-07',500.00,180.00,'2026-09-15'),
(3,2,1,'DY-2208-B','2027-08-14',500.00,455.00,'2026-09-06'),
(4,3,2,'JUV-7741','2028-02-14',12.00,2.00,'2026-08-28'),
(5,4,1,'RST-3390','2027-11-14',8.00,6.00,'2026-08-19'),
(6,5,2,'MNT-9921','2028-06-14',60.00,31.00,'2026-08-10'),
(7,6,1,'PRO-1180','2027-09-14',10.00,7.00,'2026-08-01'),
(8,7,2,'EML-5567','2028-04-14',20.00,12.00,'2026-07-23');
/*!40000 ALTER TABLE `batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consents`
--

DROP TABLE IF EXISTS `consents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `consents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` int(10) unsigned NOT NULL,
  `appointment_id` int(10) unsigned DEFAULT NULL,
  `treatment_id` int(10) unsigned NOT NULL,
  `version` varchar(20) NOT NULL DEFAULT '1.0',
  `body_snapshot` text NOT NULL COMMENT 'exact wording the patient agreed to',
  `signed_name` varchar(140) DEFAULT NULL,
  `signed_at` datetime DEFAULT NULL,
  `signature_data` mediumtext DEFAULT NULL COMMENT 'drawn signature, data URL',
  `photo_consent` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'separate permission to use images',
  `witnessed_by` int(10) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_consent_patient` (`patient_id`),
  KEY `ix_consent_appt` (`appointment_id`),
  KEY `fk_consent_treatment` (`treatment_id`),
  KEY `fk_consent_user` (`witnessed_by`),
  CONSTRAINT `fk_consent_appt` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_consent_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_consent_treatment` FOREIGN KEY (`treatment_id`) REFERENCES `treatments` (`id`),
  CONSTRAINT `fk_consent_user` FOREIGN KEY (`witnessed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consents`
--

LOCK TABLES `consents` WRITE;
/*!40000 ALTER TABLE `consents` DISABLE KEYS */;
INSERT INTO `consents` VALUES
(1,1,1,2,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Rana Haidar','2026-07-30 09:00:00',NULL,1,4,NULL),
(2,2,2,3,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Maya Khalil','2026-08-01 10:30:00',NULL,0,4,NULL),
(3,3,3,4,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Lara Saad','2026-08-02 11:00:00',NULL,0,4,NULL),
(4,4,4,5,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Nour Ayoub','2026-08-04 12:30:00',NULL,1,4,NULL),
(5,5,5,6,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Zeina Fares','2026-08-06 13:00:00',NULL,0,4,NULL),
(6,6,6,7,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Sara Daher','2026-08-08 14:30:00',NULL,0,4,NULL),
(7,7,7,8,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Hiba Mansour','2026-08-09 15:00:00',NULL,1,4,NULL),
(8,8,8,9,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Yasmine Traboulsi','2026-08-11 16:30:00',NULL,0,4,NULL),
(9,9,9,10,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Dima Chehab','2026-08-13 09:00:00',NULL,0,4,NULL),
(10,10,10,2,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Carla Semaan','2026-08-15 10:30:00',NULL,1,4,NULL),
(11,11,11,3,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Rita Khoury','2026-08-16 11:00:00',NULL,0,4,NULL),
(12,12,12,4,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Joelle Abi Rached','2026-08-18 12:30:00',NULL,0,4,NULL),
(13,13,13,5,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Nadia Fakhoury','2026-08-20 13:00:00',NULL,1,4,NULL),
(14,14,14,6,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Perla Ghanem','2026-08-21 14:30:00',NULL,0,4,NULL),
(15,1,15,7,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Rana Haidar','2026-08-23 15:00:00',NULL,0,4,NULL),
(16,2,16,8,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Maya Khalil','2026-08-25 16:30:00',NULL,1,4,NULL),
(17,3,17,9,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Lara Saad','2026-08-27 09:00:00',NULL,0,4,NULL),
(18,4,18,10,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Nour Ayoub','2026-08-28 10:30:00',NULL,0,4,NULL),
(19,5,19,2,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Zeina Fares','2026-08-30 11:00:00',NULL,1,4,NULL),
(20,6,20,3,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Sara Daher','2026-09-01 12:30:00',NULL,0,4,NULL),
(21,7,21,4,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Hiba Mansour','2026-09-03 13:00:00',NULL,0,4,NULL),
(22,8,22,5,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Yasmine Traboulsi','2026-09-04 14:30:00',NULL,1,4,NULL),
(23,9,23,6,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Dima Chehab','2026-09-06 15:00:00',NULL,0,4,NULL),
(24,10,24,7,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Carla Semaan','2026-09-08 16:30:00',NULL,0,4,NULL),
(25,11,25,8,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Rita Khoury','2026-09-10 09:00:00',NULL,1,4,NULL),
(26,12,26,9,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Joelle Abi Rached','2026-09-11 10:30:00',NULL,0,4,NULL),
(27,13,27,10,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Nadia Fakhoury','2026-09-13 11:00:00',NULL,0,4,NULL),
(28,14,28,2,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Perla Ghanem','2026-09-15 12:30:00',NULL,1,4,NULL),
(29,1,29,3,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Rana Haidar','2026-09-16 13:00:00',NULL,0,4,NULL),
(30,2,30,4,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Maya Khalil','2026-09-18 14:30:00',NULL,0,4,NULL),
(31,3,31,5,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Lara Saad','2026-09-20 15:00:00',NULL,1,4,NULL),
(32,4,32,6,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Nour Ayoub','2026-09-22 16:30:00',NULL,0,4,NULL),
(33,5,33,7,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Zeina Fares','2026-09-23 09:00:00',NULL,0,4,NULL),
(34,6,34,8,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Sara Daher','2026-09-25 10:30:00',NULL,1,4,NULL),
(35,7,35,9,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Hiba Mansour','2026-09-27 11:00:00',NULL,0,4,NULL),
(36,8,36,10,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Yasmine Traboulsi','2026-09-29 12:30:00',NULL,0,4,NULL),
(37,9,37,2,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Dima Chehab','2026-09-30 13:00:00',NULL,1,4,NULL),
(38,10,38,3,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Carla Semaan','2026-10-02 14:30:00',NULL,0,4,NULL),
(39,11,39,4,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Rita Khoury','2026-10-04 15:00:00',NULL,0,4,NULL),
(40,12,40,5,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Joelle Abi Rached','2026-10-05 16:30:00',NULL,1,4,NULL),
(41,13,41,6,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Nadia Fakhoury','2026-10-07 09:00:00',NULL,0,4,NULL),
(42,14,42,7,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Perla Ghanem','2026-10-09 10:30:00',NULL,0,4,NULL),
(43,1,43,8,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Rana Haidar','2026-10-11 11:00:00',NULL,1,4,NULL),
(44,2,44,9,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Maya Khalil','2026-10-12 12:30:00',NULL,0,4,NULL),
(45,3,45,10,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Lara Saad','2026-10-14 13:00:00',NULL,0,4,NULL),
(46,1,46,3,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Rana Haidar','2026-10-14 09:30:00',NULL,0,4,NULL),
(47,3,48,6,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Lara Saad','2026-10-14 11:00:00',NULL,0,4,NULL),
(48,4,49,9,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Nour Ayoub','2026-10-14 13:30:00',NULL,1,4,NULL),
(49,6,51,4,'1.0','I confirm the risks, benefits and alternatives have been explained to me.','Sara Daher','2026-10-14 16:30:00',NULL,1,4,NULL);
/*!40000 ALTER TABLE `consents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consultations`
--

DROP TABLE IF EXISTS `consultations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `consultations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `appointment_id` int(10) unsigned NOT NULL,
  `assessment` text DEFAULT NULL,
  `plan` text DEFAULT NULL,
  `quote_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_consult_appt` (`appointment_id`),
  CONSTRAINT `fk_consult_appt` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consultations`
--

LOCK TABLES `consultations` WRITE;
/*!40000 ALTER TABLE `consultations` DISABLE KEYS */;
/*!40000 ALTER TABLE `consultations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `follow_ups`
--

DROP TABLE IF EXISTS `follow_ups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `follow_ups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` int(10) unsigned NOT NULL,
  `treatment_record_id` int(10) unsigned DEFAULT NULL,
  `type` enum('review','top_up','recall','session') NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','done','dismissed') NOT NULL DEFAULT 'pending',
  `notes` varchar(255) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_follow_due` (`due_date`,`status`),
  KEY `ix_follow_patient` (`patient_id`),
  KEY `fk_follow_record` (`treatment_record_id`),
  CONSTRAINT `fk_follow_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_follow_record` FOREIGN KEY (`treatment_record_id`) REFERENCES `treatment_records` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `follow_ups`
--

LOCK TABLES `follow_ups` WRITE;
/*!40000 ALTER TABLE `follow_ups` DISABLE KEYS */;
INSERT INTO `follow_ups` VALUES
(1,1,1,'review','2026-08-13','done','Two-week review',NULL),
(2,1,1,'recall','2026-11-27','pending','Due for repeat treatment',NULL),
(3,2,2,'review','2026-08-15','done','Two-week review',NULL),
(4,3,3,'review','2026-08-16','done','Two-week review',NULL),
(5,4,4,'review','2026-08-25','done','Two-week review',NULL),
(6,5,5,'review','2026-08-20','done','Two-week review',NULL),
(7,5,5,'recall','2027-05-03','pending','Due for repeat treatment',NULL),
(8,6,6,'review','2026-08-22','done','Two-week review',NULL),
(9,7,7,'review','2026-08-23','done','Two-week review',NULL),
(10,8,8,'review','2026-09-01','done','Two-week review',NULL),
(11,9,9,'review','2026-09-10','done','Two-week review',NULL),
(12,9,9,'recall','2027-02-09','pending','Due for repeat treatment',NULL),
(13,10,10,'review','2026-08-29','done','Two-week review',NULL),
(14,11,11,'review','2026-08-30','done','Two-week review',NULL),
(15,12,12,'review','2026-09-01','done','Two-week review',NULL),
(16,13,13,'review','2026-09-10','done','Two-week review',NULL),
(17,13,13,'recall','2027-01-17','pending','Due for repeat treatment',NULL),
(18,14,14,'review','2026-09-04','done','Two-week review',NULL),
(19,1,15,'review','2026-09-06','done','Two-week review',NULL),
(20,2,16,'review','2026-09-08','done','Two-week review',NULL),
(21,3,17,'review','2026-09-17','done','Two-week review',NULL),
(22,3,17,'recall','2027-08-27','pending','Due for repeat treatment',NULL),
(23,4,18,'review','2026-09-25','done','Two-week review',NULL),
(24,5,19,'review','2026-09-13','done','Two-week review',NULL),
(25,6,20,'review','2026-09-15','done','Two-week review',NULL),
(26,7,21,'review','2026-09-17','done','Two-week review',NULL),
(27,7,21,'recall','2027-01-01','pending','Due for repeat treatment',NULL),
(28,8,22,'review','2026-09-25','done','Two-week review',NULL),
(29,9,23,'review','2026-09-20','done','Two-week review',NULL),
(30,10,24,'review','2026-09-22','done','Two-week review',NULL),
(31,11,25,'review','2026-09-24','done','Two-week review',NULL),
(32,11,25,'recall','2027-06-07','pending','Due for repeat treatment',NULL),
(33,12,26,'review','2026-10-02','done','Two-week review',NULL),
(34,13,27,'review','2026-10-11','pending','Two-week review',NULL),
(35,14,28,'review','2026-09-29','done','Two-week review',NULL),
(36,1,29,'review','2026-09-30','done','Two-week review',NULL),
(37,1,29,'recall','2027-01-14','pending','Due for repeat treatment',NULL),
(38,2,30,'review','2026-10-02','done','Two-week review',NULL),
(39,3,31,'review','2026-10-11','pending','Two-week review',NULL),
(40,4,32,'review','2026-10-06','done','Two-week review',NULL),
(41,5,33,'review','2026-10-07','pending','Two-week review',NULL),
(42,5,33,'recall','2027-07-20','pending','Due for repeat treatment',NULL),
(43,6,34,'review','2026-10-09','pending','Two-week review',NULL),
(44,7,35,'review','2026-10-18','pending','Two-week review',NULL),
(45,8,36,'review','2026-10-27','pending','Two-week review',NULL),
(46,9,37,'review','2026-10-14','pending','Two-week review',NULL),
(47,9,37,'recall','2027-01-28','pending','Due for repeat treatment',NULL),
(48,10,38,'review','2026-10-16','pending','Two-week review',NULL),
(49,11,39,'review','2026-10-18','pending','Two-week review',NULL),
(50,12,40,'review','2026-10-26','pending','Two-week review',NULL),
(51,13,41,'review','2026-10-21','pending','Two-week review',NULL),
(52,13,41,'recall','2027-07-04','pending','Due for repeat treatment',NULL),
(53,14,42,'review','2026-10-23','pending','Two-week review',NULL),
(54,1,43,'review','2026-10-25','pending','Two-week review',NULL),
(55,2,44,'review','2026-11-02','pending','Two-week review',NULL),
(56,3,45,'review','2026-11-11','pending','Two-week review',NULL),
(57,3,45,'recall','2027-04-12','pending','Due for repeat treatment',NULL);
/*!40000 ALTER TABLE `follow_ups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) unsigned NOT NULL,
  `description` varchar(200) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `ix_ii_invoice` (`invoice_id`),
  CONSTRAINT `fk_ii_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
INSERT INTO `invoice_items` VALUES
(1,1,'Botox — upper face — 27 units',1.00,324.00,324.00),
(2,2,'Botox — forehead & glabella — 30 units',1.00,360.00,360.00),
(3,3,'Botox — crow’s feet — 21 units',1.00,252.00,252.00),
(4,4,'Masseter / jaw slimming — 23 units',1.00,276.00,276.00),
(5,5,'Lip filler — 1ml',1.00,450.00,450.00),
(6,6,'Cheek filler — 1ml',1.00,520.00,520.00),
(7,7,'Nasolabial folds — 1ml',1.00,480.00,480.00),
(8,8,'PDO threads — jawline',1.00,780.00,780.00),
(9,9,'Profhilo — skin booster',1.00,390.00,390.00),
(10,10,'Botox — upper face — 36 units',1.00,432.00,432.00),
(11,11,'Botox — forehead & glabella — 33 units',1.00,396.00,396.00),
(12,12,'Botox — crow’s feet — 21 units',1.00,252.00,252.00),
(13,13,'Masseter / jaw slimming — 17 units',1.00,204.00,204.00),
(14,14,'Lip filler — 1ml',1.00,450.00,450.00),
(15,15,'Cheek filler — 1ml',1.00,520.00,520.00),
(16,16,'Nasolabial folds — 1ml',1.00,480.00,480.00),
(17,17,'PDO threads — jawline',1.00,780.00,780.00),
(18,18,'Profhilo — skin booster',1.00,390.00,390.00),
(19,19,'Botox — upper face — 27 units',1.00,324.00,324.00),
(20,20,'Botox — forehead & glabella — 30 units',1.00,360.00,360.00),
(21,21,'Botox — crow’s feet — 21 units',1.00,252.00,252.00),
(22,22,'Masseter / jaw slimming — 23 units',1.00,276.00,276.00),
(23,23,'Lip filler — 1ml',1.00,450.00,450.00),
(24,24,'Cheek filler — 1ml',1.00,520.00,520.00),
(25,25,'Nasolabial folds — 1ml',1.00,480.00,480.00),
(26,26,'PDO threads — jawline',1.00,780.00,780.00),
(27,27,'Profhilo — skin booster',1.00,390.00,390.00),
(28,28,'Botox — upper face — 36 units',1.00,432.00,432.00),
(29,29,'Botox — forehead & glabella — 33 units',1.00,396.00,396.00),
(30,30,'Botox — crow’s feet — 21 units',1.00,252.00,252.00),
(31,31,'Masseter / jaw slimming — 17 units',1.00,204.00,204.00),
(32,32,'Lip filler — 1ml',1.00,450.00,450.00),
(33,33,'Cheek filler — 1ml',1.00,520.00,520.00),
(34,34,'Nasolabial folds — 1ml',1.00,480.00,480.00),
(35,35,'PDO threads — jawline',1.00,780.00,780.00),
(36,36,'Profhilo — skin booster',1.00,390.00,390.00),
(37,37,'Botox — upper face — 27 units',1.00,324.00,324.00),
(38,38,'Botox — forehead & glabella — 30 units',1.00,360.00,360.00),
(39,39,'Botox — crow’s feet — 21 units',1.00,252.00,252.00),
(40,40,'Masseter / jaw slimming — 23 units',1.00,276.00,276.00),
(41,41,'Lip filler — 1ml',1.00,450.00,450.00),
(42,42,'Cheek filler — 1ml',1.00,520.00,520.00),
(43,43,'Nasolabial folds — 1ml',1.00,480.00,480.00),
(44,44,'PDO threads — jawline',1.00,780.00,780.00),
(45,45,'Profhilo — skin booster',1.00,390.00,390.00);
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `number` varchar(20) NOT NULL,
  `patient_id` int(10) unsigned NOT NULL,
  `appointment_id` int(10) unsigned DEFAULT NULL,
  `issued_at` datetime NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('unpaid','partial','paid','void') NOT NULL DEFAULT 'unpaid',
  `created_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoice_number` (`number`),
  KEY `ix_invoice_patient` (`patient_id`),
  KEY `ix_invoice_issued` (`issued_at`),
  KEY `fk_inv_appt` (`appointment_id`),
  KEY `fk_inv_user` (`created_by`),
  CONSTRAINT `fk_inv_appt` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_inv_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inv_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES
(1,'INV-1001',1,1,'2026-07-30 09:00:00',324.00,0.00,324.00,324.00,'paid',5),
(2,'INV-1002',2,2,'2026-08-01 10:30:00',360.00,0.00,360.00,360.00,'paid',5),
(3,'INV-1003',3,3,'2026-08-02 11:00:00',252.00,0.00,252.00,252.00,'paid',5),
(4,'INV-1004',4,4,'2026-08-04 12:30:00',276.00,0.00,276.00,276.00,'paid',5),
(5,'INV-1005',5,5,'2026-08-06 13:00:00',450.00,0.00,450.00,450.00,'paid',5),
(6,'INV-1006',6,6,'2026-08-08 14:30:00',520.00,0.00,520.00,520.00,'paid',5),
(7,'INV-1007',7,7,'2026-08-09 15:00:00',480.00,0.00,480.00,480.00,'paid',5),
(8,'INV-1008',8,8,'2026-08-11 16:30:00',780.00,0.00,780.00,780.00,'paid',5),
(9,'INV-1009',9,9,'2026-08-13 09:00:00',390.00,0.00,390.00,390.00,'paid',5),
(10,'INV-1010',10,10,'2026-08-15 10:30:00',432.00,0.00,432.00,432.00,'paid',5),
(11,'INV-1011',11,11,'2026-08-16 11:00:00',396.00,0.00,396.00,396.00,'paid',5),
(12,'INV-1012',12,12,'2026-08-18 12:30:00',252.00,0.00,252.00,252.00,'paid',5),
(13,'INV-1013',13,13,'2026-08-20 13:00:00',204.00,0.00,204.00,204.00,'paid',5),
(14,'INV-1014',14,14,'2026-08-21 14:30:00',450.00,0.00,450.00,450.00,'paid',5),
(15,'INV-1015',1,15,'2026-08-23 15:00:00',520.00,0.00,520.00,520.00,'paid',5),
(16,'INV-1016',2,16,'2026-08-25 16:30:00',480.00,0.00,480.00,480.00,'paid',5),
(17,'INV-1017',3,17,'2026-08-27 09:00:00',780.00,0.00,780.00,780.00,'paid',5),
(18,'INV-1018',4,18,'2026-08-28 10:30:00',390.00,0.00,390.00,390.00,'paid',5),
(19,'INV-1019',5,19,'2026-08-30 11:00:00',324.00,0.00,324.00,324.00,'paid',5),
(20,'INV-1020',6,20,'2026-09-01 12:30:00',360.00,0.00,360.00,360.00,'paid',5),
(21,'INV-1021',7,21,'2026-09-03 13:00:00',252.00,0.00,252.00,252.00,'paid',5),
(22,'INV-1022',8,22,'2026-09-04 14:30:00',276.00,0.00,276.00,276.00,'paid',5),
(23,'INV-1023',9,23,'2026-09-06 15:00:00',450.00,0.00,450.00,450.00,'paid',5),
(24,'INV-1024',10,24,'2026-09-08 16:30:00',520.00,0.00,520.00,520.00,'paid',5),
(25,'INV-1025',11,25,'2026-09-10 09:00:00',480.00,0.00,480.00,480.00,'paid',5),
(26,'INV-1026',12,26,'2026-09-11 10:30:00',780.00,0.00,780.00,780.00,'paid',5),
(27,'INV-1027',13,27,'2026-09-13 11:00:00',390.00,0.00,390.00,390.00,'paid',5),
(28,'INV-1028',14,28,'2026-09-15 12:30:00',432.00,0.00,432.00,432.00,'paid',5),
(29,'INV-1029',1,29,'2026-09-16 13:00:00',396.00,0.00,396.00,396.00,'paid',5),
(30,'INV-1030',2,30,'2026-09-18 14:30:00',252.00,0.00,252.00,252.00,'paid',5),
(31,'INV-1031',3,31,'2026-09-20 15:00:00',204.00,0.00,204.00,204.00,'paid',5),
(32,'INV-1032',4,32,'2026-09-22 16:30:00',450.00,0.00,450.00,450.00,'paid',5),
(33,'INV-1033',5,33,'2026-09-23 09:00:00',520.00,0.00,520.00,520.00,'paid',5),
(34,'INV-1034',6,34,'2026-09-25 10:30:00',480.00,0.00,480.00,480.00,'paid',5),
(35,'INV-1035',7,35,'2026-09-27 11:00:00',780.00,0.00,780.00,780.00,'paid',5),
(36,'INV-1036',8,36,'2026-09-29 12:30:00',390.00,0.00,390.00,390.00,'paid',5),
(37,'INV-1037',9,37,'2026-09-30 13:00:00',324.00,0.00,324.00,324.00,'paid',5),
(38,'INV-1038',10,38,'2026-10-02 14:30:00',360.00,0.00,360.00,360.00,'paid',5),
(39,'INV-1039',11,39,'2026-10-04 15:00:00',252.00,0.00,252.00,252.00,'paid',5),
(40,'INV-1040',12,40,'2026-10-05 16:30:00',276.00,0.00,276.00,276.00,'paid',5),
(41,'INV-1041',13,41,'2026-10-07 09:00:00',450.00,0.00,450.00,450.00,'paid',5),
(42,'INV-1042',14,42,'2026-10-09 10:30:00',520.00,0.00,520.00,520.00,'paid',5),
(43,'INV-1043',1,43,'2026-10-11 11:00:00',480.00,0.00,480.00,480.00,'paid',5),
(44,'INV-1044',2,44,'2026-10-12 12:30:00',780.00,0.00,780.00,780.00,'paid',5),
(45,'INV-1045',3,45,'2026-10-14 13:00:00',390.00,0.00,390.00,390.00,'paid',5);
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medical_histories`
--

DROP TABLE IF EXISTS `medical_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `medical_histories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` int(10) unsigned NOT NULL,
  `allergies` text DEFAULT NULL,
  `medications` text DEFAULT NULL,
  `conditions` text DEFAULT NULL,
  `is_pregnant` tinyint(1) NOT NULL DEFAULT 0,
  `is_breastfeeding` tinyint(1) NOT NULL DEFAULT 0,
  `on_blood_thinners` tinyint(1) NOT NULL DEFAULT 0,
  `has_autoimmune` tinyint(1) NOT NULL DEFAULT 0,
  `had_prior_reaction` tinyint(1) NOT NULL DEFAULT 0,
  `prior_treatments` text DEFAULT NULL,
  `smoker` tinyint(1) NOT NULL DEFAULT 0,
  `reviewed_by` int(10) unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_history_patient` (`patient_id`),
  KEY `fk_history_user` (`reviewed_by`),
  CONSTRAINT `fk_history_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_history_user` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_histories`
--

LOCK TABLES `medical_histories` WRITE;
/*!40000 ALTER TABLE `medical_histories` DISABLE KEYS */;
INSERT INTO `medical_histories` VALUES
(1,1,'Penicillin','Aspirin 75mg daily','Hypothyroidism — controlled',0,0,1,0,0,'Botox elsewhere, 2024',1,4,'2026-04-07 05:43:51'),
(2,2,'None known','None','None',0,0,0,0,0,NULL,0,4,'2026-04-18 05:43:51'),
(3,3,'None known','None','None',0,0,0,0,0,NULL,0,4,'2026-04-29 05:43:51'),
(4,4,'None known','None','None',0,0,0,0,0,'Botox elsewhere, 2024',0,4,'2026-05-10 05:43:51'),
(5,5,'None known','Aspirin 75mg daily','None',0,0,1,0,0,NULL,0,4,'2026-05-21 05:43:51'),
(6,6,'Penicillin','None','None',0,0,0,0,0,NULL,0,4,'2026-06-01 05:43:51'),
(7,7,'None known','None','None',0,0,0,1,0,'Botox elsewhere, 2024',1,4,'2026-06-12 05:43:51'),
(8,8,'None known','None','Hypothyroidism — controlled',0,0,0,0,0,NULL,0,4,'2026-06-23 05:43:51'),
(9,9,'None known','Aspirin 75mg daily','None',0,0,1,0,0,NULL,0,4,'2026-07-04 05:43:51'),
(10,10,'None known','None','None',0,0,0,0,0,'Botox elsewhere, 2024',0,4,'2026-07-15 05:43:51'),
(11,11,'Penicillin','None','None',0,0,0,0,0,NULL,0,4,'2026-07-26 05:43:51'),
(12,12,'None known','None','None',0,1,0,0,0,NULL,0,4,'2026-08-06 05:43:51'),
(13,13,'None known','Aspirin 75mg daily','None',0,0,1,0,0,'Botox elsewhere, 2024',1,4,'2026-08-17 05:43:51'),
(14,14,'None known','None','None',0,0,0,0,0,NULL,0,4,'2026-08-28 05:43:51');
/*!40000 ALTER TABLE `medical_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patient_photos`
--

DROP TABLE IF EXISTS `patient_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `patient_photos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` int(10) unsigned NOT NULL,
  `treatment_record_id` int(10) unsigned DEFAULT NULL,
  `type` enum('before','after','progress') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `taken_at` datetime NOT NULL DEFAULT current_timestamp(),
  `marketing_consent` tinyint(1) NOT NULL DEFAULT 0,
  `uploaded_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_photo_patient` (`patient_id`),
  KEY `fk_photo_record` (`treatment_record_id`),
  KEY `fk_photo_user` (`uploaded_by`),
  CONSTRAINT `fk_photo_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_photo_record` FOREIGN KEY (`treatment_record_id`) REFERENCES `treatment_records` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_photo_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patient_photos`
--

LOCK TABLES `patient_photos` WRITE;
/*!40000 ALTER TABLE `patient_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `patient_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `patients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL COMMENT 'human reference, e.g. PT-00142',
  `full_name` varchar(140) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('female','male','other') DEFAULT NULL,
  `phone` varchar(40) NOT NULL,
  `email` varchar(160) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `source` enum('instagram','referral','walk_in','google','facebook','other') NOT NULL DEFAULT 'other',
  `notes` text DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_patients_code` (`code`),
  KEY `ix_patients_name` (`full_name`),
  KEY `ix_patients_phone` (`phone`),
  KEY `fk_patients_user` (`created_by`),
  CONSTRAINT `fk_patients_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patients`
--

LOCK TABLES `patients` WRITE;
/*!40000 ALTER TABLE `patients` DISABLE KEYS */;
INSERT INTO `patients` VALUES
(1,'PT-00100','Rana Haidar','1989-04-12','female','+961 70 441 220','rana.haidar@example.com',NULL,'instagram',NULL,5,'2026-03-28 05:43:51','2026-10-14 08:43:51'),
(2,'PT-00101','Maya Khalil','1996-11-02','female','+961 71 338 907','maya.khalil@example.com',NULL,'instagram',NULL,5,'2026-04-08 05:43:51','2026-10-14 08:43:51'),
(3,'PT-00102','Lara Saad','1992-02-24','female','+961 76 220 118','lara.saad@example.com',NULL,'referral',NULL,5,'2026-04-19 05:43:51','2026-10-14 08:43:51'),
(4,'PT-00103','Nour Ayoub','1985-07-30','female','+961 3 889 441','nour.ayoub@example.com',NULL,'referral',NULL,5,'2026-04-30 05:43:51','2026-10-14 08:43:51'),
(5,'PT-00104','Zeina Fares','1998-09-15','female','+961 81 556 230','zeina.fares@example.com',NULL,'instagram',NULL,5,'2026-05-11 05:43:51','2026-10-14 08:43:51'),
(6,'PT-00105','Sara Daher','1990-01-08','female','+961 70 912 004','sara.daher@example.com',NULL,'google',NULL,5,'2026-05-22 05:43:51','2026-10-14 08:43:51'),
(7,'PT-00106','Hiba Mansour','1983-05-19','female','+961 71 664 882','hiba.mansour@example.com',NULL,'walk_in',NULL,5,'2026-06-02 05:43:51','2026-10-14 08:43:51'),
(8,'PT-00107','Yasmine Traboulsi','1994-12-03','female','+961 76 101 559','yasmine.traboulsi@example.com',NULL,'instagram',NULL,5,'2026-06-13 05:43:51','2026-10-14 08:43:51'),
(9,'PT-00108','Dima Chehab','1987-08-21','female','+961 3 447 219','dima.chehab@example.com',NULL,'referral',NULL,5,'2026-06-24 05:43:51','2026-10-14 08:43:51'),
(10,'PT-00109','Carla Semaan','1991-06-11','female','+961 70 775 330','carla.semaan@example.com',NULL,'facebook',NULL,5,'2026-07-05 05:43:51','2026-10-14 08:43:51'),
(11,'PT-00110','Rita Khoury','1979-03-27','female','+961 71 229 806','rita.khoury@example.com',NULL,'referral',NULL,5,'2026-07-16 05:43:51','2026-10-14 08:43:51'),
(12,'PT-00111','Joelle Abi Rached','1997-10-09','female','+961 76 554 018','joelle.abi.rached@example.com',NULL,'instagram',NULL,5,'2026-07-27 05:43:51','2026-10-14 08:43:51'),
(13,'PT-00112','Nadia Fakhoury','1982-12-16','female','+961 3 660 147','nadia.fakhoury@example.com',NULL,'google',NULL,5,'2026-08-07 05:43:51','2026-10-14 08:43:51'),
(14,'PT-00113','Perla Ghanem','1999-05-05','female','+961 81 330 992','perla.ghanem@example.com',NULL,'instagram',NULL,5,'2026-08-18 05:43:51','2026-10-14 08:43:51');
/*!40000 ALTER TABLE `patients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) unsigned NOT NULL,
  `method` enum('cash','card','transfer','other') NOT NULL DEFAULT 'cash',
  `amount` decimal(10,2) NOT NULL,
  `paid_at` datetime NOT NULL DEFAULT current_timestamp(),
  `received_by` int(10) unsigned DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_pay_invoice` (`invoice_id`),
  KEY `ix_pay_date` (`paid_at`),
  KEY `fk_pay_user` (`received_by`),
  CONSTRAINT `fk_pay_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_user` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES
(1,1,'cash',324.00,'2026-07-30 09:00:00',5,NULL),
(2,2,'card',360.00,'2026-08-01 10:30:00',5,NULL),
(3,3,'transfer',252.00,'2026-08-02 11:00:00',5,NULL),
(4,4,'cash',276.00,'2026-08-04 12:30:00',5,NULL),
(5,5,'card',450.00,'2026-08-06 13:00:00',5,NULL),
(6,6,'transfer',520.00,'2026-08-08 14:30:00',5,NULL),
(7,7,'cash',480.00,'2026-08-09 15:00:00',5,NULL),
(8,8,'card',780.00,'2026-08-11 16:30:00',5,NULL),
(9,9,'transfer',390.00,'2026-08-13 09:00:00',5,NULL),
(10,10,'cash',432.00,'2026-08-15 10:30:00',5,NULL),
(11,11,'card',396.00,'2026-08-16 11:00:00',5,NULL),
(12,12,'transfer',252.00,'2026-08-18 12:30:00',5,NULL),
(13,13,'cash',204.00,'2026-08-20 13:00:00',5,NULL),
(14,14,'card',450.00,'2026-08-21 14:30:00',5,NULL),
(15,15,'transfer',520.00,'2026-08-23 15:00:00',5,NULL),
(16,16,'cash',480.00,'2026-08-25 16:30:00',5,NULL),
(17,17,'card',780.00,'2026-08-27 09:00:00',5,NULL),
(18,18,'transfer',390.00,'2026-08-28 10:30:00',5,NULL),
(19,19,'cash',324.00,'2026-08-30 11:00:00',5,NULL),
(20,20,'card',360.00,'2026-09-01 12:30:00',5,NULL),
(21,21,'transfer',252.00,'2026-09-03 13:00:00',5,NULL),
(22,22,'cash',276.00,'2026-09-04 14:30:00',5,NULL),
(23,23,'card',450.00,'2026-09-06 15:00:00',5,NULL),
(24,24,'transfer',520.00,'2026-09-08 16:30:00',5,NULL),
(25,25,'cash',480.00,'2026-09-10 09:00:00',5,NULL),
(26,26,'card',780.00,'2026-09-11 10:30:00',5,NULL),
(27,27,'transfer',390.00,'2026-09-13 11:00:00',5,NULL),
(28,28,'cash',432.00,'2026-09-15 12:30:00',5,NULL),
(29,29,'card',396.00,'2026-09-16 13:00:00',5,NULL),
(30,30,'transfer',252.00,'2026-09-18 14:30:00',5,NULL),
(31,31,'cash',204.00,'2026-09-20 15:00:00',5,NULL),
(32,32,'card',450.00,'2026-09-22 16:30:00',5,NULL),
(33,33,'transfer',520.00,'2026-09-23 09:00:00',5,NULL),
(34,34,'cash',480.00,'2026-09-25 10:30:00',5,NULL),
(35,35,'card',780.00,'2026-09-27 11:00:00',5,NULL),
(36,36,'transfer',390.00,'2026-09-29 12:30:00',5,NULL),
(37,37,'cash',324.00,'2026-09-30 13:00:00',5,NULL),
(38,38,'card',360.00,'2026-10-02 14:30:00',5,NULL),
(39,39,'transfer',252.00,'2026-10-04 15:00:00',5,NULL),
(40,40,'cash',276.00,'2026-10-05 16:30:00',5,NULL),
(41,41,'card',450.00,'2026-10-07 09:00:00',5,NULL),
(42,42,'transfer',520.00,'2026-10-09 10:30:00',5,NULL),
(43,43,'cash',480.00,'2026-10-11 11:00:00',5,NULL),
(44,44,'card',780.00,'2026-10-12 12:30:00',5,NULL),
(45,45,'transfer',390.00,'2026-10-14 13:00:00',5,NULL);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(60) NOT NULL,
  `area` varchar(40) NOT NULL,
  `label` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_permissions_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES
(1,'dashboard.view','General','View dashboard'),
(2,'patients.view','Patients','View patients'),
(3,'patients.manage','Patients','Create and edit patients'),
(4,'patients.clinical','Patients','View clinical notes, history and photos'),
(5,'appointments.view','Diary','View the diary'),
(6,'appointments.manage','Diary','Book, move and cancel appointments'),
(7,'records.view','Clinical','View treatment records'),
(8,'records.manage','Clinical','Record treatments performed'),
(9,'consents.manage','Clinical','Issue and sign consent forms'),
(10,'followups.manage','Clinical','Manage follow-ups and recalls'),
(11,'events.manage','Clinical','Record adverse events'),
(12,'treatments.manage','Catalogue','Manage the treatment menu'),
(13,'stock.view','Stock','View stock and batches'),
(14,'stock.manage','Stock','Receive stock, record wastage'),
(15,'billing.view','Billing','View invoices'),
(16,'billing.manage','Billing','Create invoices and take payment'),
(17,'reports.view','Reports','View reports and revenue'),
(18,'staff.manage','Admin','Manage staff and practitioners'),
(19,'settings.manage','Admin','Change clinic settings'),
(20,'audit.view','Admin','View the audit log');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `practitioner_schedules`
--

DROP TABLE IF EXISTS `practitioner_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `practitioner_schedules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `practitioner_id` int(10) unsigned NOT NULL,
  `weekday` tinyint(3) unsigned NOT NULL COMMENT '0=Sunday … 6=Saturday',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_sched_prac` (`practitioner_id`,`weekday`),
  CONSTRAINT `fk_sched_prac` FOREIGN KEY (`practitioner_id`) REFERENCES `practitioners` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `practitioner_schedules`
--

LOCK TABLES `practitioner_schedules` WRITE;
/*!40000 ALTER TABLE `practitioner_schedules` DISABLE KEYS */;
INSERT INTO `practitioner_schedules` VALUES
(1,1,1,'09:00:00','18:00:00'),
(2,1,2,'09:00:00','18:00:00'),
(3,1,3,'09:00:00','18:00:00'),
(4,1,4,'09:00:00','18:00:00'),
(5,1,5,'09:00:00','18:00:00'),
(6,1,6,'09:00:00','14:00:00'),
(7,2,1,'09:00:00','18:00:00'),
(8,2,2,'09:00:00','18:00:00'),
(9,2,3,'09:00:00','18:00:00'),
(10,2,4,'09:00:00','18:00:00'),
(11,2,5,'09:00:00','18:00:00'),
(12,2,6,'09:00:00','14:00:00'),
(13,3,2,'09:00:00','18:00:00'),
(14,3,3,'09:00:00','18:00:00'),
(15,3,4,'09:00:00','18:00:00'),
(16,3,5,'09:00:00','18:00:00'),
(17,3,6,'09:00:00','14:00:00');
/*!40000 ALTER TABLE `practitioner_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `practitioners`
--

DROP TABLE IF EXISTS `practitioners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `practitioners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(30) NOT NULL DEFAULT 'Dr.',
  `speciality` varchar(120) DEFAULT NULL,
  `license_no` varchar(60) DEFAULT NULL,
  `commission_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `calendar_colour` varchar(7) NOT NULL DEFAULT '#1F6F52',
  `bio` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_practitioner_user` (`user_id`),
  CONSTRAINT `fk_prac_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `practitioners`
--

LOCK TABLES `practitioners` WRITE;
/*!40000 ALTER TABLE `practitioners` DISABLE KEYS */;
INSERT INTO `practitioners` VALUES
(1,1,'Dr.','Aesthetic medicine · injectables','LB-AM-2291',0.00,'#1F6F52',NULL,1),
(2,2,'Dr.','Dermatology · fillers','LB-DR-4417',35.00,'#2F7F8F',NULL,1),
(3,3,'Dr.','Facial aesthetics · threads','LB-AM-5530',35.00,'#8A6BBF',NULL,1);
/*!40000 ALTER TABLE `practitioners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(140) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `type` enum('toxin','filler','thread','skinbooster','consumable') NOT NULL,
  `unit` enum('unit','ml','piece') NOT NULL DEFAULT 'unit',
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reorder_level` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'alert when remaining falls below',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `ix_products_type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES
(1,'Botulinum Toxin 100u','Botox® Allergan','toxin','unit',2.10,150.00,1),
(2,'Botulinum Toxin 100u','Dysport®','toxin','unit',1.70,100.00,1),
(3,'Hyaluronic Filler 1ml','Juvéderm Ultra','filler','ml',110.00,4.00,1),
(4,'Hyaluronic Filler 1ml','Restylane Kysse','filler','ml',125.00,3.00,1),
(5,'PDO Thread — Cog','MINT Threads','thread','piece',22.00,12.00,1),
(6,'Profhilo 2ml','IBSA','skinbooster','ml',95.00,3.00,1),
(7,'Numbing Cream 30g','Emla','consumable','piece',14.00,5.00,1);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_id` tinyint(3) unsigned NOT NULL,
  `permission_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_rp_perm` (`permission_id`),
  CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES
(1,1),
(1,2),
(1,3),
(1,4),
(1,5),
(1,6),
(1,7),
(1,8),
(1,9),
(1,10),
(1,11),
(1,12),
(1,13),
(1,14),
(1,15),
(1,16),
(1,17),
(1,18),
(1,19),
(1,20),
(2,1),
(2,2),
(2,3),
(2,4),
(2,5),
(2,6),
(2,7),
(2,8),
(2,9),
(2,10),
(2,11),
(2,13),
(2,17),
(3,1),
(3,2),
(3,3),
(3,4),
(3,5),
(3,7),
(3,9),
(3,13),
(4,1),
(4,2),
(4,3),
(4,5),
(4,6),
(4,9),
(4,15),
(4,16),
(5,1),
(5,15),
(5,17);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(40) NOT NULL,
  `name` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,'owner','Owner / Admin','Full access including finance and settings'),
(2,'doctor','Doctor / Injector','Clinical records, own calendar, treatments'),
(3,'nurse','Nurse / Assistant','Assists, photos, medical history'),
(4,'receptionist','Receptionist','Bookings, check-in, payments — no clinical notes'),
(5,'accountant','Accountant','Financial reports only');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `key` varchar(60) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES
('clinic_address','Verdun, Beirut, Lebanon'),
('clinic_email','hello@aesthetica.test'),
('clinic_name','Aesthetica Clinic'),
('clinic_phone','+961 1 555 330'),
('clinic_tagline','Aesthetic medicine & injectables'),
('currency','USD'),
('currency_symbol','$'),
('topup_window_days','21');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` int(10) unsigned NOT NULL,
  `type` enum('in','out','waste','adjustment') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `reference_type` varchar(40) DEFAULT NULL COMMENT 'e.g. treatment_record',
  `reference_id` int(10) unsigned DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ix_moves_batch` (`batch_id`,`created_at`),
  KEY `fk_moves_user` (`user_id`),
  CONSTRAINT `fk_moves_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_moves_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES
(1,1,'in',1000.00,NULL,NULL,'Opening stock received',1,'2026-09-24 05:43:51'),
(2,2,'in',500.00,NULL,NULL,'Opening stock received',1,'2026-09-15 05:43:51'),
(3,3,'in',500.00,NULL,NULL,'Opening stock received',1,'2026-09-06 05:43:51'),
(4,4,'in',12.00,NULL,NULL,'Opening stock received',1,'2026-08-28 05:43:51'),
(5,5,'in',8.00,NULL,NULL,'Opening stock received',1,'2026-08-19 05:43:51'),
(6,6,'in',60.00,NULL,NULL,'Opening stock received',1,'2026-08-10 05:43:51'),
(7,7,'in',10.00,NULL,NULL,'Opening stock received',1,'2026-08-01 05:43:51'),
(8,8,'in',20.00,NULL,NULL,'Opening stock received',1,'2026-07-23 05:43:51'),
(9,1,'out',8.00,'treatment_record',1,'Used in treatment',1,'2026-10-14 08:43:51'),
(10,1,'out',9.00,'treatment_record',1,'Used in treatment',1,'2026-10-14 08:43:51'),
(11,1,'out',10.00,'treatment_record',1,'Used in treatment',1,'2026-10-14 08:43:51'),
(12,1,'out',9.00,'treatment_record',2,'Used in treatment',1,'2026-10-14 08:43:51'),
(13,1,'out',10.00,'treatment_record',2,'Used in treatment',1,'2026-10-14 08:43:51'),
(14,1,'out',11.00,'treatment_record',2,'Used in treatment',1,'2026-10-14 08:43:51'),
(15,1,'out',10.00,'treatment_record',3,'Used in treatment',1,'2026-10-14 08:43:51'),
(16,1,'out',11.00,'treatment_record',3,'Used in treatment',1,'2026-10-14 08:43:51'),
(17,1,'out',11.00,'treatment_record',4,'Used in treatment',1,'2026-10-14 08:43:51'),
(18,1,'out',12.00,'treatment_record',4,'Used in treatment',1,'2026-10-14 08:43:51'),
(19,4,'out',0.50,'treatment_record',5,'Used in treatment',1,'2026-10-14 08:43:51'),
(20,4,'out',0.70,'treatment_record',5,'Used in treatment',1,'2026-10-14 08:43:51'),
(21,4,'out',0.70,'treatment_record',6,'Used in treatment',1,'2026-10-14 08:43:51'),
(22,4,'out',0.30,'treatment_record',6,'Used in treatment',1,'2026-10-14 08:43:51'),
(23,5,'out',0.30,'treatment_record',7,'Used in treatment',1,'2026-10-14 08:43:51'),
(24,5,'out',0.50,'treatment_record',7,'Used in treatment',1,'2026-10-14 08:43:51'),
(25,6,'out',2.00,'treatment_record',8,'Used in treatment',1,'2026-10-14 08:43:51'),
(26,6,'out',2.00,'treatment_record',8,'Used in treatment',1,'2026-10-14 08:43:51'),
(27,7,'out',0.70,'treatment_record',9,'Used in treatment',1,'2026-10-14 08:43:51'),
(28,7,'out',0.30,'treatment_record',9,'Used in treatment',1,'2026-10-14 08:43:51'),
(29,7,'out',0.50,'treatment_record',9,'Used in treatment',1,'2026-10-14 08:43:51'),
(30,1,'out',11.00,'treatment_record',10,'Used in treatment',1,'2026-10-14 08:43:51'),
(31,1,'out',12.00,'treatment_record',10,'Used in treatment',1,'2026-10-14 08:43:51'),
(32,1,'out',13.00,'treatment_record',10,'Used in treatment',1,'2026-10-14 08:43:51'),
(33,1,'out',12.00,'treatment_record',11,'Used in treatment',1,'2026-10-14 08:43:51'),
(34,1,'out',13.00,'treatment_record',11,'Used in treatment',1,'2026-10-14 08:43:51'),
(35,1,'out',8.00,'treatment_record',11,'Used in treatment',1,'2026-10-14 08:43:51'),
(36,1,'out',13.00,'treatment_record',12,'Used in treatment',1,'2026-10-14 08:43:51'),
(37,1,'out',8.00,'treatment_record',12,'Used in treatment',1,'2026-10-14 08:43:51'),
(38,1,'out',8.00,'treatment_record',13,'Used in treatment',1,'2026-10-14 08:43:51'),
(39,1,'out',9.00,'treatment_record',13,'Used in treatment',1,'2026-10-14 08:43:51'),
(40,4,'out',0.50,'treatment_record',14,'Used in treatment',1,'2026-10-14 08:43:51'),
(41,4,'out',0.70,'treatment_record',14,'Used in treatment',1,'2026-10-14 08:43:51'),
(42,4,'out',0.70,'treatment_record',15,'Used in treatment',1,'2026-10-14 08:43:51'),
(43,4,'out',0.30,'treatment_record',15,'Used in treatment',1,'2026-10-14 08:43:51'),
(44,5,'out',0.30,'treatment_record',16,'Used in treatment',1,'2026-10-14 08:43:51'),
(45,5,'out',0.50,'treatment_record',16,'Used in treatment',1,'2026-10-14 08:43:51'),
(46,6,'out',2.00,'treatment_record',17,'Used in treatment',1,'2026-10-14 08:43:51'),
(47,6,'out',2.00,'treatment_record',17,'Used in treatment',1,'2026-10-14 08:43:51'),
(48,7,'out',0.70,'treatment_record',18,'Used in treatment',1,'2026-10-14 08:43:51'),
(49,7,'out',0.30,'treatment_record',18,'Used in treatment',1,'2026-10-14 08:43:51'),
(50,7,'out',0.50,'treatment_record',18,'Used in treatment',1,'2026-10-14 08:43:51'),
(51,1,'out',8.00,'treatment_record',19,'Used in treatment',1,'2026-10-14 08:43:51'),
(52,1,'out',9.00,'treatment_record',19,'Used in treatment',1,'2026-10-14 08:43:51'),
(53,1,'out',10.00,'treatment_record',19,'Used in treatment',1,'2026-10-14 08:43:51'),
(54,1,'out',9.00,'treatment_record',20,'Used in treatment',1,'2026-10-14 08:43:51'),
(55,1,'out',10.00,'treatment_record',20,'Used in treatment',1,'2026-10-14 08:43:51'),
(56,1,'out',11.00,'treatment_record',20,'Used in treatment',1,'2026-10-14 08:43:51'),
(57,1,'out',10.00,'treatment_record',21,'Used in treatment',1,'2026-10-14 08:43:51'),
(58,1,'out',11.00,'treatment_record',21,'Used in treatment',1,'2026-10-14 08:43:51'),
(59,1,'out',11.00,'treatment_record',22,'Used in treatment',1,'2026-10-14 08:43:51'),
(60,1,'out',12.00,'treatment_record',22,'Used in treatment',1,'2026-10-14 08:43:51'),
(61,4,'out',0.50,'treatment_record',23,'Used in treatment',1,'2026-10-14 08:43:51'),
(62,4,'out',0.70,'treatment_record',23,'Used in treatment',1,'2026-10-14 08:43:51'),
(63,4,'out',0.70,'treatment_record',24,'Used in treatment',1,'2026-10-14 08:43:51'),
(64,4,'out',0.30,'treatment_record',24,'Used in treatment',1,'2026-10-14 08:43:51'),
(65,5,'out',0.30,'treatment_record',25,'Used in treatment',1,'2026-10-14 08:43:51'),
(66,5,'out',0.50,'treatment_record',25,'Used in treatment',1,'2026-10-14 08:43:51'),
(67,6,'out',2.00,'treatment_record',26,'Used in treatment',1,'2026-10-14 08:43:51'),
(68,6,'out',2.00,'treatment_record',26,'Used in treatment',1,'2026-10-14 08:43:51'),
(69,7,'out',0.70,'treatment_record',27,'Used in treatment',1,'2026-10-14 08:43:51'),
(70,7,'out',0.30,'treatment_record',27,'Used in treatment',1,'2026-10-14 08:43:51'),
(71,7,'out',0.50,'treatment_record',27,'Used in treatment',1,'2026-10-14 08:43:51'),
(72,1,'out',11.00,'treatment_record',28,'Used in treatment',1,'2026-10-14 08:43:51'),
(73,1,'out',12.00,'treatment_record',28,'Used in treatment',1,'2026-10-14 08:43:51'),
(74,1,'out',13.00,'treatment_record',28,'Used in treatment',1,'2026-10-14 08:43:51'),
(75,1,'out',12.00,'treatment_record',29,'Used in treatment',1,'2026-10-14 08:43:51'),
(76,1,'out',13.00,'treatment_record',29,'Used in treatment',1,'2026-10-14 08:43:51'),
(77,1,'out',8.00,'treatment_record',29,'Used in treatment',1,'2026-10-14 08:43:51'),
(78,1,'out',13.00,'treatment_record',30,'Used in treatment',1,'2026-10-14 08:43:51'),
(79,1,'out',8.00,'treatment_record',30,'Used in treatment',1,'2026-10-14 08:43:51'),
(80,1,'out',8.00,'treatment_record',31,'Used in treatment',1,'2026-10-14 08:43:51'),
(81,1,'out',9.00,'treatment_record',31,'Used in treatment',1,'2026-10-14 08:43:51'),
(82,4,'out',0.50,'treatment_record',32,'Used in treatment',1,'2026-10-14 08:43:51'),
(83,4,'out',0.70,'treatment_record',32,'Used in treatment',1,'2026-10-14 08:43:51'),
(84,4,'out',0.70,'treatment_record',33,'Used in treatment',1,'2026-10-14 08:43:51'),
(85,4,'out',0.30,'treatment_record',33,'Used in treatment',1,'2026-10-14 08:43:51'),
(86,5,'out',0.30,'treatment_record',34,'Used in treatment',1,'2026-10-14 08:43:51'),
(87,5,'out',0.50,'treatment_record',34,'Used in treatment',1,'2026-10-14 08:43:51'),
(88,6,'out',2.00,'treatment_record',35,'Used in treatment',1,'2026-10-14 08:43:51'),
(89,6,'out',2.00,'treatment_record',35,'Used in treatment',1,'2026-10-14 08:43:51'),
(90,7,'out',0.70,'treatment_record',36,'Used in treatment',1,'2026-10-14 08:43:51'),
(91,7,'out',0.30,'treatment_record',36,'Used in treatment',1,'2026-10-14 08:43:51'),
(92,7,'out',0.50,'treatment_record',36,'Used in treatment',1,'2026-10-14 08:43:51'),
(93,1,'out',8.00,'treatment_record',37,'Used in treatment',1,'2026-10-14 08:43:51'),
(94,1,'out',9.00,'treatment_record',37,'Used in treatment',1,'2026-10-14 08:43:51'),
(95,1,'out',10.00,'treatment_record',37,'Used in treatment',1,'2026-10-14 08:43:51'),
(96,1,'out',9.00,'treatment_record',38,'Used in treatment',1,'2026-10-14 08:43:51'),
(97,1,'out',10.00,'treatment_record',38,'Used in treatment',1,'2026-10-14 08:43:51'),
(98,1,'out',11.00,'treatment_record',38,'Used in treatment',1,'2026-10-14 08:43:51'),
(99,1,'out',10.00,'treatment_record',39,'Used in treatment',1,'2026-10-14 08:43:51'),
(100,1,'out',11.00,'treatment_record',39,'Used in treatment',1,'2026-10-14 08:43:51'),
(101,1,'out',11.00,'treatment_record',40,'Used in treatment',1,'2026-10-14 08:43:51'),
(102,1,'out',12.00,'treatment_record',40,'Used in treatment',1,'2026-10-14 08:43:51'),
(103,4,'out',0.50,'treatment_record',41,'Used in treatment',1,'2026-10-14 08:43:51'),
(104,4,'out',0.70,'treatment_record',41,'Used in treatment',1,'2026-10-14 08:43:51'),
(105,4,'out',0.70,'treatment_record',42,'Used in treatment',1,'2026-10-14 08:43:51'),
(106,4,'out',0.30,'treatment_record',42,'Used in treatment',1,'2026-10-14 08:43:51'),
(107,5,'out',0.30,'treatment_record',43,'Used in treatment',1,'2026-10-14 08:43:51'),
(108,5,'out',0.50,'treatment_record',43,'Used in treatment',1,'2026-10-14 08:43:51'),
(109,6,'out',2.00,'treatment_record',44,'Used in treatment',1,'2026-10-14 08:43:51'),
(110,6,'out',2.00,'treatment_record',44,'Used in treatment',1,'2026-10-14 08:43:51'),
(111,7,'out',0.70,'treatment_record',45,'Used in treatment',1,'2026-10-14 08:43:51'),
(112,7,'out',0.30,'treatment_record',45,'Used in treatment',1,'2026-10-14 08:43:51'),
(113,7,'out',0.50,'treatment_record',45,'Used in treatment',1,'2026-10-14 08:43:51');
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(140) NOT NULL,
  `contact_name` varchar(120) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `email` varchar(160) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES
(1,'MedAesthetic Lebanon','Ziad Aoun','+961 1 480 220','orders@medaesthetic.lb',NULL),
(2,'DermaSupply Gulf','Hala Mroue','+971 4 221 9080','sales@dermasupply.ae',NULL);
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `time_off`
--

DROP TABLE IF EXISTS `time_off`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `time_off` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `practitioner_id` int(10) unsigned NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `reason` varchar(160) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_timeoff_prac` (`practitioner_id`,`starts_at`),
  CONSTRAINT `fk_timeoff_prac` FOREIGN KEY (`practitioner_id`) REFERENCES `practitioners` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `time_off`
--

LOCK TABLES `time_off` WRITE;
/*!40000 ALTER TABLE `time_off` DISABLE KEYS */;
INSERT INTO `time_off` VALUES
(1,2,'2026-10-23 00:00:00','2026-10-26 23:59:59','Conference — Dubai Derma');
/*!40000 ALTER TABLE `time_off` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `treatment_products`
--

DROP TABLE IF EXISTS `treatment_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `treatment_products` (
  `treatment_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `default_qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`treatment_id`,`product_id`),
  KEY `fk_tp_product` (`product_id`),
  CONSTRAINT `fk_tp_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tp_treatment` FOREIGN KEY (`treatment_id`) REFERENCES `treatments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `treatment_products`
--

LOCK TABLES `treatment_products` WRITE;
/*!40000 ALTER TABLE `treatment_products` DISABLE KEYS */;
INSERT INTO `treatment_products` VALUES
(2,1,24.00),
(3,1,24.00),
(4,1,24.00),
(5,1,24.00),
(6,3,1.00),
(7,3,1.00),
(8,4,1.00),
(9,5,6.00),
(10,6,2.00);
/*!40000 ALTER TABLE `treatment_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `treatment_record_items`
--

DROP TABLE IF EXISTS `treatment_record_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `treatment_record_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `treatment_record_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `batch_id` int(10) unsigned NOT NULL,
  `injection_site` varchar(80) NOT NULL COMMENT 'glabella, left lip, jawline…',
  `quantity` decimal(10,2) NOT NULL,
  `unit` enum('unit','ml','piece') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_item_record` (`treatment_record_id`),
  KEY `ix_item_batch` (`batch_id`),
  KEY `fk_item_product` (`product_id`),
  CONSTRAINT `fk_item_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`),
  CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `fk_item_record` FOREIGN KEY (`treatment_record_id`) REFERENCES `treatment_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `treatment_record_items`
--

LOCK TABLES `treatment_record_items` WRITE;
/*!40000 ALTER TABLE `treatment_record_items` DISABLE KEYS */;
INSERT INTO `treatment_record_items` VALUES
(1,1,1,1,'Glabella',8.00,'unit'),
(2,1,1,1,'Left frontalis',9.00,'unit'),
(3,1,1,1,'Right frontalis',10.00,'unit'),
(4,2,1,1,'Glabella',9.00,'unit'),
(5,2,1,1,'Forehead — central',10.00,'unit'),
(6,2,1,1,'Forehead — lateral',11.00,'unit'),
(7,3,1,1,'Left orbicularis',10.00,'unit'),
(8,3,1,1,'Right orbicularis',11.00,'unit'),
(9,4,1,1,'Left masseter',11.00,'unit'),
(10,4,1,1,'Right masseter',12.00,'unit'),
(11,5,3,4,'Upper lip — body',0.50,'ml'),
(12,5,3,4,'Lower lip — body',0.70,'ml'),
(13,6,3,4,'Left malar',0.70,'ml'),
(14,6,3,4,'Right malar',0.30,'ml'),
(15,7,4,5,'Left nasolabial',0.30,'ml'),
(16,7,4,5,'Right nasolabial',0.50,'ml'),
(17,8,5,6,'Left jawline',2.00,'piece'),
(18,8,5,6,'Right jawline',2.00,'piece'),
(19,9,6,7,'Left cheek',0.70,'ml'),
(20,9,6,7,'Right cheek',0.30,'ml'),
(21,9,6,7,'Neck',0.50,'ml'),
(22,10,1,1,'Glabella',11.00,'unit'),
(23,10,1,1,'Left frontalis',12.00,'unit'),
(24,10,1,1,'Right frontalis',13.00,'unit'),
(25,11,1,1,'Glabella',12.00,'unit'),
(26,11,1,1,'Forehead — central',13.00,'unit'),
(27,11,1,1,'Forehead — lateral',8.00,'unit'),
(28,12,1,1,'Left orbicularis',13.00,'unit'),
(29,12,1,1,'Right orbicularis',8.00,'unit'),
(30,13,1,1,'Left masseter',8.00,'unit'),
(31,13,1,1,'Right masseter',9.00,'unit'),
(32,14,3,4,'Upper lip — body',0.50,'ml'),
(33,14,3,4,'Lower lip — body',0.70,'ml'),
(34,15,3,4,'Left malar',0.70,'ml'),
(35,15,3,4,'Right malar',0.30,'ml'),
(36,16,4,5,'Left nasolabial',0.30,'ml'),
(37,16,4,5,'Right nasolabial',0.50,'ml'),
(38,17,5,6,'Left jawline',2.00,'piece'),
(39,17,5,6,'Right jawline',2.00,'piece'),
(40,18,6,7,'Left cheek',0.70,'ml'),
(41,18,6,7,'Right cheek',0.30,'ml'),
(42,18,6,7,'Neck',0.50,'ml'),
(43,19,1,1,'Glabella',8.00,'unit'),
(44,19,1,1,'Left frontalis',9.00,'unit'),
(45,19,1,1,'Right frontalis',10.00,'unit'),
(46,20,1,1,'Glabella',9.00,'unit'),
(47,20,1,1,'Forehead — central',10.00,'unit'),
(48,20,1,1,'Forehead — lateral',11.00,'unit'),
(49,21,1,1,'Left orbicularis',10.00,'unit'),
(50,21,1,1,'Right orbicularis',11.00,'unit'),
(51,22,1,1,'Left masseter',11.00,'unit'),
(52,22,1,1,'Right masseter',12.00,'unit'),
(53,23,3,4,'Upper lip — body',0.50,'ml'),
(54,23,3,4,'Lower lip — body',0.70,'ml'),
(55,24,3,4,'Left malar',0.70,'ml'),
(56,24,3,4,'Right malar',0.30,'ml'),
(57,25,4,5,'Left nasolabial',0.30,'ml'),
(58,25,4,5,'Right nasolabial',0.50,'ml'),
(59,26,5,6,'Left jawline',2.00,'piece'),
(60,26,5,6,'Right jawline',2.00,'piece'),
(61,27,6,7,'Left cheek',0.70,'ml'),
(62,27,6,7,'Right cheek',0.30,'ml'),
(63,27,6,7,'Neck',0.50,'ml'),
(64,28,1,1,'Glabella',11.00,'unit'),
(65,28,1,1,'Left frontalis',12.00,'unit'),
(66,28,1,1,'Right frontalis',13.00,'unit'),
(67,29,1,1,'Glabella',12.00,'unit'),
(68,29,1,1,'Forehead — central',13.00,'unit'),
(69,29,1,1,'Forehead — lateral',8.00,'unit'),
(70,30,1,1,'Left orbicularis',13.00,'unit'),
(71,30,1,1,'Right orbicularis',8.00,'unit'),
(72,31,1,1,'Left masseter',8.00,'unit'),
(73,31,1,1,'Right masseter',9.00,'unit'),
(74,32,3,4,'Upper lip — body',0.50,'ml'),
(75,32,3,4,'Lower lip — body',0.70,'ml'),
(76,33,3,4,'Left malar',0.70,'ml'),
(77,33,3,4,'Right malar',0.30,'ml'),
(78,34,4,5,'Left nasolabial',0.30,'ml'),
(79,34,4,5,'Right nasolabial',0.50,'ml'),
(80,35,5,6,'Left jawline',2.00,'piece'),
(81,35,5,6,'Right jawline',2.00,'piece'),
(82,36,6,7,'Left cheek',0.70,'ml'),
(83,36,6,7,'Right cheek',0.30,'ml'),
(84,36,6,7,'Neck',0.50,'ml'),
(85,37,1,1,'Glabella',8.00,'unit'),
(86,37,1,1,'Left frontalis',9.00,'unit'),
(87,37,1,1,'Right frontalis',10.00,'unit'),
(88,38,1,1,'Glabella',9.00,'unit'),
(89,38,1,1,'Forehead — central',10.00,'unit'),
(90,38,1,1,'Forehead — lateral',11.00,'unit'),
(91,39,1,1,'Left orbicularis',10.00,'unit'),
(92,39,1,1,'Right orbicularis',11.00,'unit'),
(93,40,1,1,'Left masseter',11.00,'unit'),
(94,40,1,1,'Right masseter',12.00,'unit'),
(95,41,3,4,'Upper lip — body',0.50,'ml'),
(96,41,3,4,'Lower lip — body',0.70,'ml'),
(97,42,3,4,'Left malar',0.70,'ml'),
(98,42,3,4,'Right malar',0.30,'ml'),
(99,43,4,5,'Left nasolabial',0.30,'ml'),
(100,43,4,5,'Right nasolabial',0.50,'ml'),
(101,44,5,6,'Left jawline',2.00,'piece'),
(102,44,5,6,'Right jawline',2.00,'piece'),
(103,45,6,7,'Left cheek',0.70,'ml'),
(104,45,6,7,'Right cheek',0.30,'ml'),
(105,45,6,7,'Neck',0.50,'ml');
/*!40000 ALTER TABLE `treatment_record_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `treatment_records`
--

DROP TABLE IF EXISTS `treatment_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `treatment_records` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `appointment_id` int(10) unsigned DEFAULT NULL,
  `patient_id` int(10) unsigned NOT NULL,
  `practitioner_id` int(10) unsigned NOT NULL,
  `treatment_id` int(10) unsigned NOT NULL,
  `performed_at` datetime NOT NULL,
  `technique` varchar(160) DEFAULT NULL COMMENT 'needle / cannula, dilution',
  `notes` text DEFAULT NULL,
  `total_units` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_ml` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ix_rec_patient` (`patient_id`,`performed_at`),
  KEY `ix_rec_prac` (`practitioner_id`,`performed_at`),
  KEY `fk_rec_appt` (`appointment_id`),
  KEY `fk_rec_treatment` (`treatment_id`),
  CONSTRAINT `fk_rec_appt` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_rec_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rec_prac` FOREIGN KEY (`practitioner_id`) REFERENCES `practitioners` (`id`),
  CONSTRAINT `fk_rec_treatment` FOREIGN KEY (`treatment_id`) REFERENCES `treatments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `treatment_records`
--

LOCK TABLES `treatment_records` WRITE;
/*!40000 ALTER TABLE `treatment_records` DISABLE KEYS */;
INSERT INTO `treatment_records` VALUES
(1,1,1,1,2,'2026-07-30 09:00:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',27.00,0.00,'2026-07-30 09:00:00'),
(2,2,2,2,3,'2026-08-01 10:30:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',30.00,0.00,'2026-08-01 10:30:00'),
(3,3,3,3,4,'2026-08-02 11:00:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',21.00,0.00,'2026-08-02 11:00:00'),
(4,4,4,1,5,'2026-08-04 12:30:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',23.00,0.00,'2026-08-04 12:30:00'),
(5,5,5,2,6,'2026-08-06 13:00:00','27G cannula','Uneventful. Patient tolerated well.',0.00,1.20,'2026-08-06 13:00:00'),
(6,6,6,3,7,'2026-08-08 14:30:00','27G cannula','Uneventful. Patient tolerated well.',0.00,1.00,'2026-08-08 14:30:00'),
(7,7,7,1,8,'2026-08-09 15:00:00','27G cannula','Uneventful. Patient tolerated well.',0.00,0.80,'2026-08-09 15:00:00'),
(8,8,8,2,9,'2026-08-11 16:30:00','Blunt cannula 19G','Uneventful. Patient tolerated well.',0.00,0.00,'2026-08-11 16:30:00'),
(9,9,9,3,10,'2026-08-13 09:00:00','27G cannula','Uneventful. Patient tolerated well.',0.00,1.50,'2026-08-13 09:00:00'),
(10,10,10,1,2,'2026-08-15 10:30:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',36.00,0.00,'2026-08-15 10:30:00'),
(11,11,11,2,3,'2026-08-16 11:00:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',33.00,0.00,'2026-08-16 11:00:00'),
(12,12,12,3,4,'2026-08-18 12:30:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',21.00,0.00,'2026-08-18 12:30:00'),
(13,13,13,1,5,'2026-08-20 13:00:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',17.00,0.00,'2026-08-20 13:00:00'),
(14,14,14,2,6,'2026-08-21 14:30:00','27G cannula','Uneventful. Patient tolerated well.',0.00,1.20,'2026-08-21 14:30:00'),
(15,15,1,3,7,'2026-08-23 15:00:00','27G cannula','Uneventful. Patient tolerated well.',0.00,1.00,'2026-08-23 15:00:00'),
(16,16,2,1,8,'2026-08-25 16:30:00','27G cannula','Uneventful. Patient tolerated well.',0.00,0.80,'2026-08-25 16:30:00'),
(17,17,3,2,9,'2026-08-27 09:00:00','Blunt cannula 19G','Uneventful. Patient tolerated well.',0.00,0.00,'2026-08-27 09:00:00'),
(18,18,4,3,10,'2026-08-28 10:30:00','27G cannula','Uneventful. Patient tolerated well.',0.00,1.50,'2026-08-28 10:30:00'),
(19,19,5,1,2,'2026-08-30 11:00:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',27.00,0.00,'2026-08-30 11:00:00'),
(20,20,6,2,3,'2026-09-01 12:30:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',30.00,0.00,'2026-09-01 12:30:00'),
(21,21,7,3,4,'2026-09-03 13:00:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',21.00,0.00,'2026-09-03 13:00:00'),
(22,22,8,1,5,'2026-09-04 14:30:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',23.00,0.00,'2026-09-04 14:30:00'),
(23,23,9,2,6,'2026-09-06 15:00:00','27G cannula','Uneventful. Patient tolerated well.',0.00,1.20,'2026-09-06 15:00:00'),
(24,24,10,3,7,'2026-09-08 16:30:00','27G cannula','Uneventful. Patient tolerated well.',0.00,1.00,'2026-09-08 16:30:00'),
(25,25,11,1,8,'2026-09-10 09:00:00','27G cannula','Uneventful. Patient tolerated well.',0.00,0.80,'2026-09-10 09:00:00'),
(26,26,12,2,9,'2026-09-11 10:30:00','Blunt cannula 19G','Uneventful. Patient tolerated well.',0.00,0.00,'2026-09-11 10:30:00'),
(27,27,13,3,10,'2026-09-13 11:00:00','27G cannula','Uneventful. Patient tolerated well.',0.00,1.50,'2026-09-13 11:00:00'),
(28,28,14,1,2,'2026-09-15 12:30:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',36.00,0.00,'2026-09-15 12:30:00'),
(29,29,1,2,3,'2026-09-16 13:00:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',33.00,0.00,'2026-09-16 13:00:00'),
(30,30,2,3,4,'2026-09-18 14:30:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',21.00,0.00,'2026-09-18 14:30:00'),
(31,31,3,1,5,'2026-09-20 15:00:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',17.00,0.00,'2026-09-20 15:00:00'),
(32,32,4,2,6,'2026-09-22 16:30:00','27G cannula','Uneventful. Patient tolerated well.',0.00,1.20,'2026-09-22 16:30:00'),
(33,33,5,3,7,'2026-09-23 09:00:00','27G cannula','Uneventful. Patient tolerated well.',0.00,1.00,'2026-09-23 09:00:00'),
(34,34,6,1,8,'2026-09-25 10:30:00','27G cannula','Uneventful. Patient tolerated well.',0.00,0.80,'2026-09-25 10:30:00'),
(35,35,7,2,9,'2026-09-27 11:00:00','Blunt cannula 19G','Uneventful. Patient tolerated well.',0.00,0.00,'2026-09-27 11:00:00'),
(36,36,8,3,10,'2026-09-29 12:30:00','27G cannula','Uneventful. Patient tolerated well.',0.00,1.50,'2026-09-29 12:30:00'),
(37,37,9,1,2,'2026-09-30 13:00:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',27.00,0.00,'2026-09-30 13:00:00'),
(38,38,10,2,3,'2026-10-02 14:30:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',30.00,0.00,'2026-10-02 14:30:00'),
(39,39,11,3,4,'2026-10-04 15:00:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',21.00,0.00,'2026-10-04 15:00:00'),
(40,40,12,1,5,'2026-10-05 16:30:00','30G needle · 2.5ml saline dilution','Uneventful. Patient tolerated well.',23.00,0.00,'2026-10-05 16:30:00'),
(41,41,13,2,6,'2026-10-07 09:00:00','27G cannula','Uneventful. Patient tolerated well.',0.00,1.20,'2026-10-07 09:00:00'),
(42,42,14,3,7,'2026-10-09 10:30:00','27G cannula','Uneventful. Patient tolerated well.',0.00,1.00,'2026-10-09 10:30:00'),
(43,43,1,1,8,'2026-10-11 11:00:00','27G cannula','Uneventful. Patient tolerated well.',0.00,0.80,'2026-10-11 11:00:00'),
(44,44,2,2,9,'2026-10-12 12:30:00','Blunt cannula 19G','Uneventful. Patient tolerated well.',0.00,0.00,'2026-10-12 12:30:00'),
(45,45,3,3,10,'2026-10-14 13:00:00','27G cannula','Uneventful. Patient tolerated well.',0.00,1.50,'2026-10-14 13:00:00');
/*!40000 ALTER TABLE `treatment_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `treatments`
--

DROP TABLE IF EXISTS `treatments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `treatments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(140) NOT NULL,
  `category` enum('toxin','filler','thread','skinbooster','consultation','other') NOT NULL,
  `duration_min` smallint(5) unsigned NOT NULL DEFAULT 30,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_type` enum('fixed','per_unit','per_ml') NOT NULL DEFAULT 'fixed',
  `requires_consent` tinyint(1) NOT NULL DEFAULT 1,
  `followup_days` smallint(5) unsigned DEFAULT NULL COMMENT 'review due after N days',
  `recall_days` smallint(5) unsigned DEFAULT NULL COMMENT 'recall patient after N days',
  `aftercare_id` int(10) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `ix_treatments_cat` (`category`),
  KEY `fk_treat_aftercare` (`aftercare_id`),
  CONSTRAINT `fk_treat_aftercare` FOREIGN KEY (`aftercare_id`) REFERENCES `aftercare_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `treatments`
--

LOCK TABLES `treatments` WRITE;
/*!40000 ALTER TABLE `treatments` DISABLE KEYS */;
INSERT INTO `treatments` VALUES
(1,'Consultation','consultation',30,0.00,'fixed',0,NULL,NULL,NULL,NULL,1),
(2,'Botox — upper face','toxin',30,12.00,'per_unit',1,14,120,1,NULL,1),
(3,'Botox — forehead & glabella','toxin',30,12.00,'per_unit',1,14,120,1,NULL,1),
(4,'Botox — crow’s feet','toxin',25,12.00,'per_unit',1,14,120,1,NULL,1),
(5,'Masseter / jaw slimming','toxin',30,12.00,'per_unit',1,21,150,1,NULL,1),
(6,'Lip filler — 1ml','filler',45,450.00,'fixed',1,14,270,2,NULL,1),
(7,'Cheek filler — 1ml','filler',45,520.00,'fixed',1,14,300,2,NULL,1),
(8,'Nasolabial folds — 1ml','filler',40,480.00,'fixed',1,14,270,2,NULL,1),
(9,'PDO threads — jawline','thread',60,780.00,'fixed',1,21,365,3,NULL,1),
(10,'Profhilo — skin booster','skinbooster',30,390.00,'fixed',1,28,180,4,NULL,1);
/*!40000 ALTER TABLE `treatments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` tinyint(3) unsigned NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `avatar_initials` varchar(4) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `ix_users_role` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,1,'Dr. Rana Hajjar','owner@aesthetica.test','$2y$12$4mDLq7BIMT3yhTfmrlcR0OIaW/cN/clUtPWfMYiNwoTxYC4LsKe3W','+961 3 111 222','RH',1,NULL,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(2,2,'Dr. Karim Nassar','karim@aesthetica.test','$2y$12$4mDLq7BIMT3yhTfmrlcR0OIaW/cN/clUtPWfMYiNwoTxYC4LsKe3W','+961 3 333 444','KN',1,NULL,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(3,2,'Dr. Lea Haddad','lea@aesthetica.test','$2y$12$4mDLq7BIMT3yhTfmrlcR0OIaW/cN/clUtPWfMYiNwoTxYC4LsKe3W','+961 3 555 666','LH',1,NULL,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(4,3,'Yara Chami','yara@aesthetica.test','$2y$12$4mDLq7BIMT3yhTfmrlcR0OIaW/cN/clUtPWfMYiNwoTxYC4LsKe3W','+961 3 777 888','YC',1,NULL,'2026-10-14 08:43:51','2026-10-14 08:43:51'),
(5,4,'Nadine Rizk','reception@aesthetica.test','$2y$12$4mDLq7BIMT3yhTfmrlcR0OIaW/cN/clUtPWfMYiNwoTxYC4LsKe3W','+961 3 999 000','NR',1,NULL,'2026-10-14 08:43:51','2026-10-14 08:43:51');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
