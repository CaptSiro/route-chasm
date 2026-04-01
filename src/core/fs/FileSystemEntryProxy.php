<?php

namespace core\fs;

use Closure;
use components\core\Admin\Nexus\NexusProxy;
use components\core\Html\Html;
use components\core\Icon;
use core\fs\variants\FileVariantTransformer;
use core\fs\variants\ImageVariant;
use core\ResourceLoader;
use core\sideloader\importers\Css\Css;
use core\sideloader\importers\Javascript\Javascript;
use core\utils\Strings;
use models\core\fs\Directory;
use models\core\fs\File;

class FileSystemEntryProxy extends NexusProxy {
    use ResourceLoader;

    protected static bool $imported = false;
    protected static function import(): void {
        if (self::$imported) {
            return;
        }

        Javascript::import(static::getStaticResource('fs.js'));
        Css::import(static::getStaticResource('fs.css'));
        self::$imported = true;
    }



    protected FileVariantTransformer $imageTransformer;

    public function __construct(
        protected Closure $directoryLinkProvider
    ) {
        self::import();
        $this->imageTransformer = ImageVariant::get(ImageVariant::TRANSFORMER_FULL_HD);
    }



    public function getValue(string $name): string {
        if ($name === 'size') {
            if ($this->item instanceof File) {
                return Html::wrap('span', $this->item->getHumanReadableSize());
            }

            return '';
        }

        if ($name === "name") {
            if ($this->item instanceof Directory) {
                $link = ($this->directoryLinkProvider)($this->item);

                return Html::wrapUnsafe(
                    'div',
                    $this->item->getEntryIcon() . $link,
                    ['class' => 'row']
                );
            }

            if ($this->item instanceof File) {
                $link = Html::createLinkUnsafe(
                    $this->item->isImage()
                        ? $this->item->getUrl($this->imageTransformer)
                        : $this->item->getUrlToModel(),
                    Html::escape($this->item->getEntryName()),
                    '_blank'
                );

                return Html::wrapUnsafe(
                    'div',
                    $this->item->getEntryIcon() . $link,
                    [
                        'class' => 'row',
                        'data-file-hash' => $this->item->hash
                    ]
                );
            }

            return json_encode($this->item) . ' is not FileSystemEntry';
        }

        return parent::getValue($name);
    }

    protected function createEditValue(?string $url): string {
        if (!($this->item instanceof FileSystemEntry)) {
            return '';
        }

        $rename = $this->item->createRenameEntryUrl();
        $id = $this->item->id;
        $type = Html::escapeAttribute($this->item::class);
        $content = Icon::edit();

        return "<button class='link no-decoration' x-init='fs_renameButton_init' data-url='$rename' data-id='$id'>$content</button>";
    }

    protected function createDeleteValue(?string $url): string {
        if (!($this->item instanceof FileSystemEntry)) {
            return '';
        }

        $delete = $this->item->createDeleteEntryUrl();
        $id = $this->item->id;
        $type = Html::escapeAttribute($this->item::class);
        $content = Icon::delete();
        return "<button class='link no-decoration' x-init='fs_deleteButton_init' data-url='$delete' data-id='$id'>$content</button>";
    }
}