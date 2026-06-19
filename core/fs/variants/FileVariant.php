<?php

namespace core\fs\variants;

interface FileVariant {
    public function getName(): string;

    public function getTransformer(string $transformer): ?FileVariantTransformer;

    /**
     * @return array<FileVariantTransformer>
     */
    public function getTransformers(): array;
}