<?php

namespace core\tf;

use components\tf\CaughtError;
use components\tf\CaughtException;
use Error;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class Test {
    public const LEXICON_GROUP = 'unit-testing';

    public static function case(string $name, callable $suite): void {
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

    private static function evaluateFile(string $file): TestFile {
        require $file;

        return new TestFile($file, Context::getSuitesClean());
    }

    /**
     * @param string $entry File or Directory
     * @return array<TestFile>
     */
    public static function evaluate(string $entry): array {
        if (!file_exists($entry)) {
            return [];
        }

        Context::initialize();

        if (is_file($entry)) {
            return [self::evaluateFile($entry)];
        }

        $dir_iterator = new RecursiveDirectoryIterator($entry);
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

            $files[] = self::evaluateFile($file->getRealPath());
        }

        return $files;
    }
}