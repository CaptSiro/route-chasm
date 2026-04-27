<?php

namespace models\core\Page\behavior;

use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Admin\Nexus\Editor\EditorBehaviorAction;
use components\core\Admin\Nexus\Editor\SetEditor;
use components\core\Admin\Page\AdminPageEditor;
use components\core\fs\FileControl;
use components\core\Html\Html;
use components\core\Message\Message;
use components\layout\Accordion\Accordion;
use components\layout\Column\Column;
use components\layout\Layout;
use components\layout\Row\Row;
use components\layout\Tabs\Tabs;
use core\App;
use core\collections\StrictDictionary;
use core\communication\Request;
use core\database\sql\Model;
use core\database\sql\ModelDescription;
use core\database\sql\Sql;
use core\forms\controls\HiddenField;
use core\forms\controls\MultiSelect\MultiSelect;
use core\forms\description\FormDescription;
use core\forms\Form;
use core\locale\LexiconUnit;
use core\RouteChasmEnvironment;
use core\utils\Models;
use core\view\View;
use models\core\fs\Shortcut;
use models\core\Language\Language;
use models\core\Menu;
use models\core\Navigation\NavigationContext;
use models\core\Page\PageLocalization;
use models\core\Page\Page;
use models\core\Page\PageMeta;
use models\core\Setting\Setting;
use const models\extensions\Editable\PROPERTY_EDITABLE;

class PageEditorBehavior implements EditorBehavior {
    use LexiconUnit, SetEditor;

    public const NAME_LANGUAGE_ID = 'languageId';
    public const NAME_PARENT_ID = 'parentId';
    public const NAME_MENUS = 'menuIds';
    public const NAME_RELATED_PAGES = 'relatedPages';
    public const NAME_COVER_IMAGE = 'coverImage';



    public function __construct(
        protected ?string $navigationContext = null,
        protected EditorBehavior $localization = new LocalizedPageEditorBehavior()
    ) {
        $this->setLexiconGroup(AdminPageEditor::LEXICON_GROUP);
    }



    protected function getTemplateBehavior(?Model $model): ?EditorBehavior {
        if (!($model instanceof Page)) {
            return null;
        }

        $behavior = $model->getTemplate()
            ->buildEditorBehavior();

        if (isset($this->editor)) {
            $behavior?->setEditor($this->editor);
        }

        return $behavior;
    }

    public function getNavigationContextId(): int {
        return NavigationContext::getContextId($this->navigationContext);
    }

    public function initForm(Form $form, ?Model $model): ?View {
        $form->setBodyTransformer('form_json');
        $form->add(new HiddenField(self::NAME_PARENT_ID, App::getInstance()
            ->getRequest()
            ->getUrl()
            ->getQuery()
            ->get(RouteChasmEnvironment::QUERY_PAGE_PARENT, '')));

        if (!is_null($behavior = $this->getTemplateBehavior($model))) {
            $behavior->initForm($form, $model);
        }

        return null;
    }

    public function addControls(Layout $layout, ?Model $model): ?View {
        /** @var ?Page $model */
        $error = FormDescription::extract(Page::class)
            ->addControls($pageFields = new Column(0.5), $model);
        if (!is_null($error)) {
            return $error;
        }

        $menus = Menu::createOptions();
        $selected = !is_null($model)
            ? array_map(fn(Menu $x) => $x->id, Menu::forPage($model))
            : [];

        $pageFields->add(new MultiSelect(
            self::NAME_MENUS,
            $this->tr('Place into menus'),
            $menus,
            $selected
        ));

        if ($this->editor instanceof AdminPageEditor) {
            $options = [];
            $language = App::getInstance()
                ->getRequest()
                ->getLanguage();

            $searchUrl = $this->editor->createSearchUrl();

            if (!is_null($model)) {
                $searchUrl->setQueryArgument(AdminPageEditor::QUERY_EXCLUDE, $model->getId());

                foreach ($model->getRelated() as $p) {
                    $options[$p->id] = $p->createPath($language)->toString(prependSlash: false);
                }
            }

            $relatedSelect = new MultiSelect(
                self::NAME_RELATED_PAGES,
                $this->tr('Related Pages'),
                $options,
                array_keys($options)
            );

            $relatedSelect->setAsyncSearch(
                $searchUrl,
                Setting::fromName(
                    RouteChasmEnvironment::SETTING_MIN_SEARCH_QUERY_LENGTH,
                    true,
                    RouteChasmEnvironment::SEARCH_MIN_LENGTH,
                    [PROPERTY_EDITABLE => true]
                )->toInt()
            );

            $pageFields->add($relatedSelect);
        }

        $row = new Row();
        $row->add($pageFields);

        $column = new Column(0.5);
        $column->add(FileControl::fromShortcut(
            self::NAME_COVER_IMAGE,
            'Cover Image',
            $model?->getCoverImage()
        )->accept('image'));

        $row->add($column);

        $layout->add(new Accordion($this->tr('General'), $row));

        $tabs = [];
        $localizations = is_null($model)
            ? []
            : $model->getLocalizations();

        foreach (Language::all() as $language) {
            $error = $this->localization->addControls(
                $localizationFields = new Column(),
                $localizations[$language->id] ?? null
            );

            if (!is_null($error)) {
                return $error;
            }

            $localizationFields->add(new HiddenField(self::NAME_LANGUAGE_ID, $language->id));
            $tabs[$language->getLocale()->getName()] = $localizationFields;
        }

        $selected = App::getInstance()
            ->getRequest()
            ->getLanguage()
            ->getLocale()
            ->getName();

        $layout->add(new Accordion(
            $this->tr('Localization'),
            new Tabs($tabs, $selected)
        ));

        if (!is_null($behavior = $this->getTemplateBehavior($model))) {
            $behavior->addControls($layout, $model);
        }

        return null;
    }

