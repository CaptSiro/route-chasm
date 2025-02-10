<?php

namespace core\config;

use core\App;
use core\database\pdo\config\BasicPdoConfig;
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
                ->error(
                    "Environment variable '$property' is not defined and it is required",
                    HttpCode::SE_INTERNAL_SERVER_ERROR
                );
        }

        return $value;
    }



    public function getPdoConfig(): BasicPdoConfig {
        return new BasicPdoConfig(
            $this->getOrDie("DATABASE_HOST"),
            $this->getOrDie("DATABASE_NAME"),
            $this->getOrDie("DATABASE_USER"),
            $this->getOrDie("DATABASE_PASSWORD"),
            $this->env->get("DATABASE_PORT") ?? "3306",
            $this->env->get("DATABASE_CHARSET") ?? "UTF8",
        );
    }
}