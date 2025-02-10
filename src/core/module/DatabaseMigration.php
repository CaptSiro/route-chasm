<?php

namespace core\module;

use core\database\Database;
use core\fs\Glob;

class DatabaseMigration {
    public function __construct(
        protected Database $database,
        protected array $versions,
        protected Glob $glob
    ) {}

    public function migrateDatabase(string $from, string $to): void {
        $start = array_search($from, $this->versions);
        if ($start === false) {
            $start = 0;
        }

        $end = array_search($to, $this->versions);
        if ($end === false) {
            return;
        }

        for ($i = $start; $i < $end + 1; $i++) {
            foreach ($this->glob->resolve($this->versions[$i]) as $import) {
                if (!is_readable($import)) {
                    continue;
                }

                $query = file_get_contents($import);
                $this->database->run($query);
            }
        }
    }
}