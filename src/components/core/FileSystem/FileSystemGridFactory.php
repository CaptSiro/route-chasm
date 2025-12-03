<?php

namespace components\core\FileSystem;

use components\layout\Grid\Grid;
use components\layout\Grid\GridLayout;
use components\layout\Grid\GridLayoutFactory;
use components\layout\Grid\Loader\GridLoader;
use components\layout\Grid\Proxy\Proxy;

class FileSystemGridFactory implements GridLayoutFactory {
    public function __construct(
        protected FileSystemDropArea $dropArea,
        protected array $columns,
        protected ?Proxy $proxy = null,
        protected ?GridLoader $loader = null,
        protected ?string $namespace = null,
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

        $grid = new FileSystemGrid(
            new Grid($this->namespace, proxy: $proxy),
            $this->dropArea
        );

        return $grid->addAll($this->columns);
    }
}