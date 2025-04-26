<?php

namespace components\layout\Grid\description;

use components\layout\Grid\Proxy\Proxy;
use ReflectionClass;

class GridDescription {
    /**
     * @var array<string, static>
     */
    private static array $descriptions = [];

    public static function extract(string $class): ?static {
        if (isset(self::$descriptions[$class])) {
            return self::$descriptions[$class];
        }

        $reflection = new ReflectionClass($class);
        $grids = $reflection->getAttributes(Grid::class);
        if (empty($grids)) {
            return self::$descriptions[$class] = null;
        }

        /** @var Grid $grid */
        $grid = $grids[0]->newInstance();
        $columns = [];

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(GridColumn::class);
            if (empty($attributes)) {
                continue;
            }

            /** @var GridColumn $column */
            $column = $attributes[0]->newInstance();
            $column->bindProperty($property);
            $columns[$property->getName()] = $column;
        }

        return self::$descriptions[$class] = new static($grid->proxy, $columns);
    }



    /**
     * @param array<GridColumn> $columns
     */
    public function __construct(
        public Proxy $proxy,
        public array $columns
    ) {}
}