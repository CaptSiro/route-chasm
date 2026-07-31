<?php

namespace components\forms;

use components\forms\controls\MultiSelect;
use components\forms\controls\Select;
use components\html\Attribute;
use components\html\HtmlAttribute;
use core\locale\Lexicon;
use core\locale\LexiconTranslator;
use core\sideloader\importers\Css\Css;
use core\sideloader\importers\Javascript\Javascript;
use core\view\Component;
use core\view\Container;
use core\view\ContainerTrait;
use core\view\Html;

class Form extends Component implements Container, Attribute {
    use HtmlAttribute, ContainerTrait;

    const LEXICON_GROUP = 'forms';



    private static ?Form $form = null;

    public static function rendering(): ?Form {
        return self::$form;
    }

    private static bool $imported = false;

    public static function importAssets(): bool {
        if (self::$imported) {
            return false;
        }

        self::$imported = true;
        Css::import(Form::getStaticResource('Form.css'));
        Javascript::import(Form::getStaticResource('Form.js'));

        Select::importAssets();
        MultiSelect::importAssets();

        return true;
    }



    public static function getLexiconTranslator(): LexiconTranslator {
        return Lexicon::group(self::LEXICON_GROUP);
    }

    public static function ns(string $class): string {
        return strtr(strtolower($class), "\\", "-");
    }

    public static function note(string $content): string {
        return Html::wrap("p", $content, ['class' => 'form-note']);
    }

    public static function title(string $content): string {
        return Html::wrap("h2", $content, ['class' => 'form-title']);
    }

    public static function hr(): string {
        return '<hr>';
    }



    protected string $bodyTransformer;



    /**
     * @param string $method
     * @param string|null $action If unset than it is sent to the same url where form is located
     * @param string|null $namespace
     */
    public function __construct(
        protected readonly string $method,
        protected readonly ?string $action = null,
        protected ?string $namespace = null,
    ) {
        parent::__construct();
        $this->bodyTransformer = FormTransformer::TRANSFORMER_FORM_DATA;
        $this->addJavascriptInit('form_init');
    }



    public function noStyles(): static {
        return $this->addCssClass('form-no-styles');
    }

    /**
     * @param string $javascriptFunction (HTMLFormElement, Response) => void
     * @return $this
     */
    public function setOnSubmitSuccess(string $javascriptFunction): static {
        $this->addAttribute('data-on-submit-success', $javascriptFunction);
        return $this;
    }

    /**
     * @param string $javascriptFunction (HTMLFormElement, Response) => void
     * @return $this
     */
    public function setOnSubmitFailure(string $javascriptFunction): static {
        $this->addAttribute('data-on-submit-failure', $javascriptFunction);
        return $this;
    }

    public function setBodyTransformer(string $javascriptFunction): static {
        $this->bodyTransformer = $javascriptFunction;
        return $this;
    }

    public function setNamespaceClass(string $class): self {
        return $this->setNamespace(self::ns($class));
    }

    public function setNamespace(string $namespace): self {
        $this->namespace = $namespace;
        return $this;
    }

    public function createId(string $name): string {
        if (is_null($this->namespace)) {
            return $name;
        }

        return $this->namespace ."__". $name;
    }



    public function render(): string {
        $last = self::$form;
        self::$form = $this;

        $ret = parent::render();

        self::$form = $last;
        return $ret;
    }
}