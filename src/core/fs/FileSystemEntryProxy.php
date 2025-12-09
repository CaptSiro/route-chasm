<?php

namespace core\fs;

use components\core\Admin\Nexus\NexusProxy;
use components\core\Html\Html;
use components\core\Icon;
use core\App;
use core\ResourceLoader;
use core\RouteChasmEnvironment;
use core\sideloader\importers\Css\Css;
use core\sideloader\importers\Javascript\Javascript;
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



    public function getValue(string $name): string {
        if ($name === 'size') {
            if ($this->item instanceof File) {
                return $this->item->getHumanReadableSize();
            }

            return '';
        }

        if ($name === "name") {
            if ($this->item instanceof Directory) {
                $url = App::getInstance()->getRequest()->getUrl()->copy();
                $url->setQueryArgument(RouteChasmEnvironment::QUERY_FILE_SYSTEM_DIRECTORY, $this->item->getId());
                $link = Html::createLinkUnsafe($url, Html::escape($this->item->getEntryName()));

                return Html::wrapUnsafe(
                    'div',
                    $this->item->getEntryIcon() . $link,
                    ['class' => 'row']
                );
            }

            if ($this->item instanceof File) {
                $span = Html::wrap('span', $this->item->getEntryName());
                $icon = Icon::nf('nf-fa-file');

                return Html::wrapUnsafe(
                    'div',
                    $this->item->getEntryIcon() . $span,
                    ['class' => 'row']
                );
            }

            return json_encode($this->item) . ' is not FileSystemEntry';
        }

        return parent::getValue($name);
    }

    protected function createEditValue(string $url): string {
        if (!($this->item instanceof FileSystemEntry)) {
            return '';
        }

        self::import();

        $rename = $this->item->createRenameEntryUrl();
        $id = $this->item->getId();
        $type = Html::escapeAttribute($this->item::class);
        $content = Icon::edit();

        return "<button class='link no-style' x-init='fs_renameButton_init' data-url='$rename' data-id='$id'>$content</button>";
    }

    protected function createDeleteValue(string $url): string {
        if (!($this->item instanceof FileSystemEntry)) {
            return '';
        }

        self::import();

        $delete = $this->item->createDeleteEntryUrl();
        $id = $this->item->getId();
        $type = Html::escapeAttribute($this->item::class);
        $content = Icon::delete();
        return "<button class='link no-style' x-init='fs_deleteButton_init' data-url='$delete' data-id='$id'>$content</button>";
    }
}