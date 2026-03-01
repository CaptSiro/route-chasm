<?php

namespace components\pages\Listing;

use core\view\Component;
use models\core\Page\LocalizedPage;
use models\core\Page\Page;

class Listing extends Component {
    public function __construct(
        protected Page $page,
        protected LocalizedPage $localization,
    ) {
        parent::__construct();
    }
}