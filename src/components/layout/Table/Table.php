<?php

namespace components\layout\Table;

use components\layout\Table\Proxy\Proxy;
use components\layout\Table\Proxy\TypeProxy;
use core\Flags;
use core\view\Renderer;
use core\view\View;

class Table implements View {
    use Renderer;
    use Flags;

    public const FLAG_SHOW_HEADER = 1;



    protected array $columns = [];
    protected array $rows = [];

    public function __construct(
        protected Proxy $proxy = new TypeProxy()
    ) {
        $this->setFlag(self::FLAG_SHOW_HEADER);
    }



    public function add(string $label, string $columnName): static {
        $this->columns[$columnName] = $label;
        return $this;
    }

    public function addAsFirst(string $label, string $columnName): static {
        $this->columns = [$columnName => $label] + $this->columns;
        return $this;
    }

    /**
     * @param array<string, string> $layout
     * @return $this
     */
    public function addAll(array $layout): static {
        foreach ($layout as $label => $name) {
            $this->columns[$name] = $label;
        }
        return $this;
    }

    public function load(array $rows): static {
        $this->rows = array_merge($rows, $this->rows);
        return $this;
    }
}