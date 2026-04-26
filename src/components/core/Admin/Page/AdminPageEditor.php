<?php

namespace components\core\Admin\Page;

use components\ai\InputMessage;
use components\ai\StructureGeneration\StructureGeneration;
use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\Nexus\Editor\AdminNexusEditor;
use components\core\BreadCrumbs\BreadCrumb;
use components\core\BreadCrumbs\BreadCrumbs;
use components\core\Icon;
use components\core\Message\Message;
use components\core\Search\SearchResult;
use components\core\Search\SearchResults;
use components\layout\Accordion\Accordion;
use components\layout\Column\Column;
use components\pages\AiGeneratedPage\AiPageTemplate;
use core\ai\clients\OpenAi;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\database\sql\query\Query;
use core\forms\controls\CsrfField;
use core\forms\controls\Submit\Submit;
use core\forms\controls\TextArea\TextArea;
use core\forms\Form;
use core\http\HttpCode;
use core\http\HttpMethod;
use core\pages\Pages;
use core\pages\PageTemplate;
use core\route\Path;
use core\route\RouteNode;
use core\RouteChasmEnvironment;
use core\sideloader\importers\Css\Css;
use core\sideloader\importers\Javascript\Javascript;
use core\url\Url;
use core\utils\Arrays;
use models\core\Language\Language;
use models\core\Page\behavior\PageEditorBehavior;
use models\core\Page\Page;
use models\core\Page\PageStatus;
use models\core\Setting\Setting;
use RuntimeException;
use const models\extensions\Editable\PROPERTY_EDITABLE;

class AdminPageEditor extends AdminNexusEditor {
    public const LEXICON_GROUP = 'admin.page.editor';

    public const QUERY_EXCLUDE = 'exclude';

    public const NAME_PROMPT = 'prompt';

    public const PROP_TITLE = 'title';
    public const PROP_CHILDREN = 'children';
    public const PROP_TEMPLATE = 'template';



    public function __construct(
        protected PageEditorBehavior $pageBehavior,
        protected string $navigatorMountAlias = RouteChasmEnvironment::MOUNT_DEFAULT_CONTEXT
    ) {
        parent::__construct($pageBehavior);
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function setContext(AdminNexus $context): static {
        $context->setTitle($this->getLocalizedTitle());
        $context->setTemplateSlot(AdminNexus::SLOT_BREAD_CRUMBS, $this->getBreadCrumbs());
        return parent::setContext($context);
    }

    public function getUrlToModel(): Url {
        if (!($this->model instanceof Page)) {
            throw new RuntimeException("Model is not Page");
        }

        return $this->model->getUrlToModel(
            RouteChasmEnvironment::MOUNT_DEFAULT_CONTEXT
        );
    }

    public function getLocalizedTitle(): string {
        $request = App::getInstance()->getRequest();
        $url = $request->getUrl();
        $pageLabel = $this->tr('Pages');

        if (empty($parentId = $url->getQuery()->get(RouteChasmEnvironment::QUERY_PAGE_PARENT))) {
            return $pageLabel;
        }

        $page = Page::fromId(intval($parentId));
        $title = $page->getLocalization($request->getLanguage())->title
            ?? $this->tr('(No title)');
        return "$pageLabel - $title";
    }

    public function getBreadCrumbs(): BreadCrumbs {
        $request = App::getInstance()->getRequest();

        $url = $request->getUrl()
            ->copy()
            ->setQueryArgument(RouteChasmEnvironment::QUERY_PAGE_PARENT);
        $breadCrumbs = [
            $url->toString() => Icon::home()
        ];

        if (empty($parentId = $request->getUrl()->getQuery()->get(RouteChasmEnvironment::QUERY_PAGE_PARENT))) {
            return BreadCrumbs::from($breadCrumbs);
        }

        $page = Page::fromId(intval($parentId));
        $language = $request->getLanguage();

        foreach ($page->getParents() as $parent) {
            $parentUrl = $url
                ->copy()
                ->setQueryArgument(RouteChasmEnvironment::QUERY_PAGE_PARENT, $parent->id);
            $breadCrumbs[$parentUrl->toString()] = $parent->getLocalizationOrDefault($language)->title;
        }

        $ret = BreadCrumbs::from($breadCrumbs);
        $ret->add(new BreadCrumb($page->getLocalizationOrDefault($language)->title));

        return $ret;
    }

    public function getPageTemplate(?Request $request = null): ?PageTemplate {
        $request ??= App::getInstance()
            ->getRequest();

        $pageId = $request->getUrl()->getQuery()->get(RouteChasmEnvironment::QUERY_PAGE);
        if (is_null($pageId)) {
            return null;
        }

        $page = Page::fromId(intval($pageId));
        return $page->getTemplate();
    }

    public function createSearchUrl(): Url {
        return $this->createUrl(Path::from('search'));
    }

    public function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();

        $router->use('/search', function (Request $request, Response $response) {
            $exclude = $request->getUrl()
                ->getQuery()
                ->getStrict(RouteChasmEnvironment::QUERY_SEARCH);

            $maxEntries = Setting::fromName(
                RouteChasmEnvironment::SETTING_DROPDOWN_MAX_ENTRIES,
                true,
                RouteChasmEnvironment::SEARCH_DROPDOWN_MAX_ENTRIES,
                [PROPERTY_EDITABLE => true]
            )->toInt();

            $language = $request->getLanguage();
            $sql = Page::searchFullTextQuery($exclude, $language->id)
                ->limit($maxEntries);

            $exclude = $request->getUrl()
                ->getQuery()
                ->get(self::QUERY_EXCLUDE);
            if (!is_null($exclude)) {
                $sql->where(Query::infer("id_page != ?", $exclude));
            }

            /** @var array<Page> $pages */
            $pages = Page::getDescription()
                ->getFactory()
                ->allExecute($sql);

            $results = [];
            foreach ($pages as $page) {
                $results[] = new SearchResult(
                    $page->createPath($language)->toString(prependSlash: false),
                    $page->id,
                    isLink: false
                );
            }

            $response->renderRoot(new SearchResults($results));
        });

        $router->use('/template', function (Request $request, Response $response) {
            $pageId = $request->getUrl()->getQuery()->get(RouteChasmEnvironment::QUERY_PAGE);
            if (is_null($pageId)) {
                $queryParameter = RouteChasmEnvironment::QUERY_PAGE;
                $response->renderRoot(new Message(
                    $this->tr("URL Query parameter '$queryParameter' is missing")
                ));
            }

            $page = Page::fromId(intval($pageId));
            if (is_null($template = $page->getTemplate())) {
                $response->renderRoot(new Message($this->tr("Template is not set for this page")));
            }

            return $template->buildEditor($page);
        });

        $router->use('/generate-structure', function (Request $request, Response $response) {
            $parentId = $request->getUrl()
                ->getQuery()
                ->get('parent');

            $parent = is_null($parentId)
                ? null
                : Page::fromId($parentId);

            $prompt = $request->getBody()
                ->getStrict(self::NAME_PROMPT);

            if (empty($prompt)) {
                $response->sendMessage(
                    $this->tr('Prompt must not be empty'),
                    HttpCode::CE_BAD_REQUEST
                );
            }

            $templates = Pages::getTemplates();
            $language = App::getDefaultLanguage();

            $client = OpenAi::fromEnv();
            $ai = $client->createRequest();

            OpenAi::addGenericJsonFormat($ai);

            $ai->add(new StructureGeneration(InputMessage::ROLE_SYSTEM, $prompt, $templates, $language));
            $ai->add(new StructureGeneration(InputMessage::ROLE_USER, $prompt, $templates, $language));

            if (is_null($structure = $client->parseResponse($client->chat($ai)))) {
                $response->sendMessage(
                    $this->tr('AI refused to generate structure'),
                    HttpCode::SE_INTERNAL_SERVER_ERROR
                );
            }

            $children = [];
            foreach ($structure as $values) {
                if (!is_array($values)) {
                    continue;
                }

                foreach ($values as $child) {
                    $children[] = $child;
                    $this->createPage($parent, $child, $language);
                }
            }

            if (is_array(Arrays::first($structure))) {
                foreach ($structure as $child) {
                    $children[] = $child;
                    $this->createPage($parent, $child, $language);
                }
            }

            $response->json($children);
        });

        $this->context->setTemplateSlot(
            AdminNexus::SLOT_FOOTER,
            $this->createGenerateStructureForm()
        );
    }

