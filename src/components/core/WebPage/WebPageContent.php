<?php

namespace components\core\WebPage;

use components\core\HtmlHead\HtmlHead;
use core\communication\Request;
use core\communication\Response;
use core\view\Component;
use core\view\Render;

class WebPageContent extends Component {
    protected WebPage $page;



    public function __construct(?string $language = null, ?HtmlHead $head = null) {
        $this->page = new WebPage(language: $language, head: $head);
        $this->page->setContent($this);
    }



    public function getRoot(): Render {
        return $this->page;
    }

    public function execute(Request $request, Response $response): void {
        $response->renderRoot($this);
    }
}