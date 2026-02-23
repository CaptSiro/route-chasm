<?php

namespace components\pages\AiGeneratedPage;

use components\core\fs\FileContent\FileContent;
use components\core\Html\Html;
use components\pages\Wireframe\Wireframe;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\data\DataItem;
use core\http\HttpCode;
use core\http\HttpMethod;
use core\locale\LexiconUnit;
use core\sideloader\importers\Css\Css;
use core\sideloader\importers\Javascript\Javascript;
use core\view\Component;
use core\view\Renderer;
use core\view\StringRenderer;
use models\core\Page\Page;
use models\core\User\User;

class AiGeneratedPage extends Component {
    use Renderer, LexiconUnit;



    const LEXICON_GROUP = 'ai-page';

    public const TARGET_HTML = 'ai-page-source_html';
    public const TARGET_CSS = 'ai-page-source_css';
    public const TARGET_JS = 'ai-page-source_js';

    public static function build(Wireframe $wireframe, Page $page): Component {
        $html = $page->get(AiPageTemplate::DATA_ITEM_HTML);
        $css = $page->get(AiPageTemplate::DATA_ITEM_CSS);
        $js = $page->get(AiPageTemplate::DATA_ITEM_JS);

        $request = App::getInstance()
            ->getRequest();
        if (is_null($user = User::fromRequest($request)) || !$user->isAdmin()) {
            return static::renderHtml($html, $css, $js);
        }

        return new static($wireframe, $html, $css, $js);
    }

    protected static function renderHtml(DataItem $html, DataItem $css, DataItem $js): Component {
        if (!$html->exists()) {
            return new StringRenderer('');
        }

        if ($css->exists()) {
            Css::import($css->getFilePath());
        }

        if ($js->exists()) {
            Javascript::import($js->getFilePath());
        }

        return new StringRenderer($html->read());
    }



    protected array $sources;
    protected bool $sourcesCreated = false;

    public function __construct(
        protected Wireframe $wireframe,
        protected DataItem $html,
        protected DataItem $css,
        protected DataItem $js,
    ) {
        parent::__construct();

        $this->setLexiconGroup(self::LEXICON_GROUP);
        $this->sources = $this->createSources();
    }



    public function render(): string {
        if (!$this->html->exists()) {
            return '';
        }

        return $this->renderTemplated();
    }

    protected function createFileContent(DataItem $source, string $targetSelector): FileContent {
        $fileContent = new FileContent(
            $source->getFilePath(),
            readonly: false
        );

        $fileContent->addJavascriptInit('aiPage_source');
        $fileContent->addDataAttribute('target', $targetSelector);

        return $fileContent;
    }

    protected function createSources(): array {
        if ($this->sourcesCreated) {
            return $this->sources;
        }

        $this->sourcesCreated = true;

        $this->sources = [
            'index.html' => $this->createFileContent($this->html, '#'. self::TARGET_HTML)
        ];

        $head = $this->wireframe->getHead();

        if ($this->css->exists()) {
            $this->sources['styles.css'] = $this->createFileContent($this->css, '#'. self::TARGET_CSS);
            $head->addElement(new Html(
                'style',
                attributes: ['id' => self::TARGET_CSS],
                content: $this->css->read()
            ));
        }

        if ($this->js->exists()) {
            $this->sources['script.js'] = $this->createFileContent($this->js, '#'. self::TARGET_JS);
            $head->addElement(new Html(
                'script',
                attributes: [
                    'defer' => '',
                    'id' => self::TARGET_JS
                ],
                content: $this->js->read()
            ));
        }

        return $this->sources;
    }

    public function perform(Request $request, Response $response): void {
        switch ($request->getHttpMethod()) {
            case HttpMethod::GET: {
                parent::perform($request, $response);
                return;
            }

            case HttpMethod::PUT: {
                $user = User::fromRequest($request);
                if (is_null($user) || !$user->isAdmin()) {
                    $response->sendStatus(HttpCode::CE_FORBIDDEN);
                }

                $body = $request->getBody();

                if (!is_null($html = $body->get('html')) && $this->html->exists()) {
                    $this->html->write($html);
                }

                if (!is_null($css = $body->get('css')) && $this->css->exists()) {
                    $this->css->write($css);
                }

                if (!is_null($js = $body->get('js')) && $this->js->exists()) {
                    $this->js->write($js);
                }

                $response->sendStatus(HttpCode::S_OK);
            }

            default: {
                $response->sendStatus(HttpCode::CE_METHOD_NOT_ALLOWED);
            }
        }
    }
}