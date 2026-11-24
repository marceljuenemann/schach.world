# Docker-based development environment 

This folder contains configuration for a development environment based on Docker. It provides everything that is needed for local development without the need to install a lot of different tools manually:

- Apache webserver with PHP: https://localhost:6464/ 
- MySQL server (exposed to the host on port 6465)
- PhpMyAdmin: http://localhost:6466/

## Setup Docker

1. Install Docker or Docker Desktop
1. Within the `dev` directory, run `docker compose up -d --wait` to start docker
    - The first time this is run, Docker will automatically download necessary dependencies and compile container images. This will take a few minutes and might take up to 1 GB of storage.
    - It will automatically initialize the database with the scripts located in `dev/dbinit/` (see [Database](#database) below). The data is stored in the Docker volume `nsv_db-data`.
1. Verify you can access PhpMyAdmin via http://localhost:6466/
1. Verify the webserver is running by going to https://localhost:6464/core/nsv2020/images/nsv.png
    - You should see a warning that the HTTPS certificate is invalid. In Chrome, you can ignore that by clicking on the small *Advanced* link

Apache and PHP error logs are not written to files. View them with `docker logs -f nsv-webserver`.

## Setup WordPress

The WordPress database is created by `dev/dbinit/50-wordpress.sql`, with the *NSV 2020 Local Dev Edition* theme and all NSV plugins already activated and permalinks configured. You only need to install the WordPress files:

1. Install WordPress into public/wordpress directory. On the command line, you can use these commands:
    - In `public` run `wget https://wordpress.org/latest.tar.gz`
    - Unpack with `tar -xzvf latest.tar.gz`
1. Copy `public/wordpress/wp-config-sample-nsv.php` to `public/wordpress/wp-config.php`. If you are using docker, all settings should be correct in order for WordPress to connect to the docker database container from the webserver container.
1. For the theme to work locally, create the following two **empty** files: `public/core/config.inc.php` and `public/core/functions.inc.php`. These are legacy files that are only needed for some minor widget functions (TODO: Remove the need for this)
1. Log into the admin area under https://localhost:6464/wp-admin/ with user `admin` and password `123456`. If WordPress asks for a database update (because your WordPress version is newer than the one the dump was made with), confirm it.
1. Your WordPress site should now be up and running! Try these URLs:
    - Frontpage: https://localhost:6464/ 
    - Contact form: https://localhost:6464/kontakt/ 

## Set up Symfony

1. Create an empty `.env.local` file where you can override settings from the `.env` file. That said, you should not need any changes to database connection strings or otherwise if you are running everything through docker.
1. If you are running docker, ssh into the webserver container with `docker exec -it nsv-webserver /bin/bash` to run the following commands. If you are not using docker, you can just run these straight from the command line:
    - Run `composer update` to install dependencies. You will want to run this again whenever you pull updates from GitHub or when you are changing dependencies yourself
    - Run `./bin/console doctrine:migrations:migrate --em main` to apply database migrations to the dev database. Do this again after resetting the database.
1. Symfony should now be up and running for all routes allowed in the `nsv-v3` plugin. You can verify by going to https://localhost:6464/termine/ 

## Set up League Manager

1. Copy `public/ligen/config-sample.inc.php` to `config.inc.php` in the same directory. If you are using docker, no changes should be necessary. The master password with which you can log into any account within the league manager is set to `123456`.
1. You should now be able to use the test tournaments under https://localhost:6464/ligen/test-2022/ and https://localhost:6464/ligen/test-2627/.
1. Optional: Import the DWZ database by running `./bin/console dwz:update` in the webserver container. It downloads the file set in `DWZ_DATABASE_URL`. The default is the data of all of Germany; for a single state, override it in `.env.local` (e.g. `LV-7-csv.zip` for Niedersachsen).

## Database

The MySQL server contains three databases:

- `nsv-main`: dev database used by Symfony and the league manager. Edit it freely, it is not used by tests.
- `nsv-wp`: WordPress database.
- `nsv-test`: used by the PHP tests. `tests/bootstrap.php` rebuilds it at the start of every phpunit run (with `dev/scripts/reset-test-db.sh`), so every run, locally and in CI, starts from the same data. Set `SKIP_DB_RESET=1` to skip this, e.g. `docker exec -e SKIP_DB_RESET=1 nsv-webserver ./bin/phpunit tests/...`.

## Credits

The docker setup is heavily inspired by https://github.com/sprintcube/docker-compose-lamp
