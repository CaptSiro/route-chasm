<?php

namespace components\ai\DocumentGeneration;

use components\ai\InputMessage;
use components\ai\MarkdownSpecification\MarkdownSpec;
use core\route\Path;
use core\RouteChasmEnvironment;
use core\utils\Files;
use models\core\Language\Language;
use models\docs\Fragment;

class DocumentGeneration extends InputMessage {
    use MarkdownSpec;



    /**
     * @param string $role
     * @param Language $language
     * @param string $file
     * @param array<Fragment> $fragments
     */
    public function __construct(
        string $role,
        protected Language $language,
        protected string $file,
        protected array $fragments
    ) {
        parent::__construct($role);
        $this->setTemplate($this->getTemplateVariant(strtolower($role)));
    }



    public function getFragments(): array {
        $fragments = [];
        $src = strlen(realpath(RouteChasmEnvironment::SRC));

        foreach ($this->fragments as $fragment) {
            $name = Path::from(
                Files::removeExtension(substr($fragment->name, $src))
            );

            $fragments[] = [
                'name' => $name->toString('\\'),
                'summary' => $fragment->summary
            ];
        }

        return $fragments;
    }
}