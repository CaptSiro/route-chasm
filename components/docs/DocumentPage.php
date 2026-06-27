<?php

namespace components\docs;

use components\html\HtmlHead;
use components\layout\BreadCrumbs\BreadCrumbs;
use components\layout\PageMenu\PageMenu;
use components\pages\Article\Article;
use components\Search\HeaderSearch;
use components\Search\Search;
use components\layout\WebPage\WebPage;
use core\route\Path;
use core\view\ContainerContent;
use core\view\StringRenderer;
use example\components\Header;
use models\Menu;

class DocumentPage extends ContainerContent {
    public const LEXICON_GROUP = Article::LEXICON_GROUP;



    protected WebPage $webPage;

    public function __construct(
        string $title,
        protected Docs $docs,
        protected BreadCrumbs $breadCrumbs,
        protected ?string $directory = null
    ) {
        parent::__construct($this->webPage = new WebPage(head: $head = new HtmlHead(title: 'Docs - '. $title)));
        $head->addElement(new StringRenderer(Search::createApi()));

        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function setDirectory(?string $directory): static {
        $this->directory = $directory;
        return $this;
    }

    public function createHeader(): Header {
        return new Header(
            PageMenu::fromModelName(Menu::NAME_HEADER_DOCS),
            new HeaderSearch(
                url: Docs::getInstance()->createSearchUrl(),
                placeholder: $this->tr("Search documents...")
            )
        );
    }

    protected function getEntries(): array {
        if (is_null($this->directory)) {
            return [];
        }

        $entries = [];
        foreach (scandir($this->directory) as $entry) {
            if ($entry === '..' || $entry === '.') {
                continue;
            }

            $entries[$entry] = Path::joinArray([$this->directory, $entry], DIRECTORY_SEPARATOR);
        }

        return $entries;
    }
}