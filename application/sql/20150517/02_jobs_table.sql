
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` int(11) NOT NULL,
  `sub_query_id` int(11) NOT NULL,
  `carrier_id` int(11) NOT NULL,
  `esn` char(140) NOT NULL,
  `started` datetime DEFAULT NULL,
  `canceled` datetime DEFAULT NULL,
  `completed` datetime DEFAULT NULL,
  `failed` datetime DEFAULT NULL,
  `status` char(100) DEFAULT NULL,
  `status_details` varchar(500) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;