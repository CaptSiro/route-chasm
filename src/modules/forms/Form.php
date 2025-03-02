<?php

namespace modules\forms;

use components\core\Html\Html;
use core\view\Component;
use core\view\View;

class Form extends Component {
    private static ?Form $form = null;

    public static function rendering(): ?Form {
        return self::$form;
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
        $this->elements = [];
        $this->bodyTransformer = FormTransformer::TRANSFORMER_FORM_DATA;
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

    public function add(View $control): self {
        $this->elements[] = $control;
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