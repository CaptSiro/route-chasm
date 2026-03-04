<?php

namespace models\core\Language\Lexicon;

use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\Phrase\AdminPhraseEditor;
use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\DatabaseAction;
use core\database\sql\Model;
use core\database\sql\ModelDescription;
use core\database\sql\Table;
use core\forms\description\TextField;
use core\locale\Lexicon;
use core\utils\Arrays;
use models\core\Language\Language;
use models\core\Language\Lexicon\Grid\LexiconGridRow;

#[Grid]
#[Table('core_lexicon')]
#[Database(App::DATABASE)]
class Phrase extends Model {
    public static function getNexus(): AdminNexus {
        return (new AdminNexus(
            ModelDescription::extract(Phrase::class),
            new AdminPhraseEditor(),
            LexiconGridRow::getGridDescription()
        ))
            ->showCreateButton(false);
    }

    /** @var array<string, array<string, static>> */
    private static array $groups = [];

    protected static function getGroup(string $group): array {
        if (isset(static::$groups[$group])) {
            return static::$groups[$group];
        }

        $phrases = LexiconGroup::fromName($group, create: true)->getPhrases();
        $ret = [];

        foreach ($phrases as $phrase) {
            $ret[$phrase->default] = $phrase;
        }

        return static::$groups[$group] = $ret;
    }

    public static function createPhrase(string $group, string $default, bool $isDynamic = false): static {
        $lexiconGroup = LexiconGroup::fromName($group, create: true);

        return static::create([
            "groupId" => $lexiconGroup->id,
            "default" => $default,
            "isDynamic" => $isDynamic,
        ]);
    }


    /**
     * @param string $group
     * @param string $default
     * @param Language $language
     * @param array<string, string> $templates
     * @return static
     */
    public static function createTemplate(string $group, string $default, Language $language, array $templates = []): static {
        $instance = static::createPhrase($group, $default, true);

        foreach ($templates as $rule => $translation) {
            $instance->addTranslation(
                $language,
                $translation,
                Rule::fromRule($rule, create: true)
            );
        }

        return $instance;
    }

    public static function fromPair(string $group, string $default, bool $create = false): ?static {
        $g = static::getGroup($group);
        if (!isset($g[$default])) {
            if ($create) {
                return static::$groups[$group][$default] = self::createPhrase($group, $default);
            }

            return null;
        }

        return $g[$default];
    }



    #[Column('id_phrase', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[TextField]
    #[GridColumn]
    #[Column('id_lexicon_group', type: Column::TYPE_STRING)]
    public int $groupId;

    #[TextField]
    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    public string $default;

    #[GridColumn]
    #[Column('is_dynamic', type: Column::TYPE_BOOLEAN)]
    public bool $isDynamic;

    /** @var array<Translation> */
    private array $translations;
    /** @var array<Translation> */
    private array $staticTranslations;
    private LexiconGroup $group;



    // Model
    public function getHumanIdentifier(): string {
        return $this->default;
    }

    public function delete(): DatabaseAction {
        $group = $this->getLexiconGroup();

        $this->deleteTranslations();
        $ret = parent::delete();

        if ($group->getPhraseCount() === 0) {
            $group->delete();
        }

        return $ret;
    }

    public function deleteTranslations(): DatabaseAction {
        if (empty($translations = $this->getTranslations())) {
            return DatabaseAction::NONE;
        }

        foreach ($translations as $translation) {
            $translation->delete();
        }

        return DatabaseAction::DELETE;
    }



    public function getLexiconGroup(): LexiconGroup {
        if (!isset($this->group)) {
            $this->group = LexiconGroup::fromId($this->groupId);
        }

        return $this->group;
    }

    public function getTranslations(): array {
        if (!isset($this->translations)) {
            $this->translations = Translation::forPhrase($this);

            if (!$this->isDynamic) {
                $this->staticTranslations = Arrays::changeKeys(
                    $this->translations,
                    fn(Translation $x) => $x->languageId
                );
            }
        }

        return $this->translations;
    }

    public function addTranslation(Language $language, string $translation, ?Rule $rule = null): Translation {
        return $this->addTranslationRaw(
            $language->id,
            $translation,
            $rule?->id
        );
    }

    public function addTranslationRaw(int $languageId, string $translation, ?int $ruleId = null): Translation {
        foreach ($this->getTranslations() as $t) {
            $equal = $t->languageId === $languageId
                && $t->ruleId === $ruleId
                && $t->translation === $translation;
            if ($equal) {
                return $t;
            }
        }

        $t = Translation::createTranslationRaw(
            $this->id,
            $languageId,
            $translation,
            $ruleId
        );

        $this->translations[] = $t;
        if (!$this->isDynamic) {
            $this->staticTranslations[$languageId] = $t;
        }

        return $t;
    }

    public function translate(Language $language): ?string {
        return $this->translateRaw($language->id);
    }

    public function translateRaw(int $languageId): ?string {
        $this->getTranslations();
        $translations = $this->staticTranslations;
        if (!isset($translations[$languageId])) {
            return null;
        }

        return $translations[$languageId]->translation;
    }

    public function translateTemplate(string $value, Language $language): ?string {
        return $this->translateTemplateRaw($value, $language->id);
    }

    public function translateTemplateRaw(string $value, int $languageId): ?string {
        $translations = $this->getTranslations();

        foreach ($translations as $translation) {
            if ($translation->languageId !== $languageId) {
                continue;
            }

            $translation->setPhraseModel($this);
            if ($translation->getRule()->match($value)) {
                return $translation->format($value);
            }
        }

        return null;
    }

    public function formatDefault(string $value): string {
        return Lexicon::format($this->default, $value);
    }
}