<?php

namespace models\core\Language\Lexicon;

use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\forms\description\TextField;
use core\locale\Lexicon;
use core\utils\Arrays;
use models\core\Language\Language;

/**
 * @property string $group
 * @property string $default
 * @property bool $isDynamic
 */

#[Grid]
#[Table('core_lexicon')]
#[Database(App::DATABASE)]
class Phrase extends Model {
    /** @var array<string, array<string, static>> */
    private static array $groups = [];

    protected static function getGroup(string $group): array {
        if (isset(static::$groups[$group])) {
            return static::$groups[$group];
        }

        $phrases = static::all(where: Query::infer("group = ?", $group));
        $ret = [];

        foreach ($phrases as $phrase) {
            $ret[$phrase->default] = $phrase;
        }

        return static::$groups[$group] = $ret;
    }

    public static function create(string $group, string $default, bool $isDynamic = false): static {
        $instance = new static();

        $instance->set([
            "group" => $group,
            "default" => $default,
            "isDynamic" => $isDynamic,
        ]);

        $instance->save();
        return $instance;
    }

    /**
     * @param array<string, array<string>> $templates
     */
    public static function createTemplate(string $group, string $default, Language $language, array $templates = []): static {
        $instance = static::create($group, $default, true);

        foreach ($templates as $template => $rules) {
            $translation = Translation::create(
                $instance,
                $language,
                $template,
            );

            foreach ($rules as $rule) {
                $translation->addRule(Rule::fromRule($rule, doCreate: true));
            }
        }

        return $instance;
    }

    public static function fromPair(string $group, string $default, bool $doCreate = false): ?static {
        $g = static::getGroup($group);
        if (!isset($g[$default])) {
            if ($doCreate) {
                return static::$groups[$group][$default] = self::create($group, $default);
            }

            return null;
        }

        return $g[$default];
    }



    #[Column('id_phrase', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[TextField]
    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    protected string $group;

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
            foreach ($translation->getRules() as $rule) {
                if (!$rule->match($value)) {
                    continue 2;
                }
            }

            return $translation->format($value);
        }

        return null;
    }

    public function formatDefault(string $value): string {
        return Lexicon::format($this->default, $value);
    }
}