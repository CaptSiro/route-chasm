<?php

namespace core\view2;

trait ContainerTrait {
    protected array $views = [];



    public function add(View $view): static {
        $this->views[] = $view;
        return $this;
    }

    public function addAll(array $views): static {
        $this->views = array_merge($this->views, $views);
        return $this;
    }



    // Component
    public function setRenderer(Renderer $renderer): static {
        foreach ($this->views as $view) {
            if ($view instanceof Component) {
                $view->setRenderer($renderer);
            }
        }

        if ($this instanceof Component) {
            parent::setRenderer($renderer);
        }

        return $this;
    }
}