<?php

namespace core\configs;

use core\App;
use core\database\sql\Config as SqlConfig;
use core\http\HttpCode;
use core\route\compiler\RouteCompiler;
use core\route\compiler\RouteCompilerConfig;
use core\route\Path;
use core\RouteChasmEnvironment;
use core\utils\Strings;
use dotenv\Env;

class EnvConfig implements Config {
    protected RouteCompiler $routeCompiler;



    public function __construct(
        protected Env $env
    ) {
        $routeCompilerConfig = new RouteCompilerConfig();
        $routeCompilerConfig
            ->setAnyRegex(
                $this->env->get("ROUTING_ANY") ?? RouteCompilerConfig::REGEX_ANY
            )
            ->setMergeConsecutiveSlashes(Strings::fromHumanReadableBoolean(
                $this->env->get("ROUTING_MERGE_SLASHES") ?? true
            ))
            ->setIdentRegex(
                $this->env->get("ROUTING_IDENT") ?? RouteCompilerConfig::REGEX_IDENT
            );

        $this->routeCompiler = new RouteCompiler($routeCompilerConfig);
    }



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



    public function getSqlConfig(): SqlConfig {
        return new SqlConfig(
            $this->getOrDie(RouteChasmEnvironment::ENV_DATABASE_HOST),
            $this->getOrDie(RouteChasmEnvironment::ENV_DATABASE_NAME),
            $this->getOrDie(RouteChasmEnvironment::ENV_DATABASE_USER),
            $this->getOrDie(RouteChasmEnvironment::ENV_DATABASE_PASSWORD),
            $this->env->get(RouteChasmEnvironment::ENV_DATABASE_PORT) ?? "3306",
            $this->env->get(RouteChasmEnvironment::ENV_DATABASE_CHARSET) ?? "UTF8",
        );
    }

    public function getPublicDirectory(): string {
        $dir = $this->env->get('PUBLIC') ?? 'public';

        return App::getInstance()
            ->getSource(Path::join('..', $dir));
    }

    public function getRouteCompiler(): RouteCompiler {
        return $this->routeCompiler;
    }
}