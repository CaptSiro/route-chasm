<?php

namespace core\fs;

use components\core\Admin\FileSystem\AdminFileSystemCreateDirectory;
use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\Nexus\Editor\AdminNexusEditor;
use components\core\BreadCrumbs\BreadCrumbs;
use components\core\FileSystem\FileSystemDropArea;
use components\core\FileSystem\FileSystemGridFactory;
use components\core\Message\Message;
use components\layout\Grid\description\GridColumn;
use components\layout\Grid\GridLayoutFactory;
use core\actions\Action;
use core\App;
use core\communication\UploadedFile;
use core\data\Data;
use core\database\sql\ModelDescription;
use core\locale\Lexicon;
use core\route\Path;
use core\RouteChasmEnvironment;
use core\utils\Files;
use models\core\fs\Directory;
use models\core\fs\File;
use models\core\fs\Shortcut;

class FileSystem {
    public const LEXICON_GROUP = 'file-system';



    public static function getLocation(): string {
        return Data::namespace(RouteChasmEnvironment::FILE_SYSTEM_NAMESPACE);
    }

    public static function getRoot(): Directory {
        return Directory::getRoot();
    }

    public static function getRealPath(File $file): string {
        $hash = $file->hash;

        $offset = RouteChasmEnvironment::FILE_SYSTEM_DIRECTORY_HASH_OFFSET;
        $dir = substr($file->hash, 0, $offset);
        $f = substr($file->hash, $offset);

        return Path::join(self::getLocation(), $dir, $f);
    }



    public static function resolve(string $shortcut): ?File {
        return Shortcut::fromName($shortcut)?->getFile();
    }



    public static function storeUploadedFile(Directory $directory, UploadedFile $file): ?File {
        if (is_null($path = $file->getPath())) {
            return null;
        }

        $hash = hash_file(RouteChasmEnvironment::FILE_SYSTEM_HASH_ALGORITHM, $path);
        if (!is_null($found = File::fromHash($hash))) {
            return $found;
        }

        $entry = new File();

        [$name, $extension] = Files::split($file->getName());
        $entry->name = $name;
        $entry->type = $file->getType();
        $entry->extension = $extension;
        $entry->hash = $hash;
        $entry->size = $file->getSize();

        if ($file->move($entry->getRealPath())->isFailure()) {
            return null;
        }

        $entry->setParent($directory);
        $entry->save();
        return $entry;
    }

    public static function makeDirectory(Directory $parent, string $name): Directory {
        if (!is_null($found = Directory::fromName($parent, $name))) {
            return $found;
        }

        $directory = new Directory();
        $directory->name = trim($name);

        $directory->setParent($parent);
        $directory->save();

        return $directory;
    }



    public static function getNexus(): Action {
        $directoryId = App::getInstance()
            ->getRequest()
            ->getUrl()
            ->getQuery()
            ->get(RouteChasmEnvironment::QUERY_FILE_SYSTEM_DIRECTORY);

        $directory = empty($directoryId) || $directoryId == 0
            ? static::getRoot()
            : Directory::fromId($directoryId);

        if (is_null($directory)) {
            return new Message(
                Lexicon::translate(self::LEXICON_GROUP, 'Could not find directory')
            );
        }

        $nexus = new AdminNexus(
            ModelDescription::extract(File::class),
            new AdminNexusEditor(new FileSystemEntryEditorBehavior()),
            new FileSystemGridFactory(
                new FileSystemDropArea($directory, readonly: false),
                [
                    'name' => new GridColumn('Name'),
                    'size' => new GridColumn('Size', '96px')
                ],
                new FileSystemEntryProxy(),
                new FileSystemGridLoader($directory)
            )
        );

        $nexus->showCreateButton(false)
            ->setHeaderContent(new AdminFileSystemCreateDirectory($directory))
            ->setBreadCrumbs($bc = static::generateBreadCrumbs(
                $directory,
                function (Directory $x) {
                    $url = App::getInstance()->getRequest()->getUrl()->copy();
                    $url->setQueryArgument(RouteChasmEnvironment::QUERY_FILE_SYSTEM_DIRECTORY, $x->getId());
                    return $url->toString();
                }
            ));


        return $nexus;
    }

    public static function listDirectory(Directory $directory, bool $readonly = false): GridLayoutFactory {
        return new FileSystemGridFactory(
            new FileSystemDropArea($directory, readonly: $readonly),
            ['name' => new GridColumn('Name')],
            new FileSystemEntryProxy(),
            new FileSystemGridLoader($directory)
        );
    }

    public static function generateBreadCrumbs(FileSystemEntry $entry, callable $urlCreator): BreadCrumbs {
        $crumbs = [];

        $path = $entry->getParents();
        $path[] = $entry;

        foreach ($path as $parent) {
            $crumbs[$urlCreator($parent)] = $parent->getEntryName();
        }

        return BreadCrumbs::from($crumbs);
    }
}