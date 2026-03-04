<?php

namespace core\pages;

use core\ResourceLoader;
use core\utils\Php;
use models\core\Page\PageTemplateRecord;
use RuntimeException;

class Pages {
    use ResourceLoader;



    /** @var $templates array<PageTemplate> */
    private static array $templates = [];

    public static function register(PageTemplate $template): void {
        $templateRecord = PageTemplateRecord::fromNameCreate($template->getName(), create: true);
        self::$templates[$templateRecord->id] = $template;
    }

    public static function getTemplate(int $templateId): PageTemplate {
        if (!isset(self::$templates[$templateId])) {
            throw new RuntimeException("Page Template ($templateId) is not loaded");
        }

        return self::$templates[$templateId];
    }

    public static function load(): void {
        Php::run(self::getSelfResource("../../components/pages/page-templates.php"));
    }
}