<?php

namespace components\docs;

use components\layout\Accordion\Accordion;
use components\layout\Column\Column;
use components\layout\Grid\Grid;
use components\layout\Grid\Loader\ModelGridLoader;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\forms\controls\CsrfField;
use core\forms\controls\Submit\Submit;
use core\forms\Form;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\http\HttpMethod;
use core\view\ContainerContent;
use core\view\View;
use models\docs\Document;

class DocsDashboard extends ContainerContent {
    public const LEXICON_GROUP = Docs::LEXICON_GROUP;

    public const NAME_FILES = 'files';



    protected ModelGridLoader $loader;

    public function __construct() {
        parent::__construct();

        $this->setLexiconGroup(self::LEXICON_GROUP);
        $this->loader = new ModelGridLoader(Document::class);
    }



    public function createForm(): Form {
        $form = new Form(HttpMethod::POST);

        $form->add(new CsrfField(App::getInstance()->getRequest()));

        $form->add(new Accordion($this->tr('Request documentation'), $column = new Column()));
        $column->add(new DocumentRequestsList(self::NAME_FILES));
        $column->add(new Submit($this->tr('Submit')));

        return $form;
    }

    public function createGrid(): View {
        $grid = new Grid(proxy: new DocsDashboardProxy());

        $grid->add(DocsDashboardProxy::NAME_UPTO_DATE, $this->tr('Upto Date'), '128px');
        $grid->add(DocsDashboardProxy::NAME_FILE, $this->tr('File'));

        $grid->load(
            $this->loader->load($grid)
        );

        return $grid;
    }

    public function performComponentAction(Request $request, Response $response): void {
        switch ($request->getHttpMethod()) {
            case HttpMethod::GET: {
                parent::performComponentAction($request, $response);
                break;
            }

            case HttpMethod::POST: {
                $docs = Docs::getInstance();
                $filesEncoded = array_filter(
                    explode(',', $request->getBody()->getStrict(self::NAME_FILES)),
                    fn(string $x) => trim($x) !== ''
                );
                
                if (empty($filesEncoded)) {
                    $response->sendStatus(HttpCode::S_OK);
                }

                $language = $request->getLanguage();

                foreach ($filesEncoded as $encoded) {
                    $file = urldecode($encoded);
                    if (!file_exists($file)) {
                        continue;
                    }

                    $file = realpath($file);
                    $document = Document::fromFile($file);
                    if (!is_null($document) && $document->getContent($language)->exists()) {
                        continue;
                    }

                    $docs->document($file, $language);
                }

                $response->setHeader(HttpHeader::X_RELOAD, 'Reload');
                $response->sendStatus(HttpCode::S_OK);
                break;
            }

            default: {
                $response->sendStatus(HttpCode::CE_METHOD_NOT_ALLOWED);
                break;
            }
        }
    }
}