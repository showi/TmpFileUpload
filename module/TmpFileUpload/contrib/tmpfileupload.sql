--/*
--* Copyright (c) 2014 Joachim Basmaison
--*
--* This program is free software; you can redistribute it and/or modify it
--* under the terms of the GNU General Public License as published by
--* the Free Software Foundation; either version 2 of the License,
--* or (at your option) any later version. This program is distributed in the
--* hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
--* implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
--*
--* See the GNU General Public License for more details.
--*/

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

-- --------------------------------------------------------

--
-- Structure de la table `file`
--

DROP TABLE IF EXISTS `file`;
CREATE TABLE IF NOT EXISTS `file` (
`id` int(11) NOT NULL,
  `pubkey` varchar(128) NOT NULL,
  `valid_until` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hash` varchar(128) NOT NULL,
  `mime_id` int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=69 ;

-- --------------------------------------------------------

--
-- Structure de la table `mime`
--

DROP TABLE IF EXISTS `mime`;
CREATE TABLE IF NOT EXISTS `mime` (
`id` int(11) NOT NULL,
  `value` varchar(128) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `file`
--
ALTER TABLE `file`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `pubkey` (`pubkey`), ADD UNIQUE KEY `hash` (`hash`), ADD KEY `mime_id` (`mime_id`);

--
-- Index pour la table `mime`
--
ALTER TABLE `mime`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `value` (`value`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `file`
--
ALTER TABLE `file`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=69;
--
-- AUTO_INCREMENT pour la table `mime`
--
ALTER TABLE `mime`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `file`
--
ALTER TABLE `file`
ADD CONSTRAINT `file_ibfk_1` FOREIGN KEY (`mime_id`) REFERENCES `mime` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
