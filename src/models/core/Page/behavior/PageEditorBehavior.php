<?php

namespace models\core\Page\behavior;

use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Admin\Nexus\Editor\EditorBehaviorAction;
use components\core\Admin\Nexus\Editor\SetEditor;
use components\core\Admin\Page\AdminPageEditor;
use components\core\Html\Html;
use components\core\Message\Message;
use components\layout\Accordion\Accordion;
use components\layout\Column\Column;
use components\layout\Layout;
use components\layout\Tabs\Tabs;
use core\App;
use core\communication\Request;
use core\database\sql\Model;
use core\database\sql\ModelDescription;
use core\database\sql\Sql;
use core\forms\controls\HiddenField;
use core\forms\description\FormDescription;
use core\forms\Form;
use core\locale\LexiconUnit;
use core\pages\PageFactory;
use core\RouteChasmEnvironment;
use core\utils\Arrays;
use core\utils\Models;
use core\view\View;
use models\core\Language\Language;
use models\core\Navigation\NavigationContext;
use models\core\Navigation\Slug;
use models\core\Page\LocalizedPage;
use models\core\Page\Page;
use models\core\Page\PageMeta;

class PageEditorBehavior implements EditorBehavior {
    use LexiconUnit, SetEditor;

    public const NAME_LANGUAGE_ID = 'languageId';
    public const NAME_PARENT_ID = 'parentId';



    public function __construct(
        protected ?string $navigationContext = null,
        protected EditorBehavior $localization = new LocalizedPageEditorBehavior()
    ) {
        $this->setLexiconGroup(AdminPageEditor::LEXICON_GROUP);
    }



    public function initForm(Form $form, ?Model $model): ?View {
        $form->setBodyTransformer('form_json');
        $form->add(new HiddenField(self::NAME_PARENT_ID, App::getInstance()
            ->getRequest()
            ->getUrl()
            ->getQuery()
            ->get(RouteChasmEnvironment::QUERY_PAGE_PARENT, '')));
        return null;
    }

