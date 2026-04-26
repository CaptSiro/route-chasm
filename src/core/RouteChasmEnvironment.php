<?php

namespace core;

class RouteChasmEnvironment {
    /**
     * Use bit-or | to add more items to the list
     */
    public const ERROR_SEVERITY_BLACKLIST = E_USER_NOTICE;

    public const CHAR_INFINITY = '∞';

    public const FILE_SYSTEM_HASH_ALGORITHM = 'sha256';
    public const FILE_SYSTEM_NAMESPACE = 'fs';
    public const FILE_SYSTEM_DIRECTORY_HASH_OFFSET = 2;

    public const SRC = __DIR__ .'/../';
    public const FILE_ENV = __DIR__ ."/../../.env";
    public const DIRECTORY_DATA = __DIR__ . '/../../data';

    public const MOUNT_DEFAULT_CONTEXT = '';
    public const MOUNT_FILE_SERVER = 'fs';

    public const QUERY_SEARCH = 'q';
    public const QUERY_EXECUTE = 'x';
    public const QUERY_REQUEST_FORMAT = 'i';
    public const QUERY_REQUEST_FORMAT_LONG = 'in';
    public const QUERY_RESPONSE_FORMAT = 'o';
    public const QUERY_RESPONSE_FORMAT_LONG = 'out';
    public const QUERY_LANGUAGE = 'l';
    public const QUERY_LANGUAGE_LONG = 'language';
    public const QUERY_LANGUAGE_ID = 'language-id';
    public const QUERY_LOGOUT = 'logout';
    public const QUERY_PAGE_PARENT = 'parent';
    public const QUERY_PAGE = 'page';
    /**
     * If <code>QUERY_SIDELOADER_FORCE</code> is present in url query the default response format checking is ignored and
     * <code>HEADER_X_REQUIRE</code> will always be set on response
     */
    public const QUERY_SIDELOADER_FORCE = 'f';
    public const QUERY_PORTION = 'p';
    public const QUERY_GRID_PORTION = self::QUERY_PORTION;
    public const QUERY_FS_VARIANT = 'v';
    public const QUERY_FS_FILE_TYPE = 'file-type';
    public const QUERY_FILE_SYSTEM_DIRECTORY = 'directory';

    public const TRANSITIVE_QUERIES = [
        self::QUERY_LANGUAGE,
        self::QUERY_LANGUAGE_LONG,
    ];

    public const ENV_PROJECT = "PROJECT";
    public const ENV_PROJECT_LINK = "PROJECT_LINK";
    public const ENV_PROJECT_AUTHOR = "PROJECT_AUTHOR";
    public const ENV_PROJECT_AUTHOR_LINK = "PROJECT_AUTHOR_LINK";
    public const ENV_LANGUAGE = "LANGUAGE";
    public const ENV_DOMAIN_URL = "DOMAIN_URL";
    public const ENV_DATABASE_HOST = "DATABASE_HOST";
    public const ENV_DATABASE_NAME = "DATABASE_NAME";
    public const ENV_DATABASE_USER = "DATABASE_USER";
    public const ENV_DATABASE_PASSWORD = "DATABASE_PASSWORD";
    public const ENV_DATABASE_PORT = "DATABASE_PORT";
    public const ENV_DATABASE_CHARSET = "DATABASE_CHARSET";
    public const ENV_ADMIN_LOGIN_PASSWORD = "ADMIN_LOGIN_PASSWORD";

    public const USER_RESOURCE_PAGE = 'Pages';
    public const USER_RESOURCE_FILE_SYSTEM = 'File System';
    public const USER_RESOURCE_LOCALIZATION = 'Localization';
    public const USER_RESOURCE_DOMAIN = 'Domains';
    public const USER_RESOURCE_SYSTEM = 'System';
    public const USER_RESOURCE_DOCS = 'Docs';
    public const USER_RESOURCE_DOCS_ADMIN = 'Docs (Administration)';

    public const SETTING_DROPDOWN_MAX_ENTRIES = 'route-chasm-docs:search_dropdown_max_entries';
    public const SETTING_ENV_PASSWORD = 'route-chasm-core:use_env_password_method';
    public const SETTING_MIN_SEARCH_QUERY_LENGTH = 'route-chasm-core:search_minimum_query_length';
    public const SETTING_SHOW_ADMIN_LOGIN_IN_FOOTER = "route-chasm-core:show_admin_login_in_footer";

    public const SEARCH_MIN_LENGTH = 3;
    public const SEARCH_DROPDOWN_MAX_ENTRIES = 5;
    public const GRID_DEFAULT_PORTION_SIZE = 20;
    public const LISTING_PORTION_SIZE = 21;
    public const ID_DIGITS = 4;

    public const BREAD_CRUMBS_DELIMITOR = null;

    public const LIMIT_LAST_UPDATED = 10;
}