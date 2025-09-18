<?php

namespace components\layout\Grid\description;

use Attribute;
use core\locale\LexiconUnit;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GridColumn {
    use LexiconUnit;

    public const LEXICON_GROUP = 'grid.labels';



    public function __construct(
        public ?string $label = null,
        public string $template = '1fr',
        protected bool $isFirst = false,
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function bindProperty(ReflectionProperty $property): void {
        $this->label ??= $this->tr(ucfirst($property->getName()));
    }

    public function isFirst(): bool {
        return $this->isFirst;
    }
}