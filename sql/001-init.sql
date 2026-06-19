DROP TABLE IF EXISTS core_module;
CREATE TABLE IF NOT EXISTS core_module (
    `identifier` VARCHAR(255) NOT NULL,
    `version` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`identifier`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS core_fs_shortcut;
DROP TABLE IF EXISTS core_fs_file;
DROP TABLE IF EXISTS core_fs_directory;
CREATE TABLE IF NOT EXISTS core_fs_directory (
    `id_fs_directory` INT NOT NULL AUTO_INCREMENT,
    `id_fs_parent` INT DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_fs_directory`),
    FOREIGN KEY (`id_fs_parent`) REFERENCES core_fs_directory (`id_fs_directory`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS core_fs_file (
    `id_fs_file` INT NOT NULL AUTO_INCREMENT,
    `id_fs_parent` INT DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `extension` VARCHAR(16) NOT NULL,
    `hash` VARCHAR(255) NOT NULL,
    `type` VARCHAR(128) NOT NULL,
    `size` LONG NOT NULL,
    PRIMARY KEY (`id_fs_file`),
    FOREIGN KEY (`id_fs_parent`) REFERENCES core_fs_directory (`id_fs_directory`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS core_fs_shortcut (
    `id_fs_shortcut` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `id_fs_file` INT NOT NULL,
    PRIMARY KEY (`id_fs_shortcut`),
    UNIQUE (`name`),
    FOREIGN KEY (`id_fs_file`) REFERENCES core_fs_file (`id_fs_file`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS core_fs_image_variant;

CREATE TABLE IF NOT EXISTS core_fs_image_variant (
    `id_fs_image_variant` INT NOT NULL AUTO_INCREMENT,
    `transformer` VARCHAR(255) NOT NULL,
    `version` INT DEFAULT 1,
    `quality` FLOAT DEFAULT 1,
    `function` VARCHAR(255) NOT NULL,
    `width` INT DEFAULT -1,
    `height` INT DEFAULT -1,
    PRIMARY KEY (`id_fs_image_variant`),
    UNIQUE (`transformer`)
) ENGINE = InnoDB;

INSERT INTO `core_fs_image_variant` (`transformer`, `version`, `quality`, `function`, `width`, `height`)
VALUES ('full-hd', 1, 1, 'fit', 1920, 1080),
    ('hd', 1, 1, 'fit', 1280, 720);



DROP TABLE IF EXISTS core_lexicon_translation;
DROP TABLE IF EXISTS core_related_pages;
DROP TABLE IF EXISTS core_external_page;
DROP TABLE IF EXISTS core_ai_page;
DROP TABLE IF EXISTS ext_page_meta;
DROP TABLE IF EXISTS core_page_localization;
DROP TABLE IF EXISTS core_navigation;

DROP TABLE IF EXISTS core_language;
CREATE TABLE IF NOT EXISTS core_language (
    `id_language` INT NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(16) NOT NULL,
    `is_default` TINYINT NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_language`),
    UNIQUE (`code`)
) ENGINE = InnoDB;

INSERT INTO core_language (id_language, code, is_default)
VALUES (1, 'en-US', 1);



DROP TABLE IF EXISTS core_lexicon_translation_x_rule;
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



DROP TABLE IF EXISTS core_navigation_context;
DROP TABLE IF EXISTS core_navigation_factory;

