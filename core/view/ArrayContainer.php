<?php

namespace core\view;

trait ArrayContainer {
    /**
     * @var array<View|string>
     */
    protected array $children = [];



    public function addContent(View|string $view): static {
        $this->children[] = $view;
        return $this;
    }

    /**
     * @param array<View|string> $content
     * @return $this
     */
    public function addAllContent(array $content): static {
        $this->children = array_merge($this->children, $content);
        return $this;
    }
}