<?php

namespace components\Dashboard;

use components\html\HtmlHead;
use components\Message\Message;
use components\NotFound;
use core\view\Component;
use core\view\View;

class DashboardPage extends Component implements DashboardContent {
    public static function fromMessage(Dashboard $dashboard, Message $message): static {
        return new self(
            $dashboard,
            new HtmlHead($message->getContentTrimmed()),
            $message
        );
    }

    public static function notFound(Dashboard $dashboard, string $title): static {
        $notFound = new NotFound($title);

        return new self(
            $dashboard,
            new HtmlHead($notFound->createTitle()),
            $notFound
        );
    }



    public function __construct(
        protected Dashboard $dashboard,
        protected HtmlHead $head,
        protected View $view,
    ) {
        parent::__construct();
        $this->setLexiconGroup(Dashboard::LEXICON_GROUP);
    }



    public function setDashboard(Dashboard $dashboard): static {
        if ($this->view instanceof DashboardContent) {
            $this->view->setDashboard($dashboard);
        }

        $this->dashboard = $dashboard;
        return $this;
    }
}