CREATE TABLE IF NOT EXISTS core_navigation_context (
    `id_navigation_context` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_navigation_context`),
    UNIQUE (`name`)
) ENGINE = InnoDB;

INSERT INTO core_navigation_context(`id_navigation_context`, `name`)
VALUES (1, 'Default');

CREATE TABLE IF NOT EXISTS core_navigation_factory (
    `id_navigation_factory` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_navigation_factory`),
    UNIQUE (`name`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS core_navigation (
    `id_slug` INT NOT NULL AUTO_INCREMENT,
    `id_navigation_context` INT NOT NULL,
    `id_parent` INT DEFAULT NULL,
    `id_language` INT NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `id_navigation_factory` INT DEFAULT NULL,
    `data` VARCHAR(255) DEFAULT '',
    PRIMARY KEY (`id_slug`),
    UNIQUE (`id_navigation_context`, `id_parent`, `id_language`, `slug`),
    FOREIGN KEY (`id_navigation_context`) REFERENCES `core_navigation_context` (`id_navigation_context`),
    FOREIGN KEY (`id_parent`) REFERENCES `core_navigation` (`id_slug`),
    FOREIGN KEY (`id_language`) REFERENCES `core_language` (`id_language`),
    FOREIGN KEY (`id_navigation_factory`) REFERENCES `core_navigation_factory` (`id_navigation_factory`)
) ENGINE = InnoDB;




DROP TABLE IF EXISTS core_page;
DROP TABLE IF EXISTS core_page_status;
DROP TABLE IF EXISTS core_page_template;

CREATE TABLE IF NOT EXISTS core_page_status (
    `id_page_status` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `is_editable` TINYINT NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_page_status`),
    UNIQUE (`name`)
) ENGINE = InnoDB;

INSERT INTO core_page_status(id_page_status, name, is_editable)
VALUES (1, 'Draft', 0),
    (2, 'Public', 0),
    (3, 'Archived', 0);

CREATE TABLE IF NOT EXISTS core_page_template (
    `id_page_template` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_page_template`),
    UNIQUE (`name`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS core_page (
    `id_page` INT NOT NULL AUTO_INCREMENT,
    `id_page_parent` INT NULL DEFAULT NULL,
    `id_page_template` INT NOT NULL,
    `id_page_status` INT NOT NULL,
    `created` DATETIME DEFAULT NOW(),
    `updated` DATETIME DEFAULT NOW(),
    `publish` DATETIME NULL DEFAULT NULL,
    `remove` DATETIME NULL DEFAULT NULL,
    `priority` INT DEFAULT 0,
    PRIMARY KEY (`id_page`),
    FOREIGN KEY (`id_page_template`) REFERENCES `core_page_template` (`id_page_template`),
    FOREIGN KEY (`id_page_status`) REFERENCES `core_page_status` (`id_page_status`),
    INDEX (`priority`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `core_related_pages` (
    `id_source` INT NOT NULL,
    `id_target` INT NOT NULL,
    UNIQUE (`id_source`, `id_target`),
    FOREIGN KEY (`id_source`) REFERENCES `core_page` (`id_page`),
    FOREIGN KEY (`id_target`) REFERENCES `core_page` (`id_page`)
);

CREATE TABLE IF NOT EXISTS core_page_localization (
    `id_localized_page` INT NOT NULL AUTO_INCREMENT,
    `id_page` INT NOT NULL,
    `id_language` INT NOT NULL,
    `id_slug` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_localized_page`),
    FOREIGN KEY (`id_page`) REFERENCES `core_page` (`id_page`),
    FOREIGN KEY (`id_language`) REFERENCES `core_language` (`id_language`),
    FOREIGN KEY (`id_slug`) REFERENCES `core_navigation` (`id_slug`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS ext_page_meta (
    `id_page_meta` INT NOT NULL AUTO_INCREMENT,
    `id_localized_page` INT NOT NULL,
    `description` TEXT NOT NULL,
    `keywords` TEXT NOT NULL,
    `og_title` TEXT NOT NULL,
    `og_description` TEXT NOT NULL,
    PRIMARY KEY (`id_page_meta`),
    FOREIGN KEY (`id_localized_page`) REFERENCES `core_page_localization` (`id_localized_page`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS core_ai_page (
    `id_ai_page` INT NOT NULL AUTO_INCREMENT,
    `id_page` INT NOT NULL,
    `prompt` TEXT NOT NULL,
    PRIMARY KEY (`id_ai_page`),
    FOREIGN KEY (`id_page`) REFERENCES `core_page` (`id_page`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS core_external_page (
    `id_external_page` INT NOT NULL AUTO_INCREMENT,
    `id_page` INT NOT NULL,
    `url` VARCHAR(1024) NOT NULL,
    PRIMARY KEY (`id_external_page`),
    FOREIGN KEY (`id_page`) REFERENCES `core_page` (`id_page`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `core_menu_x_pages`;
DROP TABLE IF EXISTS `core_menu`;
CREATE TABLE IF NOT EXISTS `core_menu` (
    `id_menu` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(128) NOT NULL,
    PRIMARY KEY (`id_menu`),
    UNIQUE (`name`)
);

INSERT INTO `core_menu` (id_menu, name)
VALUES (1, 'Header'),
    (2, 'Footer'),
    (3, 'Legal');

CREATE TABLE IF NOT EXISTS `core_menu_x_pages` (
    `id_menu` INT NOT NULL,
    `id_page` INT NOT NULL,
    UNIQUE (`id_menu`, `id_page`),
    FOREIGN KEY (`id_menu`) REFERENCES `core_menu` (`id_menu`),
    FOREIGN KEY (`id_page`) REFERENCES `core_page` (`id_page`)
);



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
VALUES (1, 'Root', '', 'root'),
       (2, 'Anonymous', '', 'anonymous');



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
VALUES (1, 2), # @root -> Root
    (1, 3), # @root -> Admin
    (2, 1); # @anonymous -> Default



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
    `type` INT NOT NULL,
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



DROP TABLE IF EXISTS `docs_contents_x_fragments`;
DROP TABLE IF EXISTS `docs_fragment`;
CREATE TABLE IF NOT EXISTS `docs_fragment` (
    `id_fragment` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `summary` TEXT NOT NULL,
    PRIMARY KEY (`id_fragment`),
    UNIQUE (`name`)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS `docs_content`;
CREATE TABLE IF NOT EXISTS `docs_content` (
    `id_content` INT NOT NULL AUTO_INCREMENT,
    `file` VARCHAR(255) NOT NULL,
    `file_size` INT NOT NULL,
    `last_updated` INT NOT NULL,
    `hash` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_content`),
    UNIQUE (`file`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `docs_contents_x_fragments` (
    `id_content` INT NOT NULL,
    `id_fragment` INT NOT NULL,
    UNIQUE (`id_content`, `id_fragment`),
    FOREIGN KEY (`id_content`) REFERENCES `docs_content` (`id_content`),
    FOREIGN KEY (`id_fragment`) REFERENCES `docs_fragment` (`id_fragment`)
) ENGINE = InnoDB;
