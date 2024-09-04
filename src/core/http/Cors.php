<?php

namespace core\http;

class Cors {
    const ORIGIN = HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN;
    const HEADERS = HttpHeader::ACCESS_CONTROL_ALLOW_HEADERS;
    const METHODS = HttpHeader::ACCESS_CONTROL_ALLOW_METHODS;
    const CREDENTIALS = HttpHeader::ACCESS_CONTROL_ALLOW_CREDENTIALS;
}