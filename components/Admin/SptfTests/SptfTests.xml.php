<?php /** @var SptfTests $this */

use components\Admin\SptfTests\SptfTests;
use core\view\Xml;

?><test-files entry="<?= Xml::escape($this->entry) ?>">
    <?php foreach ($this->getTestFiles() as $file): ?>
        <?= $file ?>
    <?php endforeach; ?>
</test-files>