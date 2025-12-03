<?php

namespace models\core\fs;

use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\Table;
use models\extensions\Name\CachedNameExtension;

/**
 * @property string $name
 */

#[Database(App::DATABASE)]
#[Table('core_fs_shortcut')]
class Shortcut extends Model {
    use CachedNameExtension;

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
        return $this;
    }

    public function setFile(File $file): static {
        $this->file = $file;
        return $this;
    }
}