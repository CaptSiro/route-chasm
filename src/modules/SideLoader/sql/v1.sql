CREATE TABLE `module_sideloadercache` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `hash` VARCHAR(127) NOT NULL ,
    `path` VARCHAR(255) NOT NULL ,
    PRIMARY KEY (`id`)
) ENGINE = MyISAM;

ALTER TABLE `module_sideloadercache` ADD INDEX(`hash`);