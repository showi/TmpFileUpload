-- phpMyAdmin SQL Dump
-- version 4.2.7.1
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Ven 22 Août 2014 à 07:24
-- Version du serveur :  10.0.13-MariaDB-log
-- Version de PHP :  5.5.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `tmpfileupload`
--
CREATE DATABASE IF NOT EXISTS `tmpfileupload` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `tmpfileupload`;

--
-- Vider la table avant d'insérer `mime`
--

TRUNCATE TABLE `mime`;
--
-- Contenu de la table `mime`
--

INSERT INTO `mime` (`id`, `value`) VALUES
(9, 'application/pdf'),
(11, 'audio/mp3'),
(12, 'audio/mpeg'),
(10, 'audio/ogg'),
(3, 'image/gif'),
(4, 'image/jpeg'),
(5, 'image/png'),
(8, 'image/tiff'),
(13, 'text/plain');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
