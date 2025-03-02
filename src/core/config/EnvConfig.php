<?php

namespace core\config;

use core\App;
use core\database\sql\config\BasicSqlConfig;
use core\http\HttpCode;
use core\path\Path;
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
                ->sendMessage(
                    "Environment variable '$property' is not defined and it is required",
                    HttpCode::SE_INTERNAL_SERVER_ERROR
                );
        }

        return $value;
    }



    public function getSqlConfig(): BasicSqlConfig {
        return new BasicSqlConfig(
            $this->getOrDie("DATABASE_HOST"),
            $this->getOrDie("DATABASE_NAME"),
            $this->getOrDie("DATABASE_USER"),
            $this->getOrDie("DATABASE_PASSWORD"),
            $this->env->get("DATABASE_PORT") ?? "3306",
            $this->env->get("DATABASE_CHARSET") ?? "UTF8",
        );
    }

    public function getPublicDirectory(): string {
        $dir = $this->env->get('PUBLIC') ?? 'public';

        return App::getInstance()
            ->getSource(Path::join('..', $dir));
    }
}