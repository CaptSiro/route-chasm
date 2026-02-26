<?php

namespace components\core\Markdown\Editor;

use components\core\Html\Html;
use core\forms\controls\TextArea\TextArea;
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
        if (is_null($this->name)) {
            return Html::wrap(
                'pre',
                $this->content,
                [
                    'class' => 'data-markdown',
                    'contenteditable' => 'true'
                ]
            );
        }

        Form::importAssets();
        $area = new TextArea($this->name, '', $this->content);
        $area->addAttribute('rows', '50');
        $area->addCssClass('data-markdown');
        return $area;
    }
}