    public function addControls(Layout $layout, ?Model $model): ?View {
        /** @var ?Page $model */
        $error = FormDescription::extract(Page::class)
            ->addControls($pageFields = new Column(), $model);
        if (!is_null($error)) {
            return $error;
        }

        $layout->add(new Accordion($this->tr('General'), $pageFields));

        $tabs = [];
        $localizations = is_null($model)
            ? []
            : $model->getLocalizations();

        foreach (Language::all() as $language) {
            $error = $this->localization->addControls(
                $localizationFields = new Column(),
                $localizations[$language->getId()] ?? null
            );

            if (!is_null($error)) {
                return $error;
            }

            $localizationFields->add(new HiddenField(self::NAME_LANGUAGE_ID, $language->getId()));
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

        return null;
    }

    protected function getSlugParent(?Page $parent, int $languageId): ?int {
        if (is_null($parent)) {
            return null;
        }

        $localizations = $parent->getLocalizations();
        if (isset($localizations[$languageId])) {
            return $localizations[$languageId]->slugId;
        }

        $defaultLanguageId = Language::getDefault()?->getId() ?? 1; // todo
        if (isset($localizations[$defaultLanguageId])) {
            return $localizations[$defaultLanguageId]->slugId;
        }

        return null;
    }

    public function emptyTitles(array $titles): bool {
        foreach ($titles as $title) {
            if (!empty($title)) {
                return false;
            }
        }

        return true;
    }

    public function testTitleAvailability(Request $request, Page $page, ?Page $parent): ?View {
        $body = $request->getBody();
        $titles = $body->getStrict('title');
        $languageIds = $body->getStrict(self::NAME_LANGUAGE_ID);

        $localizations = $page->getLocalizations();

        $navigationContextId = NavigationContext::getContextId($this->navigationContext);
        /** @var array<int, Language> $languages */
        $languages = Models::identity(Language::all());

        foreach ($titles as $i => $title) {
            $languageId = intval($languageIds[$i]);
            if (!isset($languages[$languageId])) {
                $safe = Html::escape($title);
                return new Message($this->tr("Language for title '$safe' not found"));
            }

            $hasLocalization = isset($localizations[$languageId]);

            $slugParentId = !$hasLocalization
                ? $this->getSlugParent($parent, $languageId)
                : $localizations[$languageId]->getSlug()->parentId;

            $slug = Slug::fromSlugRaw(
                $languageId,
                $navigationContextId,
                $languages[$languageId]->getLocale()->formatUrlSegment($title),
                $slugParentId
            );

            if (!is_null($slug)) {
                if ($hasLocalization && $localizations[$languageId]->getSlug()->getId() === $slug->getId()) {
                    continue;
                }

                $safe = Html::escape($title);
                return new Message($this->tr("Title '$safe' is not unique"));
            }
        }

        return null;
    }

    protected function onSubmitPage(Page $page, EditorBehaviorAction $action, Request $request, ?Page $parent): ?View {
        $body = $request->getBody();

        $titles = $body->getStrict('title');
        if ($this->emptyTitles($titles)) {
            return new Message($this->tr('Page must have at least one title'));
        }

        $defaultLanguageId = App::getDefaultLanguage()->getId();
        $languageIds = $body->getStrict(self::NAME_LANGUAGE_ID);
        foreach ($languageIds as $i => $id) {
            if ($defaultLanguageId === intval($id)) {
                if (empty($titles[$i])) {
                    return new Message(
                        $this->tr('Page must have title for default language')
                    );
                }
            }
        }

        $languages = Arrays::changeKeys(
            Language::all(),
            fn(Language $x) => $x->getId()
        );

        $page->set($body->toArray());
        $page->setParent($parent);

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

        return null;
    }

    /**
     * @param array<string, mixed> $object
     * @param Page $page
     * @param Page|null $parent
     * @param array<int, Language> $languages
     * @param int $navigationContextId
     * @return void
     */
    protected function createLocalization(array $object, Page $page, ?Page $parent, array $languages, int $navigationContextId): void {
        $localizedPage = new LocalizedPage();
        $localizedPage->set($object);
        $localizedPage->pageId = $page->getId();

        $languageId = $localizedPage->languageId = intval($object[self::NAME_LANGUAGE_ID]);
        $language = $languages[$languageId];

        $slugParentId = $this->getSlugParent($parent, $languageId);
        $localizedPage->setSlug(PageFactory::getInstance()->createSlug(
            $languageId,
            $navigationContextId,
            $localizedPage->getSlugLiteral($language),
            $slugParentId,
            $page
        ));

        $localizedPage->save();

        $meta = new PageMeta();
        $meta->set($object);
        $meta->setLocalization($localizedPage);
        $meta->save();
    }

    protected function onSubmitLocalization(Page $page, EditorBehaviorAction $action, Request $request, ?Page $parent): ?View {
        $body = $request->getBody();
        $navigationContextId = NavigationContext::getContextId($this->navigationContext);
        /** @var array<int, Language> $languages */
        $languages = Models::identity(Language::all());

        $columns = array_merge(
            ModelDescription::extract(LocalizedPage::class)->getColumnAlias(),
            ModelDescription::extract(PageMeta::class)->getColumnAlias()
        );
        $columns[] = self::NAME_LANGUAGE_ID;

        $objects = Models::transpose(
            $body->toArray(),
            $columns,
            count($body->getStrict(self::NAME_LANGUAGE_ID)
        ));

        $localizations = $page->getLocalizations();

        foreach ($objects as $object) {
            if ($action === EditorBehaviorAction::CREATE) {
                if (empty($object['title'])) {
                    continue;
                }

                $this->createLocalization(
                    $object,
                    $page,
                    $parent,
                    $languages,
                    $navigationContextId
                );
            }

            if ($action === EditorBehaviorAction::UPDATE) {
                $languageId = intval($object[self::NAME_LANGUAGE_ID]);
                if (!isset($localizations[$languageId])) {
                    if (empty($object['title'])) {
                        continue;
                    }

                    $this->createLocalization(
                        $object,
                        $page,
                        $parent,
                        $languages,
                        $navigationContextId
                    );

                    continue;
                }

                $localization = $localizations[$languageId];
                if ($localization->title !== $object['title']) {
                    $localization->title = $object['title'];
                    $localization->save();

                    $slug = $localization->getSlug();
                    $slug->slug = $localization->getSlugLiteral($languages[$languageId]);
                    $slug->save();
                }

                $meta = PageMeta::fromLocalizedPage($localization) ?? new PageMeta();
                $meta->set($object);
                $meta->save();
            }
        }

        return null;
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        /** @var Page $model */

        $request = App::getInstance()->getRequest();
        $response = App::getInstance()->getResponse();
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

        if (!is_null($error = $this->testTitleAvailability($request, $model, $parent))) {
            return $error;
        }

        if (!is_null($error = $this->onSubmitPage($model, $action, $request, $parent))) {
            return $error;
        }

        if (!is_null($error = $this->onSubmitLocalization($model, $action, $request, $parent))) {
            return $error;
        }

        return null;
    }
}