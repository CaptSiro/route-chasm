<?php

namespace components\layout\Dashboard;

use components\Message\Message;
use components\NotFound;
use core\Deprecated;
use core\locale\LexiconUnit;
use core\view\Component;
use core\view\DataTransfer;
use core\view\Head;
use core\view\PageView;
use core\view\Payload;
use core\view\Renderer;
use core\view\View;

class DashboardPageView extends PageView implements DashboardContent {
    use LexiconUnit;

    /**
     * @param View $view
     * @param string $title
     * @return static
     * @deprecated
     */
    public static function from(View $view, string $title): static {
        throw new Deprecated(self::fromDashboard(...));
    }

    public static function fromDashboard(Dashboard $dashboard, View $view, string $title): static {
        $payload = new DataTransfer($view);
        $payload->setProperty(Head::PAYLOAD_TITLE, $title);

        return new self($dashboard, $view, $payload);
    }

    /**
     * @param Component $component
     * @return static
     * @deprecated
     */
    public static function fromComponent(Component $component): static {
        throw new Deprecated(self::fromDashboardComponent(...));
    }

    public static function fromDashboardComponent(Dashboard $dashboard, Component $component): static {
        return new self($dashboard, $component, $component);
    }

    public static function fromMessage(Dashboard $dashboard, Message $message): static {
        return new self($dashboard, $message, $message);
    }

    public static function notFound(Dashboard $dashboard, string $title): static {
        $notFound = new NotFound($title);
        return self::fromDashboardComponent($dashboard, $notFound);
    }



    public function __construct(
        protected Dashboard $dashboard,
        ?View $view = null,
        ?Payload $payload = null,
        ?Renderer $renderer = null
    ) {
        parent::__construct($view, $payload, $renderer);
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