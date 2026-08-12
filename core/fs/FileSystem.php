<?php

namespace core\fs;

use Closure;
use components\Admin\FileSystemCreateDirectory;
use components\fs\FileSystemDropArea;
use components\fs\FileSystemGridFactory;
use components\layout\BreadCrumbs\BreadCrumbs;
use components\layout\Grid\description\GridColumn;
use components\layout\Grid\GridLayoutFactory;
use components\Message\Message;
use components\nexus\Nexus;
use components\nexus\NexusActions;
use components\nexus\NexusHeader;
use core\App;
use core\communication\UploadedFile;
use core\storage\Data;
use core\database\sql\ModelDescription;
use core\fs\variants\FileVariant;
use core\fs\variants\FileVariantTransformer;
use core\fs\variants\ImageVariant;
use core\locale\Lexicon;
use core\ResourceLoader;
use core\route\Path;
use core\RouteChasmEnvironment;
use core\utils\Files;
use core\utils\Php;
use core\view\Component;
use core\view\Html;
use core\view\View;
use locales\EnglishUS;
use models\fs\Directory;
use models\fs\File;
use models\fs\Shortcut;
use models\Language\Language;
use models\UserResource;

class FileSystem {
    use ResourceLoader;

    public const LEXICON_GROUP = 'file-system';



    public static function createApi(): string {
        $fs = FileServer::getInstance();
        return Html::wrapUnsafe(
            'script',
            json_encode([
                'fileUrl' => $fs->createFileUrl(),
                'downloadUrl' => $fs->createDownloadUrl(),
                'infoUrl' => $fs->createInfoUrl(),
                'variantQuery' => RouteChasmEnvironment::QUERY_FS_VARIANT,
                'fileTypeQuery' => RouteChasmEnvironment::QUERY_FS_FILE_TYPE,
                'directoryUrl' => $fs->createDirectoryUrl(),
                'imageVariantUrl' => $fs->createVariantUrl(ImageVariant::getInstance()),
            ]),
            [
                'type' => 'application/json',
                'id' => 'api-file-system'
            ]
        );
    }



    /** @var $factories array<FileVariant> */
    private static array $variants = [];

    public static function registerVariant(FileVariant $variant): void {
        self::$variants[$variant->getName()] = $variant;
    }

    public static function getVariant(string $name): ?FileVariant {
        Php::run(self::getSelfResource("variants.php"));
        return self::$variants[$name] ?? null;
    }

    public static function getVariants(): array {
        return self::$variants;
    }

    public static function createVariantIdentifier(FileVariantTransformer $transformer): string {
        return $transformer->getFileVariant()->getName() .':'. $transformer->getTransformer();
    }

    public static function getLocation(): string {
        return Data::namespace(RouteChasmEnvironment::FILE_SYSTEM_NAMESPACE);
    }

    public static function getRoot(): Directory {
        return Directory::getRoot();
    }

    public static function getRealPath(File $file): string {
        $offset = RouteChasmEnvironment::FILE_SYSTEM_DIRECTORY_HASH_OFFSET;
        $dir = substr($file->hash, 0, $offset);
        $f = substr($file->hash, $offset);

        return Path::join(self::getLocation(), $dir, $f);
    }

    public static function getRealDirectory(File $file): string {
        $offset = RouteChasmEnvironment::FILE_SYSTEM_DIRECTORY_HASH_OFFSET;
        $dir = substr($file->hash, 0, $offset);

        return Path::join(self::getLocation(), $dir);
    }

    public static function createDirectoryLinkAttributes(string $url): array {
        return [
            "x-get" => $url,
            "x-target" => ".file-select-window .nexus",
            "x-swap" => "outer",
            "class" => "link no-decoration",
        ];
    }



    public static function resolve(string $shortcut): ?File {
        return Shortcut::fromName($shortcut)?->getFile();
    }



    public static function storeUploadedFile(Directory $directory, UploadedFile $file): ?File {
        if (empty($path = $file->getPath())) {
            return null;
        }

        $hash = hash_file(RouteChasmEnvironment::FILE_SYSTEM_HASH_ALGORITHM, $path);
        if (!is_null($found = File::fromHash($hash))) {
            if (!$found->isChildOf($directory)) {
                $found->setParent($directory);
                $found->save();
            }

            return $found;
        }

        $entry = new File();

        [$name, $extension] = Files::split($file->getName());
        $entry->name = $name;
        $entry->type = $file->getType();
        $entry->extension = $extension;
        $entry->hash = $hash;
        $entry->size = $file->getSize();

        if (!is_null($error = $file->move($entry->getRealPath()))) {
            return null;
        }

        $entry->setParent($directory);
        $entry->save();
        return $entry;
    }

