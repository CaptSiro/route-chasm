<?php

namespace sptf\structs;

use ErrorException;
use sptf\interfaces\Assertion;

require_once __DIR__ . "/../interfaces/Assertion.php";

class Context {
    /** @var Assertion[] $assertions */
    static private array $assertions = [];
    static private float $start = 0.;
    static private float $end = 0.;
    static private int $suitesCount = 0;
    static private bool $isInitialized = false;
    static private bool $isPrintingAllowed = false;



    /**
     * @return Assertion[]
     */
    static function getAssertions(): array {
        return self::$assertions;
    }



    static function assert(Assertion $assertion): void {
        self::$assertions[] = $assertion;
    }



    /**
     * @return int
     */
    static function getSuitesCount(): int {
        return self::$suitesCount;
    }



    static function init(): void {
        $contents = ob_get_clean();

        echo "<!doctype html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0'>
                <meta http-equiv='X-UA-Compatible' content='ie=edge'>
                <title>Tests</title>
                
                <style>". file_get_contents(realpath(__DIR__ . '/../css/styles.css')) ."</style>
                <script>window.addEventListener('load', () => {". file_get_contents(realpath(__DIR__ . '/../scripts/script.js')) ."})</script>
            </head>
            <body>";

        if ($contents !== false) {
            echo $contents;
        }

        self::$isInitialized = true;
    }



    /**
     * @return float
     */
    public static function getTime(): float {
        return round((self::$end - self::$start) * 1000) / 1000;
    }



    static function startSuite(): void {
        if (self::$isInitialized === false) {
            self::init();
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



    static function stopSuite(): void {
        self::$end = microtime(true);
        self::$suitesCount++;

        restore_error_handler();
    }

    /**
     * @param bool $isPrintingAllowed
     */
    public static function setIsPrintingAllowed(bool $isPrintingAllowed): void {
        self::$isPrintingAllowed = $isPrintingAllowed;
    }

    public static function getIsPrintingAllowed(): bool {
        return self::$isPrintingAllowed;
    }
}