<?php

namespace core\database\query;

use core\database\buffer\Buffer;
use core\database\buffer\EmptyBuffer;

class Query {
    public static function build(): QueryBuilder {
        return new QueryBuilder();
    }

    public static function from(string $base, string|Query|null $additional): self|string {
        if (is_null($additional)) {
            return new Query($base, new EmptyBuffer());
        }

        $base .= " WHERE ";

        if ($additional instanceof Query) {
            return new Query($base . $additional->getLiteral(), $additional->getParams());
        }

        return $base . $additional;
    }



    public function __construct(
        protected string $literal,
        protected Buffer $params
    ) {}



    /**
     * @return string
     */
    public function getLiteral(): string {
        return $this->literal;
    }

    /**
     * @return Buffer
     */
    public function getParams(): Buffer {
        return $this->params;
    }
}