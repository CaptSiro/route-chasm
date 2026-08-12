<?php /** @var Accordion $this */

use components\layout\Accordion;
use core\view\Xml;

?><accordion title="<?= Xml::escape($this->getTitle()) ?>">
    <?= $this->content ?>
</accordion>