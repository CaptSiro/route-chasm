<?php

namespace components\core\WebPage;

use core\Component;
use core\Render;

class WebPage extends Component {
    public function __construct(
        protected readonly string $language,
        protected readonly Render $head,
        protected readonly Render $content
    ) {}
}