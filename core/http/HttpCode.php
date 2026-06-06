<?php

namespace core\http;

/**
 * Follows convention of <code>type_message</code> where <code>type</code> is only first letters of words that would
 * describe the type
 */
class HttpCode {
    // Informational
    public const I_CONTINUE = 100;
    public const I_SWITCHING_PROTOCOLS = 101;

    // Successful
    public const S_OK = 200;
    public const S_CREATED = 201;
    public const S_ACCEPTED = 202;
    public const S_NON_AUTHORITATIVE_INFORMATION = 203;
    public const S_NO_CONTENT = 204;
    public const S_RESET_CONTENT = 205;
    public const S_PARTIAL_CONTENT = 206;

    // Redirection
    public const R_MULTIPLE_CHOICES = 300;
    public const R_MOVED_PERMANENTLY = 301;
    public const R_FOUND = 302;
    public const R_SEE_OTHER = 303;
    public const R_NOT_MODIFIED = 304;
    public const R_USE_PROXY = 305;

    // Client Error
    public const CE_BAD_REQUEST = 400;
    public const CE_UNAUTHORIZED = 401;
    public const CE_PAYMENT_REQUIRED = 402;
    public const CE_FORBIDDEN = 403;
    public const CE_NOT_FOUND = 404;
    public const CE_METHOD_NOT_ALLOWED = 405;
    public const CE_NOT_ACCEPTABLE = 406;
    public const CE_PROXY_AUTHENTICATION_REQUIRED = 407;
    public const CE_REQUEST_TIMEOUT = 408;
    public const CE_CONFLICT = 409;
    public const CE_GONE = 410;
    public const CE_LENGTH_REQUIRED = 411;
    public const CE_PRECONDITION_FAILED = 412;
    public const CE_PAYLOAD_TOO_LARGE = 413;
    public const CE_URI_TOO_LONG = 414;
    public const CE_UNSUPPORTED_MEDIA_TYPE = 415;
    public const CE_IM_A_TEAPOT = 418;

    // Server Error
    public const SE_INTERNAL_SERVER_ERROR = 500;
    public const SE_NOT_IMPLEMENTED = 501;
    public const SE_BAD_GATEWAY = 502;
    public const SE_SERVICE_UNAVAILABLE = 503;
    public const SE_GATEWAY_TIMEOUT = 504;
    public const SE_HTTP_VERSION_NOT_SUPPORTED = 505;
}