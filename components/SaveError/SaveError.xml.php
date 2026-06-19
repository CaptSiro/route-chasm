<?php /** @var SaveError $this */

use components\SaveError\SaveError;
use core\view\Xml;

?><error class="<?= Xml::escape($this->getClass()) ?>">
    <error-property>
        <?= Xml::escape($this->property) ?>
    </error-property>

    <error-message>
        <?= Xml::escape($this->message) ?>
    </error-message>
</error>
