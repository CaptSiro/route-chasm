<?php

namespace core\forms;

use components\core\Html\Html;
use components\layout\Layout;
use core\html\Attribute;
use core\html\HtmlAttribute;
use core\sideloader\importers\Css\Css;
use core\sideloader\importers\Javascript\Javascript;
use core\view\Component;
use core\view\View;

class Form extends Component implements Layout, Attribute {
    use HtmlAttribute;



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
        Css::import(Form::getStaticResource('form.css'));
        Javascript::import(Form::getStaticResource('form.js'));

        return true;
    }



    public static function ns(string $class): string {
        return strtr(strtolower($class), "\\", "-");
    }

    public static function note(string $content): Html {
        return new Html(
            'p',
            ['class' => 'form-note'],
            $content
        );
    }

    public static function title(string $content): Html {
        return new Html(
            'h2',
            ['class' => 'form-title'],
            $content
        );
    }

    private static Html $hr;
    public static function hr(): Html {
        if (!isset(self::$hr)) {
            self::$hr = new Html('hr');
        }

        return self::$hr;
    }



    /** @var array<View> */
    protected array $elements;
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
        $this->elements = [];
        $this->bodyTransformer = FormTransformer::TRANSFORMER_FORM_DATA;
        $this->addJavascriptInit('form_init');
    }



    public function setOnSubmitSuccess(string $javascriptFunction): static {
        $this->addAttribute('data-on-submit-success', $javascriptFunction);
        return $this;
    }

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

    public function add(View $child): static {
        $this->elements[] = $child;
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