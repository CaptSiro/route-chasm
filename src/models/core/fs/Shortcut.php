<?php

namespace models\core\fs;

use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Table;
use models\extensions\Name\NameExtension;

#[Database(App::DATABASE)]
#[Table('core_fs_shortcut')]
class Shortcut extends Model {
    use NameExtension;



    public static function fromFileHash(string $hash, string $name, bool $create = false): ?static {
        if (is_null($file = File::fromHash($hash))) {
            return null;
        }

        $where = Query::infer('id_fs_file = ? AND name = ?', $file->id, $name);
        if (!is_null($shortcut = static::first(where: $where))) {
            return $shortcut;
        }

        if (!$create) {
            return null;
        }

        return static::create([
            'fileId' => $file->id,
            'name' => $name
        ]);
    }

    public static function submit(File $file, ?Shortcut $shortcut, string $shortcutName): void {
        if (is_null($shortcut)) {
            $shortcut = new Shortcut();

            $shortcut->name = $shortcutName;
            $shortcut->setFileRaw($file->id);

            $shortcut->save();
            return;
        }

        if ($shortcut->getFileId() === $file->id) {
            return;
        }

        $shortcut->setFile($file);
        $shortcut->save();
    }

    public static function submitHash(string $hash, string $shortcutName): void {
        $shortcut = Shortcut::fromName($shortcutName);
        if (empty($hash)) {
            $shortcut?->delete();
            return;
        }

        if (is_null($file = File::fromHash($hash))) {
            return;
        }

        static::submit($file, $shortcut, $shortcutName);
    }



    #[Column('id_fs_shortcut', Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[Column('id_fs_file', Column::TYPE_INTEGER)]
    public int $fileId;



    public ?File $file;



    public function getFileId(): int {
        return $this->fileId;
    }

    public function getFile(): ?File {
        if (!isset($this->file)) {
            $this->file = File::fromId($this->fileId);
        }

        return $this->file;
    }

    public function setFileRaw(int $fileId): static {
        $this->fileId = $fileId;
        $this->file = null;
        return $this;
    }

    public function setFile(File $file): static {
        $this->fileId = $file->id;
        $this->file = $file;
        return $this;
    }
}