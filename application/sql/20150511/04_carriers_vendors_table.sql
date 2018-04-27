
CREATE TABLE `carriers_vendors` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `carrier_id` INT NOT NULL , 
    `vendor_id` INT NOT NULL , 
    `created` DATETIME NOT NULL , 
    `modified` DATETIME NOT NULL , 
    PRIMARY KEY (`id`)
) 
ENGINE = InnoDB;
