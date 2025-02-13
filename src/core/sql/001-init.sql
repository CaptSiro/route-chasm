CREATE TABLE `core_modules` (
    `identifier` VARCHAR(255) NOT NULL ,
    `version` VARCHAR(255) NOT NULL ,
    PRIMARY KEY (`identifier`)
) ENGINE = InnoDB;



CREATE TABLE `core_domains` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `host` VARCHAR(255) NOT NULL,
    `port` INT NOT NULL DEFAULT '0',
    `path` VARCHAR(255) NULL DEFAULT NULL,
    `cost` INT NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;



CREATE TABLE `core_settings` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `value` TEXT NOT NULL,
    `editable` TINYINT(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE `index_name` (`name`)
) ENGINE = InnoDB;

ALTER TABLE `core_settings` CHANGE `value` `value` TEXT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL;