<?php

namespace models\core\fs;

use components\core\Html\Html;
use components\core\Icon;
use core\App;
use core\communication\Request;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\DatabaseAction;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\fs\FileServer;
use core\fs\FileSystem;
use core\fs\FileSystemEntry;
use core\RouteChasmEnvironment;
use core\url\Url;

#[Database(App::DATABASE)]
#[Table('core_fs_directory')]
class Directory extends Model implements FileSystemEntry {
    protected static Directory $root;

    public static function getRoot(): Directory {
        if (isset(static::$root)) {
            return static::$root;
        }

        $dir = new static();

        $dir->id = 0;
        $dir->parentId = 0;
        $dir->name = 'Files';

        $dir->notSavable();
        return static::$root = $dir;
    }

    public static function fromRequest(Request $request): Directory {
        $directoryId = $request
            ->getUrl()
            ->getQuery()
            ->get(RouteChasmEnvironment::QUERY_FILE_SYSTEM_DIRECTORY);

        if (is_null($directoryId)) {
            return FileSystem::getRoot();
        }

        return Directory::fromId($directoryId)
            ?? FileSystem::getRoot();
    }

    public static function fromName(Directory $parent, string $name): ?Directory {
        $where = $parent->isRoot()
            ? Query::infer('id_fs_parent IS NULL AND name = ?', $name)
            : Query::infer('id_fs_parent = ? AND name = ?', $parent->id, $name);

        return static::first(where: $where);
    }



    #[Column('id_fs_directory', Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[Column('id_fs_parent', Column::TYPE_INTEGER, nullable: true)]
    public ?int $parentId;

    #[Column(type: Column::TYPE_STRING)]
    public string $name;



    protected array $subDirectories;
    protected array $files;
    protected ?Directory $parent;



    // Model
    public function getHumanIdentifier(): string {
        return $this->name;
    }

    public function delete(): DatabaseAction {
        foreach ($this->getFiles() as $file) {
            $file->delete();
        }

        foreach ($this->getSubDirectories() as $directory) {
            $directory->delete();
        }

        return parent::delete();
    }



    public function isRoot(): bool {
        $id = $this->getId();
        return is_null($id) || $id === 0;
    }

    /**
     * @return array<Directory>
     */
    public function getSubDirectories(): array {
        if (!isset($this->subDirectories)) {
            $this->subDirectories = static::all(
                where: $this->isRoot()
                    ? Query::infer('id_fs_parent IS NULL')
                    : Query::infer('id_fs_parent = ?', $this->id)
            );
        }

        return $this->subDirectories;
    }

    /**
     * @return array<File>
     */
    public function getFiles(): array {
        if (!isset($this->files)) {
            $this->files = File::all(
                where: $this->isRoot()
                    ? Query::infer('id_fs_parent IS NULL')
                    : Query::infer('id_fs_parent = ?', $this->id)
            );
        }

        return $this->files;
    }

    public function filterFiles(string $type): array {
        return array_filter($this->getFiles(), fn(File $x) => $x->isTypeOf($type));
    }

    public function getParent(): ?Directory {
        if (!isset($this->parent)) {
            $this->parent = Directory::fromId($this->parentId) ?? FileSystem::getRoot();
        }

        return $this->parent;
    }

    public function setParent(Directory $directory): static {
        if ($directory->isRoot()) {
            $this->parent = FileSystem::getRoot();
            return $this->setParentRaw(null);
        }

        $this->parent = $directory;
        return $this->setParentRaw($directory->id);
    }

    public function setParentRaw(mixed $parentId): static {
        $this->parentId = $parentId;
        return $this;
    }



    // FileSystemEntry
    public function renameEntry(string $name): static {
        if (!is_null(static::fromName($this->getParent(), $name))) {
            return $this;
        }

        $this->name = $name;
        $this->save();

        return $this;
    }

    public function createRenameEntryUrl(): Url {
        return FileServer::getInstance()
            ->createDirectoryUrl($this);
    }

    public function deleteEntry(): void {
        $this->delete();
    }

    public function createDeleteEntryUrl(): Url {
        return FileServer::getInstance()
            ->createDirectoryUrl($this);
    }

    public function moveEntry(Directory $destination): static {
        $this->setParent($destination);
        $this->save();
        return $this;
    }

    /**
     * @return array<Directory>
     */
    public function getParents(): array {
        if ($this->isRoot()) {
            return [];
        }

        $ret = [];
        $current = $this;

        while (true) {
            if (is_null($parent = $current->getParent())) {
                break;
            }

            $ret[] = $current = $parent;

            if ($current->isRoot()) {
                break;
            }
        }

        return array_reverse($ret);
    }

    public function getEntryName(): string {
        return $this->name;
    }

    public function getEntryIcon(): string {
        return Html::wrapUnsafe(
            'span',
            Icon::nf('nf-fa-folder', 'Folder'),
            ['class' => 'folder-color']
        );
    }
}