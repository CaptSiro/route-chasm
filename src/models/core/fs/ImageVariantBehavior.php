<?php

namespace models\core\fs;

use components\core\Admin\Nexus\Editor;
use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Admin\Nexus\Editor\EditorBehaviorAction;
use components\core\fs\ImageVariantPreview;
use components\layout\Column\Column;
use components\layout\Layout;
use components\layout\Row\Row;
use core\database\sql\Model;
use core\forms\description\FormDescription;
use core\forms\Form;
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

    public function addControls(Layout $layout, ?Model $model): ?View {
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

        $layout->add($row);
        return null;
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        return $this->description->onSubmit($model, $action);
    }
}