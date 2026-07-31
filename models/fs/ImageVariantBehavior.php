<?php

namespace models\fs;

use components\Admin\Nexus\Editor;
use components\Admin\Nexus\Editor\EditorBehavior;
use components\Admin\Nexus\Editor\EditorBehaviorAction;
use components\forms\description\FormDescription;
use components\forms\Form;
use components\fs\ImageVariantPreview;
use components\layout\Column;
use components\layout\Row;
use core\database\sql\Model;
use core\view\Container;
use core\view\View;

class ImageVariantBehavior implements EditorBehavior {
    use Editor\SetEditor;

    public static function createEditor(): Editor {
        return new Editor\AdminNexusEditor(new static());
    }



    protected FormDescription $description;

    public function __construct() {
        $this->description = FormDescription::extract(ImageVariantTransformer::class);
    }



    public function initForm(Form $form, ?Model $model): ?View {
        return $this->description->initForm($form, $model);
    }

    public function addControls(Container $container, ?Model $model): ?View {
        $row = new Row();

        $controls = new Column(0.5);
        $this->description->addControls($controls, $model);
        $row->add($controls);

        $preview = new Column(0.5);
        $preview->add(new ImageVariantPreview(
            '[name=width]',
            '[name=height]'
        ));
        $row->add($preview);

        $container->add($row);
        return null;
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        return $this->description->onSubmit($model, $action);
    }
}