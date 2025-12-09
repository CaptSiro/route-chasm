<?php

namespace models\core\fs;

use components\core\Icon;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\DatabaseAction;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\fs\FileServer;
use core\fs\FileSystem;
use core\fs\FileSystemEntry;
use core\navigation\Destination;
use core\route\Path;
use core\RouteChasmEnvironment;
use core\url\Url;
use core\utils\Files;

/**
 * @property int $parentId
 * @property string $name
 * @property string $hash
 * @property string $type
 * @property string $extension
 * @property int $size
 */

#[Database(App::DATABASE)]
#[Table('core_fs_file')]
class File extends Model implements FileSystemEntry, Destination {
    public static function fromHash(string $hash): ?File {
        return static::first(
            where: Query::infer('hash = ?', $hash)
        );
    }

    public static function fromName(?Directory $parent, string $name): static {
        $where = $parent->isRoot()
            ? Query::infer('id_fs_parent IS NULL AND name = ?', $name)
            : Query::infer('id_fs_parent = ? AND name = ?', $parent->getId(), $name);

        return static::first(where: $where);
    }



    #[Column('id_fs_file', Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[Column('id_fs_parent', Column::TYPE_INTEGER, nullable: true)]
    protected ?int $parentId;

    #[Column(type: Column::TYPE_STRING)]
    protected string $name;

    #[Column(type: Column::TYPE_STRING)]
    protected string $hash;

    #[Column(type: Column::TYPE_STRING)]
    protected string $type;

    #[Column(type: Column::TYPE_STRING)]
    protected string $extension;

    #[Column(type: Column::TYPE_LONG)]
    protected int $size;



    protected ?Directory $parent;



    public function delete(): DatabaseAction {
        unlink($this->getRealPath());
        return parent::delete();
    }



    public function getRealPath(): string {
        return FileSystem::getRealPath($this);
    }

    public function getParent(): ?Directory {
        if ($this->parent == 0) {
            return FileSystem::getRoot();
        }

        if (!isset($this->parent)) {
            $this->parent = Directory::fromId($this->parentId);
        }

        return $this->parent;
    }

    public function setParent(Directory $directory): static {
        if ($directory->isRoot()) {
            $this->parent = FileSystem::getRoot();
            return $this->setParentRaw(null);
        }

        $this->parent = $directory;
        return $this->setParentRaw($directory->getId());
    }

    public function setParentRaw(mixed $parentId): static {
        $this->set(['parentId' => $parentId]);
        return $this;
    }

    public function createShortcut(string $shortcutName): Shortcut {
        $shortcut = new Shortcut();

        $shortcut->name = $shortcutName;
        $shortcut->setFile($this);
        $shortcut->save();

        return $shortcut;
    }

    public function getHumanReadableSize(): string {
        return Files::humanSize($this->size);
    }



    // FileSystemEntry
    public function renameEntry(string $name): static {
        if (!is_null(static::fromName($this->getParent(), $name))) {
            return $this;
        }

        $this->set(['name' => $name]);
        $this->save();
        return $this;
    }

    public function createRenameEntryUrl(): Url {
        return $this->getUrlToModel();
    }

    public function deleteEntry(): void {
        $this->delete();
    }

    public function createDeleteEntryUrl(): Url {
        return $this->getUrlToModel();
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
        $ret = [];
        $current = $this;

        while (true) {
            if (is_null($parent = $current->getParent())) {
                break;
            }

            $ret[] = $current = $parent;
        }

        return array_reverse($ret);
    }

    public function getEntryName(): string {
        return $this->name .'.'. $this->extension;
    }

    public function getEntryIcon(): string {
        if (str_starts_with($this->type, 'text')) {
            return Icon::nf('nf-fa-file_text');
        }

        if (str_starts_with($this->type, 'image')) {
            return Icon::nf('nf-fa-file_image');
        }

        if (str_starts_with($this->type, 'audio')) {
            return Icon::nf('nf-fa-file_audio');
        }

        return match ($this->type) {
            'application/zip' => Icon::nf('nf-fa-file_zip_o'),
            'application/xml' => Icon::nf('nf-fa-file_code'),
            default => Icon::nf('nf-fa-file'),
        };
    }



    // Destination
    public function getPathToSelf(string $alias): Path {
        return FileServer::getInstance()
            ->createFilePath($this);
    }

    public function getUrlToModel(string $fileServerMountAlias = RouteChasmEnvironment::MOUNT_FILE_SERVER): Url {
        $request = App::getInstance()->getRequest();
        $path = $this->getPathToSelf($fileServerMountAlias);
        $ret = $request
            ->getDomain()
            ->createUrl($path);

        $ret->getQuery()->load($request->getUrl()->getQuery()->toArray());
        return $ret;
    }
}