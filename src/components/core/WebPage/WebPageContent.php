<?php

namespace components\core\WebPage;

use components\core\HtmlHead\HtmlHead;
use core\Component;
use core\Request;
use core\Response;

class WebPageContent extends Component {
    protected WebPage $page;



    public function __construct(?string $language = null, ?HtmlHead $head = null) {
        $this->page = new WebPage(language: $language, head: $head);
        $this->page->setContent($this);
    }



    public function execute(Request $request, Response $response): void {
        $response->render($this->page);
    }
}