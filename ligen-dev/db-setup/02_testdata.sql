-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 09, 2022 at 03:30 PM
-- Server version: 8.0.31-0ubuntu0.22.04.1
-- PHP Version: 8.1.2-1ubuntu2.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nsv-ligen`
--

--
-- Dumping data for table `benutzer`
--

INSERT INTO `benutzer` (`id`, `name`, `passwort`, `letzterzugriff`, `random`, `telefon`, `telefon2`, `email`) VALUES
(7, 'Marcel', '25d55ad283aa400af464c76d713c07ad', '2022-12-09 14:28:10', 'de7hes5jmh', NULL, NULL, 'dev@marcel.world'),
(8, 'Staffel Leity', '81dc9bdb52d04dc20036dbd8313ed055', '2022-12-09 14:26:56', 'g9m9od348d', '123456', '', 'staffel@marcel.world');

--
-- Dumping data for table `mannschaften`
--

INSERT INTO `mannschaften` (`id`, `turnier`, `staffel`, `gruppe`, `zps`, `name`, `mnr`, `so_name`, `so_hinweis`, `so_strasse`, `so_plz`, `so_stadt`, `so_telefon`, `mf_name`, `mf_email`, `mf_telefon`, `mf_telefon2`) VALUES
(3, 1, 1, 'default', '70156', 'SK Lehrte', 1, 'Haus der Vereine', NULL, '', '', '', '', 'Marcel Jünemann', '', '', ''),
(5, 1, 1, 'default', '70107', 'HSK Lister Turm', 1, '', NULL, '', '', '', '', '', '', '', '');

--
-- Dumping data for table `paarungen`
--

INSERT INTO `paarungen` (`id`, `staffel`, `runde`, `mannschaft1`, `mannschaft2`, `erg1`, `erg2`, `bemerkung`, `termin`, `ausrichter`, `linkGesendet`, `festgelegt`, `timestamp`) VALUES
(1, 1, 1, 5, 3, 0, 1, 'Hello World!', NULL, NULL, 0, 0, '2022-12-09 14:26:08');

--
-- Dumping data for table `spieler`
--

INSERT INTO `spieler` (`id`, `mannschaft`, `zps`, `brettnr`, `vorname`, `nachname`, `titel`, `dwz`, `elo`, `geburt`, `geschlecht`, `nmSid`, `nmR`) VALUES
(2, 3, '70156-117', 1, 'Marcel', 'Jünemann', '', '1745', 1782, '1990', '', NULL, NULL),
(3, 5, '', 1, 'Hans', 'Testspieler', '', '', NULL, '2000', '', NULL, NULL);

--
-- Dumping data for table `spielerpaarungen`
--

INSERT INTO `spielerpaarungen` (`id`, `paarung`, `brett`, `spieler1`, `spieler2`, `ergebnis1`, `ergebnis2`) VALUES
(1, 1, 1, 3, 2, '0', '1'),
(2, 1, 2, NULL, NULL, '-', '-'),
(3, 1, 3, NULL, NULL, '-', '-'),
(4, 1, 4, NULL, NULL, '-', '-');

--
-- Dumping data for table `staffeln`
--

INSERT INTO `staffeln` (`id`, `leiter`, `name`, `turnier`, `runden`, `brettzahl`, `spielAufsteiger`, `spielAbsteiger`, `spielAufsteigerRelegation`, `spielAbsteigerRelegation`, `sysEingabelinks`, `showTabelle`, `showNachmeldungen`, `showSpieltagvorschau`, `showPassNr`, `sortid`) VALUES
(1, 8, 'Teststaffel', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);

--
-- Dumping data for table `termine`
--

INSERT INTO `termine` (`id`, `turnier`, `staffel`, `runde`, `datum`) VALUES
(1, 1, NULL, 1, '2022-12-13');

--
-- Dumping data for table `turniere`
--

INSERT INTO `turniere` (`id`, `leiter`, `name`, `organisation`, `startjahr`, `runden`, `brettzahl`, `directory`, `template`, `infomeldung`, `anmAktiv`, `anmGeburt`, `anmGeschlecht`, `anmVerband`, `anmZusatzfelder`, `anmTLMail`, `spielErsatzmannschaft`, `spielNachmeldungen`, `spielDreistelligeNr`, `spielAufsteiger`, `spielAbsteiger`, `spielAufsteigerRelegation`, `spielAbsteigerRelegation`, `spielHatGruppen`, `sysKeinNewsletter`, `sysEingabelinks`, `showTabelle`, `showNachmeldungen`, `showSpieltagvorschau`, `showPassNr`) VALUES
(1, 7, 'Test', '7', 2022, 1, 4, 'test-2022', 'sjbh', '', 1, 1900, NULL, '7', NULL, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 1, 1, 1, 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
