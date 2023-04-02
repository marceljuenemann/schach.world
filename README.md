# nsv-online.de

This repository contains the open-sourced parts of nsv-online.de. Notably:
* `core/nsv2020`: The template used by nsv-online.de and schachbezirk-hannover.de. It runs on bootstrap, can work with both UTF-8 and ISO charsets, and is themable to some degree.
* `ligen`: Ergebnisdienst for managing chess leagues. Note that most of the code was written 15 years ago, so isn't exactly the most modern :) Also be careful as some files are in UTF-8 and some are in ISO charsets, so you need to make sure you load every file in the correct charset in your editor. The output to the browser is always ISO though. 

## Setup for local development  

These are the general steps required for any local development

1. Install apache with PHP 8, pointing the DocumentRoot to the `public` directory
1. Enable the `headers` apache module. On Ubuntu that's just `sudo a2enmod headers` and `sudo service apache2 restart` (alternatively, just uncomment the `Header` lines from `.htaccess`)
1. Enable SSL
    * This is necessary because `.htaccess` will redirect any request to HTTPs. Alternatively, you can uncomment those redirects in the `.htaccess`, but then you need to be careful when committing...
    * Instructions for Ubuntu can be found here: https://stackoverflow.com/a/25946171/1620264
    * You probably also want to tell your browser to accept the self-sigend certificate: https://stackoverflow.com/a/31900210/1620264
1. Open https://localhost/infobox-lem.php to make sure PHP is working
1. Verify short_open_tag is set to On. Otherwise modify your php.ini accordingly.
1. Make sure display_errors is set to On for debugging purposes. I recommend disabling E_NOTICE and E_STRICT though as the code will spew lots of those otherwise :)
1. Install MySQL (or MariaDB)
1. Install phpmyadmin or a DB editor of your choice
 
## Setup ligen/ locally

These are the setup steps to get the Ergebnisdienst (ligen/) running locally

1. Open localhost/ligen/. You should get a Fatal error from mysql_connect at this point
1. Set up the database
    1. Create a database for the Ergebnisdienst (excuse my Denglish :)
    1. Import structure from setup/ligen-db/00_structure.sql
    1. Import DWZ data (vereine.sql and spieler.sql) from https://www.schachbund.de/download-dwz-daten.html
        - The files are in iso-8859-1 charset. Make sure to select that when importing!
        - If you use all DSB data, you might have to increase upload_max_filesize and post_max_size in your php.ini. But for testing using the data from a single state is probably sufficient.
        - Spielberechtigung seems to be nullable these days, just change the field to be nullable :)
    1. Import data for geodb and verbaende tables from `setup/ligen-db/01_data.sql`
    1. Import test data from `setup/ligen-db/02_testdata.sql`. This contains a simple test tournament.
1. Copy `ligen/config.inc.php.example` to `ligen/config.inc.php` and add your database connection info to it. This file should not be commited and is part of `.gitignore`
1. Open https://localhost/ligen/?m=serverinfo. You should now get some statistics including "Anzahl Turniere: 1"
1. Open https://localhost/ligen/. You should see the same statistics screen. If you don't, you need to enable `mod_rewrite` in apache. Also make sure .htaccess files are parsed at all by setting `AllowOverride All` in your apache config.
1. Open localhost/ligen/test-2022/. You should now see the test tournament data :)
1. You can use the master password (123456 by default) to login as any user (Staffelleiter oder Turnierleiter).

## Setup WordPress locally

In order to get a local WordPress installation with the NSV theme and plugins, follow these instructions.

1. [Download WordPress](https://wordpress.org/download/) and extract it into `public/wordpress/`
1. Create a database for WordPress and enter the connection credentials into `public/wordpress/wp-config.php`
1. Open https://localhost/wordpress and follow WordPress' installation wizard
1. You should now be able to see the default WordPress theme at https://localhost
1. Create the following empty files: `public/core/config.inc.php` and `public/core/functions.inc.php` (these are part of the legacy NSV site and still required at the moment by the theme)  
1. Log into /wp-admin and change the following settings:
    1. Under General settings, set the URLs to https://localhost/ (without the wordpress/ suffix)
    1. Under Appearance / Themes, activate *NSV 2020 Local Dev Edition*. This is a child theme of the production theme with some sidebar widgets excluded that require legacy code not included in this repo.
1. https://localhost should now show you a website in the NSV theme :)

TODO: Get some plugins working

## TODO for migration to Github

(just some notes for Marcel right now, ignore this section)

* Move hack in turniere.inc.php to correct line
* My local MySQL db doesn't allow '' instead of null for integer fields. That seems to work in production right now. Either need to change the code or configure the MySQL connection to allow for this (maybe sql_mode=''?)
    * We should just build a whole new data layer anyways, really :)
* Update this README a bit
