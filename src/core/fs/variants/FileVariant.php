<?php

namespace core\fs\variants;

interface FileVariant {
    public function getName(): string;

    public function createTransformer(string $transformer): ?FileVariantTransformer;
}