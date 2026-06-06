<?php

namespace components\layout\Grid\description;

use components\layout\Grid\description\Grid as GridAttribute;
use components\layout\Grid\Grid;
use components\layout\Grid\GridLayout;
use components\layout\Grid\GridLayoutFactory;
use components\layout\Grid\Loader\GridLoader;
use components\layout\Grid\Proxy\Proxy;
use ReflectionClass;

class GridDescription implements GridLayoutFactory {
    /**
     * @var array<string, static>
     */
    private static array $descriptions = [];

    public static function extract(string $class): ?static {
        if (isset(self::$descriptions[$class])) {
            return self::$descriptions[$class];
        }

        $reflection = new ReflectionClass($class);
        $grids = $reflection->getAttributes(GridAttribute::class);
        if (empty($grids)) {
            return self::$descriptions[$class] = null;
        }

        /** @var GridAttribute $grid */
        $grid = $grids[0]->newInstance();
        $grid->bindClass($reflection);
        $columns = [];

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(GridColumn::class);
            if (empty($attributes)) {
                continue;
            }

            /** @var GridColumn $column */
            $column = $attributes[0]->newInstance();
            $column->bindProperty($property);

            if ($column->isFirst()) {
                $columns = [$property->getName() => $column] + $columns;
            } else {
                $columns[$property->getName()] = $column;
            }
        }

        return self::$descriptions[$class] = new static(
            $columns,
            $grid->getLoader(),
            $grid->getNamespace(),
            proxy: $grid->getProxy(),
        );
    }



    /**
     * @param array<string, GridColumn> $columns
     */
    public function __construct(
        protected array $columns,
        protected GridLoader $loader,
        protected ?string $namespace = null,
        protected ?Proxy $proxy = null,
    ) {}



    public function getProxy(): ?Proxy {
        return $this->proxy;
    }

    public function setProxy(?Proxy $proxy): static {
        $this->proxy = $proxy;
        return $this;
    }

    public function getColumns(): array {
        return $this->columns;
    }

    public function addColumn(string $property, GridColumn $column): static {
        $this->columns[$property] = $column;
        return $this;
    }

    public function setColumns(array $columns): static {
        $this->columns = $columns;
        return $this;
    }

    public function getLoader(): ?GridLoader {
        return $this->loader;
    }

    public function setLoader(?GridLoader $loader): static {
        $this->loader = $loader;
        return $this;
    }

    public function createGrid(Proxy $proxy): ?GridLayout {
        if (empty($this->columns)) {
            return null;
        }

        $table = new Grid($this->namespace, proxy: $proxy);
        return $table->addAll($this->columns);
    }
}