<?php

namespace models\core\Language;
use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\locale\Locale;
use core\RouteChasmEnvironment;
use models\extensions\IsDefault\IsDefaultExtension;
use models\extensions\IsDefault\IsDefault;

/**
 * @property string $code
 */

#[Grid]
#[Table('core_language')]
#[Database(App::DATABASE)]
class Language extends Model implements IsDefault {
    public static function fromEnv(): static {
        $code = App::getEnvStatic()->getOrDie(RouteChasmEnvironment::LANGUAGE);
        $language = new static();

        $language->set([
            'code' => $code,
        ]);

        $language->notSavable();
        return $language;
    }

    public static function fromCode(string $code): ?static {
        return static::first(where: Query::infer("code = ?", [$code]));
    }



    use IsDefaultExtension;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING, primaryKey: true)]
    protected string $code;



    public function getLocale(): ?Locale {
        $locales = App::getInstance()->getLocales();

        if (!isset($locales[$this->code])) {
            throw new \RuntimeException("Locale '$this->code' is not loaded");
        }

        return $locales[$this->code];
    }
}