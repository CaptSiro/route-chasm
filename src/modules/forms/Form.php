<?php

namespace modules\forms;

use components\core\Html\Html;
use core\view\Component;
use core\view\Render;
use modules\forms\controls\Control;
use retval\Result;

class Form extends Component {
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

    private static Html $hr;
    public static function hr(): Html {
        if (!isset(self::$hr)) {
            self::$hr = new Html('hr');
        }

        return self::$hr;
    }



    /** @var array<Render> */
    protected array $elements;



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
    }



    public function setNamespaceClass(string $class): self {
        return $this->setNamespace(self::ns($class));
    }

    public function setNamespace(string $namespace): self {
        $this->namespace = $namespace;
        return $this;
    }

    public function add(Render $control): self {
        $this->elements[] = $control;

        if ($control instanceof Control) {
            $control->bind($this);
        }

        return $this;
    }

    public function createId(string $name): string {
        if (is_null($this->namespace)) {
            return $name;
        }

        return $this->namespace ."__". $name;
    }

    public function validate(array $values): Result {
        $reason = "";
        $valid = [];

        foreach ($this->elements as $control) {
            if (!($control instanceof Control)) {
                continue;
            }

            $name = $control->getFieldName();
            if (is_null($name)) {
                continue;
            }

            $isValid = $control->validate($values[$name] ?? null, $reason);
            if ($isValid === false) {
                return Result::fail(new InvalidFormSubmissionExc($reason));
            }

            $valid[$name] = $values[$name];
        }

        return Result::success($valid);
    }
}