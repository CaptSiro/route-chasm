<?php

namespace core;

class RouteChasmEnvironment {
    public const ENV_FILE = __DIR__ ."/../../.env";

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

    public const PROJECT = "PROJECT";
    public const PROJECT_AUTHOR = "PROJECT_AUTHOR";
    public const PROJECT_AUTHOR_LINK = "PROJECT_AUTHOR_LINK";

    public const ENV_LANGUAGE = "LANGUAGE";
    public const ENV_DOMAIN_URL = "DOMAIN_URL";
    public const ENV_DATABASE_HOST = "DATABASE_HOST";
    public const ENV_DATABASE_NAME = "DATABASE_NAME";
    public const ENV_DATABASE_USER = "DATABASE_USER";
    public const ENV_DATABASE_PASSWORD = "DATABASE_PASSWORD";
    public const ENV_DATABASE_PORT = "DATABASE_PORT";
    public const ENV_DATABASE_CHARSET = "DATABASE_CHARSET";
    public const ENV_ADMIN_LOGIN_PASSWORD = "ADMIN_LOGIN_PASSWORD";
}