-- ============================================================
--  CrewSync Sample Data (from local crewsync database)
-- ============================================================
--  Data-only dump. Run AFTER crewsync_db_final.sql.
--  NOTE: `skills` and `materials` are already seeded by the
--  schema file (crewsync_db_final.sql), so they are excluded
--  here to avoid duplicate-key errors.
--  INSERTs are ordered to respect foreign keys.
-- ============================================================

-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: crewsync
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (1,'Nimal','Perera','nimal@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234501','Colombo','property_owner','2026-07-09 17:07:58','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (2,'Kamala','Fernando','kamala@gmail.com','$2b$12$cryQwbx0UHZn/.6dL3EkouezQwz2LNZxgMPl1cfnHnnz4txoycWUK','0771234502','Gampaha','property_owner','2026-07-09 17:07:58','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (3,'Ruwan','Silva','ruwan@gmail.com','$2b$12$/WNsbOcK6YeLAa66kH8saeS/EhGLEJ/0zUUYO7c8fQiRhqPfJC76C','0771234503','Kandy','property_owner','2026-07-09 17:07:58','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (4,'Sunil','Jayasinghe','sunil@gmail.com','$2b$12$b5W1PIZNeJErmVTCUMKPpOT4Z2aAFQKaEECchPGBm02N9kKDjaibS','0771234504','Colombo','service_provider','2026-07-09 17:07:58','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (5,'Ajith','Bandara','ajith@gmail.com','$2b$12$FORMrik65LrWNV5BNBWS3Oxd8sASIyU89fimjnQG4GJ64XQ8jmMJa','0771234505','Galle','service_provider','2026-07-09 17:07:58','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (6,'Chamara','Wijesuriya','chamara@gmail.com','$2b$12$B75EB4p7.505lHrQlriD6uN5q25Li3s75bZI2zP22Bh92WMC.fh4.','0771234506','Kandy','service_provider','2026-07-09 17:07:58','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (7,'Saman','Gunawardena','saman@gmail.com','$2b$12$/ijLoE1owvreHlHN12wUGu.t0zySbVa.ZHZFx7K8cfJQv8kUgsC3y','0771234507','Colombo','material_supplier','2026-07-09 17:07:58','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (8,'Priyantha','Dias','priyantha@gmail.com','$2b$12$a43XCERgmgxuwZ9RtquLqu.SXHvh9D1l8p85EjiGpbaTBexOLPypK','0771234508','Gampaha','material_supplier','2026-07-09 17:07:58','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (9,'Lasith','Kumara','lasith@gmail.com','$2b$12$JBKqi6fLeE3Hdtv8bT9AmuZtRTdkZmRgBgOUpxFwBr0zHjS5NS6ou','0771234509','Kurunegala','admin','2026-07-09 17:07:58','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (15,'Kasun','Rajapaksa','kasun@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234510','Colombo','service_provider','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (16,'Thilina','Madushanka','thilina@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234511','Kandy','service_provider','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (17,'Nuwan','Dissanayake','nuwan@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234512','Galle','service_provider','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (18,'Damith','Prasad','damith@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234513','Matara','service_provider','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (19,'Lahiru','Perera','lahiru@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234514','Gampaha','service_provider','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (20,'Isuru','Sanjeewa','isuru@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234515','Kurunegala','service_provider','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (21,'Chathura','Senanayake','chathura@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234516','Ratnapura','service_provider','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (22,'Malith','Gunasekara','malith@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234517','Badulla','service_provider','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (23,'Sachin','Liyanage','sachin@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234518','Hambantota','service_provider','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (24,'Dilan','Wickrama','dilan@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234519','Kalutara','service_provider','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (25,'Roshan','Mendis','roshan@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234520','Colombo','material_supplier','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (26,'Bandula','Rathnayake','bandula@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234521','Kandy','material_supplier','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (27,'Upul','Jayantha','upul@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234522','Gampaha','material_supplier','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (28,'Manoj','Wickramasinghe','manoj@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234523','Galle','material_supplier','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (29,'Tharaka','Nanayakkara','tharaka@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234524','Kurunegala','material_supplier','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (30,'Chaminda','Pushpakumara','chaminda@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234525','Matara','material_supplier','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (31,'Nishantha','Ekanayake','nishantha@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234526','Ratnapura','material_supplier','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (32,'Asanka','Pathirana','asanka@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234527','Kalutara','material_supplier','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (33,'Pradeep','Samaraweera','pradeep@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234528','Badulla','material_supplier','2026-08-09 14:01:00','active');
INSERT INTO `users` (`user_id`, `fname`, `lname`, `email`, `password_hash`, `contact_no`, `district`, `role`, `created_at`, `status`) VALUES (34,'Sanjeewa','Abeyrathne','sanjeewa@gmail.com','$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6','0771234529','Hambantota','material_supplier','2026-08-09 14:01:00','active');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `property_owners`
--

LOCK TABLES `property_owners` WRITE;
/*!40000 ALTER TABLE `property_owners` DISABLE KEYS */;
INSERT INTO `property_owners` (`owner_id`, `user_id`, `address`) VALUES (1,1,'25/1 Galle Road, Dehiwala');
INSERT INTO `property_owners` (`owner_id`, `user_id`, `address`) VALUES (2,2,'112 Negombo Road, Ja-Ela');
INSERT INTO `property_owners` (`owner_id`, `user_id`, `address`) VALUES (3,3,'48 Peradeniya Road, Kandy');
/*!40000 ALTER TABLE `property_owners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `service_providers`
--

LOCK TABLES `service_providers` WRITE;
/*!40000 ALTER TABLE `service_providers` DISABLE KEYS */;
INSERT INTO `service_providers` (`provider_id`, `user_id`, `bio`, `experience_yr`, `charge_per_day`, `avg_rating`, `is_available`, `willing_outside_region`) VALUES (1,4,'Experienced mason specializing in brickwork and plastering.',10,4500.00,4.50,1,1);
INSERT INTO `service_providers` (`provider_id`, `user_id`, `bio`, `experience_yr`, `charge_per_day`, `avg_rating`, `is_available`, `willing_outside_region`) VALUES (2,5,'Licensed electrician for household and commercial wiring.',6,5000.00,4.20,1,0);
INSERT INTO `service_providers` (`provider_id`, `user_id`, `bio`, `experience_yr`, `charge_per_day`, `avg_rating`, `is_available`, `willing_outside_region`) VALUES (3,6,'Carpenter skilled in doors, windows, and roofing work.',8,4000.00,4.80,0,1);
INSERT INTO `service_providers` (`provider_id`, `user_id`, `bio`, `experience_yr`, `charge_per_day`, `avg_rating`, `is_available`, `willing_outside_region`) VALUES (14,15,'Expert mason with 12 years in residential construction.',12,5500.00,4.70,1,1);
INSERT INTO `service_providers` (`provider_id`, `user_id`, `bio`, `experience_yr`, `charge_per_day`, `avg_rating`, `is_available`, `willing_outside_region`) VALUES (15,16,'Skilled carpenter specializing in roofing and doors.',9,4800.00,4.50,1,1);
INSERT INTO `service_providers` (`provider_id`, `user_id`, `bio`, `experience_yr`, `charge_per_day`, `avg_rating`, `is_available`, `willing_outside_region`) VALUES (16,17,'Licensed plumber for residential and commercial projects.',7,5000.00,4.30,1,0);
INSERT INTO `service_providers` (`provider_id`, `user_id`, `bio`, `experience_yr`, `charge_per_day`, `avg_rating`, `is_available`, `willing_outside_region`) VALUES (17,18,'Professional painter with interior and exterior experience.',8,3500.00,4.60,1,1);
INSERT INTO `service_providers` (`provider_id`, `user_id`, `bio`, `experience_yr`, `charge_per_day`, `avg_rating`, `is_available`, `willing_outside_region`) VALUES (18,19,'Certified electrician handling wiring and solar installations.',10,6000.00,4.80,1,1);
INSERT INTO `service_providers` (`provider_id`, `user_id`, `bio`, `experience_yr`, `charge_per_day`, `avg_rating`, `is_available`, `willing_outside_region`) VALUES (19,20,'Tiling specialist for floors, walls and bathrooms.',6,4200.00,4.40,1,0);
INSERT INTO `service_providers` (`provider_id`, `user_id`, `bio`, `experience_yr`, `charge_per_day`, `avg_rating`, `is_available`, `willing_outside_region`) VALUES (20,21,'Waterproofing expert for roofs, basements and wet areas.',5,4500.00,4.20,0,1);
INSERT INTO `service_providers` (`provider_id`, `user_id`, `bio`, `experience_yr`, `charge_per_day`, `avg_rating`, `is_available`, `willing_outside_region`) VALUES (21,22,'Welder experienced in structural steel and gate fabrication.',11,4700.00,4.55,1,0);
INSERT INTO `service_providers` (`provider_id`, `user_id`, `bio`, `experience_yr`, `charge_per_day`, `avg_rating`, `is_available`, `willing_outside_region`) VALUES (22,23,'Landscaper and outdoor construction specialist.',7,3800.00,4.35,1,1);
INSERT INTO `service_providers` (`provider_id`, `user_id`, `bio`, `experience_yr`, `charge_per_day`, `avg_rating`, `is_available`, `willing_outside_region`) VALUES (23,24,'Aluminium and glass work specialist for windows and partitions.',8,5200.00,4.65,1,1);
/*!40000 ALTER TABLE `service_providers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `supplier_profiles`
--

LOCK TABLES `supplier_profiles` WRITE;
/*!40000 ALTER TABLE `supplier_profiles` DISABLE KEYS */;
INSERT INTO `supplier_profiles` (`supplier_id`, `user_id`, `business_name`, `business_address`, `is_hardware_shop`, `avg_rating`, `is_delivery`) VALUES (1,7,'Saman Hardware & Building Materials','10 High Level Road, Nugegoda',1,4.60,1);
INSERT INTO `supplier_profiles` (`supplier_id`, `user_id`, `business_name`, `business_address`, `is_hardware_shop`, `avg_rating`, `is_delivery`) VALUES (2,8,'Dias Cement Suppliers','55 Kandy Road, Kadawatha',0,4.10,1);
INSERT INTO `supplier_profiles` (`supplier_id`, `user_id`, `business_name`, `business_address`, `is_hardware_shop`, `avg_rating`, `is_delivery`) VALUES (3,9,'Kumara Timber Depot','8 Puttalam Road, Kurunegala',1,4.30,0);
INSERT INTO `supplier_profiles` (`supplier_id`, `user_id`, `business_name`, `business_address`, `is_hardware_shop`, `avg_rating`, `is_delivery`) VALUES (5,25,'Mendis Building Supplies','120 High Level Road, Colombo 5',1,4.60,1);
INSERT INTO `supplier_profiles` (`supplier_id`, `user_id`, `business_name`, `business_address`, `is_hardware_shop`, `avg_rating`, `is_delivery`) VALUES (6,26,'Bandula Sand & Gravel','34 Peradeniya Road, Kandy',0,4.20,1);
INSERT INTO `supplier_profiles` (`supplier_id`, `user_id`, `business_name`, `business_address`, `is_hardware_shop`, `avg_rating`, `is_delivery`) VALUES (7,27,'Upul Cement Trading','78 Colombo Road, Gampaha',0,4.40,1);
INSERT INTO `supplier_profiles` (`supplier_id`, `user_id`, `business_name`, `business_address`, `is_hardware_shop`, `avg_rating`, `is_delivery`) VALUES (8,28,'Manoj Construction Materials','15 Matara Road, Galle',1,4.55,1);
INSERT INTO `supplier_profiles` (`supplier_id`, `user_id`, `business_name`, `business_address`, `is_hardware_shop`, `avg_rating`, `is_delivery`) VALUES (9,29,'Tharaka Timber & Hardware','9 Puttalam Road, Kurunegala',1,4.30,0);
INSERT INTO `supplier_profiles` (`supplier_id`, `user_id`, `business_name`, `business_address`, `is_hardware_shop`, `avg_rating`, `is_delivery`) VALUES (10,30,'Chaminda Stone Suppliers','44 Galle Road, Matara',0,4.10,1);
INSERT INTO `supplier_profiles` (`supplier_id`, `user_id`, `business_name`, `business_address`, `is_hardware_shop`, `avg_rating`, `is_delivery`) VALUES (11,31,'Nishantha Hardware','22 Colombo Road, Ratnapura',1,4.70,1);
INSERT INTO `supplier_profiles` (`supplier_id`, `user_id`, `business_name`, `business_address`, `is_hardware_shop`, `avg_rating`, `is_delivery`) VALUES (12,32,'Asanka Glass & Aluminium','56 Galle Road, Kalutara',1,4.45,1);
INSERT INTO `supplier_profiles` (`supplier_id`, `user_id`, `business_name`, `business_address`, `is_hardware_shop`, `avg_rating`, `is_delivery`) VALUES (13,33,'Pradeep Building Center','11 Badulla Road, Badulla',0,4.25,1);
INSERT INTO `supplier_profiles` (`supplier_id`, `user_id`, `business_name`, `business_address`, `is_hardware_shop`, `avg_rating`, `is_delivery`) VALUES (14,34,'Sanjeewa Roofing Supplies','33 Tangalle Road, Hambantota',0,4.35,0);
/*!40000 ALTER TABLE `supplier_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `hardware_stores`
--

LOCK TABLES `hardware_stores` WRITE;
/*!40000 ALTER TABLE `hardware_stores` DISABLE KEYS */;
INSERT INTO `hardware_stores` (`hardware_id`, `supplier_id`, `store_name`, `br_number`, `address`) VALUES (1,1,'Saman Hardware','BR-2024-1001','10 High Level Road, Nugegoda');
INSERT INTO `hardware_stores` (`hardware_id`, `supplier_id`, `store_name`, `br_number`, `address`) VALUES (2,3,'Kumara Timber Depot','BR-2024-1003','8 Puttalam Road, Kurunegala');
/*!40000 ALTER TABLE `hardware_stores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `provider_skills`
--

LOCK TABLES `provider_skills` WRITE;
/*!40000 ALTER TABLE `provider_skills` DISABLE KEYS */;
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (1,1,1,10,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (2,1,5,4,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (3,2,3,6,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (4,3,2,8,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (5,3,4,3,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (6,14,1,12,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (7,14,5,5,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (8,15,2,9,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (9,15,8,6,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (10,16,4,7,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (11,16,9,4,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (12,17,5,8,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (13,17,12,3,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (14,18,3,10,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (15,19,6,6,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (16,19,1,4,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (17,20,9,5,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (18,20,8,3,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (19,21,7,11,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (20,22,10,7,NULL);
INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `experience_yr`, `description`) VALUES (21,23,11,8,NULL);
/*!40000 ALTER TABLE `provider_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `supplier_materials`
--

LOCK TABLES `supplier_materials` WRITE;
/*!40000 ALTER TABLE `supplier_materials` DISABLE KEYS */;
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (1,1,2,2350.00,500,1,'Tokyo Super cement 50kg bags');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (2,1,7,45.00,10000,1,'Engineering bricks');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (3,1,5,188.00,3899,1,'Standard cement blocks 4 inch');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (4,2,2,2300.00,800,1,'Bulk cement orders welcome');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (5,2,1,25000.00,20,1,'Clean river sand per cube');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (6,2,3,22000.00,15,1,'Crushed metal 3/4 inch per cube');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (7,3,6,350.00,2000,1,'Treated mahogany planks per ft');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (8,3,8,1200.00,150,1,'Clear glass sheets 5mm');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (10,5,2,2400.00,600,1,'Premium cement 50kg bags');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (11,5,7,50.00,8000,1,'Standard engineering bricks');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (12,5,5,190.00,2500,1,'Cement blocks 4 inch');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (13,6,1,24000.00,25,1,'Clean river sand per cube');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (14,6,3,21000.00,20,1,'Crushed metal 3/4 inch per cube');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (15,7,2,2350.00,700,1,'Bulk cement orders welcome');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (16,7,4,18000.00,30,1,'Stone rubble per cube');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (17,8,7,48.00,12000,1,'High quality bricks');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (18,8,5,185.00,3500,1,'Cement blocks 6 inch');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (19,8,2,2380.00,400,1,'Cement 50kg bags');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (20,9,6,380.00,1500,1,'Treated mahogany planks per ft');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (21,9,9,500.00,100,1,'Misc hardware and fittings');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (22,10,4,19000.00,18,1,'Quarry stone per cube');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (23,10,3,22000.00,12,1,'Metal aggregate per cube');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (24,10,1,25000.00,10,1,'River sand per cube');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (25,11,5,195.00,2000,1,'Standard cement blocks');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (26,11,7,52.00,9000,1,'Wire cut bricks');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (27,11,2,2420.00,300,1,'Cement 50kg bags');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (28,12,8,1300.00,200,1,'Clear float glass 5mm per sheet');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (29,12,6,360.00,1800,1,'Timber planks per ft');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (30,13,1,23000.00,15,1,'Fine river sand per cube');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (31,13,2,2300.00,500,1,'Ordinary Portland cement');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (32,13,3,20000.00,22,1,'Metal per cube');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (33,14,6,370.00,1200,1,'Roofing timber per ft');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (34,14,4,17500.00,25,1,'Rubble stone per cube');
INSERT INTO `supplier_materials` (`id`, `supplier_id`, `material_id`, `unit_price`, `stock_qty`, `is_available`, `description`) VALUES (35,14,9,450.00,80,1,'Roofing accessories');
/*!40000 ALTER TABLE `supplier_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` (`project_id`, `owner_id`, `project_name`, `district`, `address`, `p_budget`, `p_cost`, `start_date`, `end_date`, `target_end_date`, `is_finished`) VALUES (1,1,'Two Storey House Build','Colombo','25/1 Galle Road, Dehiwala',8500000.00,1200000.00,'2026-06-01','2027-02-28',NULL,0);
INSERT INTO `projects` (`project_id`, `owner_id`, `project_name`, `district`, `address`, `p_budget`, `p_cost`, `start_date`, `end_date`, `target_end_date`, `is_finished`) VALUES (2,2,'Kitchen Renovation','Gampaha','112 Negombo Road, Ja-Ela',750000.00,300000.00,'2026-07-01','2026-08-15',NULL,0);
INSERT INTO `projects` (`project_id`, `owner_id`, `project_name`, `district`, `address`, `p_budget`, `p_cost`, `start_date`, `end_date`, `target_end_date`, `is_finished`) VALUES (3,3,'Boundary Wall','Kandy','48 Peradeniya Road, Kandy',450000.00,450000.00,'2026-05-01','2026-06-10',NULL,0);
INSERT INTO `projects` (`project_id`, `owner_id`, `project_name`, `district`, `address`, `p_budget`, `p_cost`, `start_date`, `end_date`, `target_end_date`, `is_finished`) VALUES (4,3,'Pakaya','Mannar','123, pakaya gedara, watte hulan, kilinochchiya',9999999999999.99,0.00,'2026-07-09',NULL,'2026-07-25',0);
INSERT INTO `projects` (`project_id`, `owner_id`, `project_name`, `district`, `address`, `p_budget`, `p_cost`, `start_date`, `end_date`, `target_end_date`, `is_finished`) VALUES (5,3,'test project for astimate end date','Vavuniya','no idk idc',3000000.00,0.00,'2026-07-10',NULL,'2026-07-15',0);
INSERT INTO `projects` (`project_id`, `owner_id`, `project_name`, `district`, `address`, `p_budget`, `p_cost`, `start_date`, `end_date`, `target_end_date`, `is_finished`) VALUES (6,3,'testif new project load','Puttalam','idk now idgaf',600000.00,0.00,'2026-07-11',NULL,'2026-07-25',0);
INSERT INTO `projects` (`project_id`, `owner_id`, `project_name`, `district`, `address`, `p_budget`, `p_cost`, `start_date`, `end_date`, `target_end_date`, `is_finished`) VALUES (7,1,'Roofing – Nimal\'s House, Kandy','Kandy','45 Temple Road, Kandy',500000.00,250000.00,'2026-05-05',NULL,'2026-05-25',0);
INSERT INTO `projects` (`project_id`, `owner_id`, `project_name`, `district`, `address`, `p_budget`, `p_cost`, `start_date`, `end_date`, `target_end_date`, `is_finished`) VALUES (8,1,'Foundation Work – Gampola Site','Kandy','Gampola',800000.00,0.00,'2026-06-01',NULL,'2026-07-15',0);
INSERT INTO `projects` (`project_id`, `owner_id`, `project_name`, `district`, `address`, `p_budget`, `p_cost`, `start_date`, `end_date`, `target_end_date`, `is_finished`) VALUES (9,1,'Multi project handeling','Ampara','tessting for multiproject handeling',2000000.00,0.00,'2026-08-22',NULL,'2026-11-17',1);
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (3,2,'Electrical Wiring','2026-07-05','2026-07-20',120000.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (4,4,'Architectural Design','2026-08-26','2026-08-31',2299.00,120323.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (5,4,'Roofing',NULL,NULL,99.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (6,4,'Electrical',NULL,NULL,11199999999999.00,99999999.99,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (7,4,'task2',NULL,NULL,100000.00,0.00,1);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (8,5,'Architectural Design',NULL,NULL,0.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (9,5,'Slab',NULL,NULL,0.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (10,5,'Boundary Wall & Gate',NULL,NULL,0.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (11,6,'Foundation',NULL,NULL,0.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (12,6,'Roofing',NULL,NULL,0.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (13,6,'Doors & Windows',NULL,NULL,0.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (14,6,'Electrical',NULL,NULL,0.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (15,6,'Plumbing',NULL,NULL,0.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (16,6,'Wall & Ceiling Plastering',NULL,NULL,0.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (17,2,'foundation',NULL,NULL,250000.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (18,2,'rtrthrh',NULL,NULL,0.00,0.00,1);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (22,1,'landscape',NULL,NULL,100000.00,5.00,1);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (28,1,'foundation','2026-05-05','2026-08-08',100000.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (29,4,'Roofing Installation','2026-05-05',NULL,500000.00,250000.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (30,5,'Foundation Laying','2026-06-01','0000-00-00',800000.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (31,4,'Gutter Installation','2026-07-01','2026-07-10',50000.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (32,5,'Site Excavation','2026-08-01','2026-08-15',120000.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (33,1,'report test','2026-06-08','2026-08-08',250000.00,0.00,1);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (34,9,'Site Preparation',NULL,NULL,0.00,0.00,1);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (35,9,'Architectural Design',NULL,NULL,0.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (36,9,'Foundation',NULL,NULL,0.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (37,8,'test 1',NULL,NULL,0.00,0.00,0);
INSERT INTO `tasks` (`task_id`, `project_id`, `task_name`, `start_date`, `end_date`, `task_budget`, `t_cost`, `is_finished`) VALUES (38,7,'test 2',NULL,NULL,0.00,0.00,0);
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `task_daily_status`
--

LOCK TABLES `task_daily_status` WRITE;
/*!40000 ALTER TABLE `task_daily_status` DISABLE KEYS */;
INSERT INTO `task_daily_status` (`status_id`, `task_id`, `status_date`, `status`, `updated_at`) VALUES (1,22,'2026-08-02','done','2026-08-07 10:57:24');
INSERT INTO `task_daily_status` (`status_id`, `task_id`, `status_date`, `status`, `updated_at`) VALUES (2,22,'2026-08-03','done','2026-08-07 10:45:57');
INSERT INTO `task_daily_status` (`status_id`, `task_id`, `status_date`, `status`, `updated_at`) VALUES (3,22,'2026-08-04','done','2026-08-07 10:57:24');
INSERT INTO `task_daily_status` (`status_id`, `task_id`, `status_date`, `status`, `updated_at`) VALUES (4,28,'2026-08-05','in_progress','2026-08-07 10:45:57');
INSERT INTO `task_daily_status` (`status_id`, `task_id`, `status_date`, `status`, `updated_at`) VALUES (5,22,'2026-08-05','blocked','2026-08-07 10:45:57');
INSERT INTO `task_daily_status` (`status_id`, `task_id`, `status_date`, `status`, `updated_at`) VALUES (6,28,'2026-08-04','done','2026-08-07 10:45:57');
INSERT INTO `task_daily_status` (`status_id`, `task_id`, `status_date`, `status`, `updated_at`) VALUES (23,28,'2026-08-11','done','2026-08-09 06:24:55');
INSERT INTO `task_daily_status` (`status_id`, `task_id`, `status_date`, `status`, `updated_at`) VALUES (26,22,'2026-08-09','in_progress','2026-08-09 06:24:55');
INSERT INTO `task_daily_status` (`status_id`, `task_id`, `status_date`, `status`, `updated_at`) VALUES (27,22,'2026-08-10','done','2026-08-09 06:24:55');
INSERT INTO `task_daily_status` (`status_id`, `task_id`, `status_date`, `status`, `updated_at`) VALUES (28,22,'2026-08-11','in_progress','2026-08-09 06:24:55');
INSERT INTO `task_daily_status` (`status_id`, `task_id`, `status_date`, `status`, `updated_at`) VALUES (29,37,'2026-08-24','in_progress','2026-08-26 08:01:47');
INSERT INTO `task_daily_status` (`status_id`, `task_id`, `status_date`, `status`, `updated_at`) VALUES (30,37,'2026-08-26','blocked','2026-08-26 08:01:47');
INSERT INTO `task_daily_status` (`status_id`, `task_id`, `status_date`, `status`, `updated_at`) VALUES (31,34,'2026-08-26','in_progress','2026-08-26 08:03:58');
INSERT INTO `task_daily_status` (`status_id`, `task_id`, `status_date`, `status`, `updated_at`) VALUES (33,35,'2026-08-26','in_progress','2026-08-26 08:04:29');
INSERT INTO `task_daily_status` (`status_id`, `task_id`, `status_date`, `status`, `updated_at`) VALUES (34,35,'2026-08-27','in_progress','2026-08-26 08:04:29');
/*!40000 ALTER TABLE `task_daily_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `task_assignments`
--

LOCK TABLES `task_assignments` WRITE;
/*!40000 ALTER TABLE `task_assignments` DISABLE KEYS */;
INSERT INTO `task_assignments` (`id`, `task_id`, `provider_id`) VALUES (2,3,2);
INSERT INTO `task_assignments` (`id`, `task_id`, `provider_id`) VALUES (3,4,1);
INSERT INTO `task_assignments` (`id`, `task_id`, `provider_id`) VALUES (4,5,1);
INSERT INTO `task_assignments` (`id`, `task_id`, `provider_id`) VALUES (5,38,1);
/*!40000 ALTER TABLE `task_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `service_requests`
--

LOCK TABLES `service_requests` WRITE;
/*!40000 ALTER TABLE `service_requests` DISABLE KEYS */;
INSERT INTO `service_requests` (`request_id`, `owner_id`, `provider_id`, `task_id`, `request_date`, `expires_at`, `request_status`) VALUES (1,1,1,NULL,'2026-07-09 17:07:58','2026-07-12 23:59:59','expired');
INSERT INTO `service_requests` (`request_id`, `owner_id`, `provider_id`, `task_id`, `request_date`, `expires_at`, `request_status`) VALUES (2,2,2,3,'2026-07-09 17:07:58','2026-07-11 23:59:59','pending');
INSERT INTO `service_requests` (`request_id`, `owner_id`, `provider_id`, `task_id`, `request_date`, `expires_at`, `request_status`) VALUES (3,3,3,NULL,'2026-07-09 17:07:58','2026-07-01 23:59:59','pending');
INSERT INTO `service_requests` (`request_id`, `owner_id`, `provider_id`, `task_id`, `request_date`, `expires_at`, `request_status`) VALUES (4,1,1,6,'2026-08-01 08:39:56','2026-08-02 14:09:56','expired');
INSERT INTO `service_requests` (`request_id`, `owner_id`, `provider_id`, `task_id`, `request_date`, `expires_at`, `request_status`) VALUES (5,1,1,7,'2026-08-01 08:39:56','2026-08-02 14:09:56','expired');
INSERT INTO `service_requests` (`request_id`, `owner_id`, `provider_id`, `task_id`, `request_date`, `expires_at`, `request_status`) VALUES (6,1,1,NULL,'2026-08-01 08:39:56','2026-08-01 13:09:56','expired');
INSERT INTO `service_requests` (`request_id`, `owner_id`, `provider_id`, `task_id`, `request_date`, `expires_at`, `request_status`) VALUES (9,1,1,22,'2026-08-09 16:00:58','2026-08-12 21:30:58','expired');
INSERT INTO `service_requests` (`request_id`, `owner_id`, `provider_id`, `task_id`, `request_date`, `expires_at`, `request_status`) VALUES (10,1,1,28,'2026-08-10 12:17:09','2026-08-13 17:47:09','expired');
INSERT INTO `service_requests` (`request_id`, `owner_id`, `provider_id`, `task_id`, `request_date`, `expires_at`, `request_status`) VALUES (11,1,2,33,'2026-08-10 12:17:46','2026-08-13 17:47:46','pending');
INSERT INTO `service_requests` (`request_id`, `owner_id`, `provider_id`, `task_id`, `request_date`, `expires_at`, `request_status`) VALUES (12,1,1,38,'2026-08-26 12:29:49','2026-08-29 17:59:49','accepted');
INSERT INTO `service_requests` (`request_id`, `owner_id`, `provider_id`, `task_id`, `request_date`, `expires_at`, `request_status`) VALUES (13,1,14,37,'2026-08-26 12:30:17','2026-08-29 18:00:17','pending');
/*!40000 ALTER TABLE `service_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `material_orders`
--

LOCK TABLES `material_orders` WRITE;
/*!40000 ALTER TABLE `material_orders` DISABLE KEYS */;
INSERT INTO `material_orders` (`order_id`, `owner_id`, `supplier_material_id`, `quantity`, `total_cost`, `order_status`, `ordered_at`) VALUES (1,1,1,100,235000.00,'accepted','2026-07-09 17:07:58');
INSERT INTO `material_orders` (`order_id`, `owner_id`, `supplier_material_id`, `quantity`, `total_cost`, `order_status`, `ordered_at`) VALUES (2,1,7,200,70000.00,'pending','2026-07-09 17:07:58');
INSERT INTO `material_orders` (`order_id`, `owner_id`, `supplier_material_id`, `quantity`, `total_cost`, `order_status`, `ordered_at`) VALUES (3,2,4,50,115000.00,'delivered','2026-07-09 17:07:58');
/*!40000 ALTER TABLE `material_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` (`review_id`, `owner_id`, `provider_id`, `rating`, `comment`, `review_date`) VALUES (1,1,1,5,'Excellent masonry work, finished on time.','2026-07-09 17:07:58');
INSERT INTO `reviews` (`review_id`, `owner_id`, `provider_id`, `rating`, `comment`, `review_date`) VALUES (2,3,3,4,'Good carpentry but slightly delayed.','2026-07-09 17:07:58');
INSERT INTO `reviews` (`review_id`, `owner_id`, `provider_id`, `rating`, `comment`, `review_date`) VALUES (3,1,1,4,'Excellent work on the foundation. Very professional.','2026-08-01 08:39:56');
INSERT INTO `reviews` (`review_id`, `owner_id`, `provider_id`, `rating`, `comment`, `review_date`) VALUES (4,1,1,5,'On time and great quality. Will hire again.','2026-08-01 08:39:56');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `review_photos`
--

LOCK TABLES `review_photos` WRITE;
/*!40000 ALTER TABLE `review_photos` DISABLE KEYS */;
INSERT INTO `review_photos` (`photo_id`, `review_id`, `file_path`, `uploaded_at`) VALUES (1,3,'review_photos/review_3_1787756770_c08ab26a.jpg','2026-08-26 15:06:10');
INSERT INTO `review_photos` (`photo_id`, `review_id`, `file_path`, `uploaded_at`) VALUES (2,3,'review_photos/review_3_1787773013_38c3be0d.jpg','2026-08-26 19:36:53');
/*!40000 ALTER TABLE `review_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `feedback`
--

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (1,NULL,'fgdfg','dfgdfg@hfgh.com','General Inquiry','sdsdvsdvsdgdfhfgncgnxfgnxfgnxgfgn',0,'2026-07-12 11:31:34');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (2,NULL,'testforuserid','nimal@gmail.com','Bug Report','check if this feed backhave the userid',0,'2026-07-12 12:20:02');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (3,NULL,'userid','sd@sdsd.cm','General Inquiry','testing for user id',0,'2026-07-12 13:14:17');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (4,NULL,'kamala','kamala@gmail.com','Bug Report','userid test\n\\',0,'2026-07-12 13:16:11');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (5,NULL,'nimal','nima@gmail.com','Bug Report','useridtesting',0,'2026-07-12 13:23:51');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (6,NULL,'nimal','kamala@gmail.com','Bug Report','this is after fixiing jwt tolken issue',0,'2026-07-12 13:37:26');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (7,NULL,'kamala@gmail.com','kamala@gmail.com','General Inquiry','test2 after jwt',0,'2026-07-12 13:43:38');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (8,NULL,'nimal','nimal@gmail.com','Bug Report','2nd error in jwt tolken -userid',0,'2026-07-12 13:52:50');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (9,NULL,'nimal@gmail.com','nimal@gmail.com','General Inquiry','test 3 with jwt',0,'2026-07-12 14:05:04');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (10,NULL,'nimal','asd@cas.cm','General Inquiry','after hard refresh',0,'2026-07-12 14:10:35');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (11,NULL,'vgvvjjhjh','password01@yh.vbb','General Inquiry','vgvvjvjvjvhjjhvjh',0,'2026-07-12 14:12:01');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (12,NULL,'fbdf','dfbdfb@fdfb.ccbnnc','Bug Report','fbdfbdfbd',0,'2026-07-12 14:12:45');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (13,NULL,'kamal','kamala@gmail.com','Bug Report','idk restaring npm run dev',0,'2026-07-12 14:39:48');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (14,NULL,'ewefwef','kamala@gmail.com','General Inquiry','npm run dev',0,'2026-07-12 14:40:52');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (15,NULL,'fddfg','dfgdfg@gfg.cnm','General Inquiry','Network tab open\nSubmit the feedback form\nClick the submit request → Headers tab → scroll to Request Headers\nIs there a Cookie: crewsync_token=... line now?',0,'2026-07-12 14:45:41');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (16,NULL,'kamala@gmail.com','kamala@gmail.com','General Inquiry','last test in this shittttttt',0,'2026-07-12 15:02:19');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (17,NULL,'password02','kamala@gmail.com','General Inquiry','last test in edge',0,'2026-07-12 15:05:49');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (18,NULL,'xvbxbvbcv','bvxcvx@dsdf.xcv','General Inquiry','testing with http://localhost:3000/home',0,'2026-07-12 15:14:43');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (19,NULL,'asdfsdfsdf','sdfsd@dfgdfg.com','General Inquiry','console do prit?',0,'2026-07-12 15:15:56');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (20,NULL,'nimal','last@email.com','Bug Report','sdsdfsfsdfsff',0,'2026-07-12 15:23:09');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (21,1,'last test with anti','anti@gm.cm','General Inquiry','creditials not saved or something test last',0,'2026-07-12 15:38:45');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (22,NULL,'h','j@gmail.com','Bug Report','tree',0,'2026-07-13 07:22:29');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (23,NULL,'tharushi','th@gmail.com','Bug Report','aaaa',0,'2026-07-13 07:59:58');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (24,NULL,'Tharushi','th@gmail.com','Other','excellent!',0,'2026-07-13 08:13:54');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (25,NULL,'nimal','nimal@gmail.com','General Inquiry','excellent',0,'2026-07-13 08:46:40');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (26,NULL,'nimal','nimal@gmail.com','Suggestion / Feature Request','dddd',0,'2026-07-13 09:05:44');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (27,NULL,'nimal','nimal@gmail.com','Other','excellent work!',0,'2026-07-13 09:15:11');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (28,NULL,'nimal','nimal@gmail.com','Other','good work!',0,'2026-07-13 09:39:48');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (29,NULL,'nimal','nimal@gmail.com','Other','excellent',0,'2026-07-13 09:59:28');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (30,NULL,'nimal','nimal@gmail.com','Other','good work',0,'2026-07-13 11:04:15');
INSERT INTO `feedback` (`feedback_id`, `user_id`, `name`, `email`, `subject`, `message`, `is_handled`, `created_at`) VALUES (31,4,'dileepa piya','dil@gmail.com','Bug Report','idk just test for home oage last',0,'2026-07-29 14:56:44');
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES (1,1,'system','Task <strong>landscape</strong> is completed. A task report is now available in the <a href=\"/dashboard/propertyowner/reports\" class=\"font-semibold text-[#16a34a] hover:underline\">Reports</a> page.',0,'2026-08-28 14:20:46');
INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES (2,1,'system','Task <strong>landscape</strong> is completed. A task report is now available in the <a href=\"/dashboard/propertyowner/reports\" class=\"font-semibold text-[#16a34a] hover:underline\">Reports</a> page.',0,'2026-08-28 14:20:46');
INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES (3,1,'task_report','Task <strong>landscape</strong> is completed. A task report is now available in the <a href=\'/dashboard/propertyowner/reports\' class=\'font-semibold text-[#16a34a] hover:underline\'>Reports</a> page.',0,'2026-08-28 14:20:46');
INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES (4,1,'system','Task <strong>foundation</strong> marked as <strong>In Progress</strong>',0,'2026-08-28 14:21:23');
INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES (5,1,'system','Task <strong>foundation</strong> marked as <strong>In Progress</strong>',0,'2026-08-28 14:21:23');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
INSERT INTO `reports` (`report_id`, `project_id`, `task_id`, `report_type`, `file_path`, `generated_date`) VALUES (8,1,22,'task','http://127.0.0.1/crewsync/reports/task_22_report_20260808_151434.pdf','2026-08-08 13:14:34');
INSERT INTO `reports` (`report_id`, `project_id`, `task_id`, `report_type`, `file_path`, `generated_date`) VALUES (9,1,28,'task','http://127.0.0.1/crewsync/reports/task_28_report_20260808_151608.pdf','2026-08-08 13:16:08');
INSERT INTO `reports` (`report_id`, `project_id`, `task_id`, `report_type`, `file_path`, `generated_date`) VALUES (10,1,33,'task','http://127.0.0.1/crewsync/reports/task_33_report_20260808_151905.pdf','2026-08-08 13:19:05');
INSERT INTO `reports` (`report_id`, `project_id`, `task_id`, `report_type`, `file_path`, `generated_date`) VALUES (12,9,NULL,'project','http://127.0.0.1/crewsync/reports/project_9_report_20260826_135225.pdf','2026-08-26 10:55:26');
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `project_comments`
--

LOCK TABLES `project_comments` WRITE;
/*!40000 ALTER TABLE `project_comments` DISABLE KEYS */;
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (1,1,1,'Foundation work starts Monday, all welcome to discuss.','2026-07-09 17:07:58');
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (2,1,4,'I will arrive at 8am with my team.','2026-07-09 17:07:58');
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (3,1,7,'Cement delivery scheduled for Tuesday morning.','2026-07-09 17:07:58');
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (4,2,2,'Any electricians available for a quick quote?','2026-07-09 17:07:58');
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (5,2,5,'Yes, I can visit the site this weekend.','2026-07-09 17:07:58');
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (6,1,1,'xczxczxc','2026-07-12 17:30:05');
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (7,1,1,'zxczxc','2026-07-12 17:30:17');
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (8,1,1,'tomorrow','2026-07-13 08:08:41');
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (9,1,1,'today','2026-07-13 08:16:38');
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (10,1,1,'today is okay','2026-07-13 08:49:31');
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (11,1,1,'good!','2026-07-13 09:12:09');
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (12,1,1,'Tomorrow morning','2026-07-13 09:19:07');
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (13,1,1,'Today','2026-07-13 09:44:08');
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (14,1,1,'tomorrow is okay','2026-07-13 11:06:53');
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (15,7,4,'vsvxcvx','2026-08-26 19:39:47');
INSERT INTO `project_comments` (`comment_id`, `project_id`, `user_id`, `comment`, `created_at`) VALUES (16,4,4,'xcvxcvxsdfsdfsd','2026-08-26 19:39:50');
/*!40000 ALTER TABLE `project_comments` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-30 16:54:13
