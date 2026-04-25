<?php

namespace components\pages\Article;

use components\ai\ArticleGeneration\ArticleGeneration;
use components\ai\ArticleGeneration\ArticleGenerationLength;
use components\ai\ArticleGeneration\ArticleGenerationOptions;
use components\ai\ArticleGeneration\ArticleGenerationTone;
use components\ai\InputMessage;
use components\ai\Schema\ObjectSchema;
use components\ai\Schema\Schema;
use components\ai\Schema\StringSchema;
use components\core\Markdown\Editor\MarkdownEditor;
use components\core\WebPage\ContextAwareWebPage;
use components\layout\Accordion\Accordion;
use components\layout\Column\Column;
use components\layout\Tabs\Tabs;
use core\actions\UnexpectedHttpMethod;
use core\ai\clients\OpenAi;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\forms\controls\CsrfField;
use core\forms\controls\MultiSubmit\MultiSubmit;
use core\forms\controls\Select\Select;
use core\forms\controls\Submit\Submit;
use core\forms\controls\TextArea\TextArea;
use core\forms\Form;
use core\forms\FormAction;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\http\HttpMethod;
use core\RouteChasmEnvironment;
use core\utils\Arrays;
use core\view\ContainerContent;
use core\view\View;
use models\core\Language\Language;
use models\core\Page\PageLocalization;
use models\core\Page\Page;
use models\core\Privilege\Privilege;
use models\core\UserResource;

class ArticleEditor extends ContainerContent {
    use UnexpectedHttpMethod;

    public const LEXICON_GROUP = 'admin.article.editor';

    public const NAME_CONTENT = 'content';
    public const NAME_LANGUAGE = 'language';
    public const NAME_PROMPT = 'prompt';
    public const NAME_LENGTH = 'length';
    public const NAME_TONE = 'tone';
    public const PROPERTY_CONTENT = 'content';



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

    public function createAiGeneration(): View {
        $form = new Form(HttpMethod::PUT);

        $form->add(new CsrfField(App::getInstance()->getRequest()));
        $form->add(new Accordion($this->tr('Generate with AI'), $accordion = new Column(), false));

        $languages = [];
        foreach ($this->page->getLocalizations() as $localization) {
            $language = $localization->getLanguage();
            $languages[$language->id] = $language->getLocale()->getName();
        }

        $accordion->add(new Select(
            self::NAME_LANGUAGE,
            $this->tr('Target language'),
            $languages,
            Arrays::first(array_keys($languages))
        ));

        $accordion->add(new Select(
            self::NAME_LENGTH,
            $this->tr('Article length'),
            ArticleGenerationLength::options(),
            ArticleGenerationLength::MEDIUM->name
        ));

        $accordion->add(new Select(
            self::NAME_TONE,
            $this->tr("Writer's tone"),
            ArticleGenerationTone::options(),
            ArticleGenerationTone::FORMAL->name
        ));

        $accordion->add(new TextArea(self::NAME_PROMPT, $this->tr('Prompt')));
        $accordion->add(new Submit($this->tr('Generate')));

        return $form;
    }

    public function createEditorEnvironment(): View {
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

    protected function checkRequest(Request $request, Response $response): void {
        if (!$this->hasRequestAccess(Privilege::fromName(Privilege::UPDATE))) {
            $response->sendStatus(HttpCode::CE_FORBIDDEN);
        }

        if (!CsrfField::check($request)) {
            $response->sendMessage(
                'Cross-Site request forgery detected',
                HttpCode::CE_NOT_ACCEPTABLE
            );
        }
    }

    public function perform(Request $request, Response $response): void {
        switch ($request->getHttpMethod()) {
            case HttpMethod::GET: {
                parent::perform($request, $response);
                break;
            }

            case HttpMethod::POST: {
                $this->checkRequest($request, $response);

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

            case HttpMethod::PUT: {
                $this->checkRequest($request, $response);

                $body = $request->getBody();

                if (empty($prompt = $body->getStrict(self::NAME_PROMPT))) {
                    $response->sendMessage(
                        $this->tr('Prompt must not be empty'),
                        HttpCode::CE_BAD_REQUEST
                    );
                }

                if (is_null($language = Language::fromId($body->getStrict(self::NAME_LANGUAGE)))) {
                    $response->sendMessage(
                        $this->tr('Language must be defined'),
                        HttpCode::CE_BAD_REQUEST
                    );
                }

                $options = new ArticleGenerationOptions(
                    ArticleGenerationLength::fromOption($body->getStrict(self::NAME_LENGTH)),
                    ArticleGenerationTone::fromOption($body->getStrict(self::NAME_TONE))
                );

                $tone = $body->getStrict(self::NAME_TONE);

                $client = OpenAi::fromEnv();
                $ai = $client->createRequest();

                $ai->add(new ArticleGeneration(InputMessage::ROLE_SYSTEM, $prompt, $language, $options));
                $ai->add(new ArticleGeneration(InputMessage::ROLE_USER, $prompt, $language, $options));

                $ai->setSchema(
                    new Schema('article_generation', (new ObjectSchema())
                        ->add(self::PROPERTY_CONTENT, new StringSchema())
                        ->setRequired([self::PROPERTY_CONTENT]))
                );

                if (is_null($result = $client->parseResponse($client->chat($ai)))) {
                    $response->sendMessage(
                        $this->tr('AI refused to generate article'),
                        HttpCode::CE_BAD_REQUEST
                    );
                }

                if (!empty($result[self::PROPERTY_CONTENT])) {
                    if (is_null($localization = $this->page->getLocalization($language))) {
                        $response->sendMessage(
                            $this->trt("Page is not localized for this language: {}", $language->code),
                            HttpCode::CE_BAD_REQUEST
                        );
                    }

                    $localization
                        ->get(ArticleTemplate::DATA_CONTENT)
                        ->write($result[self::PROPERTY_CONTENT]);
                }

                $response->setHeader(HttpHeader::X_RELOAD, 'Reload');
                $response->sendStatus(HttpCode::S_OK);
            }

            default: $this->handleUnexpectedMethod($request, $response);
        }
    }
}