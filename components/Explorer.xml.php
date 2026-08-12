<?php /** @var Explorer $this */

use components\Explorer;
use core\view\Xml;

?><explorer entry="<?= Xml::escape($this->label) ?>">
    <?php foreach (scandir($this->directory) as $entry): ?>
        <?php if ($entry === ".") continue; ?>
        <?php if ($entry === ".." && !$this->isParentEntryAllowed) continue; ?>

        <entry
            type="<?= is_dir($this->directory ."/". $entry) ? 'directory' : 'file' ?>"
            url="<?= Xml::escape($this->url . urlencode($entry)) ?>"
        >
            <?= Xml::escape($entry) ?>
        </entry>
    <?php endforeach; ?>
</explorer>
