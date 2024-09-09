<?php

namespace components\resources\Cards;

use core\App;
use core\database\parameter\Primitive;
use core\Render;
use core\Resource;
use core\Singleton;
use tables\Card;

class Cards extends Resource {
    use Singleton;



    public function __construct() {
        App::getInstance()
            ->link(__DIR__ ."/cards.css");
        parent::__construct();
    }



    protected function getTable(): string {
        return Card::class;
    }

    public function index(?array $models = null): Render {
        $id = new Primitive(18);
        return parent::index(Card::fetchAll());
    }
}