CREATE TABLE IF NOT EXISTS `devices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `esn` char(140) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `devuidesn` (`user_id`,`esn`);
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;