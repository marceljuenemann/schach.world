USE `nsv-webapp`;

CREATE TABLE `termine2` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `url` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `is_nsv` tinyint(1) NOT NULL DEFAULT '0',
  `is_approved` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `termine2`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `termine2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
