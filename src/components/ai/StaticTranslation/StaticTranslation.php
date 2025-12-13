<?php

namespace components\ai\StaticTranslation;

use components\ai\InputMessage;
use models\core\Language\Language;
use models\core\Language\Lexicon\Phrase;

class StaticTranslation extends InputMessage {
    public function __construct(
        string $role,
        protected Phrase $phrase
    ) {
        parent::__construct($role);
        $this->setTemplate($this->getTemplateVariant(strtolower($role)));
    }



    public function getLanguages(): string {
        return json_encode(Language::getCodes(), JSON_UNESCAPED_UNICODE);
    }
}