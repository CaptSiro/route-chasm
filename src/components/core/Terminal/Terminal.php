<?php

namespace components\core\Terminal;

use components\core\CallStack\CallStack;
use core\App;
use core\communication\Response;
use core\Singleton;
use core\view\BufferTransform;
use core\view\Component;

class Terminal extends Component {
    use Singleton;

    public const DEBUG_DEV = 'DEV';
    public const TEMPLATE_PLACEHOLDER = '<!-- route-chasm-core:terminal --->';

    public static function dump(mixed ...$var): void {
        $instance = self::getInstance();
        foreach ($var as $item) {
            $instance->varDump($item);
        }
    }

    public static function trace(): void {
        self::getInstance()
            ->addMessage((new CallStack(1))->render());
    }



    private bool $hasBeenRendered = false;
    public function __construct() {
        parent::__construct();

        App::getInstance()
            ->on(Response::EVENT_OB_TRANSFORM, function (BufferTransform $buffer) {
                if (!$this->hasBeenRendered) {
                    return;
                }

                $replacement = parent::render();

                $buffer->setContents(
                    str_replace(self::TEMPLATE_PLACEHOLDER, $replacement, $buffer->getContents())
                );
            });
    }



    protected array $messages;

    public function addMessage(string $message): void {
        $this->messages[] = $message;
    }

    public function varDump(mixed $var): void {
        ob_start();
        var_dump($var);
        $this->messages[] = ob_get_clean();
    }

    public function shouldDisplay(): bool {
        $isDev = boolval(App::getInstance()
            ->getEnv()
            ->get(self::DEBUG_DEV) ?? true);

        return $isDev && !empty($this->messages);
    }

    public function render(): string {
        $this->hasBeenRendered = true;
        return self::TEMPLATE_PLACEHOLDER;
    }
}