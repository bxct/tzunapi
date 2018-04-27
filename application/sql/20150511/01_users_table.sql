CREATE TABLE `users` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `public_key` CHAR(140) NOT NULL , 
    `private_key` CHAR(140) NOT NULL , 
    `username` CHAR(140) NULL , 
    `password` CHAR(140) NULL , 
    `created` DATETIME NOT NULL , 
    `modified` DATETIME NOT NULL , 
    PRIMARY KEY (`id`), 
    UNIQUE `upubk` (`public_key`), 
    UNIQUE `uuname` (`username`)
) 
ENGINE = InnoDB;