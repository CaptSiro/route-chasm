<?php

namespace models\core\Language\Lexicon;

use components\core\Icon;
use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use components\layout\Row\Row;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\SideEffect;
use core\database\sql\Sql;
use core\database\sql\Table;
use core\forms\controls\Button\Button;
use core\forms\controls\HiddenField;
use core\forms\controls\Select\Select;
use core\forms\controls\TextField;
use core\locale\Lexicon;
use core\utils\Models;
use core\view\View;
use models\core\Language\Language;
use RuntimeException;

/**
 * @property int $phraseId
 * @property int $languageId
 * @property string $translation
 * @property ?int $ruleId
 */

#[Grid]
#[Table('core_lexicon_translation')]
#[Database(App::DATABASE)]
class Translation extends Model {
    public const NAME_TRANSLATION_ID = 'translationId';
    public const NAME_LANGUAGE_ID = 'languageId';
    public const NAME_TRANSLATION = 'translation';
    public const NAME_RULE_ID = 'ruleId';
    public const CSS_CLASS_TRANSLATION_CONTROL = 'translation-control';



    public static function createTranslation(Phrase $phrase, Language $language, string $translation, ?Rule $rule = null): static {
        return static::createTranslationRaw(
            $phrase->id,
            $language->id,
            $translation,
            $rule?->id
        );
    }

    public static function createTranslationRaw(int $phraseId, int $languageId, string $translation, ?int $ruleId = null): static {
        $properties = [
            'phraseId' => $phraseId,
            'languageId' => $languageId,
            'translation' => $translation,
        ];

        if (!is_null($ruleId)) {
            $properties['ruleId'] = $ruleId;
        }

        return static::create($properties);
    }

    public static function getControlNames(): array {
        return [
            self::NAME_TRANSLATION_ID,
            self::NAME_LANGUAGE_ID,
            self::NAME_TRANSLATION,
            self::NAME_RULE_ID,
        ];
    }

    public static function createDynamicTranslationControl(
        int $languageId,
        ?Translation $translation = null,
        ?string $deletionAccumulator = null,
    ): View {
        $row = new Row();
        $row->addCssClass(self::CSS_CLASS_TRANSLATION_CONTROL);

        $row->add(new HiddenField(self::NAME_TRANSLATION_ID, $translation?->id));
        $row->add(new HiddenField(
            self::NAME_LANGUAGE_ID,
            Models::get($translation, 'languageId', $languageId)
        ));

        $row->add(new TextField(
            self::NAME_TRANSLATION,
            'Translation',
            Models::getString($translation, 'translation')
        ));

        $row->add(new Select(
            self::NAME_RULE_ID,
            'Rule',
            Rule::options(),
            Models::get($translation, 'ruleId')
        ));

        $remove = new Button(Icon::nf('nf-fa-remove', 'X'));

        $remove->addAttribute('data-control', self::CSS_CLASS_TRANSLATION_CONTROL);
        $remove->addJavascriptInit('phraseEditor_removeTranslationButton');

        if (!is_null($translation)) {
            $remove->addAttribute('data-id', $translation->id);
            $remove->addAttribute('data-accumulator', $deletionAccumulator);
        }

        $row->add($remove);

        return $row;
    }

    public static function createStaticTranslationControl(int $languageId, ?Translation $translation = null): View {
        $row = new Row();

        $row->add(new HiddenField(self::NAME_TRANSLATION_ID, $translation?->id));
        $row->add(new HiddenField(
            self::NAME_LANGUAGE_ID,
            Models::get($translation, 'languageId', $languageId)
        ));

        $row->add(new TextField(
            self::NAME_TRANSLATION,
            'Translation',
            Models::getString($translation, 'translation')
        ));

        return $row;
    }

    public static function forPhrase(Phrase $phrase): array {
        return static::forPhraseRaw($phrase->id);
    }

    public static function forPhraseRaw(int $phraseId): array {
        return static::all(
            where: Query::infer('id_phrase = ?', $phraseId)
        );
    }



    #[Column('id_translation', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[Column('id_phrase', type: Column::TYPE_INTEGER)]
    public int $phraseId;

    #[Column('id_language', type: Column::TYPE_INTEGER)]
    public int $languageId;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    public string $translation;

    #[Column('id_rule', type: Column::TYPE_INTEGER)]
    public ?int $ruleId;

    protected Phrase $phrase;
    protected ?Rule $rule;



    public function setPhraseModel(Phrase $phrase): void {
        $this->phrase = $phrase;
    }

    public function setPhrase(Phrase $phrase): void {
        $this->setPhraseModel($phrase);
        $this->phraseId = $phrase->id;
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

    public function getRule(): ?Rule {
        if (!$this->getPhrase()->isDynamic) {
            return null;
        }

        if (!isset($this->rule)) {
            $this->rule = Rule::fromId($this->ruleId);
        }

        return $this->rule;
    }

    public function setRule(Rule $rule): static {
        $this->setRuleId($rule->id);
        return $this;
    }

    public function setRuleId(int $ruleId): SideEffect {
        $description = static::getDescription();

        return Sql::update($description->getEscapedTable())
            ->set('id_rule', Parameter::infer($ruleId))
            ->where(Query::infer('id_translation = ?', $this->id))
            ->run($description->getConnection());
    }

    public function format(string $value): string {
        return Lexicon::format($this->translation, $value);
    }
}