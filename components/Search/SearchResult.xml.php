<?php /** @var SearchResult $this */

use components\Search\SearchResult;
use core\view\Xml;

?><search-result is-link="<?= Xml::escape(json_encode($this->isLink)) ?>">
    <search-result-value>
        <?= Xml::escape($this->value) ?>
    </search-result-value>

    <search-result-label>
        <?= Xml::escape($this->label) ?>
    </search-result-label>

    <search-result-meta>
        <?= Xml::escape($this->meta) ?>
    </search-result-meta>
</search-result>