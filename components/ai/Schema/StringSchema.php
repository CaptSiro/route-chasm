<?php

namespace components\ai\Schema;

use core\view\JsonStructure;

class StringSchema extends JsonStructure {
    use Description, Nullable;

    public const FORMAT_DATETIME = 'date-time';
    public const FORMAT_TIME = 'time';
    public const FORMAT_DATE = 'date';
    public const FORMAT_DURATION = 'duration';
    public const FORMAT_EMAIL = 'email';
    public const FORMAT_HOSTNAME = 'hostname';
    public const FORMAT_IPV4 = 'ipv4';
    public const FORMAT_IPV6 = 'ipv6';
    public const FORMAT_UUID = 'uuid';



    public function __construct() {
        parent::__construct([
            'type' => 'string',
        ]);
    }



    public function setPattern(string $pattern): static {
        $this->set('pattern', $pattern);
        return $this;
    }

    public function setFormat(string $format): static {
        $this->set('format', $format);
        return $this;
    }
}