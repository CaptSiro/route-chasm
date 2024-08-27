<?php

namespace components\core\Head;

use core\Component;

class Head extends Component {
    public static function def(string $title): self {
        return (new Head($title))
            ->addMeta("author", "CaptSiro")
            ->addMeta("MobileOptimized", "width")
            ->addMeta("HandleFriendly", "true");
    }

    protected array $meta;



    public function __construct(
        public readonly string $title,
    ) {
        $this->meta = [];
    }



    public function addMeta(string $name, string $content): self {
        $this->meta[$name] = $content;
        return $this;
    }
}