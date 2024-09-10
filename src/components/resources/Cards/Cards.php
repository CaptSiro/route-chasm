<?php

namespace components\resources\Cards;

use core\App;
use core\database\parameter\Primitive;
use core\Render;
use core\Resource;
use core\Singleton;
use core\url\UrlBuilder;
use modules\SideLoader\Css;
use modules\SideLoader\Javascript;
use modules\SideLoader\SideLoader;
use tables\Card;

class Cards extends Resource {
    use Singleton;



    public function __construct() {
        Css::import(__DIR__ ."/cards.css");
        Css::import(__DIR__ ."/dark.css");
        Javascript::import(__DIR__ .'/cards.js');
        parent::__construct();
    }



    protected function getTable(): string {
        return Card::class;
    }

    public function index(?array $models = null): Render {
        $id = new Primitive(18);
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