<?php

namespace models\core\Language;
use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use components\layout\Grid\description\GridDescription;
use components\layout\Grid\Loader\ModelGridLoader;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\DatabaseAction;
use core\database\sql\Model;
use core\database\sql\ModelCache;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\locale\Locale;
use core\RouteChasmEnvironment;
use core\view\View;
use models\extensions\IsDefault\IsDefaultTrait;
use models\extensions\IsDefault\IsDefault;
use RuntimeException;

#[Grid]
#[Table('core_language')]
#[Database(App::DATABASE)]
class Language extends Model implements IsDefault {
    use ModelCache;

    public static function getGridDescription(): GridDescription {
        $columns = [];

        static::addIsDefaultGridColumn($columns);
        $columns[LanguageProxy::COLUMN_LANGUAGE] = new GridColumn('Language');

        return new GridDescription(
            $columns,
            new ModelGridLoader(static::class, portionSize: ModelGridLoader::getPortionSizeSetting()),
            proxy: new LanguageProxy()
        );
    }

    public static function getCodes(): array {
        return array_map(
            fn(Language $x) => $x->code,
            self::all()
        );
    }

    public static function fromEnv(): static {
        $code = App::getEnvStatic()->getOrDie(RouteChasmEnvironment::ENV_LANGUAGE);
        $language = new static();

        $language->id = 0;
        $language->code = $code;

        $language->notSavable();
        return $language;
    }

    public static function fromCode(string $code): ?static {
        static::modelCache_loadAll(fn(Language $x) => $x->code);

        return static::modelCache_get($code)
            ?? static::first(where: Query::infer("code = ?", $code));
    }



    use IsDefaultTrait;

    #[Column('id_language', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    public string $code;



    public function save(): DatabaseAction|View {
        $ret = parent::save();

        if ($ret instanceof View) {
            return $ret;
        }

        $this->saveIsDefault();
        return $ret;
    }

    public function __toString(): string {
        return $this->code;
    }

    public function getHumanIdentifier(): string {
        return $this->code;
    }



    public function isEditable(): bool {
        return false;
    }

    public function getLocale(): ?Locale {
        $locales = App::getInstance()->getLocales();

        if (!isset($locales[$this->code])) {
            throw new RuntimeException("Locale '$this->code' is not loaded");
        }

        return $locales[$this->code];
    }
}