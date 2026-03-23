<?php

namespace components\docs;

use components\core\Html\Html;
use components\layout\Grid\Proxy\TypeProxy;
use core\utils\Strings;
use models\docs\Document;

class DocsDashboardProxy extends TypeProxy {
    public const NAME_FILE = 'file';
    public const NAME_UPTO_DATE = 'uptoDate';



    protected Docs $docs;
    public function __construct() {
        $this->docs = Docs::getInstance();
    }



    public function getValue(string $name): string {
        $item = $this->item;
        if ($name === self::NAME_UPTO_DATE && $item instanceof Document) {
            return Html::wrap('span', Strings::toHumanReadable(!$item->needsUpdate()));
        }

        if ($name === self::NAME_FILE) {
            $path = $this->getValueUnwrapped($name);

            return Html::createLinkUnsafe(
                $this->docs->createEntryUrl($path),
                Strings::prepend('\\', $this->docs->trimSrc($path)),
                target: '_blank'
            );
        }

        return parent::getValue($name);
    }
}