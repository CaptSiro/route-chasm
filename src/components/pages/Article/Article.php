<?php

namespace components\pages\Article;

use core\view\Component;

class Article extends Component {
    public function __construct(
        protected string $content
    ) {
        parent::__construct();
    }
}