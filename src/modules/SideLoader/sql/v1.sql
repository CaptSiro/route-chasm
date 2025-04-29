DROP TABLE IF EXISTS `sideloader_cache`;
CREATE TABLE IF NOT EXISTS `sideloader_cache` (
    `id_cache` INT NOT NULL AUTO_INCREMENT ,
    `hash` VARCHAR(127) NOT NULL ,
    `path` VARCHAR(255) NOT NULL ,
    PRIMARY KEY (`id_cache`),
    INDEX (`hash`)
) ENGINE = MyISAM;
