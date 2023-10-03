-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 07, 2022 at 03:23 PM
-- Server version: 5.7.40-log
-- PHP Version: 8.0.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db117369_4`
--

-- --------------------------------------------------------

--
-- Table structure for table `anmeldungZusatzfelder`
--

CREATE TABLE `anmeldungZusatzfelder` (
  `mannschaft` int(11) NOT NULL,
  `feldname` varchar(60) NOT NULL,
  `inhalt` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bemerkungen`
--

CREATE TABLE `bemerkungen` (
  `id` int(11) NOT NULL,
  `staffel` int(11) NOT NULL DEFAULT '0',
  `runde` int(11) NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `benutzer`
--

CREATE TABLE `benutzer` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `passwort` varchar(35) NOT NULL DEFAULT '',
  `letzterzugriff` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `random` varchar(11) NOT NULL DEFAULT '0',
  `telefon` varchar(30) DEFAULT NULL,
  `telefon2` varchar(30) DEFAULT NULL,
  `email` varchar(50) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `turnier` int(11) NOT NULL,
  `staffel` int(11) NOT NULL,
  `runde` int(4) NOT NULL,
  `typ` enum('MatchDay','Spieltag','Tabelle','Kreuztabelle','TabelleOhneLinks','KreuztabelleOhneLinks','TeamSpielplan','TeamAufstellung','TeamErgebnisse') NOT NULL,
  `inhalt` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `dwz_spieler`
--

CREATE TABLE `dwz_spieler` (
  `ZPS` varchar(5) NOT NULL DEFAULT '',
  `Mgl_Nr` char(4) NOT NULL DEFAULT '',
  `Status` char(1) DEFAULT NULL,
  `Spielername` varchar(40) NOT NULL DEFAULT '',
  `Spielername_G` varchar(40) NOT NULL DEFAULT '',
  `Geschlecht` char(1) DEFAULT NULL,
  `Spielberechtigung` char(1) NOT NULL DEFAULT '',
  `Geburtsjahr` year(4) NOT NULL DEFAULT '0000',
  `Letzte_Auswertung` mediumint(6) UNSIGNED DEFAULT NULL,
  `DWZ` smallint(4) UNSIGNED DEFAULT NULL,
  `DWZ_Index` smallint(3) UNSIGNED DEFAULT NULL,
  `FIDE_Elo` smallint(4) UNSIGNED DEFAULT NULL,
  `FIDE_Titel` char(2) DEFAULT NULL,
  `FIDE_ID` int(8) UNSIGNED DEFAULT NULL,
  `FIDE_Land` char(3) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `dwz_vereine`
--

CREATE TABLE `dwz_vereine` (
  `ZPS` varchar(5) NOT NULL DEFAULT '',
  `LV` char(1) NOT NULL DEFAULT '',
  `Verband` char(3) NOT NULL DEFAULT '',
  `Vereinname` varchar(40) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `geodb`
--

CREATE TABLE `geodb` (
  `plz` varchar(5) NOT NULL DEFAULT '',
  `lat` double NOT NULL DEFAULT '0',
  `lon` double NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `subject` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mannschaften`
--

CREATE TABLE `mannschaften` (
  `id` int(11) NOT NULL,
  `turnier` int(11) NOT NULL DEFAULT '0',
  `staffel` int(11) NOT NULL DEFAULT '0',
  `gruppe` set('default','U12','U14','U16','U18','U20','OMM') NOT NULL DEFAULT 'default',
  `zps` char(10) DEFAULT '0',
  `name` varchar(20) NOT NULL,
  `mnr` int(11) NOT NULL DEFAULT '0',
  `so_name` varchar(40) DEFAULT NULL,
  `so_hinweis` varchar(255) NOT NULL,
  `so_strasse` varchar(30) DEFAULT NULL,
  `so_plz` varchar(5) DEFAULT NULL,
  `so_stadt` varchar(30) DEFAULT NULL,
  `so_telefon` varchar(15) DEFAULT NULL,
  `mf_name` varchar(40) NOT NULL,
  `mf_email` varchar(50) NOT NULL,
  `mf_telefon` varchar(30) NOT NULL,
  `mf_telefon2` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `paarungen`
--

CREATE TABLE `paarungen` (
  `id` int(11) NOT NULL,
  `staffel` int(11) NOT NULL DEFAULT '0',
  `runde` int(11) NOT NULL DEFAULT '0',
  `mannschaft1` int(11) NOT NULL DEFAULT '0',
  `mannschaft2` int(11) NOT NULL DEFAULT '0',
  `erg1` float DEFAULT NULL,
  `erg2` float DEFAULT NULL,
  `bemerkung` varchar(200) DEFAULT NULL,
  `termin` date DEFAULT NULL COMMENT 'Wenn abweichend',
  `ausrichter` int(11) DEFAULT NULL,
  `linkGesendet` tinyint(4) NOT NULL DEFAULT '0',
  `festgelegt` smallint(6) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `rundmail`
--

CREATE TABLE `rundmail` (
  `id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL DEFAULT '',
  `random` varchar(11) NOT NULL DEFAULT '0',
  `aktiv` smallint(6) NOT NULL DEFAULT '0',
  `staffel` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `spieler`
--

CREATE TABLE `spieler` (
  `id` int(11) NOT NULL,
  `mannschaft` int(11) NOT NULL DEFAULT '0',
  `zps` char(10) NOT NULL,
  `brettnr` smallint(6) NOT NULL,
  `vorname` varchar(20) NOT NULL,
  `nachname` varchar(20) NOT NULL DEFAULT '',
  `titel` varchar(15) NOT NULL,
  `dwz` varchar(4) DEFAULT NULL,
  `elo` int(11) DEFAULT NULL,
  `geburt` varchar(13) DEFAULT NULL,
  `geschlecht` char(1) DEFAULT NULL,
  `nmSid` int(11) DEFAULT NULL,
  `nmR` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `spielerpaarungen`
--

CREATE TABLE `spielerpaarungen` (
  `id` int(11) NOT NULL,
  `paarung` int(11) NOT NULL DEFAULT '0',
  `brett` int(11) NOT NULL DEFAULT '0',
  `spieler1` int(11) DEFAULT '0',
  `spieler2` int(11) DEFAULT '0',
  `ergebnis1` char(1) DEFAULT NULL,
  `ergebnis2` char(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staffeln`
--

CREATE TABLE `staffeln` (
  `id` int(11) NOT NULL,
  `leiter` int(11) NOT NULL DEFAULT '0',
  `name` varchar(30) NOT NULL DEFAULT '',
  `turnier` int(11) NOT NULL DEFAULT '0',
  `runden` int(11) DEFAULT NULL COMMENT 'Wenn abweichend',
  `brettzahl` int(11) DEFAULT NULL COMMENT 'Wenn abweichen',
  `spielAufsteiger` tinyint(4) DEFAULT NULL,
  `spielAbsteiger` tinyint(4) DEFAULT NULL,
  `spielAufsteigerRelegation` tinyint(4) DEFAULT NULL,
  `spielAbsteigerRelegation` tinyint(4) DEFAULT NULL,
  `sysEingabelinks` tinyint(4) DEFAULT NULL,
  `showTabelle` tinyint(4) DEFAULT NULL,
  `showNachmeldungen` tinyint(4) DEFAULT NULL,
  `showSpieltagvorschau` tinyint(4) DEFAULT NULL,
  `showPassNr` tinyint(4) DEFAULT NULL,
  `sortid` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `termine`
--

CREATE TABLE `termine` (
  `id` int(11) NOT NULL,
  `turnier` int(11) NOT NULL DEFAULT '0',
  `staffel` int(11) DEFAULT NULL,
  `runde` int(11) NOT NULL DEFAULT '0',
  `datum` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `turniere`
--

CREATE TABLE `turniere` (
  `id` int(11) NOT NULL,
  `leiter` int(11) NOT NULL DEFAULT '0',
  `name` varchar(40) NOT NULL DEFAULT '',
  `organisation` varchar(15) DEFAULT NULL,
  `startjahr` smallint(6) DEFAULT NULL,
  `runden` int(11) NOT NULL DEFAULT '0',
  `brettzahl` int(11) NOT NULL DEFAULT '0',
  `directory` varchar(20) DEFAULT NULL,
  `template` varchar(20) DEFAULT NULL,
  `infomeldung` varchar(200) NOT NULL,
  `anmAktiv` smallint(6) NOT NULL DEFAULT '1',
  `anmGeburt` smallint(6) NOT NULL DEFAULT '1900',
  `anmGeschlecht` char(1) DEFAULT NULL,
  `anmVerband` varchar(5) DEFAULT NULL,
  `anmZusatzfelder` text NOT NULL,
  `anmTLMail` tinyint(4) NOT NULL DEFAULT '0',
  `spielErsatzmannschaft` smallint(6) NOT NULL DEFAULT '0',
  `spielNachmeldungen` tinyint(4) NOT NULL DEFAULT '1',
  `spielDreistelligeNr` smallint(6) NOT NULL DEFAULT '0',
  `spielAufsteiger` tinyint(4) NOT NULL,
  `spielAbsteiger` tinyint(4) NOT NULL,
  `spielAufsteigerRelegation` tinyint(4) NOT NULL,
  `spielAbsteigerRelegation` tinyint(4) NOT NULL,
  `spielHatGruppen` tinyint(4) NOT NULL DEFAULT '0',
  `sysKeinNewsletter` tinyint(4) NOT NULL DEFAULT '0',
  `sysEingabelinks` tinyint(4) NOT NULL DEFAULT '0',
  `showTabelle` tinyint(4) NOT NULL DEFAULT '1',
  `showNachmeldungen` tinyint(4) NOT NULL DEFAULT '1',
  `showSpieltagvorschau` tinyint(4) NOT NULL DEFAULT '1',
  `showPassNr` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `turniermenue`
--

CREATE TABLE `turniermenue` (
  `id` int(11) NOT NULL,
  `turnier` int(11) NOT NULL,
  `sortid` smallint(6) NOT NULL,
  `titel` varchar(50) NOT NULL,
  `url` varchar(100) NOT NULL,
  `neuesfenster` smallint(6) NOT NULL DEFAULT '0',
  `topnavigation` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `verbaende`
--

CREATE TABLE `verbaende` (
  `zps` char(3) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `viewStaffeln`
-- (See below for the actual view)
--
CREATE TABLE `viewStaffeln` (
`id` int(11)
,`leiter` int(11)
,`staffelleiter` varchar(60)
,`email` varchar(50)
,`telefon` varchar(30)
,`telefon2` varchar(30)
,`name` varchar(30)
,`turnier` int(11)
,`runden` bigint(11)
,`brettzahl` bigint(11)
,`spielAufsteiger` int(4)
,`spielAbsteiger` int(4)
,`spielAufsteigerRelegation` int(4)
,`spielAbsteigerRelegation` int(4)
,`sysEingabelinks` int(4)
,`showTabelle` int(4)
,`showNachmeldungen` int(4)
,`showSpieltagvorschau` int(4)
,`showPassNr` int(4)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `viewStaffeltermine`
-- (See below for the actual view)
--
CREATE TABLE `viewStaffeltermine` (
`turnier` int(11)
,`id` int(11)
,`runde` int(11)
,`datum` date
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `viewTermine`
-- (See below for the actual view)
--
CREATE TABLE `viewTermine` (
`paarung` int(11)
,`turnier` int(11)
,`staffel` int(11)
,`runde` int(11)
,`termin` date
,`ausrichter` bigint(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `zusatzempfaenger`
--

CREATE TABLE `zusatzempfaenger` (
  `id` int(11) NOT NULL,
  `mannschaft` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `eingabelink` tinyint(4) NOT NULL DEFAULT '1',
  `bestaetigung` tinyint(4) NOT NULL DEFAULT '1',
  `rundmail` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for view `viewStaffeln`
--
DROP TABLE IF EXISTS `viewStaffeln`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `viewStaffeln`  AS SELECT `s`.`id` AS `id`, `s`.`leiter` AS `leiter`, `b`.`name` AS `staffelleiter`, `b`.`email` AS `email`, `b`.`telefon` AS `telefon`, `b`.`telefon2` AS `telefon2`, `s`.`name` AS `name`, `s`.`turnier` AS `turnier`, if(isnull(`s`.`runden`),`t`.`runden`,`s`.`runden`) AS `runden`, if(isnull(`s`.`brettzahl`),`t`.`brettzahl`,`s`.`brettzahl`) AS `brettzahl`, if(isnull(`s`.`spielAufsteiger`),`t`.`spielAufsteiger`,`s`.`spielAufsteiger`) AS `spielAufsteiger`, if(isnull(`s`.`spielAbsteiger`),`t`.`spielAbsteiger`,`s`.`spielAbsteiger`) AS `spielAbsteiger`, if(isnull(`s`.`spielAufsteigerRelegation`),`t`.`spielAufsteigerRelegation`,`s`.`spielAufsteigerRelegation`) AS `spielAufsteigerRelegation`, if(isnull(`s`.`spielAbsteigerRelegation`),`t`.`spielAbsteigerRelegation`,`s`.`spielAbsteigerRelegation`) AS `spielAbsteigerRelegation`, if(isnull(`s`.`sysEingabelinks`),`t`.`sysEingabelinks`,`s`.`sysEingabelinks`) AS `sysEingabelinks`, if(isnull(`s`.`showTabelle`),`t`.`showTabelle`,`s`.`showTabelle`) AS `showTabelle`, if(isnull(`s`.`showNachmeldungen`),`t`.`showNachmeldungen`,`s`.`showNachmeldungen`) AS `showNachmeldungen`, if(isnull(`s`.`showSpieltagvorschau`),`t`.`showSpieltagvorschau`,`s`.`showSpieltagvorschau`) AS `showSpieltagvorschau`, if(isnull(`s`.`showPassNr`),`t`.`showPassNr`,`s`.`showPassNr`) AS `showPassNr` FROM ((`staffeln` `s` join `benutzer` `b` on((`b`.`id` = `s`.`leiter`))) join `turniere` `t` on((`t`.`id` = `s`.`turnier`)))  ;

-- --------------------------------------------------------

--
-- Structure for view `viewStaffeltermine`
--
DROP TABLE IF EXISTS `viewStaffeltermine`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `viewStaffeltermine`  AS SELECT `s`.`turnier` AS `turnier`, `s`.`id` AS `id`, `t`.`runde` AS `runde`, if(isnull(`ts`.`datum`),`t`.`datum`,`ts`.`datum`) AS `datum` FROM ((`staffeln` `s` join `termine` `t` on(((`t`.`turnier` = `s`.`turnier`) and isnull(`t`.`staffel`)))) left join `termine` `ts` on(((`ts`.`staffel` = `s`.`id`) and (`ts`.`runde` = `t`.`runde`)))) WHERE 11  ;

-- --------------------------------------------------------

--
-- Structure for view `viewTermine`
--
DROP TABLE IF EXISTS `viewTermine`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `viewTermine`  AS SELECT `p`.`id` AS `paarung`, `s`.`turnier` AS `turnier`, `p`.`staffel` AS `staffel`, `p`.`runde` AS `runde`, if((`p`.`termin` is not null),`p`.`termin`,if(isnull(`t2`.`datum`),if(isnull(`t`.`datum`),NULL,`t`.`datum`),`t2`.`datum`)) AS `termin`, if(isnull(`p`.`ausrichter`),`p`.`mannschaft1`,`p`.`ausrichter`) AS `ausrichter` FROM (((`paarungen` `p` join `staffeln` `s` on((`s`.`id` = `p`.`staffel`))) left join `termine` `t` on(((`t`.`turnier` = `s`.`turnier`) and isnull(`t`.`staffel`) and (`t`.`runde` = `p`.`runde`)))) left join `termine` `t2` on(((`t2`.`turnier` = `s`.`turnier`) and (`t2`.`staffel` = `p`.`staffel`) and (`t2`.`runde` = `p`.`runde`))))  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anmeldungZusatzfelder`
--
ALTER TABLE `anmeldungZusatzfelder`
  ADD PRIMARY KEY (`mannschaft`,`feldname`);

--
-- Indexes for table `bemerkungen`
--
ALTER TABLE `bemerkungen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bemerkungen` (`staffel`,`runde`);

--
-- Indexes for table `benutzer`
--
ALTER TABLE `benutzer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`staffel`,`runde`,`typ`);

--
-- Indexes for table `dwz_spieler`
--
ALTER TABLE `dwz_spieler`
  ADD PRIMARY KEY (`ZPS`,`Mgl_Nr`),
  ADD KEY `dwz_spieler` (`Spielername`);

--
-- Indexes for table `dwz_vereine`
--
ALTER TABLE `dwz_vereine`
  ADD PRIMARY KEY (`ZPS`);

--
-- Indexes for table `geodb`
--
ALTER TABLE `geodb`
  ADD PRIMARY KEY (`plz`);

--
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mannschaften`
--
ALTER TABLE `mannschaften`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mannschaften` (`staffel`),
  ADD KEY `mannschaften3` (`turnier`),
  ADD KEY `ersatzmannschaft` (`zps`,`mnr`),
  ADD KEY `mfsuche` (`mf_name`);

--
-- Indexes for table `paarungen`
--
ALTER TABLE `paarungen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dreitagevorher` (`termin`),
  ADD KEY `paarungssuche` (`staffel`,`runde`),
  ADD KEY `m1` (`mannschaft1`),
  ADD KEY `m2` (`mannschaft2`);

--
-- Indexes for table `rundmail`
--
ALTER TABLE `rundmail`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rundmail2` (`staffel`,`email`);

--
-- Indexes for table `spieler`
--
ALTER TABLE `spieler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `spieler` (`mannschaft`),
  ADD KEY `nachmeldungen` (`nmSid`,`nmR`),
  ADD KEY `brettnr` (`brettnr`);

--
-- Indexes for table `spielerpaarungen`
--
ALTER TABLE `spielerpaarungen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `spielerpaarungen` (`paarung`),
  ADD KEY `s1` (`spieler1`),
  ADD KEY `s2` (`spieler2`);

--
-- Indexes for table `staffeln`
--
ALTER TABLE `staffeln`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staffeln` (`turnier`);

--
-- Indexes for table `termine`
--
ALTER TABLE `termine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `termine` (`turnier`),
  ADD KEY `staffel` (`staffel`);

--
-- Indexes for table `turniere`
--
ALTER TABLE `turniere`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `directory` (`directory`),
  ADD KEY `turnier` (`directory`),
  ADD KEY `organisation` (`organisation`);

--
-- Indexes for table `turniermenue`
--
ALTER TABLE `turniermenue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `turniermenue` (`turnier`);

--
-- Indexes for table `verbaende`
--
ALTER TABLE `verbaende`
  ADD PRIMARY KEY (`zps`);

--
-- Indexes for table `zusatzempfaenger`
--
ALTER TABLE `zusatzempfaenger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mannschaft` (`mannschaft`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bemerkungen`
--
ALTER TABLE `bemerkungen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `benutzer`
--
ALTER TABLE `benutzer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mannschaften`
--
ALTER TABLE `mannschaften`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `paarungen`
--
ALTER TABLE `paarungen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rundmail`
--
ALTER TABLE `rundmail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `spieler`
--
ALTER TABLE `spieler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `spielerpaarungen`
--
ALTER TABLE `spielerpaarungen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staffeln`
--
ALTER TABLE `staffeln`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `termine`
--
ALTER TABLE `termine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `turniere`
--
ALTER TABLE `turniere`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `turniermenue`
--
ALTER TABLE `turniermenue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zusatzempfaenger`
--
ALTER TABLE `zusatzempfaenger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
