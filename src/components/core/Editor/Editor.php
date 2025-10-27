<?php

namespace components\core\Editor;

use components\core\Html\Html;
use components\core\ToolBar\ToolBar;
use components\core\ToolBar\ToolBarItem;
use components\core\WebPage\WebPage;
use core\data\DataItem;
use core\route\Route;
use core\view\ContainerContent;

class Editor extends ContainerContent {
    protected WebPage $webPage;
    protected ToolBar $toolBar;

    public function __construct(
        protected DataItem $storage,
        string $title = "Editor"
    ) {
        parent::__construct($this->webPage = new WebPage());
        $this->webPage->getHead()->setTitle($title);

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
}