<?php /** @var FileVariantTransformers $this */

use components\fs\FileVariantTransformers;
use core\fs\FileSystem;
use core\view\Xml;

?><file-variant-transformers>
    <?php foreach ($this->transformers as $transformer): ?>
        <file-variant-transformer
            identifier="<?= Xml::escape(FileSystem::createVariantIdentifier($transformer)) ?>"
        >
            <?= Xml::escape($transformer->getTransformerLabel()) ?>
        </file-variant-transformer>
    <?php endforeach; ?>
</file-variant-transformers>