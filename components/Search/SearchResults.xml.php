<?php /** @var SearchResults $this */

use components\Search\SearchResults;

?><search-results>
    <?php foreach ($this->results as $result): ?>
        <?= $result ?>
    <?php endforeach; ?>
</search-results>
