Docker-based development environment 

TODO: Write README and instructions


`docker compose up -d`



- Apache webserver with PHP: https://localhost:6464/ 
- MySQL server (exposed to the host on port 6465)
- PhpMyAdmin: http://localhost:6466/


## Setup Docker

1. Install Docker
1. Start Docker containers (will build containers and initialize database with scripts in `dev/dbinit/`). Verify with phpadmin and a simple file?
    - https://localhost:6464/core/nsv2020/images/nsv.png

## Setup WordPress

1. Install WordPress into public/wordpress directory
1. Set up config files [move to sections below]
  Set up config files. Should not require any changes when running with docker (in that case the hostname is nsv-database).
  - public/wordpress/wp-config-sample-nsv.php to wp-config.php
    - When running 
1. Set up WordPress (TODO: copy instructions from README.md)
  1. Follow instructions under https://localhost:6464/
  1. Enable theme NSV 2020
    - Create empty files public/core/config.inc.php and public/core/functions.inc.php (Legacy code that is used for minor features in the theme)
  1. Enable plugins
  1. Change site URL
  1. Change permalink away from "simple" in order to enable redirects.
  1. Verify (/kontakt)

## Set up Symfony
  1. composer
  1. Verify /termine


## Set up League Manager
  1. config
  1. 


## Usage

(move to parent readme)

- Stop docker: `docker compose down`
- The database is stored in `var/database/`. If you want to reinitialize your local database (i.e. delete all data you added!), you can just delete this folder and then restart the docker container.


SSH into webserver: `docker exec -it nsv-webserver /bin/bash`

## Credits

Heavily inspired by https://github.com/sprintcube/docker-compose-lamp
