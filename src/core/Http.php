<?php

namespace core;

use Closure;
use core\endpoints\Endpoint;
use core\endpoints\Handler;
use core\endpoints\Procedure;

class Http {
    public const METHOD_CONNECT = "CONNECT";
    public const METHOD_DELETE = "DELETE";
    public const METHOD_GET = "GET";
    public const METHOD_HEAD = "HEAD";
    public const METHOD_OPTIONS = "OPTIONS";
    public const METHOD_PATCH = "PATCH";
    public const METHOD_POST = "POST";
    public const METHOD_PUT = "PUT";
    public const METHOD_TRACE = "TRACE";



    // RouteChasm headers
    public const HEADER_CONTENT_DESCRIPTION = "Content-Description";
    public const HEADER_PREGMA = "Pregma";
    public const HEADER_X_RESPONSE_TYPE = "X-Response-Type";



    const CORS_ORIGIN = self::HEADER_ACCESS_CONTROL_ALLOW_ORIGIN;
    const CORS_HEADERS = self::HEADER_ACCESS_CONTROL_ALLOW_HEADERS;
    const CORS_METHODS = self::HEADER_ACCESS_CONTROL_ALLOW_METHODS;
    const CORS_CREDENTIALS = self::HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS;



    // from standard https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers#authentication
    public const HEADER_WWW_AUTHENTICATE = "WWW-Authenticate";
    public const HEADER_AUTHORIZATION = "Authorization";
    public const HEADER_PROXY_AUTHENTICATE = "Proxy-Authenticate";
    public const HEADER_PROXY_AUTHORIZATION = "Proxy-Authorization";
    public const HEADER_AGE = "Age";
    public const HEADER_CACHE_CONTROL = "Cache-Control";
    public const HEADER_CLEAR_SITE_DATA = "Clear-Site-Data";
    public const HEADER_EXPIRES = "Expires";
    public const HEADER_NO_VARY_SEARCH = "No-Vary-Search";
    public const HEADER_LAST_MODIFIED = "Last-Modified";
    public const HEADER_ETAG = "ETag";
    public const HEADER_IF_MODIFIED_SINCE = "If-Modified-Since";
    public const HEADER_IF_UNMODIFIED_SINCE = "If-Unmodified-Since";
    public const HEADER_IF_MATCH = "If-Match";
    public const HEADER_IF_NONE_MATCH = "If-None-Match";
    public const HEADER_VARY = "Vary";
    public const HEADER_CONNECTION = "Connection";
    public const HEADER_KEEP_ALIVE = "Keep-Alive";
    public const HEADER_ACCEPT = "Accept";
    public const HEADER_ACCEPT_CHARSET = "Accept-Charset";
    public const HEADER_ACCEPT_ENCODING = "Accept-Encoding";
    public const HEADER_ACCEPT_LANGUAGE = "Accept-Language";
    public const HEADER_ACCEPT_PATCH = "Accept-Patch";
    public const HEADER_PATCH = "PATCH";
    public const HEADER_ACCEPT_POST = "Accept-Post";
    public const HEADER_POST = "POST";
    public const HEADER_EXPECT = "Expect";
    public const HEADER_MAX_FORWARDS = "Max-Forwards";
    public const HEADER_TRACE = "TRACE";
    public const HEADER_COOKIE = "Cookie";
    public const HEADER_SET_COOKIE = "Set-Cookie";
    public const HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS = "Access-Control-Allow-Credentials";
    public const HEADER_ACCESS_CONTROL_ALLOW_HEADERS = "Access-Control-Allow-Headers";
    public const HEADER_ACCESS_CONTROL_ALLOW_METHODS = "Access-Control-Allow-Methods";
    public const HEADER_ACCESS_CONTROL_ALLOW_ORIGIN = "Access-Control-Allow-Origin";
    public const HEADER_ACCESS_CONTROL_EXPOSE_HEADERS = "Access-Control-Expose-Headers";
    public const HEADER_ACCESS_CONTROL_MAX_AGE = "Access-Control-Max-Age";
    public const HEADER_ACCESS_CONTROL_REQUEST_HEADERS = "Access-Control-Request-Headers";
    public const HEADER_ACCESS_CONTROL_REQUEST_METHOD = "Access-Control-Request-Method";
    public const HEADER_ORIGIN = "Origin";
    public const HEADER_TIMING_ALLOW_ORIGIN = "Timing-Allow-Origin";
    public const HEADER_CONTENT_DISPOSITION = "Content-Disposition";
    public const HEADER_CONTENT_DIGEST = "Content-Digest";
    public const HEADER_CONTENT_ENCODING = "Content-Encoding";
    public const HEADER_CONTENT_RANGE = "Content-Range";
    public const HEADER_DIGEST = "Digest";
    public const HEADER_REPR_DIGEST = "Repr-Digest";
    public const HEADER_WANT_CONTENT_DIGEST = "Want-Content-Digest";
    public const HEADER_WANT_REPR_DIGEST = "Want-Repr-Digest";
    public const HEADER_WANT_DIGEST = "Want-Digest";
    public const HEADER_CONTENT_LENGTH = "Content-Length";
    public const HEADER_CONTENT_TYPE = "Content-Type";
    public const HEADER_CONTENT_LANGUAGE = "Content-Language";
    public const HEADER_CONTENT_LOCATION = "Content-Location";
    public const HEADER_FORWARDED = "Forwarded";
    public const HEADER_VIA = "Via";
    public const HEADER_ACCEPT_RANGES = "Accept-Ranges";
    public const HEADER_RANGE = "Range";
    public const HEADER_IF_RANGE = "If-Range";
    public const HEADER_LOCATION = "Location";
    public const HEADER_REFRESH = "Refresh";
    public const HEADER_FROM = "From";
    public const HEADER_HOST = "Host";
    public const HEADER_REFERER = "Referer";
    public const HEADER_REFERRER_POLICY = "Referrer-Policy";
    public const HEADER_USER_AGENT = "User-Agent";
    public const HEADER_ALLOW = "Allow";
    public const HEADER_SERVER = "Server";
    public const HEADER_CROSS_ORIGIN_EMBEDDER_POLICY = "Cross-Origin-Embedder-Policy";
    public const HEADER_CROSS_ORIGIN_OPENER_POLICY = "Cross-Origin-Opener-Policy";
    public const HEADER_CROSS_ORIGIN_RESOURCE_POLICY = "Cross-Origin-Resource-Policy";
    public const HEADER_CONTENT_SECURITY_POLICY = "Content-Security-Policy";
    public const HEADER_CONTENT_SECURITY_POLICY_REPORT_ONLY = "Content-Security-Policy-Report-Only";
    public const HEADER_EXPECT_CT = "Expect-CT";
    public const HEADER_PERMISSIONS_POLICY = "Permissions-Policy";
    public const HEADER_REPORTING_ENDPOINTS = "Reporting-Endpoints";
    public const HEADER_STRICT_TRANSPORT_SECURITY = "Strict-Transport-Security";
    public const HEADER_UPGRADE_INSECURE_REQUESTS = "Upgrade-Insecure-Requests";
    public const HEADER_X_CONTENT_TYPE_OPTIONS = "X-Content-Type-Options";
    public const HEADER_X_FRAME_OPTIONS = "X-Frame-Options";
    public const HEADER_X_PERMITTED_CROSS_DOMAIN_POLICIES = "X-Permitted-Cross-Domain-Policies";
    public const HEADER_X_POWERED_BY = "X-Powered-By";
    public const HEADER_X_XSS_PROTECTION = "X-XSS-Protection";
    public const HEADER_SEC_FETCH_SITE = "Sec-Fetch-Site";
    public const HEADER_SEC_FETCH_MODE = "Sec-Fetch-Mode";
    public const HEADER_SEC_FETCH_USER = "Sec-Fetch-User";
    public const HEADER_SEC_FETCH_DEST = "Sec-Fetch-Dest";
    public const HEADER_SEC_PURPOSE = "Sec-Purpose";
    public const HEADER_SERVICE_WORKER_NAVIGATION_PRELOAD = "Service-Worker-Navigation-Preload";
    public const HEADER_REPORT_TO = "Report-To";
    public const HEADER_TRANSFER_ENCODING = "Transfer-Encoding";
    public const HEADER_TE = "TE";
    public const HEADER_TRAILER = "Trailer";
    public const HEADER_SEC_WEBSOCKET_ACCEPT = "Sec-WebSocket-Accept";
    public const HEADER_ALT_SVC = "Alt-Svc";
    public const HEADER_ALT_USED = "Alt-Used";
    public const HEADER_DATE = "Date";
    public const HEADER_LINK = "Link";
    public const HEADER_RETRY_AFTER = "Retry-After";
    public const HEADER_SERVER_TIMING = "Server-Timing";
    public const HEADER_SERVICE_WORKER_ALLOWED = "Service-Worker-Allowed";
    public const HEADER_SOURCEMAP = "SourceMap";
    public const HEADER_UPGRADE = "Upgrade";
    public const HEADER_PRIORITY = "Priority";
    public const HEADER_ATTRIBUTION_REPORTING_ELIGIBLE = "Attribution-Reporting-Eligible";
    public const HEADER_ATTRIBUTION_REPORTING_REGISTER_SOURCE = "Attribution-Reporting-Register-Source";
    public const HEADER_ATTRIBUTION_REPORTING_REGISTER_TRIGGER = "Attribution-Reporting-Register-Trigger";
    public const HEADER_ACCEPT_CH = "Accept-CH";
    public const HEADER_HTTP_EQUIV = "http-equiv";
    public const HEADER_CRITICAL_CH = "Critical-CH";
    public const HEADER_SEC_CH_UA = "Sec-CH-UA";
    public const HEADER_SEC_CH_UA_ARCH = "Sec-CH-UA-Arch";
    public const HEADER_SEC_CH_UA_BITNESS = "Sec-CH-UA-Bitness";
    public const HEADER_SEC_CH_UA_FULL_VERSION_LIST = "Sec-CH-UA-Full-Version-List";
    public const HEADER_SEC_CH_UA_MOBILE = "Sec-CH-UA-Mobile";
    public const HEADER_SEC_CH_UA_MODEL = "Sec-CH-UA-Model";
    public const HEADER_SEC_CH_UA_PLATFORM = "Sec-CH-UA-Platform";
    public const HEADER_SEC_CH_UA_PLATFORM_VERSION = "Sec-CH-UA-Platform-Version";
    public const HEADER_SEC_CH_UA_WOW64 = "Sec-CH-UA-WoW64";
    public const HEADER_SEC_CH_PREFERS_COLOR_SCHEME = "Sec-CH-Prefers-Color-Scheme";
    public const HEADER_SEC_CH_PREFERS_REDUCED_MOTION = "Sec-CH-Prefers-Reduced-Motion";
    public const HEADER_SEC_CH_PREFERS_REDUCED_TRANSPARENCY = "Sec-CH-Prefers-Reduced-Transparency";
    public const HEADER_CONTENT_DPR = "Content-DPR";
    public const HEADER_DPR = "DPR";
    public const HEADER_DEVICE_MEMORY = "Device-Memory";
    public const HEADER_VIEWPORT_WIDTH = "Viewport-Width";
    public const HEADER_WIDTH = "Width";
    public const HEADER_DOWNLINK = "Downlink";
    public const HEADER_ECT = "ECT";
    public const HEADER_RTT = "RTT";
    public const HEADER_SAVE_DATA = "Save-Data";
    public const HEADER_DNT = "DNT";
    public const HEADER_SEC_GPC = "Sec-GPC";
    public const HEADER_ORIGIN_ISOLATION = "Origin-Isolation";
    public const HEADER_NEL = "NEL";
    public const HEADER_OBSERVE_BROWSING_TOPICS = "Observe-Browsing-Topics";
    public const HEADER_SEC_BROWSING_TOPICS = "Sec-Browsing-Topics";
    public const HEADER_ACCEPT_PUSH_POLICY = "Accept-Push-Policy";
    public const HEADER_ACCEPT_SIGNATURE = "Accept-Signature";
    public const HEADER_EARLY_DATA = "Early-Data";
    public const HEADER_ORIGIN_AGENT_CLUSTER = "Origin-Agent-Cluster";
    public const HEADER_DOCUMENT = "Document";
    public const HEADER_PUSH_POLICY = "Push-Policy";
    public const HEADER_SET_LOGIN = "Set-Login";
    public const HEADER_SIGNATURE = "Signature";
    public const HEADER_SIGNED_HEADERS = "Signed-Headers";
    public const HEADER_SPECULATION_RULES = "Speculation-Rules";
    public const HEADER_SUPPORTS_LOADING_MODE = "Supports-Loading-Mode";
    public const HEADER_X_FORWARDED_FOR = "X-Forwarded-For";
    public const HEADER_X_FORWARDED_HOST = "X-Forwarded-Host";
    public const HEADER_X_FORWARDED_PROTO = "X-Forwarded-Proto";
    public const HEADER_X_DNS_PREFETCH_CONTROL = "X-DNS-Prefetch-Control";
    public const HEADER_X_ROBOTS_TAG = "X-Robots-Tag";



    /**
     * @param array<Endpoint|Closure> $endpoints
     * @return array<Endpoint>
     */
    protected static function createHandles(array &$endpoints): array {
        foreach ($endpoints as $i => $endpoint) {
            if ($endpoint instanceof Closure) {
                $endpoints[$i] = new Procedure($endpoint);
            }
        }

        return $endpoints;
    }

    public static function connect(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler(self::METHOD_CONNECT))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function delete(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler(self::METHOD_DELETE))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function get(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler(self::METHOD_GET))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function head(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler(self::METHOD_HEAD))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function options(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler(self::METHOD_OPTIONS))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function patch(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler(self::METHOD_PATCH))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function post(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler(self::METHOD_POST))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function put(Endpoint|Closure ...$endpoints): Handler {
        return (new Handler(self::METHOD_PUT))
            ->setHandles(self::createHandles($endpoints));
    }

    public static function trace(Endpoint|Closure $enpoint, Endpoint|Closure ...$endpoints): Handler {
        return (new Handler(self::METHOD_TRACE))
            ->setHandles(self::createHandles($endpoints));
    }
}