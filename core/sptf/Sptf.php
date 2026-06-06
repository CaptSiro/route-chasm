<?php

namespace core\sptf;

use Closure;
use core\sptf\interfaces\Expect;
use core\sptf\structs\CaughtError;
use core\sptf\structs\CaughtException;
use core\sptf\structs\Context;
use core\sptf\structs\Expectation;
use core\sptf\structs\Func;
use core\sptf\structs\Result;
use core\sptf\structs\Suite;
use core\sptf\structs\SuiteOutput;
use core\sptf\structs\TestFile;
use Error;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class Sptf {
    public static function expect(mixed $value): Expect {
        $e = new Expectation($value, debug_backtrace());
        Context::assert($e);
        return $e;
    }

    public static function fail(string $reason = ""): void {
        $result = new Result(false, debug_backtrace());

        if ($reason !== "") {
            $result->setMessage($reason);
        }

        Context::assert($result);
    }

    public static function func(Closure $fn, bool $propagateExceptions = false): Func {
        return new Func($fn, $propagateExceptions);
    }

    public static function pass(): void {
        Context::assert(new Result(true, debug_backtrace()));
    }

    public static function test(string $name, callable $suite): void {
        Context::startSuite();
        Context::setIsPrintingAllowed(false);

        ob_start();

        try {
            $suite();
        } catch (Exception $exception) {
            Context::assert(new CaughtException($exception));
        } catch (Error $error) {
            Context::assert(new CaughtError($error));
        }

        $printed = ob_get_clean();

        Context::stopSuite();
        $time = Context::getTime();

        Context::addSuite(
            new Suite(
                $name,
                $time,
                Context::getAssertions(),
                new SuiteOutput(
                    Context::getIsPrintingAllowed(),
                    $printed !== false
                        ? $printed
                        : ''
                )
            )
        );
    }

    public static function allowPrinting(): void {
        Context::setIsPrintingAllowed(true);
    }



    /**
     * @param $dir
     * @return array<TestFile>
     */
    public static function evaluateDirectory($dir): array {
        Context::init();

        $dir_iterator = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

        $files = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            $isNotValidTestFile = !$file->isFile()
                || $file->getFilename() === "."
                || $file->getFilename() === ".."
                || !str_ends_with($file->getFilename(), ".php");
            if ($isNotValidTestFile) {
                continue;
            }

            $p = $file->getRealPath();
            require $p;

            $files[] = new TestFile(
                $p,
                Context::getSuitesClean()
            );
        }

        return $files;
    }
}