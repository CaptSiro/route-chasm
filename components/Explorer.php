<?php

namespace components;

use core\locale\LexiconUnit;
use core\sideloader\importers\Css\Css;
use core\view\Controller;
use core\view\Renderer;
use core\view\renderers\HtmlRenderer;
use JsonSerializable;

class Explorer extends Controller implements JsonSerializable {
    use LexiconUnit;



    public function __construct(
        protected string $directory,
        protected string $label,
        protected string $url,
        protected bool $isParentEntryAllowed = true,
        ?Renderer $renderer = new HtmlRenderer()
    ) {
        Css::importDefault($this);

        if (!str_ends_with($this->url, "/")) {
            $this->url .= "/";
        }

        parent::__construct($renderer);
        $this->setTitle($this->trt("Explorer - {}", $this->label));
    }



    // JsonSerializable
    public function jsonSerialize(): array {
        $ret = [];

        foreach (scandir($this->directory) as $entry) {
            if ($entry === "." || ($entry === ".." && !$this->isParentEntryAllowed)) {
                continue;
            }

            $ret[] = [
                'entry' => $entry,
                'type' => is_dir($this->directory . "/" . $entry) ? 'directory' : 'file',
                'url' => $this->url . urlencode($entry),
            ];
        }

        return $ret;
    }
}