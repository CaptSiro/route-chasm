<?php

namespace components\pages\Article;

use components\core\Markdown\Editor\MarkdownEditor;
use components\core\WebPage\ContextAwareWebPage;
use components\layout\Tabs\Tabs;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\forms\controls\CsrfField;
use core\forms\controls\MultiSubmit\MultiSubmit;
use core\forms\Form;
use core\forms\FormAction;
use core\http\HttpCode;
use core\http\HttpMethod;
use core\RouteChasmEnvironment;
use core\utils\Arrays;
use core\view\ContainerContent;
use models\core\Page\PageLocalization;
use models\core\Page\Page;
use models\core\Privilege\Privilege;
use models\core\UserResource;

class ArticleEditor extends ContainerContent {
    public const LEXICON_GROUP = 'admin.article.editor';
    public const NAME_CONTENT = 'content';



    protected ContextAwareWebPage $webPage;

    public function __construct(
        protected Page $page
    ) {
        parent::__construct($this->webPage = new ContextAwareWebPage());
        $this->setLexiconGroup(self::LEXICON_GROUP);
        $this->setUserResource(UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_PAGE));

        $this->webPage
            ->getHead()
            ->setTitle($page->getLocalizationOrDefault()?->title ?? 'Article Editor');
    }



    protected function createName(PageLocalization $localization, string $element): string {
        return 'localization-'. $localization->id .'_'. $element;
    }

    public function createForm(): Form {
        $form = new Form(HttpMethod::POST);
        $form->setOnSubmitSuccess('articleEditor_success');

        $form->add(new CsrfField(App::getInstance()->getRequest()));

        $tabs = [];
        foreach ($this->page->getLocalizations() as $localization) {
            $language = $localization->getLanguage()->getLocale()->getName();
            $content = $localization->get(ArticleTemplate::DATA_CONTENT)
                ->read() ?? '';

            $tabs[$language] = new MarkdownEditor(
                $content,
                $this->createName($localization, self::NAME_CONTENT)
            );
        }

        if (count($tabs) === 1) {
            $form->add(Form::note(array_key_first($tabs)));
            $form->add(Arrays::first($tabs));
        } else if (empty($tabs)) {
            $form->add(Form::note($this->tr('Can not create article without a single localization')));
        } else {
            $selected = App::getInstance()
                ->getRequest()
                ->getLanguage()
                ->getLocale()
                ->getName();

            $form->add(new Tabs($tabs, $selected));
        }

        $actions = [
            (new FormAction(FormAction::TYPE_BUTTON, $this->tr('Cancel')))
                ->addJavascriptInit('articleEditor_cancel'),
            new FormAction(FormAction::TYPE_SUBMIT, $this->tr('Submit'))
        ];

        $form->add(new MultiSubmit($actions));
        return $form;
    }

    public function perform(Request $request, Response $response): void {
        switch ($request->getHttpMethod()) {
            case HttpMethod::GET: {
                parent::perform($request, $response);
                break;
            }

            case HttpMethod::POST: {
                if (!$this->hasRequestAccess(Privilege::fromName(Privilege::UPDATE))) {
                    $response->sendStatus(HttpCode::CE_FORBIDDEN);
                }

                if (!CsrfField::check($request)) {
                    $response->sendMessage(
                        'Cross-Site request forgery detected',
                        HttpCode::CE_NOT_ACCEPTABLE
                    );
                }

                $body = $request->getBody();
                foreach ($this->page->getLocalizations() as $localization) {
                    $name = $this->createName($localization, self::NAME_CONTENT);
                    if (is_null($content = $body->get($name))) {
                        continue;
                    }

                    $localization
                        ->get(ArticleTemplate::DATA_CONTENT)
                        ->write($content);
                }

                $response->sendStatus(HttpCode::S_OK);
            }
        }
    }
}