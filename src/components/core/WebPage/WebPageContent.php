<?php

namespace components\core\WebPage;

use components\core\HtmlHead\HtmlHead;
use core\Component;
use core\Request;
use core\Response;

class WebPageContent extends Component {
    public function __construct(?string $language = null, ?HtmlHead $head = null) {
        $page = WebPage::getInstance(language: $language, head: $head);
        $page->setContent($this);
    }



    public function call(Request $request, Response $response): void {
        $response->render(WebPage::getInstance());
    }
}