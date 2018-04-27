CREATE TABLE IF NOT EXISTS `sub_queries` (
  `id` int(11) NOT NULL,
  `query_id` int(11) NOT NULL,
  `carrier_id` int(11) NOT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `esn` char(140) NOT NULL,
  `attempts` int(1) DEFAULT '0',
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
-- Indexes for table `sub_queries`
--
ALTER TABLE `sub_queries`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sub_queries`
--
ALTER TABLE `sub_queries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;