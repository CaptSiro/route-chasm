<?php

namespace components\core\Editor;

use components\core\Html\Html;
use components\core\ToolBar\ToolBar;
use components\core\ToolBar\ToolBarItem;
use components\core\WebPage\WebPage;
use components\widgets\Code\CodeWidget;
use components\widgets\Command\CommandWidget;
use components\widgets\CommentSection\CommentSectionWidget;
use components\widgets\Decoration\TextDecorationWidget;
use components\widgets\Divider\DividerWidget;
use components\widgets\FileDownload\FileDownloadWidget;
use components\widgets\Header\HeaderWidget;
use components\widgets\Heading\HeadingWidget;
use components\widgets\Image\ImageWidget;
use components\widgets\Link\LinkWidget;
use components\widgets\List\ListWidget;
use components\widgets\ListItem\ListItemWidget;
use components\widgets\Page\PageWidget;
use components\widgets\Quote\QuoteWidget;
use components\widgets\Root\RootWidget;
use components\widgets\Text\TextWidget;
use components\widgets\TextEditor\TextEditorWidget;
use components\widgets\Widget;
use components\widgets\WidgetImporter;
use core\data\DataItem;
use core\route\Route;
use core\utils\Arrays;
use core\view\ContainerContent;

class Editor extends ContainerContent {
    public static function getDefaultWidgets(): array {
        return [
            CodeWidget::getInstance(),
            CommandWidget::getInstance(),
            CommentSectionWidget::getInstance(),
            TextDecorationWidget::getInstance(),
            DividerWidget::getInstance(),
            FileDownloadWidget::getInstance(),
            HeaderWidget::getInstance(),
            HeadingWidget::getInstance(),
            ImageWidget::getInstance(),
            LinkWidget::getInstance(),
            ListWidget::getInstance(),
            ListItemWidget::getInstance(),
            PageWidget::getInstance(),
            QuoteWidget::getInstance(),
            RootWidget::getInstance(),
            TextWidget::getInstance(),
            TextEditorWidget::getInstance(),
        ];
    }



    protected WebPage $webPage;
    protected ToolBar $toolBar;
    /** @var array<Widget> */
    protected array $widgets;

    private WidgetImporter $importer;

    /**
     * @param DataItem $storage
     * @param string $title
     * @param array<Widget>|null $widgets
     */
    public function __construct(
        protected DataItem $storage,
        string $title = "Editor",
        ?array $widgets = null
    ) {
        parent::__construct($this->webPage = new WebPage());
        $this->webPage->getHead()->setTitle($title);

        $this->importer = new WidgetImporter();

        if (!is_null($widgets)) {
            $widgets = Arrays::changeKeys($widgets, fn(Widget $x) => $x->getName());
        }

        $this->widgets = $widgets ?? self::getDefaultWidgets();

        $this->toolBar = new ToolBar();
        $this->toolBar
            ->add(
                Route::menu('/File/Save'),
                new ToolBarItem('file_save', 'ctrl + s')
            )
            ->add(
                Route::menu('/File/Exit'),
                new ToolBarItem('close', 'ctrl + e'),
            )
            ->add(
                Route::menu('/Edit/Select all'),
                new ToolBarItem('edit_selectAll', 'ctrl + a'),
            )
            ->add(
                Route::menu('/Edit/Deselect'),
                new ToolBarItem('edit_deselect', 'esc'),
            )
            ->add(
                Route::menu('/Edit/Delete'),
                new ToolBarItem('edit_delete'),
            )
            ->add(
                Route::menu('/Edit/Copy'),
                new ToolBarItem('edit_copy', 'ctrl + c'),
            )
            ->add(
                Route::menu('/Edit/Cut'),
                new ToolBarItem('edit_cut', 'ctrl + x'),
            )
            ->add(
                Route::menu('/Edit/Paste'),
                new ToolBarItem('edit_paste', 'ctrl + v'),
            )
            ->add(
                Route::menu('/Edit/Properties'),
                new ToolBarItem('edit_properties'),
            );

        $this->addViewportMode('Mobile', 'mobile', 9/16);
        $this->addViewportMode('Computer', 'computer', 16/9);
    }



    public function getToolBar(): ToolBar {
        return $this->toolBar;
    }

    public function addViewportMode(string $label, string $name, float $aspectRatio): static {
        $item = new ToolBarItem("editor_viewport_setMode");
        $item->addAttribute("data-name", Html::escapeAttribute($name));
        $item->addAttribute("data-aspect-ratio", Html::escapeAttribute($aspectRatio));

        $this->toolBar->add(Route::menu("/View/Mode/". Html::escape($label)), $item);
        return $this;
    }

    public function setTitle(string $title): static {
        $this->webPage->getHead()->setTitle($title);
        return $this;
    }

    public function addWidget(Widget $widget): static {
        $this->widgets[$widget->getName()] = $widget;
        return $this;
    }

    /**
     * @return array<string, Widget>
     */
    public function explodeWidgets(): array {
        $widgets = [];

        foreach ($this->widgets as $widget) {
            $widgets[$widget->getName()] = $widget;
            $this->explodeWidget($widget, $widgets);
        }

        return $widgets;
    }

    /**
     * @param Widget $widget
     * @param array<string, Widget> $accumulator
     * @return void
     */
    protected function explodeWidget(Widget $widget, array &$accumulator): void {
        foreach ($widget->getDependencies() as $dependency) {
            $accumulator[$widget->getName()] = $dependency;
            $this->explodeWidget($dependency, $accumulator);
        }
    }

    public function importWidget(Widget $widget): string {
        return $this->importer
            ->setWidget($widget)
            ->render();
    }
}