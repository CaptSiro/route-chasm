<?php

namespace core\database\query;

use core\database\buffer\Buffer;
use core\database\buffer\ParamBuffer;
use core\database\buffer\QueryBuffer;

class QueryBuilder {
    protected array $temp;
    protected Buffer $params;



    public function __construct() {
        $this->temp = ParamBuffer::getInstance()
            ->dump();
        $this->params = new QueryBuffer();
    }



    public function use(string $query): Query {
        $this->params->load(
            ParamBuffer::getInstance()
                ->dump()
        );

        ParamBuffer::getInstance()
            ->load($this->temp);

        return new Query(
            $query,
            $this->params
        );
    }
}