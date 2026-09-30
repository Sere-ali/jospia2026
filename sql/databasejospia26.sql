/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: jospia2026
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
-- Current Database: `jospia2026`
--

/*!40000 DROP DATABASE IF EXISTS `jospia2026`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `jospia2026` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `jospia2026`;

--
-- Table structure for table `comptes`
--

DROP TABLE IF EXISTS `comptes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `comptes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifiant` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('membre','seminariste','admin','superadmin') NOT NULL DEFAULT 'membre',
  `membre_id` int(11) DEFAULT NULL,
  `seminariste_id` int(11) DEFAULT NULL,
  `nom_affiche` varchar(150) DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifiant` (`identifiant`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comptes`
--

LOCK TABLES `comptes` WRITE;
/*!40000 ALTER TABLE `comptes` DISABLE KEYS */;
INSERT INTO `comptes` VALUES
(1,'superadmin','$2y$10$zGCxD0qtD/pTYjP/PbUGWO5AsjAOW9AEixZIfU6ui/yTjlEomcIEC','superadmin',NULL,NULL,'Super Administrateur',1,'2026-09-08 00:08:07');
/*!40000 ALTER TABLE `comptes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `critiques`
--

DROP TABLE IF EXISTS `critiques`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `critiques` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seminariste_id` int(11) NOT NULL,
  `commission` varchar(50) NOT NULL,
  `contenu` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `seminariste_id` (`seminariste_id`),
  CONSTRAINT `critiques_ibfk_1` FOREIGN KEY (`seminariste_id`) REFERENCES `seminaristes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `critiques`
--

LOCK TABLES `critiques` WRITE;
/*!40000 ALTER TABLE `critiques` DISABLE KEYS */;
/*!40000 ALTER TABLE `critiques` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dortoirs`
--

DROP TABLE IF EXISTS `dortoirs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dortoirs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(30) NOT NULL,
  `genre` enum('Masculin','Féminin') NOT NULL,
  `capacite` int(11) NOT NULL DEFAULT 40,
  `occupation` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dortoirs`
--

LOCK TABLES `dortoirs` WRITE;
/*!40000 ALTER TABLE `dortoirs` DISABLE KEYS */;
INSERT INTO `dortoirs` VALUES
(1,'Dortoir Hommes 1','Masculin',40,0),
(2,'Dortoir Hommes 2','Masculin',40,0),
(3,'Dortoir Hommes 3','Masculin',40,0),
(4,'Dortoir Hommes 4','Masculin',40,0),
(5,'Dortoir Femmes 1','Féminin',40,0),
(6,'Dortoir Femmes 2','Féminin',40,0),
(7,'Dortoir Femmes 3','Féminin',40,0),
(8,'Dortoir Femmes 4','Féminin',40,0);
/*!40000 ALTER TABLE `dortoirs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `matieres`
--

DROP TABLE IF EXISTS `matieres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `matieres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `note_max` decimal(5,2) NOT NULL DEFAULT 20.00,
  `ordre` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `matieres`
--

LOCK TABLES `matieres` WRITE;
/*!40000 ALTER TABLE `matieres` DISABLE KEYS */;
INSERT INTO `matieres` VALUES
(1,'Connaissance du Coran',20.00,1),
(2,'Jurisprudence islamique (Fiqh)',20.00,2),
(3,'Dogme (Aqida)',20.00,3),
(4,'Culture générale islamique',20.00,4),
(5,'Gestion associative (AEEMCI)',20.00,5);
/*!40000 ALTER TABLE `matieres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `membres_commission`
--

DROP TABLE IF EXISTS `membres_commission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `membres_commission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_prenoms` varchar(150) NOT NULL,
  `commission` varchar(50) NOT NULL,
  `contact` varchar(30) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `matricule` varchar(30) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule` (`matricule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `membres_commission`
--

LOCK TABLES `membres_commission` WRITE;
/*!40000 ALTER TABLE `membres_commission` DISABLE KEYS */;
/*!40000 ALTER TABLE `membres_commission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seminariste_id` int(11) NOT NULL,
  `matiere_id` int(11) NOT NULL,
  `note` decimal(5,2) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_sem_matiere` (`seminariste_id`,`matiere_id`),
  KEY `matiere_id` (`matiere_id`),
  CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`seminariste_id`) REFERENCES `seminaristes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`matiere_id`) REFERENCES `matieres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes`
--

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parametres`
--

DROP TABLE IF EXISTS `parametres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `parametres` (
  `cle` varchar(50) NOT NULL,
  `valeur` varchar(255) NOT NULL,
  PRIMARY KEY (`cle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parametres`
--

LOCK TABLES `parametres` WRITE;
/*!40000 ALTER TABLE `parametres` DISABLE KEYS */;
INSERT INTO `parametres` VALUES
('resultats_publies','0');
/*!40000 ALTER TABLE `parametres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categorie` varchar(80) NOT NULL,
  `enonce` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `bonne_reponse` enum('A','B','C','D') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES
(1,'Connaissance du Coran','Quelle est la première sourate du Coran ?','Al-Baqara','Al-Fatiha','Al-Ikhlas','An-Nas','B'),
(2,'Connaissance du Coran','Combien de sourates compte le Coran ?','100','104','114','120','C'),
(3,'Connaissance du Coran','La sourate Al-Ikhlas traite principalement :','Du jeûne','De l\'unicité d\'Allah (Tawhid)','Du pèlerinage','De l\'héritage','B'),
(4,'Connaissance du Coran','Sur quel mont la révélation du Coran a-t-elle commencé ?','Mont Uhud','Mont Arafat','Mont Hira','Mont Sinaï','C'),
(5,'Jurisprudence islamique','Combien de prières obligatoires compte une journée en Islam ?','3','4','5','6','C'),
(6,'Jurisprudence islamique','Quel acte annule les ablutions (wudu) ?','Manger du pain','Dormir profondément','Parler','Marcher','B'),
(7,'Jurisprudence islamique','Le jeûne du mois de Ramadan est un pilier :','Facultatif','Obligatoire (fard)','Réservé aux savants','Réservé aux hommes','B'),
(8,'Jurisprudence islamique','La Zakat est prélevée sur :','Le temps libre','Les biens atteignant le Nissab','Les enfants','Les prières','B'),
(9,'Dogme (Aqida)','Combien de piliers compte la foi (Iman) selon la tradition la plus connue ?','4','5','6','7','C'),
(10,'Dogme (Aqida)','Le Tawhid signifie :','La prière collective','L\'unicité d\'Allah','Le jeûne','L\'aumône','B'),
(11,'Dogme (Aqida)','Croire aux anges fait partie :','Des piliers de l\'Islam','Des piliers de la foi','Des recommandations','D\'aucune obligation','B'),
(12,'Dogme (Aqida)','Le dernier des prophètes envoyés à l\'humanité est :','Moussa (Moïse)','Issa (Jésus)','Muhammad ﷺ','Ibrahim (Abraham)','C'),
(13,'Culture générale','Dans quelle ville se trouve la Mosquée Al-Aqsa ?','La Mecque','Médine','Jérusalem (Al-Qods)','Bagdad','C'),
(14,'Culture générale','L\'Hégire correspond à :','La naissance du Prophète ﷺ','L\'émigration de La Mecque vers Médine','La révélation du premier verset','La conquête de La Mecque','B'),
(15,'Culture générale','La capitale administrative de la Côte d\'Ivoire est :','Abidjan','Bouaké','Yamoussoukro','San Pedro','C'),
(16,'Culture générale','Un raisonnement qui va du général au particulier est dit :','Inductif','Déductif','Analogique','Statistique','B'),
(17,'Gestion associative (AEEMCI)','Que signifie le sigle AEEMCI ?','Association des Élèves et Étudiants Musulmans de Côte d\'Ivoire','Alliance des Écoles Musulmanes de Côte d\'Ivoire','Association des Étudiants Musulmans du Continent Ivoirien','Aucune de ces réponses','A'),
(18,'Gestion associative (AEEMCI)','Dans une association, l\'organe qui contrôle la gestion financière est généralement :','Le bureau exécutif','Le commissariat aux comptes / contrôle financier','Le service communication','Aucun organe','B'),
(19,'Gestion associative (AEEMCI)','Un procès-verbal (PV) de réunion sert à :','Décorer le local','Garder une trace écrite des décisions prises','Remplacer le règlement intérieur','Rien de particulier','B'),
(20,'Gestion associative (AEEMCI)','Le premier responsable d\'une commission au sein d\'une organisation est souvent appelé :','Trésorier','Président / Coordonnateur de commission','Simple membre','Invité','B');
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reponses_test`
--

DROP TABLE IF EXISTS `reponses_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reponses_test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seminariste_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `reponse_donnee` enum('A','B','C','D') DEFAULT NULL,
  `correcte` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `seminariste_id` (`seminariste_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `reponses_test_ibfk_1` FOREIGN KEY (`seminariste_id`) REFERENCES `seminaristes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reponses_test_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reponses_test`
--

LOCK TABLES `reponses_test` WRITE;
/*!40000 ALTER TABLE `reponses_test` DISABLE KEYS */;
/*!40000 ALTER TABLE `reponses_test` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seminaristes`
--

DROP TABLE IF EXISTS `seminaristes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `seminaristes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_prenoms` varchar(150) NOT NULL,
  `genre` enum('Masculin','Féminin') NOT NULL,
  `niveau_etude` varchar(100) NOT NULL,
  `anyama` enum('Anyama 1','Anyama 2') NOT NULL,
  `section` varchar(50) NOT NULL,
  `sous_comite_final` varchar(50) NOT NULL,
  `lieu_residence` varchar(150) NOT NULL,
  `maladie` varchar(50) NOT NULL DEFAULT 'Aucune',
  `maladie_autre` varchar(150) DEFAULT NULL,
  `age` int(11) NOT NULL,
  `contact` varchar(30) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `parent_nom` varchar(150) NOT NULL,
  `parent_lien` varchar(50) NOT NULL,
  `parent_contact` varchar(30) NOT NULL,
  `matricule` varchar(30) NOT NULL,
  `dortoir` varchar(30) DEFAULT NULL,
  `test_complete` tinyint(1) NOT NULL DEFAULT 0,
  `note_test` decimal(4,2) DEFAULT NULL,
  `niveau_affecte` varchar(30) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule` (`matricule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seminaristes`
--

LOCK TABLES `seminaristes` WRITE;
/*!40000 ALTER TABLE `seminaristes` DISABLE KEYS */;
/*!40000 ALTER TABLE `seminaristes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-08  0:08:19
