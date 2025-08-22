<?php

use Nsv\Registration\Api\Model\TournamentConfig;
use Nsv\Registration\Api\Model\GroupConfig;
use Nsv\Registration\Api\Model\RegistrationConstraint;
use Nsv\Registration\Api\Model\AdditionalFieldConfig;
use Nsv\Registration\Api\Model\AdditionalFieldOption;

$config = new TournamentConfig();
$config->tournamentName = 'Testturnier';
$config->deadline = '2035-12-27';
$config->maxPlayers = 38;

$config->managers = ['marcel', 'beni', 'joerg'];
$config->emailCc = [
  'test-cc1@marcel.world',
  'test-cc2@marcel.world',
];
$config->emailReplyTo = $config->emailCc;

$config->links = [
  'Ausschreibung' => 'https://example.com/ausschreibung.pdf',
  'NSV Homepage' => 'https://nsv-online.de',
];
$config->termsAndConditions = "
  Ich stimme der Verarbeitung meiner personenbezogenen Daten zum Zwecke
  der Turnierdurchführung sowie der Veröffentlichung von 
  Turnierergebnissen, Fotos und Partien zu.
  Ich erlaube die Weiterleitung meiner Daten an den Deutschen Schachbund
  und die FIDE zur DWZ- und ELO-Auswertung.
";

$group = new GroupConfig();
$group->id = 'A';
$group->name = 'Gruppe A (ab DWZ 1750)';
$group->minDwz = 1750;
$group->maxPlayers = 10;
$group->requireFideId = true;
$config->groups[] = $group;

$group = new GroupConfig();
$group->id = 'B';
$group->name = 'Gruppe B (DWZ 1500-1750)';
$group->minDwz = 1500;
$group->maxDwz = 1750;
$group->maxPlayers = 30;
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
$group->maxPlayers = 3;
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

$constraint = new RegistrationConstraint();
$constraint->groups = ['A', 'B', 'C'];
$constraint->maxPlayers = 25;
$config->constraints[] = $constraint;

$constraint = new RegistrationConstraint();
$constraint->groups = ['U18', 'U16', 'U14', 'U12', 'U10'];
$constraint->maxPlayers = 15;
$config->constraints[] = $constraint;

$field = new AdditionalFieldConfig();
$field->type = 'multiline';
$field->id = 'customField1';
$field->label = 'Custom Field 1';
$field->required = true;
$config->additionalFields[] = $field;

$field = new AdditionalFieldConfig();
$field->type = 'int';
$field->id = 'customField2';
$field->label = 'Custom Field 2';
$field->required = false;
$config->additionalFields[] = $field;

$field = new AdditionalFieldConfig();
$field->type = 'select';
$field->id = 'customField3';
$field->label = 'Custom Field 3';
$field->required = true;
$config->additionalFields[] = $field;

$option = new AdditionalFieldOption();
$option->label = 'Option 1';
$option->value = 'option1';
$field->options[] = $option;

$option = new AdditionalFieldOption();
$option->label = 'Option 2';
$option->value = 'option2';
$option->disabled = true;
$field->options[] = $option;

$option = new AdditionalFieldOption();
$option->label = 'Option 3';
$option->value = 'option3';
$field->options[] = $option;

return $config;
