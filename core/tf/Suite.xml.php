<?php /** @var Suite $this */

use core\tf\Suite;
use core\view\Xml;

?><suite
    name="<?= Xml::escape($this->name) ?>"
    passed="<?= Xml::escape($this->passed) ?>"
    failed="<?= Xml::escape($this->failed) ?>"
    time="<?= Xml::escape($this->time) ?>"
>
    <assertions>
        <?php foreach ($this->failedAssertions as $assertion): ?>
            <assertion>
                <?= Xml::escape($assertion) ?>
            </assertion>
        <?php endforeach; ?>
    </assertions>
</suite>