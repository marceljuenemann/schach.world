CREATE DATABASE `nsv-main`;
CREATE DATABASE `nsv-wp`;

GRANT ALL PRIVILEGES ON `nsv-main`.* TO 'docker';
GRANT ALL PRIVILEGES ON `nsv-wp`.* TO 'docker';
