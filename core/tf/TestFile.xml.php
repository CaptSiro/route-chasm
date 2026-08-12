<?php /** @var TestFile $this */

use core\tf\TestFile;
use core\view\Xml;

?><test-file
    file="<?= Xml::escape($this->file) ?>"
    passed="<?= Xml::escape($this->passed) ?>"
    failed="<?= Xml::escape($this->failed) ?>"
>
    <suites>
        <?php foreach ($this->suites as $suite): ?>
            <?= $suite->toXml() ?>
        <?php endforeach; ?>
    </suites>
</test-file>