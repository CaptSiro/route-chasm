<?php

namespace components\resources\Cards;

use core\database\pdo\parameter\PdoPrimitiveParam;
use core\Resource;
use core\Singleton;
use core\url\UrlBuilder;
use core\view\Render;
use entities\Card;

class Cards extends Resource {
    use Singleton;



    public function __construct() {
        parent::__construct();
    }



    protected function getTable(): string {
        return Card::class;
    }

    public function index(?array $models = null): Render {
        $id = new PdoPrimitiveParam(18);
        return parent::index(Card::fetchAll());
    }

    public function createUrl(Card $card, ?UrlBuilder $builder = null): string {
        return ($builder ?? $this->getUrl(Resource::URL_READ))
            ->clean()
            ->setParam(Resource::PARAM_UNIQUE, $card->getId())
            ->setFragment($card->question)
            ->build();
    }
}