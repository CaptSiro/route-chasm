<?php

namespace components\core\Markdown\Editor;

use components\core\Html\Html;
use core\forms\Form;
use core\view\Renderer;
use core\view\View;

class MarkdownEditor implements View {
    use Renderer;



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