    protected function emptyTitles(array $titles): bool {
        foreach ($titles as $title) {
            if (!empty($title)) {
                return false;
            }
        }

        return true;
    }

    protected function testTitleAvailability(
        string $title, array $languages, int $languageId, Page $page, int $navigationContextId
    ): ?View {
        if (!isset($languages[$languageId])) {
            $safe = Html::escape($title);
            return new Message($this->tr("Language for title '$safe' not found"));
        }

        $isAvailable = $page->isTitleAvailable(
            $title, $languages[$languageId], $navigationContextId
        );

        if ($isAvailable) {
            return null;
        }

        $safe = Html::escape($title);
        return new Message($this->tr("Title '$safe' is not unique"));
    }

    protected function testTitlesAvailability(Request $request, Page $page): ?View {
        $body = $request->getBody();

        $titles = $body->getStrict('title');
        $languageIds = $body->getStrict(self::NAME_LANGUAGE_ID);

        /** @var array<int, Language> $languages */
        $languages = Models::identity(Language::all());
        $navigationContextId = $this->getNavigationContextId();

        if (!is_array($titles)) {
            return $this->testTitleAvailability(
                $titles,
                $languages,
                intval($languageIds),
                $page,
                $navigationContextId
            );
        }

        $navigationContextId = $this->getNavigationContextId();
        /** @var array<int, Language> $languages */
        $languages = Models::identity(Language::all());

        foreach ($titles as $i => $title) {
            $this->testTitleAvailability(
                $title,
                $languages,
                intval($languageIds[$i]),
                $page,
                $navigationContextId
            );
        }

        return null;
    }

    protected function onSubmitPlaceInMenus(Page $page, StrictDictionary $body): void {
        $alreadyAssigned = Menu::forPage($page);
        $newlyAssigned = array_map(
            fn($x) => intval($x),
            MultiSelect::parse($body->get(self::NAME_MENUS, ''))
        );

        $sameLength = count($alreadyAssigned) === count($newlyAssigned);
        $sameMenus = true;

        if ($sameLength) {
            foreach ($newlyAssigned as $id) {
                if (isset($alreadyAssigned[$id])) {
                    continue;
                }

                $sameMenus = false;
                break;
            }
        }

        if ($sameLength && $sameMenus) {
            return;
        }

        foreach ($alreadyAssigned as $menu) {
            $menu->deletePage($page);
        }

        foreach ($newlyAssigned as $id) {
            Menu::fromId($id)?->addPage($page);
        }
    }

    protected function isTitleForDefaultLanguageSubmitted(StrictDictionary $body): ?View {
        $titles = $body->getStrict('title');
        $languageIds = $body->getStrict(self::NAME_LANGUAGE_ID);
        $defaultLanguageId = App::getDefaultLanguage()->id;

        if (!is_array($titles)) {
            if (intval($languageIds) === $defaultLanguageId) {
                return null;
            }

            return new Message(
                $this->tr('Page must have title for default language')
            );
        }

        if ($this->emptyTitles($titles)) {
            return new Message($this->tr('Page must have at least one title'));
        }

        foreach ($languageIds as $i => $id) {
            if ($defaultLanguageId !== intval($id) || !empty($titles[$i])) {
                continue;
            }

            return new Message(
                $this->tr('Page must have title for default language')
            );
        }

        return null;
    }

