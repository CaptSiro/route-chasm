<?php

namespace core\tf;

use ErrorException;

class Context {
    /** @var array<Assertion> $assertions */
    protected static array $assertions = [];

    protected static float $start = 0.0;
    protected static float $end = 0.0;
    protected static int $suitesCount = 0;

    protected static bool $isInitialized = false;
    protected static bool $isPrintingAllowed = false;

    protected static array $suites = [];



    /**
     * @return array<Assertion>
     */
    public static function getAssertions(): array {
        return self::$assertions;
    }

    public static function assert(Assertion $assertion): void {
        self::$assertions[] = $assertion;
    }

    public static function getSuitesCount(): int {
        return self::$suitesCount;
    }

    public static function initialize(): void {
        self::$isInitialized = true;
    }

    public static function getTime(): float {
        return round((self::$end - self::$start) * 1000) / 1000;
    }

    public static function startSuite(): void {
        if (self::$isInitialized === false) {
            self::initialize();
        }

        self::$assertions = [];
        self::$start = microtime(true);

        set_error_handler(function($severity, $message, $file, $line) {
            if (0 === error_reporting()) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });
    }

    public static function stopSuite(): void {
        self::$end = microtime(true);
        self::$suitesCount++;

        restore_error_handler();
    }

    public static function addSuite(Suite $suite): void {
        self::$suites[] = $suite;
    }

    public static function getSuitesClean(): array {
        $suites = self::$suites;
        self::$suites = [];
        return $suites;
    }

    public static function setIsPrintingAllowed(bool $isPrintingAllowed): void {
        self::$isPrintingAllowed = $isPrintingAllowed;
    }

    public static function getIsPrintingAllowed(): bool {
        return self::$isPrintingAllowed;
    }
}