    public function createPage(?Page $parent, array $description, Language $language): void {
        $page = new Page();

        $page->statusId = PageStatus::ID_DRAFT;
        $page->setParent($parent);

        if (!isset($description[self::PROP_TITLE])) {
            return;
        }

        $contextId = $this->pageBehavior->getNavigationContextId();
        $isTitleAvailable = $page->isTitleAvailable(
            $description[self::PROP_TITLE],
            $language,
            $contextId
        );

        if (!$isTitleAvailable) {
            return;
        }

        $isTemplateSet = false;
        $templates = Pages::getTemplates();
        foreach ($templates as $id => $template) {
            if ($template->getName() !== $description[self::PROP_TEMPLATE]) {
                continue;
            }

            $isTemplateSet = true;
            $page->templateId = $id;
        }

        if (!$isTemplateSet) {
            $page->templateId = Pages::register(new AiPageTemplate());
        }

        $page->save();

        $page->createLocalization(
            $description[self::PROP_TITLE],
            $language,
            $contextId
        );

        if (isset($description[self::PROP_CHILDREN]) && is_array($description[self::PROP_CHILDREN])) {
            foreach ($description[self::PROP_CHILDREN] as $child) {
                $this->createPage($page, $child, $language);
            }
        }
    }

    public function getGenerateStructureLink(): ?Url {
        if (is_null($url = $this->context->getEditorLink())) {
            return null;
        }

        $url->getPath()->append('generate-structure');

        return $url;
    }

    public function createGenerateStructureForm(): Form {
        $form = new Form(HttpMethod::POST, $action = $this->getGenerateStructureLink());

        Javascript::import($this->getResource('page-editor.js'));
        Css::import($this->getResource('page-editor.css'));
        $form->setOnSubmitSuccess('pageEditor_onStructureSubmitSuccess');

        $form->add(new CsrfField(App::getInstance()->getRequest()));
        $form->add(new Accordion($this->tr("Generate Structure"), $accordion = new Column()));

        $accordion->add(new TextArea(self::NAME_PROMPT, $this->tr("AI Prompt")));
        $accordion->add(new Submit());

        return $form;
    }

    public function getTemplateEditorLink(): ?Url {
        if (is_null($this->model) || is_null($url = $this->context->getEditorLink())) {
            return null;
        }

        $url->getPath()->append('template');
        $url->setQueryArgument('page', $this->model->getId());

        return $url;
    }
}