    public static function storeFile(Directory $directory, string $filePath): ?File {
        if (!file_exists($filePath)) {
            return null;
        }

        $hash = hash_file(RouteChasmEnvironment::FILE_SYSTEM_HASH_ALGORITHM, $filePath);
        if (!is_null($found = File::fromHash($hash))) {
            if (!$found->isChildOf($directory)) {
                $found->setParent($directory);
                $found->updateMetadata($filePath);
                $found->save();
            } else {
                $found->updateMetadata($filePath, true);
            }

            return $found;
        }

        $entry = new File();

        [$name, $extension] = Files::split($filePath);
        $entry->name = $name;
        $entry->type = mime_content_type($filePath);
        $entry->extension = $extension;
        $entry->hash = $hash;
        $entry->size = filesize($filePath);

        $location = dirname($entryPath = $entry->getRealPath());
        if (!file_exists($location)) {
            mkdir($location, recursive: true);
        }

        if (!copy($filePath, $entryPath)) {
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

    public static function makeDirectoryRecursive(Directory $parent, string|Path $path): Directory {
        $currentParent = $parent;

        foreach (Path::resolve($path) as $name) {
            $currentParent = self::makeDirectory($currentParent, $name);
        }

        return $currentParent;
    }



    protected static function getMessageDirectoryNotFound(): string {
        return Lexicon::translate(
            self::LEXICON_GROUP,
            'Could not find directory',
            Language::fromLocale(EnglishUS::getInstance())
        );
    }

    public static function getUserResource(): UserResource {
        return UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_FILE_SYSTEM);
    }

    public static function getNexus(): Component {
        $messageDirectoryNotFound = self::getMessageDirectoryNotFound();

        $directoryId = App::getInstance()
            ->getRequest()
            ->getUrl()
            ->getQuery()
            ->get(RouteChasmEnvironment::QUERY_FILE_SYSTEM_DIRECTORY);

        $directory = empty($directoryId) || $directoryId == 0
            ? static::getRoot()
            : Directory::fromId($directoryId);

        if (is_null($directory)) {
            return new Message($messageDirectoryNotFound);
        }

        $directoryUrlProvider = function (Directory $directory) {
            $url = App::getInstance()->getRequest()->getUrl()->copy();
            $url->setQueryArgument(RouteChasmEnvironment::QUERY_FILE_SYSTEM_DIRECTORY, $directory->id);

            return Html::createLinkUnsafe(
                $url,
                Html::escape($directory->getEntryName())
            );
        };

        $nexus = Nexus::fromBehavior(
            ModelDescription::extract(File::class),
            new FileSystemEntryEditorBehavior(),
            self::listDirectory($directory, $directoryUrlProvider),
        );

        $header = new NexusHeader($nexus);
        $header
            ->setTemplateSlot($header::SLOT_ITEM, new FileSystemCreateDirectory($directory))
            ->removeTitle();

        $nexus
            ->setUserResource(self::getUserResource())
            ->setTemplateSlot(Nexus::SLOT_HEADER, $header)
            ->setTemplateSlot(Nexus::SLOT_BREAD_CRUMBS, static::generateBreadCrumbs(
                $directory,
                fn(Directory $x) => self::getBreadCrumbUrl($x)
            ));

        return $nexus;
    }

    public static function listDirectoryModal(
        ?Directory $directory = null,
        ?string $fileType = null,
        bool $readonly = false
    ): View {
        $messageDirectoryNotFound = self::getMessageDirectoryNotFound();

        if (is_null($directory)) {
            $directoryId = App::getInstance()
                ->getRequest()
                ->getUrl()
                ->getQuery()
                ->get(RouteChasmEnvironment::QUERY_FILE_SYSTEM_DIRECTORY);

            $directory = empty($directoryId) || $directoryId == 0
                ? static::getRoot()
                : Directory::fromId($directoryId);
        }

        if (is_null($directory)) {
            return new Message($messageDirectoryNotFound);
        }

        $directoryLinkProvider = fn(Directory $directory) => Html::wrapUnsafe(
            "button",
            Html::escape($directory->getEntryName()),
            self::createDirectoryLinkAttributes(
                FileServer::getInstance()->createDirectoryUrl($directory)
            )
        );

        $nexus = new Nexus(
            NexusActions::fromBehavior(
                ModelDescription::extract(File::class),
                new FileSystemEntryEditorBehavior(),
            )
        );

        $breadCrumbs = static::generateBreadCrumbs(
            $directory,
            fn(Directory $x) => self::getBreadCrumbUrl($x)
        );

        $nexus
            ->setTemplateSlot($nexus::SLOT_HEADER, null)
            ->setTemplateSlot($nexus::SLOT_OVERVIEW, $nexus::createOverviewFromGridLayout(
                $nexus,
                self::listDirectory(
                    $directory,
                    $directoryLinkProvider,
                    $fileType,
                    $readonly,
                ),
                false
            ))
            ->setTitle($directory->name)
            ->setTemplateSlot($nexus::SLOT_BREAD_CRUMBS, $breadCrumbs
                ->setItemTemplate($breadCrumbs->getResource("BreadCrumb_fs.phtml")));

        if (!$readonly) {
            $nexus->setTemplateSlot(
                $nexus::SLOT_BREAD_CRUMBS,
                new FileSystemCreateDirectory($directory)
            );
        }

        return $nexus;
    }

    public static function listDirectory(
        Directory $directory,
        Closure $directoryLinkProvider,
        ?string $fileType = null,
        bool $readonly = false
    ): GridLayoutFactory {
        return new FileSystemGridFactory(
            new FileSystemDropArea($directory, readonly: $readonly),
            [
                'name' => new GridColumn('Name'),
                'size' => new GridColumn('Size', '96px')
            ],
            new FileSystemEntryProxy($directoryLinkProvider),
            new FileSystemGridLoader($directory, $fileType)
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

    public static function getBreadCrumbUrl(Directory $directory): string {
        $url = App::getInstance()->getRequest()->getUrl()->copy();
        $url->setQueryArgument(RouteChasmEnvironment::QUERY_FILE_SYSTEM_DIRECTORY, $directory->id);
        return $url->toString();
    }
}