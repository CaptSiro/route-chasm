<?php

namespace components\Markdown;

use components\forms\Form;
use core\view\Html;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class MarkdownEditor implements ViewTemplate {
    use ViewTemplateRenderer;



    public function __construct(
        protected string $content,
        protected ?string $name = null
    ) {}



    protected function getTextArea(): string {
        $attributes = [
            'class' => 'data-markdown',
            'contenteditable' => 'true'
        ];

        if (!is_null($this->name)) {
            Form::importAssets();
            $attributes['name'] = $this->name;
            $attributes['data-extract'] = 'form_extractContentEditable';
        }

        return Html::wrap(
            'pre',
            $this->content,
            $attributes
        );
    }
}