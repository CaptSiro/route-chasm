<?php

namespace core\fs\variants;

use models\core\fs\File;

interface FileVariantTransformer {
    public function getFileVariant(): FileVariant;

    public function getTransformer(): string;

    public function getTransformerLabel(): string;

    public function supports(File $file): bool;

    public function transform(File $file): string;
}