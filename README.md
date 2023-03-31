# nsv-online.de

This repository contains the open-sourced parts of nsv-online.de. Notably:
* `core/nsv2020`: The template used by nsv-online.de and schachbezirk-hannover.de. It runs on bootstrap, can work with both UTF-8 and ISO charsets, and is themable to some degree.
* `ligen`: Ergebnisdienst for managing chess leagues. Note that most of the code was written 15 years ago, so isn't exactly the most modern :) Also be careful as some files are in UTF-8 and some are in ISO charsets, so you need to make sure you load every file in the correct charset in your editor. The output to the browser is always ISO though. 

## Setup 

These are the setup steps to get the Ergebnisdienst (ligen/) running locally

### Configure Apache & PHP

1. Apache with PHP 8
1. Point DocumentRoot to this directory
1. Open localhost/info.php to make sure PHP is working
    1. Verify short_open_tag is set to On. Otherwise modify your php.ini accordingly.
    1. Make sure display_errors is set to On for debugging purposes. I recommend disabling E_NOTICE and E_STRICT though as the code will spew lots of those otherwise :)
1. Open localhost/ligen/. You should get a Fatal error from mysql_connect at this point

### Set up the database

1. Install MySQL (or MariaDB)
1. Install phpmyadmin or a DB editor of your choice
1. Set up the database
    1. Create a database for the Ergebnisdienst (excuse my Denglish :)
    1. Import structure from ligen-dev/db-setup/00_structure.sql
    1. Import DWZ data (vereine.sql and spieler.sql) from https://www.schachbund.de/download-dwz-daten.html
        - The files are in iso-8859-1 charset. Make sure to select that when importing!
        - If you use all DSB data, you might have to increase upload_max_filesize and post_max_size in your php.ini. But for testing using the data from a single state is probably sufficient.
        - Spielberechtigung seems to be nullable these days, just change the field to be nullable :)
    1. Import data for geodb and verbaende tables from ligen-dev/db-setup/01_data.sql
    1. Import test data from `ligen-dev/db-setup/02_testdata.sql`. This contains a simple test tournament.

### Configure the Ergebnisdienst

1. Copy `ligen/config.inc.php.example` to `ligen/config.inc.php` and add your database connection info to it. This file should not be commited and is part of `.gitignore`
1. Open http://localhost/ligen/?m=serverinfo. You should now get some statistics including "Anzahl Turniere: 1"
1. Open http://localhost/ligen/. You should see the same statistics screen. If you don't, you need to enable `mod_rewrite` in apache. Also make sure .htaccess files are parsed at all by setting `AllowOverride All` in your apache config.
1. Open localhost/ligen/test-2022/. You should now see the test tournament data :)
1. You can use the master password (123456 by default) to login as any user (Staffelleiter oder Turnierleiter).

## TODO for migration to Github

(just some notes for Marcel right now, ignore this section)

* Enable HTTPS for local development? Currently disabled HTTPS redirect in ligen/.htaccess
* Move hack in turniere.inc.php to correct line
* My local MySQL db doesn't allow '' instead of null for integer fields. That seems to work in production right now. Either need to change the code or configure the MySQL connection to allow for this (maybe sql_mode=''?)
* PHP 8 suppoert
  * Use __construct everywhere instead of function with same name as class
  * Fixed mysql-shim manually to work with PHP 8
* Do a diff with production, as I did a few changes...