    protected function onSubmitPage(Page $page, EditorBehaviorAction $action, Request $request): ?View {
        $body = $request->getBody();

        if (!is_null($error = $this->isTitleForDefaultLanguageSubmitted($body))) {
            return $error;
        }

        $hasTemplateChanged = isset($page->templateId)
            && $page->templateId !== intval($body->get("templateId"));

        if ($hasTemplateChanged) {
            $page->getTemplate()
                ?->delete($page);
        }

        $page->set($body->toArray());

        if (empty($body->get('publish'))) {
            $page->publish = null;
        }

        if (empty($body->get('remove'))) {
            $page->remove = null;
        }

        if ($action === EditorBehaviorAction::CREATE) {
            $page->created = Sql::datetimeNow();
        }

        $page->updated = Sql::datetimeNow();
        $page->save();

        $values = array_map(
            fn($x) => intval($x),
            MultiSelect::parse($body->getStrict(self::NAME_RELATED_PAGES))
        );

        $related = array_map(
            fn(Page $x) => $x->id,
            $page->getRelated()
        );

        if (!empty(array_diff($related, $values)) || !empty(array_diff($values, $related))) {
            $page->clearRelated();
            $page->addRelatedRaw($values);
        }

        Shortcut::submitHash($body->getStrict(self::NAME_COVER_IMAGE), $page->getCoverImageName());

        if ($hasTemplateChanged) {
            $page->getTemplate(true)
                ?->create($page);
        }

        $this->onSubmitPlaceInMenus($page, $body);
        return null;
    }

    protected function onSubmitLocalization(array $object, Page $page, Language $language, int $navigationContextId): void {
        $localizations = $page->getLocalizations();

        if (!isset($localizations[$language->id])) {
            if (empty($object['title'])) {
                return;
            }

            $page->createLocalization(
                $object['title'],
                $language,
                $navigationContextId,
                $object
            );

            return;
        }

        $localization = $localizations[$language->id];
        if ($localization->title !== $object['title']) {
            $localization->title = $object['title'];
            $localization->save();

            $slug = $localization->getSlug();
            $slug->slug = $localization->getSlugLiteral($language);
            $slug->save();
        }

        $meta = PageMeta::fromLocalization($localization, true);
        $meta->set($object);
        $meta->save();
    }

    protected function onSubmitLocalizations(Page $page, Request $request): ?View {
        $body = $request->getBody();
        $navigationContextId = $this->getNavigationContextId();

        /** @var array<int, Language> $languages */
        $languages = Models::identity(Language::all());

        $languageIds = $body->getStrict(self::NAME_LANGUAGE_ID);
        if (!is_array($languageIds)) {
            $this->onSubmitLocalization(
                $object = $body->toArray(),
                $page,
                $languages[intval($object[self::NAME_LANGUAGE_ID])],
                $navigationContextId
            );
            return null;
        }

        $columns = array_merge(
            ModelDescription::extract(PageLocalization::class)->getColumnAlias(),
            ModelDescription::extract(PageMeta::class)->getColumnAlias()
        );
        $columns[] = self::NAME_LANGUAGE_ID;

        $objects = Models::transpose(
            $body->toArray(),
            $columns,
            count($languageIds)
        );

        $localizations = $page->getLocalizations();

        foreach ($objects as $object) {
            $this->onSubmitLocalization(
                $object,
                $page,
                $languages[intval($object[self::NAME_LANGUAGE_ID])],
                $navigationContextId
            );
        }

        return null;
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        /** @var Page $model */

        $request = App::getInstance()->getRequest();
        $body = $request->getBody();

        if (!($model instanceof Page)) {
            return new Message(
                $this->tr('Model is not type of page')
            );
        }

        $parentId = $body->get(self::NAME_PARENT_ID);
        $parent = empty($parentId)
            ? null
            : Page::fromId($parentId);

        if (is_null($parent) && !empty($parentId)) {
            return new Message(
                $this->tr('Page parent not found')
            );
        }

        $model->setParent($parent);

        if (!is_null($error = $this->testTitlesAvailability($request, $model))) {
            return $error;
        }

        if (!is_null($error = $this->onSubmitPage($model, $action, $request))) {
            return $error;
        }

        if (!is_null($error = $this->onSubmitLocalizations($model, $request))) {
            return $error;
        }

        if (!is_null($behavior = $this->getTemplateBehavior($model))) {
            return $behavior->onSubmit($model, $action);
        }

        return null;
    }
}