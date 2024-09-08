<?php

namespace core\config;

use components\core\HttpError\HttpError;
use core\App;
use core\http\HttpCode;
use dotenv\Env;

class EnvConfig implements Config {
    public function __construct(
        protected Env $env
    ) {}



    protected function getOrDie(string $property): string {
        $value = $this->env->get($property);

        if ($value === null) {
            App::getInstance()
                ->getResponse()
                ->render(new HttpError(
                    "Enviroment variable $property is not defined and it is required",
                    HttpCode::SE_INTERNAL_SERVER_ERROR
                ));
        }

        return $value;
    }



    public function getDatabaseHost(): string {
        return $this->getOrDie("DATABASE_HOST");
    }

    public function getDatabasePort(): string {
        return $this->env->get("DATABASE_PORT") ?? "3306";
    }

    public function getDatabaseName(): string {
        return $this->getOrDie("DATABASE_NAME");
    }

    public function getDatabaseCharset(): string {
        return $this->env->get("DATABASE_CHARSET") ?? "UTF8";
    }

    public function getDatabaseUser(): string {
        return $this->getOrDie("DATABASE_USER");
    }

    public function getDatabasePassword(): string {
        return $this->getOrDie("DATABASE_PASSWORD");
    }
}