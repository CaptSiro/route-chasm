<?php

namespace models\core\Language\Lexicon;

use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\Phrase\AdminPhraseEditor;
use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\ModelDescription;
use core\database\sql\Table;
use core\forms\description\TextField;
use core\locale\Lexicon;
use core\utils\Arrays;
use models\core\Language\Language;
use models\core\Language\Lexicon\Grid\LexiconGridRow;

/**
 * @property string $group
 * @property string $default
 * @property bool $isDynamic
 */

#[Grid]
#[Table('core_lexicon')]
#[Database(App::DATABASE)]
class Phrase extends Model {
    public static function getNexus(): AdminNexus {
        return (new AdminNexus(
            ModelDescription::extract(Phrase::class),
            new AdminPhraseEditor(new PhraseEditorBehavior()),
            LexiconGridRow::getGridDescription()
        ))->showCreateButton(false);
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
            "groupId" => $lexiconGroup->getId(),
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



    #[Column('id_phrase', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[TextField]
    #[GridColumn]
    #[Column('id_lexicon_group', type: Column::TYPE_STRING)]
    protected int $groupId;

    #[TextField]
    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    protected string $default;

    #[GridColumn]
    #[Column('is_dynamic', type: Column::TYPE_BOOLEAN)]
    protected bool $isDynamic;

    /** @var array<Translation> */
    private array $translations;
    /** @var array<Translation> */
    private array $staticTranslations;
    private LexiconGroup $group;



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
            $language->getId(),
            $translation,
            $rule?->getId()
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
            $this->getId(),
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
        return $this->translateRaw($language->getId());
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
        return $this->translateTemplateRaw($value, $language->getId());
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