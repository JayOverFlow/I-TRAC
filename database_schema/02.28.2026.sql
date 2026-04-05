-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: itrac_db
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins_tbl`
--

DROP TABLE IF EXISTS `admins_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins_tbl` (
  `admin_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `admin_username` varchar(100) DEFAULT NULL,
  `admin_password` varchar(100) DEFAULT NULL,
  `admin_key` bigint DEFAULT NULL,
  PRIMARY KEY (`admin_id`),
  KEY `admin_key` (`admin_key`),
  CONSTRAINT `admins_tbl_ibfk_1` FOREIGN KEY (`admin_key`) REFERENCES `master_keys_tbl` (`master_key_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins_tbl`
--

LOCK TABLES `admins_tbl` WRITE;
/*!40000 ALTER TABLE `admins_tbl` DISABLE KEYS */;
INSERT INTO `admins_tbl` VALUES (1,'emmanadmin','$2y$12$2Fx3A90/xuLsrZvdjjcgi.hU04aYOQx38s1bifn5Lk35Qms6Ky5z.',5);
/*!40000 ALTER TABLE `admins_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_items_tbl`
--

DROP TABLE IF EXISTS `app_items_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_items_tbl` (
  `app_item_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `app_id_fk` bigint unsigned DEFAULT NULL,
  `app_item_name` varchar(100) DEFAULT NULL,
  `app_item_pmo` varchar(150) DEFAULT NULL,
  `app_item_mode` varchar(100) DEFAULT NULL,
  `app_item_estimated_total` decimal(10,2) DEFAULT NULL,
  `app_item_estimated_mooe` decimal(10,2) DEFAULT NULL,
  `app_item_estimated_co` decimal(10,2) DEFAULT NULL,
  `app_item_remarks` varchar(150) DEFAULT NULL,
  `app_item_adspost` date DEFAULT NULL,
  `app_item_subopen` date DEFAULT NULL,
  `app_item_notice` date DEFAULT NULL,
  `app_item_contract` date DEFAULT NULL,
  `app_item_source_fund` varchar(100) DEFAULT NULL,
  `app_item_code` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`app_item_id`),
  KEY `fk_app_items_app` (`app_id_fk`),
  CONSTRAINT `fk_app_items_app` FOREIGN KEY (`app_id_fk`) REFERENCES `app_tbl` (`app_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_items_tbl`
--

LOCK TABLES `app_items_tbl` WRITE;
/*!40000 ALTER TABLE `app_items_tbl` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_items_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_tbl`
--

DROP TABLE IF EXISTS `app_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_tbl` (
  `app_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `app_ppmp_items_id_fk` bigint DEFAULT NULL,
  `app_status` enum('Draft','Pending','Rejected','Approved') DEFAULT NULL,
  `saved_by_user_id_fk` bigint unsigned DEFAULT NULL,
  `app_prepared_by_name` bigint unsigned DEFAULT NULL,
  `app_prepared_by_designation` varchar(100) DEFAULT NULL,
  `app_recommending_by_name` bigint unsigned DEFAULT NULL,
  `app_recommending_by_designation` varchar(100) DEFAULT NULL,
  `app_approved_by_name` bigint unsigned DEFAULT NULL,
  `app_approved_by_designation` varchar(100) DEFAULT NULL,
  `app_dep_id_fk` bigint DEFAULT NULL,
  PRIMARY KEY (`app_id`),
  KEY `fk_app_saved_by` (`saved_by_user_id_fk`),
  KEY `fk_app_prepared_by` (`app_prepared_by_name`),
  KEY `fk_app_recommending_by` (`app_recommending_by_name`),
  KEY `fk_app_approved_by` (`app_approved_by_name`),
  KEY `fk_app_department` (`app_dep_id_fk`),
  CONSTRAINT `fk_app_approved_by` FOREIGN KEY (`app_approved_by_name`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_app_department` FOREIGN KEY (`app_dep_id_fk`) REFERENCES `departments_tbl` (`dep_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_app_prepared_by` FOREIGN KEY (`app_prepared_by_name`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_app_recommending_by` FOREIGN KEY (`app_recommending_by_name`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_app_saved_by` FOREIGN KEY (`saved_by_user_id_fk`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_tbl`
--

LOCK TABLES `app_tbl` WRITE;
/*!40000 ALTER TABLE `app_tbl` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments_tbl`
--

DROP TABLE IF EXISTS `departments_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments_tbl` (
  `dep_id` bigint NOT NULL AUTO_INCREMENT,
  `dep_name` varchar(255) NOT NULL,
  `dep_type` enum('academic','administrative') NOT NULL,
  PRIMARY KEY (`dep_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments_tbl`
--

LOCK TABLES `departments_tbl` WRITE;
/*!40000 ALTER TABLE `departments_tbl` DISABLE KEYS */;
INSERT INTO `departments_tbl` VALUES (1,'Basic Arts and Sciences Department','academic'),(2,'Civil and Allied Department','academic'),(3,'Mechanical and Allied Department','academic'),(4,'Electrical and Allied Department','academic'),(5,'Office of Student Affairs','administrative'),(6,'Admission, Guidance and Counseling','administrative'),(7,'Research and Development Services','administrative'),(8,'Extension Services','administrative'),(9,'Innovation and Technology Support Office','administrative'),(10,'Technology Licensing Office Coordinator','administrative'),(11,'Quality Assurance','administrative'),(12,'University Information Technology Center','administrative'),(13,'Gender and Development','administrative'),(14,'Human Resource Management','administrative'),(15,'Property and Supply','administrative'),(16,'Procurement','administrative'),(17,'Infrastructure Development','administrative'),(18,'Building and Grounds Maintenance','administrative'),(19,'Accounting','administrative'),(20,'Budgeting','administrative'),(21,'Collecting and Disbursing','administrative'),(22,'Medical Services','administrative'),(23,'Dental Services','administrative'),(24,'Records Management','administrative'),(25,'BAC Secretariat','administrative'),(26,'Campus Business Manager','administrative'),(27,'Registration','administrative'),(28,'Learning Resource Center','administrative'),(29,'Sports and Cultural Development','administrative'),(30,'Planning Office','administrative'),(31,'National Service Training Program','administrative'),(35,'Director\'s Office','administrative'),(36,'Assistant Director for Academic Affairs Office','administrative'),(38,'Assistant Director for Research and Extension Office','administrative'),(39,'Project Management Committee','administrative'),(40,'Assistant Director for Administration and Finance Office','administrative');
/*!40000 ALTER TABLE `departments_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_verifications`
--

DROP TABLE IF EXISTS `email_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_verifications` (
  `email_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `verification_code` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`email_id`),
  KEY `email_verifications_email_index` (`email`),
  KEY `email_verifications_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_verifications`
--

LOCK TABLES `email_verifications` WRITE;
/*!40000 ALTER TABLE `email_verifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_verifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iar_items_specs_tbl`
--

DROP TABLE IF EXISTS `iar_items_specs_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iar_items_specs_tbl` (
  `iar_items_spec_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `iar_items_id_fk` bigint unsigned NOT NULL,
  `iar_spec_description` text NOT NULL,
  PRIMARY KEY (`iar_items_spec_id`),
  KEY `fk_iar_specs_item` (`iar_items_id_fk`),
  CONSTRAINT `fk_iar_specs_item` FOREIGN KEY (`iar_items_id_fk`) REFERENCES `iar_items_tbl` (`iar_items_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iar_items_specs_tbl`
--

LOCK TABLES `iar_items_specs_tbl` WRITE;
/*!40000 ALTER TABLE `iar_items_specs_tbl` DISABLE KEYS */;
/*!40000 ALTER TABLE `iar_items_specs_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iar_items_tbl`
--

DROP TABLE IF EXISTS `iar_items_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iar_items_tbl` (
  `iar_items_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `iar_id_fk` bigint unsigned NOT NULL,
  `iar_po_items_id_fk` bigint unsigned NOT NULL,
  `iar_stock_no` varchar(20) DEFAULT NULL,
  `iar_items_descrip` varchar(255) NOT NULL,
  `iar_unit` varchar(20) DEFAULT NULL,
  `iar_quantity` int DEFAULT NULL,
  PRIMARY KEY (`iar_items_id`),
  KEY `fk_iar_items_header` (`iar_id_fk`),
  KEY `fk_iar_items_po` (`iar_po_items_id_fk`),
  CONSTRAINT `fk_iar_items_header` FOREIGN KEY (`iar_id_fk`) REFERENCES `iar_tbl` (`iar_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_iar_items_po` FOREIGN KEY (`iar_po_items_id_fk`) REFERENCES `po_items_tbl` (`po_items_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iar_items_tbl`
--

LOCK TABLES `iar_items_tbl` WRITE;
/*!40000 ALTER TABLE `iar_items_tbl` DISABLE KEYS */;
/*!40000 ALTER TABLE `iar_items_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iar_tbl`
--

DROP TABLE IF EXISTS `iar_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iar_tbl` (
  `iar_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `iar_po_id_fk` bigint unsigned NOT NULL,
  `iar_fund_cluster` varchar(50) DEFAULT NULL,
  `iar_supplier` varchar(100) DEFAULT NULL,
  `iar_no` varchar(50) DEFAULT NULL,
  `iar_no_date` varchar(20) DEFAULT NULL,
  `iar_invoice_no` varchar(50) DEFAULT NULL,
  `iar_invoice_date` varchar(20) DEFAULT NULL,
  `iar_po_no_date` varchar(20) DEFAULT NULL,
  `iar_office` varchar(50) DEFAULT NULL,
  `iar_center_code` varchar(20) DEFAULT NULL,
  `iar_date_inspected` date DEFAULT NULL,
  `iar_inspected_by` bigint unsigned DEFAULT NULL,
  `iar_received_by` bigint unsigned DEFAULT NULL,
  `iar_date_received` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`iar_id`),
  KEY `fk_iar_inspected` (`iar_inspected_by`),
  KEY `fk_iar_received` (`iar_received_by`),
  KEY `fk_iar_po` (`iar_po_id_fk`),
  CONSTRAINT `fk_iar_inspected` FOREIGN KEY (`iar_inspected_by`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_iar_po` FOREIGN KEY (`iar_po_id_fk`) REFERENCES `po_tbl` (`po_id`),
  CONSTRAINT `fk_iar_received` FOREIGN KEY (`iar_received_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iar_tbl`
--

LOCK TABLES `iar_tbl` WRITE;
/*!40000 ALTER TABLE `iar_tbl` DISABLE KEYS */;
/*!40000 ALTER TABLE `iar_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_keys_tbl`
--

DROP TABLE IF EXISTS `master_keys_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `master_keys_tbl` (
  `master_key_id` bigint NOT NULL AUTO_INCREMENT,
  `master_key` varchar(100) NOT NULL,
  PRIMARY KEY (`master_key_id`),
  UNIQUE KEY `master_key` (`master_key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_keys_tbl`
--

LOCK TABLES `master_keys_tbl` WRITE;
/*!40000 ALTER TABLE `master_keys_tbl` DISABLE KEYS */;
INSERT INTO `master_keys_tbl` VALUES (1,'6oYknwNzbC'),(2,'J54oN8U6p6'),(4,'MAjqqnoQK0'),(3,'rx0qnPiajP'),(5,'yz5QBm908y');
/*!40000 ALTER TABLE `master_keys_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_02_22_052530_create_personal_access_tokens_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User',5,'auth_token','23ad10ed23c217db573dfde80d8ff5246824d2aee53331487f2d53777aee7a23','[\"*\"]',NULL,NULL,'2026-02-21 21:29:25','2026-02-21 21:29:25'),(2,'App\\Models\\User',5,'auth_token','2b1a0814c158c8fde08341cba4b613358a9f10d251c0d210c8e51e4f1bea5739','[\"*\"]',NULL,NULL,'2026-02-23 05:13:22','2026-02-23 05:13:22'),(3,'App\\Models\\User',5,'auth_token','749eafeeb84a4908ee1f1e2a975103663a467e8ea568465d6fe795abbd1e65e4','[\"*\"]',NULL,NULL,'2026-02-24 05:32:20','2026-02-24 05:32:20'),(4,'App\\Models\\User',5,'auth_token','7a6964654214663570701fb67e7aa8ae0a60944a34f9de2f2b6dbe9d5b0f486b','[\"*\"]',NULL,NULL,'2026-02-24 05:33:13','2026-02-24 05:33:13'),(5,'App\\Models\\User',6,'auth_token','2f8d36be756883f37d2bc49f6f09659b85fc536113cceffcf533981b9e0b462d','[\"*\"]',NULL,NULL,'2026-02-24 06:00:18','2026-02-24 06:00:18'),(6,'App\\Models\\User',5,'auth_token','cc4844f96007d1e9fbc3a5d865b96745df6488ff29073a16625f7f8e6c58c816','[\"*\"]',NULL,NULL,'2026-02-26 05:07:05','2026-02-26 05:07:05'),(7,'App\\Models\\User',7,'auth_token','d9254edb9edbc3199d97442118a5223d9858c26de1f844eab141a914598c31b9','[\"*\"]',NULL,NULL,'2026-02-26 05:45:03','2026-02-26 05:45:03'),(8,'App\\Models\\User',7,'auth_token','6250fafbcadd01a876eb155c290a9f492e2ca91465cf87dccf8f3736f5ff0efd','[\"*\"]',NULL,NULL,'2026-02-26 15:35:04','2026-02-26 15:35:04'),(9,'App\\Models\\User',7,'auth_token','e0bc5c96ecf51e121fbe821b81c6a40294d1472a69ca74d0e193041ed59f5b86','[\"*\"]',NULL,NULL,'2026-02-26 20:46:54','2026-02-26 20:46:54'),(10,'App\\Models\\User',7,'auth_token','263278b93235e1af5e59a7b3a265a134a06dbcf22bafb9bb23931e24627104b2','[\"*\"]',NULL,NULL,'2026-02-27 06:05:19','2026-02-27 06:05:19'),(11,'App\\Models\\User',7,'auth_token','b12077d9b5817c447799bc19161040270491920264ffdf1d906fce06e20813d2','[\"*\"]',NULL,NULL,'2026-02-27 06:34:12','2026-02-27 06:34:12'),(12,'App\\Models\\User',7,'auth_token','c1f9ebae6e51af352f10f771139045f075cff44b5dc662d580a196c8dc8dd8af','[\"*\"]',NULL,NULL,'2026-02-27 13:38:44','2026-02-27 13:38:44'),(13,'App\\Models\\User',7,'auth_token','029eeed40c6ce816c2e98bc63c9220f4bbf9dfea031efa0b059a174bfae7fda5','[\"*\"]',NULL,NULL,'2026-02-27 17:56:01','2026-02-27 17:56:01'),(14,'App\\Models\\User',7,'auth_token','a74ddd3a5f3b3a22ec91679830b6d02f2b08e5f3d6b776eaa5ac9db192bd7d50','[\"*\"]',NULL,NULL,'2026-02-27 21:49:45','2026-02-27 21:49:45'),(15,'App\\Models\\User',7,'auth_token','ee91a9291b82f47b77b27b9062629aa96ad6a94d4842b2a21c0baa794c8f28b0','[\"*\"]',NULL,NULL,'2026-02-27 21:55:09','2026-02-27 21:55:09'),(16,'App\\Models\\User',7,'auth_token','d1a2001b88c1f55e8e341102477093c290ada11011499f44a818a6ea28b22890','[\"*\"]',NULL,NULL,'2026-02-27 22:37:04','2026-02-27 22:37:04'),(17,'App\\Models\\User',8,'auth_token','03b75973ff79c761139704ffacb3d75e06ee0388d0269988ecedfb3edfb03cd3','[\"*\"]',NULL,NULL,'2026-02-28 01:46:43','2026-02-28 01:46:43'),(18,'App\\Models\\User',8,'auth_token','37db8c613d397c3a42e0cd1f870b3530dc16484a6d4e7d2643239c49c5f9ead6','[\"*\"]',NULL,NULL,'2026-02-28 01:57:58','2026-02-28 01:57:58');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `po_items_specs_tbl`
--

DROP TABLE IF EXISTS `po_items_specs_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `po_items_specs_tbl` (
  `po_items_spec_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `po_items_id_fk` bigint unsigned NOT NULL,
  `po_spec_description` text,
  PRIMARY KEY (`po_items_spec_id`),
  KEY `idx_po_items_fk` (`po_items_id_fk`),
  CONSTRAINT `fk_po_specs_item` FOREIGN KEY (`po_items_id_fk`) REFERENCES `po_items_tbl` (`po_items_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `po_items_specs_tbl`
--

LOCK TABLES `po_items_specs_tbl` WRITE;
/*!40000 ALTER TABLE `po_items_specs_tbl` DISABLE KEYS */;
/*!40000 ALTER TABLE `po_items_specs_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `po_items_tbl`
--

DROP TABLE IF EXISTS `po_items_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `po_items_tbl` (
  `po_items_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `po_id_fk` bigint unsigned NOT NULL,
  `po_pr_items_id_fk` bigint unsigned NOT NULL,
  `po_items_stockno` bigint unsigned DEFAULT NULL,
  `po_items_unit` varchar(20) DEFAULT NULL,
  `po_items_descrip` varchar(255) DEFAULT NULL,
  `po_items_quantity` int DEFAULT NULL,
  `po_items_cost` decimal(10,2) DEFAULT NULL,
  `po_items_amount` decimal(10,2) DEFAULT NULL,
  `po_items_total` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`po_items_id`),
  KEY `idx_po_id_fk` (`po_id_fk`),
  KEY `idx_pr_items_fk` (`po_pr_items_id_fk`),
  CONSTRAINT `fk_po_items_po` FOREIGN KEY (`po_id_fk`) REFERENCES `po_tbl` (`po_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_po_items_pr_items` FOREIGN KEY (`po_pr_items_id_fk`) REFERENCES `pr_items_tbl` (`pr_items_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `po_items_tbl`
--

LOCK TABLES `po_items_tbl` WRITE;
/*!40000 ALTER TABLE `po_items_tbl` DISABLE KEYS */;
/*!40000 ALTER TABLE `po_items_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `po_tbl`
--

DROP TABLE IF EXISTS `po_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `po_tbl` (
  `po_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `po_supplier` varchar(100) DEFAULT NULL,
  `po_no` varchar(50) DEFAULT NULL,
  `po_date` varchar(50) DEFAULT NULL,
  `po_address` varchar(200) DEFAULT NULL,
  `po_tele` varchar(60) DEFAULT NULL,
  `po_tin` varchar(60) DEFAULT NULL,
  `po_mode` varchar(50) DEFAULT NULL,
  `po_tuptin` varchar(60) DEFAULT NULL,
  `po_place_delivery` varchar(100) DEFAULT NULL,
  `po_delivery_term` varchar(50) DEFAULT NULL,
  `po_date_delivery` varchar(50) DEFAULT NULL,
  `po_payment_term` varchar(50) DEFAULT NULL,
  `po_signed_by_fk` bigint unsigned DEFAULT NULL,
  `po_fund_cluster` varchar(50) DEFAULT NULL,
  `po_fund_available` varchar(100) DEFAULT NULL,
  `po_orsburs` varchar(50) DEFAULT NULL,
  `po_date_orsburs` varchar(50) DEFAULT NULL,
  `po_amount` bigint unsigned DEFAULT NULL,
  `po_total_amount` decimal(10,2) DEFAULT NULL,
  `po_amount_in_words` varchar(100) DEFAULT NULL,
  `po_description` text,
  `po_remarks` text,
  `conforme_name_of_supplier` varchar(50) DEFAULT NULL,
  `conforme_date` varchar(55) DEFAULT NULL,
  `conforme_campus_director` varchar(50) DEFAULT NULL,
  `saved_by_user_id_fk` bigint unsigned DEFAULT NULL,
  `po_unique_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`po_id`),
  KEY `idx_po_signed_by` (`po_signed_by_fk`),
  KEY `idx_saved_by_user` (`saved_by_user_id_fk`),
  CONSTRAINT `fk_po_saved_by` FOREIGN KEY (`saved_by_user_id_fk`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_po_signed_by` FOREIGN KEY (`po_signed_by_fk`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `po_tbl`
--

LOCK TABLES `po_tbl` WRITE;
/*!40000 ALTER TABLE `po_tbl` DISABLE KEYS */;
/*!40000 ALTER TABLE `po_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr_items_specs_tbl`
--

DROP TABLE IF EXISTS `pr_items_specs_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pr_items_specs_tbl` (
  `pr_items_spec_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pr_items_id_fk` bigint unsigned NOT NULL,
  `pr_spec_description` text NOT NULL,
  PRIMARY KEY (`pr_items_spec_id`),
  KEY `idx_pr_items_id_fk` (`pr_items_id_fk`),
  CONSTRAINT `fk_pr_specs_item` FOREIGN KEY (`pr_items_id_fk`) REFERENCES `pr_items_tbl` (`pr_items_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr_items_specs_tbl`
--

LOCK TABLES `pr_items_specs_tbl` WRITE;
/*!40000 ALTER TABLE `pr_items_specs_tbl` DISABLE KEYS */;
/*!40000 ALTER TABLE `pr_items_specs_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr_items_tbl`
--

DROP TABLE IF EXISTS `pr_items_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pr_items_tbl` (
  `pr_items_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pr_id_fk` bigint unsigned NOT NULL,
  `pr_app_item_id_fk` bigint unsigned NOT NULL,
  `pr_items_quantity` int DEFAULT NULL,
  `pr_items_unit` varchar(20) DEFAULT NULL,
  `pr_items_cost` decimal(10,2) DEFAULT NULL,
  `pr_items_total_cost` decimal(10,2) DEFAULT NULL,
  `pr_items_descrip` varchar(255) DEFAULT NULL,
  `bidding_status` enum('pending','successful','unsuccessful') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`pr_items_id`),
  KEY `idx_pr_id_fk` (`pr_id_fk`),
  KEY `idx_app_item_fk` (`pr_app_item_id_fk`),
  CONSTRAINT `fk_pr_items_app_item` FOREIGN KEY (`pr_app_item_id_fk`) REFERENCES `app_items_tbl` (`app_item_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pr_items_pr` FOREIGN KEY (`pr_id_fk`) REFERENCES `pr_tbl` (`pr_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr_items_tbl`
--

LOCK TABLES `pr_items_tbl` WRITE;
/*!40000 ALTER TABLE `pr_items_tbl` DISABLE KEYS */;
/*!40000 ALTER TABLE `pr_items_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr_tbl`
--

DROP TABLE IF EXISTS `pr_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pr_tbl` (
  `pr_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pr_section` varchar(50) DEFAULT NULL,
  `pr_department` bigint unsigned DEFAULT NULL,
  `pr_no` varchar(20) DEFAULT NULL,
  `pr_date` varchar(20) DEFAULT NULL,
  `pr_purpose` varchar(50) DEFAULT NULL,
  `pr_name_of_requestor` bigint unsigned DEFAULT NULL,
  `pr_designation` varchar(100) DEFAULT NULL,
  `pr_approved_by` bigint unsigned DEFAULT NULL,
  `pr_approved_by_designation` varchar(100) DEFAULT NULL,
  `saved_by_user_id_fk` bigint unsigned DEFAULT NULL,
  `pr_unique_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pr_id`),
  KEY `fk_pr_department` (`pr_department`),
  KEY `fk_pr_name_of_requestor` (`pr_name_of_requestor`),
  KEY `fk_pr_approved_by` (`pr_approved_by`),
  KEY `fk_saved_by_user_id_pr` (`saved_by_user_id_fk`),
  CONSTRAINT `fk_pr_approved_by` FOREIGN KEY (`pr_approved_by`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_pr_department` FOREIGN KEY (`pr_department`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_pr_requested_by` FOREIGN KEY (`pr_name_of_requestor`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_saved_by_user_id_pr` FOREIGN KEY (`saved_by_user_id_fk`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr_tbl`
--

LOCK TABLES `pr_tbl` WRITE;
/*!40000 ALTER TABLE `pr_tbl` DISABLE KEYS */;
/*!40000 ALTER TABLE `pr_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_tbl`
--

DROP TABLE IF EXISTS `roles_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles_tbl` (
  `role_id` bigint NOT NULL AUTO_INCREMENT,
  `role_name` varchar(255) NOT NULL,
  `role_dep_id_fk` bigint DEFAULT NULL,
  `role_parent_id` bigint DEFAULT NULL,
  `gen_role` enum('Head','Procurement','Supply','Unassigned') DEFAULT 'Unassigned',
  PRIMARY KEY (`role_id`),
  KEY `role_dep_id_fk` (`role_dep_id_fk`),
  KEY `role_parent_id` (`role_parent_id`),
  CONSTRAINT `roles_tbl_ibfk_1` FOREIGN KEY (`role_dep_id_fk`) REFERENCES `departments_tbl` (`dep_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `roles_tbl_ibfk_2` FOREIGN KEY (`role_parent_id`) REFERENCES `roles_tbl` (`role_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_tbl`
--

LOCK TABLES `roles_tbl` WRITE;
/*!40000 ALTER TABLE `roles_tbl` DISABLE KEYS */;
INSERT INTO `roles_tbl` VALUES (1,'Department Head - Basic Arts and Sciences Department',1,NULL,'Unassigned'),(2,'Campus Director',35,NULL,'Unassigned'),(3,'Assistant Director for Research and Extension',38,NULL,'Unassigned'),(5,'Department Head - Civil and Allied Department',2,NULL,'Unassigned'),(6,'Department Head - Mechanical and Allied Department',3,NULL,'Unassigned'),(7,'Department Head - Electrical and Allied Department',4,NULL,'Unassigned'),(8,'Planning Officer',30,NULL,'Unassigned'),(9,'Head - Human Resource Management',14,NULL,'Unassigned'),(10,'Head - Property and Supply',15,NULL,'Unassigned'),(11,'Head - Procurement',16,NULL,'Unassigned'),(12,'Head - Infrastructure Development',17,NULL,'Unassigned'),(13,'Head - Building and Grounds Maintenance',18,NULL,'Unassigned'),(14,'Head - Accounting',19,NULL,'Unassigned'),(15,'Head - Budgeting',20,NULL,'Unassigned'),(16,'Head - Collecting and Disbursing',21,NULL,'Unassigned'),(17,'Head - Medical Services',22,NULL,'Unassigned'),(18,'Head - Dental Services',23,NULL,'Unassigned'),(19,'Head - Records Management',24,NULL,'Unassigned'),(20,'Head - BAC Secretariat',25,NULL,'Unassigned'),(21,'Head - Campus Business Manager',26,NULL,'Unassigned'),(22,'Head - Office of Student Affairs',5,NULL,'Unassigned'),(23,'Head - Admission, Guidance and Counseling',6,NULL,'Unassigned'),(24,'Head - National Service Training Program',31,NULL,'Unassigned'),(25,'Head - Learning Resource Center',28,NULL,'Unassigned'),(26,'Head - Sports and Cultural Development',29,NULL,'Unassigned'),(27,'Section Head - Bachelor of Engineering Technology Major in Chemical Technology',2,NULL,'Unassigned'),(28,'Section Head - Bachelor of Science in Environmental Science',2,NULL,'Unassigned'),(29,'Section Head - Bachelor of Science in Civil Engineering',2,NULL,'Unassigned'),(30,'Section Head - Bachelor of Engineering Technology Major in Civil Technology',2,NULL,'Unassigned'),(31,'Section Head - Bachelor of Science in Electronics Engineering',4,NULL,'Unassigned'),(32,'Section Head - Bachelor of Engineering Technology Major in Electronics Technology',4,NULL,'Unassigned'),(33,'Section Head - Bachelor of Science in Information Technology',4,NULL,'Unassigned'),(34,'Section Head - Bachelor of Science in Electrical Engineering',4,NULL,'Unassigned'),(35,'Section Head - Bachelor of Engineering Technology Major in Electrical Technology',4,NULL,'Unassigned'),(36,'Section Head - Bachelor of Engineering Technology Major in Instrumentation and Controls',4,NULL,'Unassigned'),(37,'Section Head - Bachelor of Engineering Technology Major in Mechatronics Technology',4,NULL,'Unassigned'),(38,'Section Head - Bachelor of Science in Mechanical Engineering',3,NULL,'Unassigned'),(39,'Section Head - Bachelor of Engineering Technology Major in Heating, Ventilation and Air Conditioning, and Refrigeration Technology',3,NULL,'Unassigned'),(40,'Section Head - Bachelor of Engineering Technology Major in Dies and Moulds Technology',3,NULL,'Unassigned'),(41,'Section Head - Bachelor of Engineering Technology Major in Non-Destructive Testing Technology',3,NULL,'Unassigned'),(42,'Section Head - Bachelor of Engineering Technology Major in Electromechanical Technology',3,NULL,'Unassigned'),(43,'Section Head - Bachelor of Engineering Technology Major in Automotive Technology',3,NULL,'Unassigned'),(44,'Section Head - Bachelor of Engineering Technology Major in Mechanical Technology',3,NULL,'Unassigned'),(45,'Head - Research and Development Services',7,NULL,'Unassigned'),(46,'Head - Extension Services',8,NULL,'Unassigned'),(47,'Head - Innovation Technology Support Office',9,NULL,'Unassigned'),(48,'Head - Technology Licensing Office Coordinator',10,NULL,'Unassigned'),(49,'Head - Quality Assurance',11,NULL,'Unassigned'),(50,'Head - University Information Technology Center',12,NULL,'Unassigned'),(51,'Head - Gender and Development',13,NULL,'Unassigned'),(52,'Head - Project Management Committee',39,NULL,'Unassigned'),(53,'Section Head - Bachelor of Technical-Vocational Teacher Education',1,NULL,'Unassigned'),(54,'Assistant Director for Academic Affairs',36,NULL,'Unassigned'),(55,'Assistant Director for Administration and Finance',40,NULL,'Unassigned');
/*!40000 ALTER TABLE `roles_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('Fav8CToYFZ18VNR0x4PcdBrYDAQfz26F5kWnqkV4',8,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','YTo3OntzOjY6Il90b2tlbiI7czo0MDoia3FHTXJVbUh0SXdwZFhoZHVYODZYengzdWt5MldOZG1YSUh1NmFIMiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly9sb2NhbGhvc3Q6ODA4MC9hZG1pbi9yb2xlcy1hc3NpZ25tZW50IjtzOjU6InJvdXRlIjtzOjIyOiJhZG1pbi5yb2xlcy1hc3NpZ25tZW50Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6ODtzOjg6ImFkbWluX2lkIjtpOjE7czoxNDoiYWRtaW5fdXNlcm5hbWUiO3M6MTA6ImVtbWFuYWRtaW4iO3M6MTg6ImlzX2FkbWluX2xvZ2dlZF9pbiI7YjoxO30=',1772276353),('X9eZMHRuyQqyFD7ngz4oSzlIErnKlnwl0VZ5EBzB',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','YTo2OntzOjY6Il90b2tlbiI7czo0MDoiYnZkWGc0VzVqQjgybUJvZ01oWjZ1ZzRqV1RDaU5GV2ZwaDZDdXlDSSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODA4MC9hZG1pbi9yb2xlcy1vZmZpY2VzIjtzOjU6InJvdXRlIjtzOjE5OiJhZG1pbi5yb2xlcy1vZmZpY2VzIjt9czo4OiJhZG1pbl9pZCI7aToxO3M6MTQ6ImFkbWluX3VzZXJuYW1lIjtzOjEwOiJlbW1hbmFkbWluIjtzOjE4OiJpc19hZG1pbl9sb2dnZWRfaW4iO2I6MTt9',1772274524);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks_tbl`
--

DROP TABLE IF EXISTS `tasks_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks_tbl` (
  `task_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assigned_by` bigint unsigned DEFAULT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `task_description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pr_id_fk` bigint unsigned DEFAULT NULL,
  `task_type` varchar(50) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `task_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  PRIMARY KEY (`task_id`),
  KEY `fk_tasks_submitted_by` (`assigned_by`),
  KEY `fk_tasks_submitted_to` (`assigned_to`),
  KEY `fk_tasks_pr` (`pr_id_fk`),
  CONSTRAINT `fk_tasks_pr` FOREIGN KEY (`pr_id_fk`) REFERENCES `pr_tbl` (`pr_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tasks_submitted_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_submitted_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks_tbl`
--

LOCK TABLES `tasks_tbl` WRITE;
/*!40000 ALTER TABLE `tasks_tbl` DISABLE KEYS */;
/*!40000 ALTER TABLE `tasks_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `user_assignments`
--

DROP TABLE IF EXISTS `user_assignments`;
/*!50001 DROP VIEW IF EXISTS `user_assignments`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `user_assignments` AS SELECT 
 1 AS `user_fullname`,
 1 AS `role_name`,
 1 AS `dep_name`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_departments_tbl`
--

DROP TABLE IF EXISTS `user_departments_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_departments_tbl` (
  `user_department_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id_fk` bigint unsigned NOT NULL,
  `department_id_fk` bigint NOT NULL,
  PRIMARY KEY (`user_department_id`),
  KEY `user_id_fk` (`user_id_fk`),
  KEY `department_id_fk` (`department_id_fk`),
  CONSTRAINT `fk_user_departments_department` FOREIGN KEY (`department_id_fk`) REFERENCES `departments_tbl` (`dep_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_user_departments_user` FOREIGN KEY (`user_id_fk`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_departments_tbl`
--

LOCK TABLES `user_departments_tbl` WRITE;
/*!40000 ALTER TABLE `user_departments_tbl` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_departments_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles_tbl`
--

DROP TABLE IF EXISTS `user_roles_tbl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles_tbl` (
  `user_role_id` bigint NOT NULL AUTO_INCREMENT,
  `user_id_fk` bigint unsigned NOT NULL,
  `role_id_fk` bigint DEFAULT NULL,
  PRIMARY KEY (`user_role_id`),
  KEY `user_id_fk` (`user_id_fk`),
  KEY `role_id_fk` (`role_id_fk`),
  CONSTRAINT `user_roles_tbl_ibfk_1` FOREIGN KEY (`user_id_fk`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_roles_tbl_ibfk_2` FOREIGN KEY (`role_id_fk`) REFERENCES `roles_tbl` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles_tbl`
--

LOCK TABLES `user_roles_tbl` WRITE;
/*!40000 ALTER TABLE `user_roles_tbl` DISABLE KEYS */;
INSERT INTO `user_roles_tbl` VALUES (1,2,1),(2,2,54);
/*!40000 ALTER TABLE `user_roles_tbl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_firstname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `user_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_middlename` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_lastname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_suffix` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_fullname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci GENERATED ALWAYS AS (concat(_utf8mb4'user_firstname',_utf8mb4' ',_utf8mb4'user_middlename',_utf8mb4' ',_utf8mb4'user_lastname',_utf8mb4' ',_utf8mb4'user_suffix',_utf8mb4' ')) VIRTUAL,
  `user_type` enum('Faculty','Staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_tupid` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `users_email_unique` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`user_id`, `user_firstname`, `user_email`, `email_verified_at`, `user_password`, `remember_token`, `created_at`, `updated_at`, `user_middlename`, `user_lastname`, `user_suffix`, `user_type`, `user_tupid`) VALUES (1,'Jayyy','jay@example.com',NULL,'$2y$12$gfqnw/WR71DZfUyzMOJGR.rm4Zn.oMdQgotZhgWZlTOWuPH1fqiHu',NULL,'2026-01-22 18:43:31','2026-01-22 18:43:31',NULL,NULL,NULL,NULL,NULL),(2,'Heherson','heherson.ramos@tup.edu.ph',NULL,'ramos12345',NULL,NULL,NULL,'Pagulayan','Ramos',NULL,NULL,NULL),(4,'Patrick Justin','patrickjustin_ariado@tup.edu.ph','2026-02-06 20:00:40','$2y$12$2Ea6JAA//GptR2vSvGt60eL9m2mZcgdfLH5kd0pC.xC5OpsDe4M8q',NULL,'2026-02-06 20:00:40','2026-02-06 20:00:40','Laurente','Ariado',NULL,'Faculty','182020'),(5,'John Rex Duran','johnrex.duran@tup.edu.ph','2026-02-12 22:07:39','$2y$12$5obQFszAXXyeD6oOapnFReZlW0b9WbBI7ui9I7iLfE60fjzHB6962',NULL,'2026-02-12 22:07:40','2026-02-12 22:07:40','Bautista','Duran',NULL,'Staff','230265'),(8,'Emmanuel','emmanuel.ferrer@tup.edu.ph','2026-02-28 00:56:05','$2y$12$EBjlQSLtSxt104CqeBMteu8VGOoeVm8/cFUMcobE5LZUhWK3VK/2C',NULL,'2026-02-28 00:56:05','2026-02-28 00:56:05','Peque','Ferrer',NULL,'Faculty','230252');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `view_user_roles_departments`
--

DROP TABLE IF EXISTS `view_user_roles_departments`;
/*!50001 DROP VIEW IF EXISTS `view_user_roles_departments`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_user_roles_departments` AS SELECT 
 1 AS `user_id`,
 1 AS `full_name`,
 1 AS `role_name`,
 1 AS `department`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `user_assignments`
--

/*!50001 DROP VIEW IF EXISTS `user_assignments`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `user_assignments` AS select concat(`u`.`user_firstname`,' ',`u`.`user_middlename`,' ',`u`.`user_lastname`) AS `user_fullname`,`r`.`role_name` AS `role_name`,`d`.`dep_name` AS `dep_name` from (((`user_roles_tbl` `ur` join `users` `u` on((`ur`.`user_id_fk` = `u`.`user_id`))) join `roles_tbl` `r` on((`ur`.`role_id_fk` = `r`.`role_id`))) join `departments_tbl` `d` on((`r`.`role_dep_id_fk` = `d`.`dep_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_user_roles_departments`
--

/*!50001 DROP VIEW IF EXISTS `view_user_roles_departments`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_user_roles_departments` AS select `u`.`user_id` AS `user_id`,concat_ws(' ',`u`.`user_firstname`,`u`.`user_middlename`,`u`.`user_lastname`) AS `full_name`,`r`.`role_name` AS `role_name`,`d`.`dep_name` AS `department` from ((((`users` `u` left join `user_roles_tbl` `ur` on((`u`.`user_id` = `ur`.`user_id_fk`))) left join `roles_tbl` `r` on((`ur`.`role_id_fk` = `r`.`role_id`))) left join `user_departments_tbl` `ud` on((`u`.`user_id` = `ud`.`user_id_fk`))) left join `departments_tbl` `d` on(((`d`.`dep_id` = `ud`.`department_id_fk`) or (`d`.`dep_id` = `r`.`role_dep_id_fk`)))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-28 19:00:18
