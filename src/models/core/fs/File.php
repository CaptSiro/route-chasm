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
use core\fs\variants\FileVariantTransformer;
use core\navigation\Destination;
use core\route\Path;
use core\RouteChasmEnvironment;
use core\url\Url;
use core\utils\Files;

#[Database(App::DATABASE)]
#[Table('core_fs_file')]
class File extends Model implements FileSystemEntry, Destination {
    public const TYPE_IMAGE = 'image';
    public const TYPE_IMAGE_GIF = 'image/gif';
    public const TYPE_IMAGE_JPEG = 'image/jpeg';
    public const TYPE_IMAGE_PNG = 'image/png';
    public const TYPE_IMAGE_AVIF = 'image/avif';
    public const TYPE_IMAGE_BMP = 'image/bmp';
    public const TYPE_IMAGE_WEBP = 'image/webp';



    public static function fromHash(string $hash): ?File {
        return static::first(where: Query::infer('hash = ?', $hash));
    }

    public static function fromName(?Directory $parent, string $name): static {
        $where = $parent->isRoot()
            ? Query::infer('id_fs_parent IS NULL AND name = ?', $name)
            : Query::infer('id_fs_parent = ? AND name = ?', $parent->id, $name);

        return static::first(where: $where);
    }

    public static function isChildOfQuery(Directory $directory): Query {
        return $directory->isRoot()
            ? Query::infer('id_fs_parent IS NULL')
            : Query::infer('id_fs_parent = ?', $directory->id);
    }

    public static function isTypeOfQuery(string $prefix): Query {
        return Query::infer('type LIKE ?', "$prefix/%");
    }



    #[Column('id_fs_file', Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[Column('id_fs_parent', Column::TYPE_INTEGER, nullable: true)]
    public ?int $parentId;

    #[Column(type: Column::TYPE_STRING)]
    public string $name;

    #[Column(type: Column::TYPE_STRING)]
    public string $hash;

    #[Column(type: Column::TYPE_STRING)]
    public string $type;

    #[Column(type: Column::TYPE_STRING)]
    public string $extension;

    #[Column(type: Column::TYPE_LONG)]
    public int $size;



    protected ?Directory $parent;
    /** @var array<Shortcut> */
    protected array $shortcuts;



    // Model
    public function getHumanIdentifier(): string {
        return $this->getFileName();
    }

    public function delete(): DatabaseAction {
        $result = parent::delete();
        if ($result === DatabaseAction::NONE) {
            return DatabaseAction::NONE;
        }

        unlink($this->getRealPath());

        foreach ($this->getShortcuts() as $shortcut) {
            $shortcut->delete();
        }

        return DatabaseAction::DELETE;
    }



    public function updateMetadata(string $filePath, bool $save = false): void {
        if (!file_exists($filePath)) {
            return;
        }

        $updated = false;
        if ($this->size !== $size = filesize($filePath)) {
            $this->size = $size;
            $updated = true;
        }

        if ($updated && $save) {
            $this->save();
        }
    }

    public function getFileName(): string {
        return $this->name .'.'. $this->extension;
    }

    public function isTypeOf(string $prefix): bool {
        return str_starts_with($this->type, $prefix .'/');
    }

    public function isImage(): bool {
        return $this->isTypeOf(self::TYPE_IMAGE);
    }

    public function getRealPath(): string {
        return FileSystem::getRealPath($this);
    }

    public function getRealDirectory(): string {
        return FileSystem::getRealDirectory($this);
    }

    public function isChildOf(Directory $directory): bool {
        return $this->isChildOfRaw($directory->id);
    }

    public function isChildOfRaw(int $directoryId): bool {
        return $this->parentId === $directoryId;
    }

    public function getParent(): ?Directory {
        if ($this->parentId == 0) {
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
        return $this->setParentRaw($directory->id);
    }

    public function setParentRaw(mixed $parentId): static {
        $this->parentId = $parentId;
        return $this;
    }

    public function createShortcut(string $shortcutName): Shortcut {
        $shortcut = new Shortcut();

        $shortcut->name = $shortcutName;
        $shortcut->setFile($this);
        $shortcut->save();

        return $shortcut;
    }

    /**
     * @return array<Shortcut>
     */
    public function getShortcuts(): array {
        if (isset($this->shortcuts)) {
            return $this->shortcuts;
        }

        return $this->shortcuts = Shortcut::all(
            where: Query::infer('id_fs_file = ?', $this->id)
        );
    }

    public function getHumanReadableSize(): string {
        return Files::humanSize($this->size);
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
        if ($this->isTypeOf('text')) {
            return Icon::nf('nf-fa-file_text', 'Text');
        }

        if ($this->isTypeOf('image')) {
            return Icon::nf('nf-fa-file_image', 'Image');
        }

        if ($this->isTypeOf('audio')) {
            return Icon::nf('nf-fa-file_audio', 'Audio');
        }

        return match ($this->type) {
            'application/zip' => Icon::nf('nf-fa-file_zip_o', 'Zip'),
            'application/xml' => Icon::nf('nf-fa-file_code', '</>'),
            default => Icon::nf('nf-fa-file', 'File'),
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

        $ret->loadTransitiveQueries($request->getUrl()->getQuery());
        return $ret;
    }

    public function getUrl(
        ?FileVariantTransformer $transformer = null,
        string $fileServerMountAlias = RouteChasmEnvironment::MOUNT_FILE_SERVER
    ): Url {
        $url = $this->getUrlToModel($fileServerMountAlias);
        
        if (is_null($transformer)) {
            return $url;
        }

        $url->setQueryArgument(
            RouteChasmEnvironment::QUERY_FS_VARIANT,
            FileSystem::createVariantIdentifier($transformer)
        );

        return $url;
    }
}