# Docker-based development environment 

This folder contains configuration for a development environment based on Docker. It provides everything that is needed for local development without the need to install a lot of different tools manually:

- Apache webserver with PHP: https://localhost:6464/ 
- MySQL server (exposed to the host on port 6465)
- PhpMyAdmin: http://localhost:6466/

## Setup Docker

1. Install Docker or Docker Desktop
1. Within the `dev` directory, run `docker compose up -d` to start docker
    - The first time this is run, Docker will automatically download necessary dependencies and compile container images. This will take a few minutes and might take up to 1 GB of storage.
    - It will automatically initialize the database with the scripts located in `dev/dbinit/`. The database will be stored in `dev/var/database`. If you delete this folder, the database will be reinitialized when you restart the docker container.
1. Verify you can access PhpMyAdmin via http://localhost:6466/
1. Verify the webserver is running by going to https://localhost:6464/core/nsv2020/images/nsv.png
    - You should see a warning that the HTTPS certificate is invalid. In Chrome, you can ignore that by clicking on the small *Advanced* link

## Setup WordPress

1. Install WordPress into public/wordpress directory. On the command line, you can use these commands:
    - In `public` run `wget https://wordpress.org/latest.tar.gz`
    - Unpack with `tar -xzvf latest.tar.gz`
1. Copy `public/wordpress/wp-config-sample-nsv.php` to `public/wordpress/wp-config.php`. If you are using docker, all settings should be correct in order for WordPress to connect to the docker database container from the webserver container.
1. Complete the WordPress setup under https://localhost:6464/ with values of your choice and log into the admin area
1. Under Appearance / Themes, activate *NSV 2020 Local Dev Edition*. This is a child theme of the production theme with some sidebar widgets excluded that require legacy code not included in this repo.
    - For the theme to work locally, you need to create the following two **empty** files: `public/core/config.inc.php` and `public/core/functions.inc.php`. These are legacy files that are only needed for some minor widget functions (TODO: Remove the need for this)
1. Under Admin > Plugins, activate all the NSV plugins
1. Go to Admin > Setting > Permalinks. Change the `Permalink structure` away from `Plain`, e.g. to `Month and name`. This is needed for the redirects in the NSV plugins to work.
1. Optional: Under General settings, remove the wordpress/ suffix from the URL. This is set in prodution in order to avoid WordPress generating ugly URLs with wordpress/ in the path.
1. Your WordPress site should now be up and running! Try these URLs:
    - Frontpage: https://localhost:6464/ 
    - Contact form: https://localhost:6464/kontakt/ 

## Set up Symfony

1. Create an empty `.env.local` file where you can override settings from the `.env` file. That said, you should not need any changes to database connection strings or otherwise if you are running everything through docker.
1. If you are running docker, ssh into the webserver container with `docker exec -it nsv-webserver /bin/bash` to run the following commands. If you are not using docker, you can just run these straight from the command line:
    - Run `composer update` to install dependencies. You will want to run this again whenever you pull updates from GitHub or when you are changing dependencies yourself
1. Symfony should now be up and running for all routes allowed in the `nsv-v3` plugin. You can verify by going to https://localhost:6464/termine/ 

## Set up League Manager

1. Copy `public/ligen/config-sample.inc.php` to `config.inc.php` in the same directory. If you are using docker, no changes should be necessary. The master password with which you can log into any account within the league manager is set to `123456`.
1. You should now be able to use the test tournament under https://localhost:6464/ligen/test-2022/.
1. Optional: Import the DWZ database
    - Download the DWZ data in SQL format from https://www.schachbund.de/download-dwz-daten.html
    - Use PhpMyAdmin to import `vereine.sql` and `spieler.sql`. Make sure to select `iso-8859-1` charset when importing. Make sure to select that when importing!
    - If you use all DSB data, you might have to increase upload_max_filesize and post_max_size in your php.ini. But for testing using the data from a single state is probably sufficient.
    - Spielberechtigung seems to be nullable these days, just change the field to be nullable :)

## Credits

The docker setup is heavily inspired by https://github.com/sprintcube/docker-compose-lamp
