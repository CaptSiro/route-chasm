<?php

namespace components\forms\controls;

use core\locale\LexiconUnit;
use core\view\Attribute;
use core\view\HtmlAttribute;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class FileControl implements Control, Attribute, ViewTemplate {
    use ViewTemplateRenderer, FormControl, FormControlInfo, HtmlAttribute, LexiconUnit;

    public const LEXICON_GROUP = 'form.drop-zone';



    public function __construct(
        protected string $name = self::class,
        protected string $label = '',
        bool $multiple = false
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
        $this->multiple($multiple);
    }



    public function accept(string $types): static {
        return $this->addAttribute('accept', $types);
    }

    public function multiple(bool $multiple = true): static {
        if (!$multiple) {
            return $this->removeAttribute('multiple');
        }

        return $this->addAttribute('multiple');
    }

    public function isMultiple(): bool {
        return $this->hasAttribute('multiple');
    }
}