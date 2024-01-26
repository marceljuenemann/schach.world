CREATE DATABASE `nsv-webapp`;
CREATE DATABASE `nsv-ligen`;
CREATE DATABASE `nsv-wp`;
CREATE DATABASE `nsv-test`;

GRANT ALL PRIVILEGES ON `nsv-webapp`.* TO 'docker';
GRANT ALL PRIVILEGES ON `nsv-ligen`.* TO 'docker';
GRANT ALL PRIVILEGES ON `nsv-wp`.* TO 'docker';
GRANT ALL PRIVILEGES ON `nsv-test`.* TO 'docker';
