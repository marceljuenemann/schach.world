Docker-based development environment 

TODO: Write README and instructions

Heavily inspired by https://github.com/sprintcube/docker-compose-lamp

`docker compose up -d`


Set up config files:
- public/wordpress/wp-config-sample-nsv.php to wp-config.php


- Local Website: https://localhost:6464/ 
- PhpMyAdmin: http://localhost:6466/



- Stop docker: `docker compose down`
- The database is stored in `var/database/`. If you want to reinitialize your local database (i.e. delete all data you added!), you can just delete this folder and then restart the docker container.
