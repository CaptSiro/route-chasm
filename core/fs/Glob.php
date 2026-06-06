<?php

namespace core\fs;

use Generator;

class Glob {
    public function __construct(
        protected string $root,
        protected ?string $extension = null,
        protected bool $recursive = false
    ) {
        if (isset($this->extension[0]) && $this->extension[0] === '.') {
            $this->extension = substr($this->extension, 1);
        }
    }

    protected function ext(): string {
        if (is_null($this->extension)) {
            return '';
        }

        return '.'. $this->extension;
    }

    public function resolve(string $path): Generator {
        $file = $this->root .'/'. $path . $this->ext();
        if (is_file($file)) {
            yield $file;
            return;
        }

        $dir = $this->root .'/'. $path;
        if (!is_dir($this->root .'/'. $path)) {
            return;
        }

        $ext = $this->ext();
        foreach (scandir($dir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            if (is_null($this->extension)) {
                yield $dir .'/'. $entry;
                continue;
            }

            if (str_ends_with($entry, $ext)) {
                yield $dir .'/'. $entry;
            }
        }
    }
}