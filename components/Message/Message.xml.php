<?php /** @var Message $this */

use components\Message\Message;
use core\view\Xml;

?><message type="<?= Xml::escape(strtolower($this->type->value)) ?>">
    <?= Xml::escape($this->content) ?>
</message>
