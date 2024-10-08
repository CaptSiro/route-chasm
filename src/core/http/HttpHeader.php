<?php

namespace core\http;

class HttpHeader {
    // RouteChasm headers
    public const CONTENT_DESCRIPTION = "Content-Description";
    public const PREGMA = "Pregma";
    public const X_RESPONSE_TYPE = "X-Response-Type";



    // from standard https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers#authentication
    public const WWW_AUTHENTICATE = "WWW-Authenticate";
    public const AUTHORIZATION = "Authorization";
    public const PROXY_AUTHENTICATE = "Proxy-Authenticate";
    public const PROXY_AUTHORIZATION = "Proxy-Authorization";
    public const AGE = "Age";
    public const CACHE_CONTROL = "Cache-Control";
    public const CLEAR_SITE_DATA = "Clear-Site-Data";
    public const EXPIRES = "Expires";
    public const NO_VARY_SEARCH = "No-Vary-Search";
    public const LAST_MODIFIED = "Last-Modified";
    public const ETAG = "ETag";
    public const IF_MODIFIED_SINCE = "If-Modified-Since";
    public const IF_UNMODIFIED_SINCE = "If-Unmodified-Since";
    public const IF_MATCH = "If-Match";
    public const IF_NONE_MATCH = "If-None-Match";
    public const VARY = "Vary";
    public const CONNECTION = "Connection";
    public const KEEP_ALIVE = "Keep-Alive";
    public const ACCEPT = "Accept";
    public const ACCEPT_CHARSET = "Accept-Charset";
    public const ACCEPT_ENCODING = "Accept-Encoding";
    public const ACCEPT_LANGUAGE = "Accept-Language";
    public const ACCEPT_PATCH = "Accept-Patch";
    public const PATCH = "PATCH";
    public const ACCEPT_POST = "Accept-Post";
    public const POST = "POST";
    public const EXPECT = "Expect";
    public const MAX_FORWARDS = "Max-Forwards";
    public const TRACE = "TRACE";
    public const COOKIE = "Cookie";
    public const SET_COOKIE = "Set-Cookie";
    public const ACCESS_CONTROL_ALLOW_CREDENTIALS = "Access-Control-Allow-Credentials";
    public const ACCESS_CONTROL_ALLOW_HEADERS = "Access-Control-Allow-Headers";
    public const ACCESS_CONTROL_ALLOW_METHODS = "Access-Control-Allow-Methods";
    public const ACCESS_CONTROL_ALLOW_ORIGIN = "Access-Control-Allow-Origin";
    public const ACCESS_CONTROL_EXPOSE_HEADERS = "Access-Control-Expose-Headers";
    public const ACCESS_CONTROL_MAX_AGE = "Access-Control-Max-Age";
    public const ACCESS_CONTROL_REQUEST_HEADERS = "Access-Control-Request-Headers";
    public const ACCESS_CONTROL_REQUEST_METHOD = "Access-Control-Request-Method";
    public const ORIGIN = "Origin";
    public const TIMING_ALLOW_ORIGIN = "Timing-Allow-Origin";
    public const CONTENT_DISPOSITION = "Content-Disposition";
    public const CONTENT_DIGEST = "Content-Digest";
    public const CONTENT_ENCODING = "Content-Encoding";
    public const CONTENT_RANGE = "Content-Range";
    public const DIGEST = "Digest";
    public const REPR_DIGEST = "Repr-Digest";
    public const WANT_CONTENT_DIGEST = "Want-Content-Digest";
    public const WANT_REPR_DIGEST = "Want-Repr-Digest";
    public const WANT_DIGEST = "Want-Digest";
    public const CONTENT_LENGTH = "Content-Length";
    public const CONTENT_TYPE = "Content-Type";
    public const CONTENT_LANGUAGE = "Content-Language";
    public const CONTENT_LOCATION = "Content-Location";
    public const FORWARDED = "Forwarded";
    public const VIA = "Via";
    public const ACCEPT_RANGES = "Accept-Ranges";
    public const RANGE = "Range";
    public const IF_RANGE = "If-Range";
    public const LOCATION = "Location";
    public const REFRESH = "Refresh";
    public const FROM = "From";
    public const HOST = "Host";
    public const REFERER = "Referer";
    public const REFERRER_POLICY = "Referrer-Policy";
    public const USER_AGENT = "User-Agent";
    public const ALLOW = "Allow";
    public const SERVER = "Server";
    public const CROSS_ORIGIN_EMBEDDER_POLICY = "Cross-Origin-Embedder-Policy";
    public const CROSS_ORIGIN_OPENER_POLICY = "Cross-Origin-Opener-Policy";
    public const CROSS_ORIGIN_RESOURCE_POLICY = "Cross-Origin-Resource-Policy";
    public const CONTENT_SECURITY_POLICY = "Content-Security-Policy";
    public const CONTENT_SECURITY_POLICY_REPORT_ONLY = "Content-Security-Policy-Report-Only";
    public const EXPECT_CT = "Expect-CT";
    public const PERMISSIONS_POLICY = "Permissions-Policy";
    public const REPORTING_ENDPOINTS = "Reporting-Endpoints";
    public const STRICT_TRANSPORT_SECURITY = "Strict-Transport-Security";
    public const UPGRADE_INSECURE_REQUESTS = "Upgrade-Insecure-Requests";
    public const X_CONTENT_TYPE_OPTIONS = "X-Content-Type-Options";
    public const X_FRAME_OPTIONS = "X-Frame-Options";
    public const X_PERMITTED_CROSS_DOMAIN_POLICIES = "X-Permitted-Cross-Domain-Policies";
    public const X_POWERED_BY = "X-Powered-By";
    public const X_XSS_PROTECTION = "X-XSS-Protection";
    public const SEC_FETCH_SITE = "Sec-Fetch-Site";
    public const SEC_FETCH_MODE = "Sec-Fetch-Mode";
    public const SEC_FETCH_USER = "Sec-Fetch-User";
    public const SEC_FETCH_DEST = "Sec-Fetch-Dest";
    public const SEC_PURPOSE = "Sec-Purpose";
    public const SERVICE_WORKER_NAVIGATION_PRELOAD = "Service-Worker-Navigation-Preload";
    public const REPORT_TO = "Report-To";
    public const TRANSFER_ENCODING = "Transfer-Encoding";
    public const TE = "TE";
    public const TRAILER = "Trailer";
    public const SEC_WEBSOCKET_ACCEPT = "Sec-WebSocket-Accept";
    public const ALT_SVC = "Alt-Svc";
    public const ALT_USED = "Alt-Used";
    public const DATE = "Date";
    public const LINK = "Link";
    public const RETRY_AFTER = "Retry-After";
    public const SERVER_TIMING = "Server-Timing";
    public const SERVICE_WORKER_ALLOWED = "Service-Worker-Allowed";
    public const SOURCEMAP = "SourceMap";
    public const UPGRADE = "Upgrade";
    public const PRIORITY = "Priority";
    public const ATTRIBUTION_REPORTING_ELIGIBLE = "Attribution-Reporting-Eligible";
    public const ATTRIBUTION_REPORTING_REGISTER_SOURCE = "Attribution-Reporting-Register-Source";
    public const ATTRIBUTION_REPORTING_REGISTER_TRIGGER = "Attribution-Reporting-Register-Trigger";
    public const ACCEPT_CH = "Accept-CH";
    public const HTTP_EQUIV = "http-equiv";
    public const CRITICAL_CH = "Critical-CH";
    public const SEC_CH_UA = "Sec-CH-UA";
    public const SEC_CH_UA_ARCH = "Sec-CH-UA-Arch";
    public const SEC_CH_UA_BITNESS = "Sec-CH-UA-Bitness";
    public const SEC_CH_UA_FULL_VERSION_LIST = "Sec-CH-UA-Full-Version-List";
    public const SEC_CH_UA_MOBILE = "Sec-CH-UA-Mobile";
    public const SEC_CH_UA_MODEL = "Sec-CH-UA-Model";
    public const SEC_CH_UA_PLATFORM = "Sec-CH-UA-Platform";
    public const SEC_CH_UA_PLATFORM_VERSION = "Sec-CH-UA-Platform-Version";
    public const SEC_CH_UA_WOW64 = "Sec-CH-UA-WoW64";
    public const SEC_CH_PREFERS_COLOR_SCHEME = "Sec-CH-Prefers-Color-Scheme";
    public const SEC_CH_PREFERS_REDUCED_MOTION = "Sec-CH-Prefers-Reduced-Motion";
    public const SEC_CH_PREFERS_REDUCED_TRANSPARENCY = "Sec-CH-Prefers-Reduced-Transparency";
    public const CONTENT_DPR = "Content-DPR";
    public const DPR = "DPR";
    public const DEVICE_MEMORY = "Device-Memory";
    public const VIEWPORT_WIDTH = "Viewport-Width";
    public const WIDTH = "Width";
    public const DOWNLINK = "Downlink";
    public const ECT = "ECT";
    public const RTT = "RTT";
    public const SAVE_DATA = "Save-Data";
    public const DNT = "DNT";
    public const SEC_GPC = "Sec-GPC";
    public const ORIGIN_ISOLATION = "Origin-Isolation";
    public const NEL = "NEL";
    public const OBSERVE_BROWSING_TOPICS = "Observe-Browsing-Topics";
    public const SEC_BROWSING_TOPICS = "Sec-Browsing-Topics";
    public const ACCEPT_PUSH_POLICY = "Accept-Push-Policy";
    public const ACCEPT_SIGNATURE = "Accept-Signature";
    public const EARLY_DATA = "Early-Data";
    public const ORIGIN_AGENT_CLUSTER = "Origin-Agent-Cluster";
    public const DOCUMENT = "Document";
    public const PUSH_POLICY = "Push-Policy";
    public const SET_LOGIN = "Set-Login";
    public const SIGNATURE = "Signature";
    public const SIGNED_HEADERS = "Signed-Headers";
    public const SPECULATION_RULES = "Speculation-Rules";
    public const SUPPORTS_LOADING_MODE = "Supports-Loading-Mode";
    public const X_FORWARDED_FOR = "X-Forwarded-For";
    public const X_FORWARDED_HOST = "X-Forwarded-Host";
    public const X_FORWARDED_PROTO = "X-Forwarded-Proto";
    public const X_DNS_PREFETCH_CONTROL = "X-DNS-Prefetch-Control";
    public const X_ROBOTS_TAG = "X-Robots-Tag";
}