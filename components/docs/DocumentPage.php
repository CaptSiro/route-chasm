<?php

namespace components\docs;

use components\layout\BreadCrumbs\BreadCrumbs;
use components\layout\PageMenu\PageMenu;
use components\pages\Article\Article;
use components\Search\HeaderSearch;
use components\Search\Search;
use core\locale\LexiconUnit;
use core\route\Path;
use core\view\Controller;
use core\view\Head;
use core\view\Renderer;
use example\components\Header;
use models\Menu;

class DocumentPage extends Controller {
    use LexiconUnit;

    public const LEXICON_GROUP = Article::LEXICON_GROUP;



    public function __construct(
        string $title,
        protected Docs $docs,
        protected BreadCrumbs $breadCrumbs,
        protected ?string $directory = null,
        ?Renderer $renderer = null
    ) {
        parent::__construct($renderer);

        $this->setLexiconGroup(self::LEXICON_GROUP);
        $this->setTitle($this->tr('Docs') . ' - ' . $title);
        $this->setProperty(Head::PAYLOAD_HTML_ELEMENTS, [Search::createApi()]);
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