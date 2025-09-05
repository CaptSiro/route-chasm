DROP TABLE IF EXISTS core_module;
CREATE TABLE IF NOT EXISTS core_module (
    `identifier` VARCHAR(255) NOT NULL,
    `version` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`identifier`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS core_language;
CREATE TABLE IF NOT EXISTS core_language (
    `id_language` INT NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(16) NOT NULL,
    `is_default` TINYINT NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_language`),
    UNIQUE (`code`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS core_lexicon_translation_x_rule;
DROP TABLE IF EXISTS core_lexicon_translation;
DROP TABLE IF EXISTS core_lexicon;

DROP TABLE IF EXISTS core_lexicon_group;
CREATE TABLE IF NOT EXISTS core_lexicon_group (
    `id_lexicon_group` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_lexicon_group`),
    UNIQUE (`name`)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS core_lexicon_rule;
CREATE TABLE IF NOT EXISTS core_lexicon_rule (
    `id_rule` INT NOT NULL AUTO_INCREMENT,
    `rule` VARCHAR(255) NOT NULL,
    `label` VARCHAR(32),
    PRIMARY KEY (`id_rule`)
) ENGINE = InnoDB;

INSERT INTO core_lexicon_rule(`rule`, `label`)
VALUES ('/.*/', '*'),
       ('/^1$/', '1'),
       ('/^[2-4]$/', '2-4'),
       ('/^0|[2-9]|\\d{2,}$/', '0, 2+'),
       ('/^0|[5-9]|\\d{2,}$/', '0, 5+');

CREATE TABLE IF NOT EXISTS core_lexicon (
    `id_phrase` INT NOT NULL AUTO_INCREMENT,
    `id_lexicon_group` INT NOT NULL,
    `default` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `is_dynamic` TINYINT NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_phrase`),
    FOREIGN KEY (`id_lexicon_group`) REFERENCES `core_lexicon_group` (`id_lexicon_group`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS core_lexicon_translation (
    `id_translation` INT NOT NULL AUTO_INCREMENT,
    `id_phrase` INT NOT NULL,
    `id_language` INT NOT NULL,
    `translation` TEXT NOT NULL,
    `id_rule` INT DEFAULT NULL,
    PRIMARY KEY (`id_translation`),
    FOREIGN KEY (`id_phrase`) REFERENCES `core_lexicon` (`id_phrase`),
    FOREIGN KEY (`id_language`) REFERENCES `core_language` (`id_language`),
    FOREIGN KEY (`id_rule`) REFERENCES `core_lexicon_rule` (`id_rule`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `core_domain`;
CREATE TABLE IF NOT EXISTS `core_domain` (
    `id_domain` INT NOT NULL AUTO_INCREMENT,
    `protocol` VARCHAR(8) NOT NULL DEFAULT 'http',
    `host` VARCHAR(255) NOT NULL,
    `port` INT NOT NULL DEFAULT '0',
    `path` VARCHAR(255) NULL DEFAULT NULL,
    `cost` INT NOT NULL DEFAULT '1',
    `is_enabled` TINYINT NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_domain`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS core_setting;
CREATE TABLE IF NOT EXISTS core_setting (
    `id_setting` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `value` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `is_editable` TINYINT NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_setting`),
    UNIQUE (`name`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `core_sideloader`;
CREATE TABLE IF NOT EXISTS `core_sideloader` (
    `id_cache` INT NOT NULL AUTO_INCREMENT ,
    `hash` VARCHAR(127) NOT NULL ,
    `path` VARCHAR(255) NOT NULL ,
    PRIMARY KEY (`id_cache`),
    INDEX (`hash`)
) ENGINE = MyISAM;



DROP TABLE IF EXISTS `core_users_x_groups`;
DROP TABLE IF EXISTS `core_user`;
CREATE TABLE IF NOT EXISTS `core_user` (
    `id_user` INT NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(128) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `tag` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_user`),
    INDEX (`username`),
    UNIQUE (`tag`)
) ENGINE = InnoDB;

INSERT INTO `core_user` (id_user, username, password, tag)
VALUES (1, 'root', '', 'root');



DROP TABLE IF EXISTS `core_groups_x_resources`;
DROP TABLE IF EXISTS `core_group`;
CREATE TABLE IF NOT EXISTS `core_group` (
    `id_group` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128) NOT NULL,
    `is_editable` TINYINT NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_group`),
    UNIQUE (`name`)
) ENGINE = InnoDB;

INSERT INTO `core_group` (id_group, name, is_editable)
VALUES (1, 'Default', 0),
       (2, 'Root', 0),
       (3, 'Admin', 0);



CREATE TABLE IF NOT EXISTS `core_users_x_groups` (
    `id_user` INT NOT NULL,
    `id_group` INT NOT NULL,
    FOREIGN KEY (`id_user`) REFERENCES `core_user` (`id_user`),
    FOREIGN KEY (`id_group`) REFERENCES `core_group` (`id_group`)
) ENGINE = InnoDB;

INSERT INTO `core_users_x_groups` (id_user, id_group)
VALUES (1, 2);



DROP TABLE IF EXISTS `core_privilege`;
CREATE TABLE IF NOT EXISTS `core_privilege` (
    `id_privilege` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(32) NOT NULL,
    `is_editable` TINYINT NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_privilege`),
    UNIQUE (`name`)
) ENGINE = InnoDB;

INSERT INTO `core_privilege` (id_privilege, name, is_editable)
VALUES (1, 'Read', 0),
       (2, 'Create', 0),
       (3, 'Update', 0);



DROP TABLE IF EXISTS `core_resource`;
CREATE TABLE IF NOT EXISTS `core_resource` (
    `id_resource` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_resource`),
    UNIQUE (`name`)
) ENGINE = InnoDB;



CREATE TABLE IF NOT EXISTS `core_groups_x_resources` (
    `id_group` INT NOT NULL,
    `id_resource` INT NOT NULL,
    `id_privilege` INT NOT NULL,
    UNIQUE (`id_group`, `id_resource`, `id_privilege`),
    FOREIGN KEY (`id_group`) REFERENCES `core_group` (`id_group`),
    FOREIGN KEY (`id_resource`) REFERENCES `core_resource` (`id_resource`),
    FOREIGN KEY (`id_privilege`) REFERENCES `core_privilege` (`id_privilege`)
) ENGINE = InnoDB;
