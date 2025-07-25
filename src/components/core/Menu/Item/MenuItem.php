<?php

namespace components\core\Menu\Item;

use components\core\Menu\Menu;
use core\route\Path;
use core\view\Renderer;
use core\view\View;

/**
 * @template T
 */
class MenuItem implements View {
    use Renderer;



    protected Menu $context;
    protected Path $path;
    protected string $label;
    protected bool $hasValue;
    /** @var T $value */
    protected mixed $value;



    public function setContext(Menu $context): static {
        $this->context = $context;
        return $this;
    }

    public function setPath(Path $path): static {
        $this->path = $path;
        return $this;
    }

    public function getPathSource(): string {
        return $this->context
            ->getRootMenu()
            ->getGraph()
            ->getPathSource($this->path);
    }

    public function setLabel(string $label): static {
        $this->label = $label;
        return $this;
    }

    public function setHasValue(bool $hasValue): static {
        $this->hasValue = $hasValue;
        return $this;
    }

    /**
     * @param T $value
     * @return $this
     */
    public function setValue(mixed $value): static {
        $this->value = $value;
        return $this;
    }
}