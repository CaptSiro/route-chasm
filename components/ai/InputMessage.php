<?php

namespace components\ai;

use core\ResourceLoader;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;
use JsonSerializable;

class InputMessage implements ViewTemplate, JsonSerializable {
    use ViewTemplateRenderer, ResourceLoader;

    const ROLE_SYSTEM = 'system';
    const ROLE_USER = 'user';



    public static function template(string $path): string {
        return static::getStaticResource($path);
    }

    public static function system(string $template): InputMessage {
        $message = new static(self::ROLE_SYSTEM);
        $message->setTemplate($template);
        return $message;
    }



    public function __construct(
        protected string $role = self::ROLE_USER
    ) {}



    public function setRole(string $role): void {
        $this->role = $role;
    }



    // JsonSerializable
    public function jsonSerialize(): array {
        return [
            "role" => $this->role,
            "content" => trim($this->render())
        ];
    }
}