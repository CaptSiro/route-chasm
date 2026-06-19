<?php /** @var SaveErrorGroup $this */

use components\SaveError\Group\SaveErrorGroup;

?><error-group>
    <?php foreach ($this->errors as $error): ?>
        <?= $error->render() ?>
    <?php endforeach; ?>
</error-group>
