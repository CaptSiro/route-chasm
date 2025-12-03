<?php

namespace components\core\FileSystem;

use components\layout\Grid\GridLayout;
use core\view\Renderer;
use core\view\View;

class FileSystemGrid implements GridLayout {
    use Renderer;

    public function __construct(
        protected GridLayout $grid,
        protected FileSystemDropArea $area,
    ) {
        $this->area->addContent($grid);
    }



    public function getFileDropArea(): FileSystemDropArea {
        return $this->area;
    }

    public function getGrid(): GridLayout {
        return $this->grid;
    }

    public function render(): string {
        return $this->area->render();
    }



    // GridLayout
    public function getColumnTemplate(): string {
        return $this->grid->getColumnTemplate();
    }

    public function add(string $name, string $label, string $template = '1fr'): static {
        $this->grid->add($name, $label, $template);
        return $this;
    }

    public function addAsFirst(string $name, string $label, string $template = '1fr'): static {
        $this->grid->addAsFirst($name, $label, $template);
        return $this;
    }

    public function addAll(array $layout): static {
        $this->grid->addAll($layout);
        return $this;
    }

    public function setFooter(?View $footer): void {
        $this->grid->setFooter($footer);
    }

    public function load(array $rows): static {
        $this->grid->load($rows);
        return $this;
    }
}