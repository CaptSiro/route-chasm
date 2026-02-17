<?php

namespace core\fs\variants;

use models\core\fs\ImageVariantTransformer;

class ImageVariant implements FileVariant {
    public function getName(): string {
        return 'img';
    }

    public function createTransformer(string $transformer): ?FileVariantTransformer {
        return ImageVariantTransformer::fromTransformer($transformer);
    }
}