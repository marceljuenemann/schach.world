# nsv-online.de

TODO: This README needs an overhaul.

[nsv-online.de](https://nsv-online.de) is the website of Lower Saxony's chess federation (Niedersächsischer Schachverband, or NSV for short). While the frontpage and many static sites are a fairly straightforward WordPress site, there are also many dynamic pages. Most notabily, there is a fully-fledged web app for managing chess leagues that is also used by various other chess organizations and allows clubs to submit their match results.   

## Overview  

Currently, all of the code is under `public`, which is supposed to be the DocumentRoot,
but the vision is to turn a lot of the code into a PSR-4 compliant web app that uses composer for package management.

* `core/nsv2020`: The template used by nsv-online.de and schachbezirk-hannover.de. It runs on bootstrap, can work with both UTF-8 and ISO charsets, and is themable to some degree.
* `ligen`: Ergebnisdienst for managing chess leagues. Note that most of the code was written 15 years ago, so isn't exactly the most modern :) Also be careful as some files are in UTF-8 and some are in ISO charsets, so you need to make sure you load every file in the correct charset in your editor. The output to the browser is always ISO though. This system is currently completely independent from the WordPress installation.
* `wordpress`: This is where the WordPress installation goes.
    * `themes/nsv2020`: the WordPress theme that hooks up to `core/nsv2020` and adds styles for the frontpage and sidebar widgets. 
    * `plugins/nsv-core`: the core plugin that is a bit of a micro-framework for building dynamic pages in WordPress
    * `plugins/nsv-turniere`: this plugin can display tournament results based on the text file export from SwissChess. 
       [Live Example](https://nsv-online.de/turniere/lem/2023/)

## Local Development  

### Initial Setup

We have a docker based development environment that runs Apache, MySQL and PhpMyAdmin for you. Follow [these instructions](dev/setup.md) for setup.

### Docker Usage

- **Start containers:** `docker compose up -d` in the `dev` directory
- **Stop containers:** `docker compose down`
- **SSH into webserver:** `docker exec -it nsv-webserver /bin/bash`. This allows you to run composer and symfony commands without having PHP installed. Even if you have PHP installed on the host, this ensures you run PHP with the same version as the webserver.

### Composer & Symfony Usage

Some useful commands for Symfony development:

- **Update dependencies:** `composer update`
- **Clear cache:** `./bin/console cache:clear`. This should only be needed if the enviornment is set to `PROD`.

### Running tests

Some of the league manager tests run against an actual database. If you use the docker environment, a separate test database is already set up for you. However, you still need to fill in test data (*fixtures*) with the following command:

`./bin/console --env=test --em=league doctrine:fixtures:load` 

You can then run the tests with

`./bin/phpunit`

Note that many of the league manager tests just dump the API output into a text file and compare it with the expected output. Use a diff tool like `meld` to compare the files in `tests/League/Api/Service/` and update the `expected` output if the changes were intended.

If you do change the fixtures, you will need to rerun the command from above again to fill the test database. However, if you want to make sure that the IDs stay the same, you should first empty the database tables of `nsv-ligen-test` manually via PhpMyAdmin. TODO: Make this work using the `--purge-with-truncate` flag.

### React Development

Some of the most recent frontend code uses React components. You can find the code and instructions for that in the [javascript](javascript/README.md) folder. 

### Debugging with XDebug

*TODO: XDebug is already installed in Docker. Just need to enable it in php.ini and add instructions*
