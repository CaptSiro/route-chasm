<?php

namespace core;

trait InstanceCounter {
    protected static int $instances = 0;

    protected static function createInstanceId(): int {
        return self::$instances++;
    }



    protected int $instanceId;

    public function getInstanceId(): int {
        return $this->instanceId;
    }
}