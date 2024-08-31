<?php

namespace sptf;

use Closure;
use Error;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use sptf\interfaces\Expect;
use sptf\structs\CaughtError;
use sptf\structs\CaughtException;
use sptf\structs\Context;
use sptf\structs\Expectation;
use sptf\structs\Func;
use sptf\structs\Result;
use sptf\structs\TestCaseHeader;

class Sptf {
    public static function expect(mixed $value): Expect {
        $e = new Expectation($value, debug_backtrace());
        Context::assert($e);
        return $e;
    }

    public static function testDirectory($dir): void {
        Context::init();

        $dir_iterator = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getFilename() === "." || $file->getFilename() === ".." || !str_ends_with($file->getFilename(), ".php")) {
                continue;
            }

            $p = $file->getRealPath();

            echo "<div><div class='file'>$p</div><div class='tests'>";
            require $p;

            $failed = count(array_filter(Context::getAssertions(), fn($x) => !$x->result()));
            if ($failed !== 0) {
                echo "</div><div class='danger'>Failed $failed tests";
            }

            echo "</div></div>";
        }
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

        $failed = [];
        $passed = 0;

        foreach (Context::getAssertions() as $assertion) {
            if ($assertion->result()) {
                $passed++;
                continue;
            }

            $failed[] = $assertion->error();
        }

        $outcome = TestOutcome::fromStats($passed, $failed);
        $header = new TestCaseHeader($outcome, $name, $time);

        $class = strtolower($outcome->value);
        echo "<div class='test $class'>";
        echo $header->html();

        switch ($outcome) {
            case TestOutcome::FAILED: {
                foreach ($failed as $fail) {
                    echo $fail->html();
                }

                break;
            }
            case TestOutcome::NONE: {
                echo "<div class='warning'>No assertions</div>";
                break;
            }
            case TestOutcome::PASSED: {
                echo "<div class='assertions'>$passed assertions</div>";
                break;
            }
        }

        echo "</div>";

        if ((Context::getIsPrintingAllowed() || $outcome !== TestOutcome::PASSED) && $printed !== false) {
            echo $printed;
        }
    }

    public static function allowPrinting(): void {
        Context::setIsPrintingAllowed(true);
    }

    protected static function testHeader(bool $outcome, string $name, float $time): string {
        $outcomeText = $outcome ? "PASS" : "FAIL";

        return "
            <div>
                <span class='outcome'>$outcomeText</span>
                <span class='name'>$name</span>
                <span class='time'>$time s</span>
            </div>
        ";
    }
}