<?php

namespace models\fs;

use components\forms\description\FormDescription;
use components\forms\Form;
use components\fs\ImageVariantPreview;
use components\layout\Column;
use components\layout\Row;
use components\nexus\NexusEditorAction;
use components\nexus\NexusEditor;
use components\nexus\NexusEditorBehavior;
use core\database\sql\Model;
use core\locale\LexiconUnit;
use core\view\Container;
use core\view\View;

class ImageVariantBehavior extends NexusEditorBehavior {
    use LexiconUnit;

    public const LEXICON_GROUP = 'file-system.editor';



    protected FormDescription $description;

    public function __construct() {
        $this->setLexiconGroup(self::LEXICON_GROUP);
        $this->description = FormDescription::extract(ImageVariantTransformer::class);
    }



    public function getTitle(): string {
        return $this->tr('Image Variant');
    }

    public function onFormInitialization(NexusEditor $editor, Form $form, ?Model $model): ?View {
        return $this->description->onFormInitialization($editor, $form, $model);
    }

    public function onFormGeneration(Container $container, ?Model $model): ?View {
        $container->add($row = new Row());

        $this->description->onFormGeneration($controls = new Column(0.5), $model);
        $row->add($controls);

        $preview = new Column(0.5);
        $preview->add(new ImageVariantPreview(
            '[name=width]',
            '[name=height]'
        ));
        $row->add($preview);

        return null;
    }

    public function onSubmit(Model $model, NexusEditorAction $action): ?View {
        return $this->description->onSubmit($model, $action);
    }
}