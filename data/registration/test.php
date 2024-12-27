<?php

use Nsv\Registration\Api\Model\TournamentConfig;
use Nsv\Registration\Api\Model\GroupConfig;

$config = new TournamentConfig();
$config->tournamentName = 'Testturnier';
$config->deadline = '2024-12-27';
$config->maxPlayers = 38;

$config->managers = ['marcel', 'beni'];
$config->emailCc = [
  'test-cc1@marcel.world',
  'test-cc2@marcel.world',
];
$config->emailReplyTo = $config->emailCc;
$config->links = [
  'Ausschreibung' => 'https://example.com/ausschreibung.pdf',
  'NSV Homepage' => 'https://nsv-online.de',
];

$group = new GroupConfig();
$group->id = 'A';
$group->name = 'Gruppe A (ab DWZ 1750)';
$config->groups[] = $group;

$group = new GroupConfig();
$group->id = 'B';
$group->name = 'Gruppe B (DWZ 1500-1750)';
$group->maxDwz = 1750;
$config->groups[] = $group;

$group = new GroupConfig();
$group->id = 'C';
$group->name = 'Gruppe C (bis DWZ 1500)';
$group->maxDwz = 1500;
$config->groups[] = $group;

$group = new GroupConfig();
$group->id = 'U18';
$group->name = 'Altersklasse U18';
$group->minYearOfBirth = 2007;
$config->groups[] = $group;

$group = new GroupConfig();
$group->id = 'U16';
$group->name = 'Altersklasse U16';
$group->minYearOfBirth = 2009;
$config->groups[] = $group;

$group = new GroupConfig();
$group->id = 'U14';
$group->name = 'Altersklasse U14';
$group->minYearOfBirth = 2011;
$config->groups[] = $group;

$group = new GroupConfig();
$group->id = 'U12';
$group->name = 'Altersklasse U12';
$group->minYearOfBirth = 2013;
$config->groups[] = $group;

$group = new GroupConfig();
$group->id = 'U10';
$group->name = 'Altersklasse U10';
$group->minYearOfBirth = 2015;
$config->groups[] = $group;

return $config;
