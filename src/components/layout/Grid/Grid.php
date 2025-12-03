<?php

namespace components\layout\Grid;

use components\layout\Grid\description\GridColumn;
use components\layout\Grid\Proxy\Proxy;
use components\layout\Grid\Proxy\TypeProxy;
use core\Flags;
use core\view\Renderer;
use core\view\View;

class Grid implements View, GridLayout {
    use Renderer, Flags;

    public const FLAG_SHOW_HEADER = 1;



    /**
     * @var array<string, GridColumn>
     */
    protected array $columns = [];
    protected array $rows = [];

    public function __construct(
        protected ?string $namespace = null,
        protected Proxy $proxy = new TypeProxy(),
        protected ?View $footer = null,
    ) {
        $this->setFlag(self::FLAG_SHOW_HEADER);
    }



    public function getColumnTemplate(): string {
        if (empty($this->columns)) {
            return '';
        }

        $template = '';
        $first = true;

        foreach ($this->columns as $layout) {
            if (!$first) {
                $template .= ' ';
            }

            $template .= $layout->getTemplate();
            $first = false;
        }

        return $template;
    }

    public function add(string $name, string $label, string $template = '1fr'): static {
        $this->columns[$name] = new GridColumn($label, $template);
        return $this;
    }

    public function addAsFirst(string $name, string $label, string $template = '1fr'): static {
        $this->columns = [$name => new GridColumn($label, $template)] + $this->columns;
        return $this;
    }

    /**
     * @param array<string, GridColumn> $layout
     * @return $this
     */
    public function addAll(array $layout): static {
        foreach ($layout as $name => $column) {
            $this->columns[$name] = $column;
        }

        return $this;
    }

    public function setFooter(?View $footer): void {
        $this->footer = $footer;
    }

    public function load(array $rows): static {
        $this->rows = array_merge($rows, $this->rows);
        return $this;
    }
}