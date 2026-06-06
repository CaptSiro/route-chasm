<?php

namespace models\fs;

use components\Admin\Nexus\NexusProxy;
use components\html\Html;

class ImageVariantProxy extends NexusProxy {
    public const COLUMN_SIZE = 'size';



    public function getValue(string $name): string {
        if ($name === 'function') {
            /**
             * @var ImageVariantTransformer $transformer
             */
            $transformer = $this->item;
            return Html::wrap('span', ucfirst($transformer->function));
        }

        if ($name === self::COLUMN_SIZE) {
            /**
             * @var ImageVariantTransformer $transformer
             */
            $transformer = $this->item;
            $width = $transformer->width > 0
                ? (string) $transformer->width
                : '∞';

            $height = $transformer->height > 0
                ? (string) $transformer->height
                : '∞';

            return Html::wrap('span', $width .'x'. $height);
        }

        return parent::getValue($name);
    }
}