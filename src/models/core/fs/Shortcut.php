<?php

namespace models\core\fs;

use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Table;
use models\extensions\Name\NameExtension;

/**
 * @property string $name
 */

#[Database(App::DATABASE)]
#[Table('core_fs_shortcut')]
class Shortcut extends Model {
    use NameExtension;



    public static function fromFileHash(string $hash, string $name, bool $create = false): ?static {
        if (is_null($file = File::fromHash($hash))) {
            return null;
        }

        $where = Query::infer('id_fs_file = ? AND name = ?', $file->getId(), $name);
        if (!is_null($shortcut = static::first(where: $where))) {
            return $shortcut;
        }

        if (!$create) {
            return null;
        }

        return static::create([
            'fileId' => $file->getId(),
            'name' => $name
        ]);
    }



    #[Column('id_fs_shortcut', Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[Column('id_fs_file', Column::TYPE_INTEGER)]
    protected int $fileId;



    protected ?File $file;



    public function getFile(): ?File {
        if (!isset($this->file)) {
            $this->file = File::fromId($this->fileId);
        }

        return $this->file;
    }

    public function setFileRaw(mixed $fileId): static {
        $this->set(['fileId', $fileId]);
        $this->file = null;
        return $this;
    }

    public function setFile(File $file): static {
        $this->setFileRaw($file->getId());
        $this->file = $file;
        return $this;
    }
}