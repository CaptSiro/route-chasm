<?php

namespace core\fs\variants;

use core\Singleton;
use models\core\fs\ImageVariantTransformer;

class ImageVariant implements FileVariant {
    use Singleton;



    public const TRANSFORMER_ARTICLE_THUMBNAIL = 'article-thumbnail';
    public const TRANSFORMER_FULL_HD = 'full-hd';
    public const TRANSFORMER_HD = 'hd';
    public const TRANSFORMER_FILE_IMAGE_PREVIEW = 'file-image-preview';
    public const TRANSFORMER_LISTING_CARD_SMALL = 'listing-card-small';
    public const TRANSFORMER_LISTING_CARD_MEDIUM = 'listing-card-medium';
    public const TRANSFORMER_LISTING_CARD_LARGE = 'listing-card-large';



    public static function get(string $transformer): ?FileVariantTransformer {
        return static::getInstance()
            ->getTransformer($transformer);
    }

    public static function resolve(
        string $transformer,
        int $width,
        int $height,
        string $function = ImageVariantTransformer::FUNCTION_FIT,
        float $quality = 1.0
    ): FileVariantTransformer {
        return static::getInstance()
            ->resolveTransformer($transformer, $width, $height, $function, $quality);
    }



    public function getName(): string {
        return 'img';
    }

    public function getTransformer(string $transformer): ?FileVariantTransformer {
        return ImageVariantTransformer::fromTransformer($transformer);
    }

    public function getTransformers(): array {
        return ImageVariantTransformer::all();
    }

    public function resolveTransformer(
        string $transformer,
        int $width,
        int $height,
        string $function = ImageVariantTransformer::FUNCTION_FIT,
        float $quality = 1.0
    ): FileVariantTransformer {
        return $this->getTransformer($transformer)
            ?? ImageVariantTransformer::createTransformer(
                $transformer, $width, $height, $function, $quality
            );
    }
}