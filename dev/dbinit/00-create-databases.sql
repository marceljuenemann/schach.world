CREATE DATABASE `nsv-main`;
CREATE DATABASE `nsv-wp`;
CREATE DATABASE `nsv-test`;

GRANT ALL PRIVILEGES ON `nsv-main`.* TO 'docker';
GRANT ALL PRIVILEGES ON `nsv-wp`.* TO 'docker';
GRANT ALL PRIVILEGES ON `nsv-test`.* TO 'docker';
