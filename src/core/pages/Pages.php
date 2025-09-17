<?php

namespace core\pages;

use models\core\Page\PageTemplateRecord;
use RuntimeException;

class Pages {
    /** @var $templates array<PageTemplate> */
    private static array $templates = [];

    public static function register(PageTemplate $template): void {
        $templateRecord = PageTemplateRecord::fromNameCreate($template->getName(), create: true);
        self::$templates[$templateRecord->getId()] = $template;
    }

    public static function getTemplate(int $templateId): PageTemplate {
        if (!isset(self::$templates[$templateId])) {
            throw new RuntimeException("Page Template ($templateId) is not loaded");
        }

        return self::$templates[$templateId];
    }

    public static function load(): void {
        require_once __DIR__. '/../../components/pages/page-templates.php';
    }
}