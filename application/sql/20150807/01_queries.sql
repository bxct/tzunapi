ALTER TABLE  `queries` ADD  `gsma_started`   DATETIME NULL DEFAULT NULL AFTER  `failed` ;
ALTER TABLE  `queries` ADD  `gsma_completed` DATETIME NULL AFTER  `gsma_started` ;