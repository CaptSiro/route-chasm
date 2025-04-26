<?php

namespace core\database_v3\sql\query;

use core\database_v3\sql\query\clause\Limit;
use core\database_v3\sql\query\clause\Where;
use core\utils\Arrays;
use Exception;

class DeleteQuery implements SqlQuery {
    use AddParameter, Where, Limit;



    public function __construct(
        protected string $table
    ) {}



    public function toQuery(): Query {
        if (empty($this->where)) {
            throw new Exception("DELETE query without WHERE clause is dangerous. Add WHERE clause and preferably LIMIT clause too");
        }

        $this->setParameterAccess(Query::getParameterAccess(Arrays::first($this->where)->condition));

        $parameters = [];
        $sql = "DELETE FROM `$this->table`";

        $this->addWhere($sql, $parameters);
        $this->addLimit($sql, $parameters);

        return new Query($sql, $parameters);
    }
}