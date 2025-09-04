<?php

namespace models\core\Language\Lexicon;

use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\SideEffect;
use core\database\sql\Sql;
use core\database\sql\Table;
use core\locale\Lexicon;
use models\core\Language\Language;
use RuntimeException;

/**
 * @property int $phraseId
 * @property int $languageId
 * @property string $translation
 */

#[Grid]
#[Table('core_lexicon_translation')]
#[Database(App::DATABASE)]
class Translation extends Model {
    public const TABLE_TRANSLATION_X_RULE = 'core_lexicon_translation_x_rule';



    public static function createTranslation(Phrase $phrase, Language $language, string $translation): static {
        return static::createTranslationRaw(
            $phrase->getId(),
            $language->getId(),
            $translation,
        );
    }

    public static function createTranslationRaw(int $phraseId, int $languageId, string $translation): static {
        return static::create([
            'phraseId' => $phraseId,
            'languageId' => $languageId,
            'translation' => $translation,
        ]);
    }

    public static function forPhrase(Phrase $phrase): array {
        return static::forPhraseId($phrase->getId());
    }

    public static function forPhraseId(int $phraseId): array {
        return static::all(
            where: Query::infer('id_phrase = ?', $phraseId)
        );
    }



    #[Column('id_translation', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[Column('id_phrase', type: Column::TYPE_INTEGER)]
    protected int $phraseId;

    #[Column('id_language', type: Column::TYPE_INTEGER)]
    protected int $languageId;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    protected string $translation;

    protected array $rules;

    protected Phrase $phrase;



    public function setPhraseModel(Phrase $phrase): void {
        $this->phrase = $phrase;
    }

    public function getPhrase(): Phrase {
        if (isset($this->phrase)) {
            return $this->phrase;
        }

        $phrase = Phrase::fromId($this->phraseId);
        if (is_null($phrase)) {
            throw new RuntimeException("Phrase for translation '$this->translation' does not exist");
        }

        return $this->phrase = $phrase;
    }

    /**
     * @return array<Rule>
     */
    public function getRules(): array {
        if (!$this->getPhrase()->isDynamic) {
            return [];
        }

        if (!isset($this->rules)) {
            $this->rules = Rule::forTranslation($this);
        }

        return $this->rules;
    }

    public function addRule(Rule $rule): static {
        $this->addRuleId($rule->getId());
        return $this;
    }

    public function addRuleId(int $rule): SideEffect {
        $description = static::getDescription();
        $tr = $description->connection->getDriver()->escapeTable(self::TABLE_TRANSLATION_X_RULE);

        return Sql::insert($tr)
            ->columns(['id_translation', 'id_rule'])
            ->value([Parameter::infer($this->getId()), Parameter::infer($rule)])
            ->run($description->connection);
    }

    public function format(string $value): string {
        return Lexicon::format($this->translation, $value);
    }
}