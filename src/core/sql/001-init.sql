CREATE TABLE IF NOT EXISTS `core_modules` (
    `identifier` VARCHAR(255) NOT NULL ,
    `version` VARCHAR(255) NOT NULL ,
    PRIMARY KEY (`identifier`)
) ENGINE = InnoDB;



CREATE TABLE IF NOT EXISTS `core_domains` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `host` VARCHAR(255) NOT NULL,
    `port` INT NOT NULL DEFAULT '0',
    `path` VARCHAR(255) NULL DEFAULT NULL,
    `cost` INT NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;



CREATE TABLE IF NOT EXISTS `core_settings` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `value` TEXT NOT NULL,
    `is_editable` TINYINT(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE `index_name` (`name`)
) ENGINE = InnoDB;

ALTER TABLE `core_settings` CHANGE `value` `value` TEXT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL;

CREATE TABLE IF NOT EXISTS `core_user` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(128) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `tag` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX (`username`),
    UNIQUE (`tag`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `core_group` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE (`name`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `core_users_x_groups` (
    `id_user` INT NOT NULL,
    `id_group` INT NOT NULL,
    FOREIGN KEY (`id_user`) REFERENCES `core_user` (`id`),
    FOREIGN KEY (`id_group`) REFERENCES `core_group` (`id`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `core_privilege` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(32) NOT NULL,
    `is_editable` TINYINT(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE (`name`)
) ENGINE = MyISAM;

INSERT INTO `core_privilege` (id, name, is_editable)
VALUES (1, 'Read', 0),
       (2, 'Create', 0),
       (3, 'Update', 0);

CREATE TABLE IF NOT EXISTS `core_resource` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE (`name`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `core_groups_x_resources` (
    `id_group` INT NOT NULL,
    `id_resource` INT NOT NULL,
    `id_privilege` INT NOT NULL,
    UNIQUE (`id_group`, `id_resource`, `id_privilege`),
    FOREIGN KEY (`id_group`) REFERENCES `core_group` (`id`),
    FOREIGN KEY (`id_resource`) REFERENCES `core_resource` (`id`),
    FOREIGN KEY (`id_privilege`) REFERENCES `core_privilege` (`id`)
) ENGINE = MyISAM;
