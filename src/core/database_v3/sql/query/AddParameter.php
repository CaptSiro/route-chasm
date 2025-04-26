<?php

namespace core\database_v3\sql\query;

trait AddParameter {
    private ParameterAccess $access = ParameterAccess::POSITION;

    public function setParameterAccess(ParameterAccess $access): void {
        $this->access = $access;
    }

    protected function addParameter(string $name, Parameter $parameter, array &$parameters): string {
        if ($this->access === ParameterAccess::NAME) {
            $parameters[$name] = $parameter;
            return ':'. $name;
        }

        $parameters[] = $parameter;
        return '?';
    }
}