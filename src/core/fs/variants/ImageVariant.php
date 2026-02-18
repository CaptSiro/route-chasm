<?php

namespace core\fs\variants;

use core\Singleton;
use models\core\fs\ImageVariantTransformer;

class ImageVariant implements FileVariant {
    use Singleton;



    public const TRANSFORMER_ARTICLE_THUMBNAIL = 'article-thumbnail';
    public const TRANSFORMER_FULL_HD = 'full-hd';
    public const TRANSFORMER_HD = 'hd';

    public static function get(string $transformer): ?FileVariantTransformer {
        return static::getInstance()
            ->getTransformer($transformer);
    }



    public function getName(): string {
        return 'img';
    }

    public function getTransformer(string $transformer): ?FileVariantTransformer {
        return ImageVariantTransformer::fromTransformer($transformer);
    }
}