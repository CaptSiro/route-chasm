<?php

namespace components\Lumora\Editor;

use components\ai\AiRequest;
use components\ai\InputMessage;
use components\ai\PageGeneration\PageGeneration;
use components\ai\Schema\ObjectSchema;
use components\ai\Schema\Schema;
use components\ai\Schema\StringSchema;
use components\core\Html\Html;
use components\core\ToolBar\ToolBar;
use components\core\ToolBar\ToolBarItem;
use components\core\WebPage\WebPage;
use components\Lumora\widgets\Ai\AiWidget;
use components\Lumora\widgets\Code\CodeWidget;
use components\Lumora\widgets\Command\CommandWidget;
use components\Lumora\widgets\CommentSection\CommentSectionWidget;
use components\Lumora\widgets\Decoration\TextDecorationWidget;
use components\Lumora\widgets\Divider\DividerWidget;
use components\Lumora\widgets\FileDownload\FileDownloadWidget;
use components\Lumora\widgets\Header\HeaderWidget;
use components\Lumora\widgets\Heading\HeadingWidget;
use components\Lumora\widgets\Html\HtmlWidget;
use components\Lumora\widgets\Image\ImageWidget;
use components\Lumora\widgets\Link\LinkWidget;
use components\Lumora\widgets\List\ListWidget;
use components\Lumora\widgets\ListItem\ListItemWidget;
use components\Lumora\widgets\Page\PageWidget;
use components\Lumora\widgets\Quote\QuoteWidget;
use components\Lumora\widgets\Root\RootWidget;
use components\Lumora\widgets\Text\TextWidget;
use components\Lumora\widgets\TextEditor\TextEditorWidget;
use components\Lumora\widgets\Widget;
use components\Lumora\widgets\WidgetImporter;
use components\pages\Wireframe\Wireframe;
use core\communication\Request;
use core\communication\Response;
use core\data\DataItem;
use core\fs\FileServer;
use core\fs\variants\ImageVariant;
use core\http\HttpCode;
use core\http\HttpMethod;
use core\route\Route;
use core\RouteChasmEnvironment;
use core\utils\Arrays;
use core\view\ContainerContent;
use DateTime;
use models\core\Page\PageLocalization;
use modules\ai\OpenAi;

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
            AiWidget::getInstance(),
            HtmlWidget::getInstance(),
        ];
    }



    protected WebPage $webPage;
    protected ToolBar $toolBar;
    /** @var array<Widget> */
    protected array $widgets;

    private WidgetImporter $importer;

    /**
     * @param DataItem $storage
     * @param PageLocalization $localization
     * @param string $title
     * @param array<Widget>|null $widgets
     */
    public function __construct(
        protected DataItem $storage,
        protected PageLocalization $localization,
        string $title = "Editor",
        ?array $widgets = null
    ) {
        parent::__construct($this->webPage = new WebPage(head: Wireframe::createHtmlHead($this->localization)));
        $this->webPage->getHead()->setTitle($title);

        $this->importer = new WidgetImporter();

        if (!is_null($widgets)) {
            $widgets = Arrays::changeKeys($widgets, fn(Widget $x) => $x->getName());
        }

        $this->widgets = $widgets ?? self::getDefaultWidgets();
        $this->toolBar = $this->initToolBar(new ToolBar());

        $this->addViewportMode('Mobile', 'mobile', 9/16);
        $this->addViewportMode('Computer', 'computer', 16/9);
    }



    public function getStorage(): DataItem {
        return $this->storage;
    }

    public function getToolBar(): ToolBar {
        return $this->toolBar;
    }

    protected function initToolBar(ToolBar $toolBar): ToolBar {
        return $toolBar
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
    }

    public function addViewportMode(string $label, string $name, float $aspectRatio): static {
        $item = new ToolBarItem("lumora_viewport_setMode");
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
        $ret = [];

        foreach ($this->widgets as $widget) {
            $ret[$widget->getName()] = $widget;
            $this->explodeWidget($widget, $ret);
        }

        return $ret;
    }

    /**
     * @param Widget $widget
     * @param array<string, Widget> $accumulator
     * @return void
     */
    protected function explodeWidget(Widget $widget, array &$accumulator): void {
        foreach ($widget->getDependencies() as $dependency) {
            $accumulator[$dependency->getName()] = $dependency;
            $this->explodeWidget($dependency, $accumulator);
        }
    }

    public function importWidget(Widget $widget): string {
        return $this->importer
            ->setWidget($widget)
            ->render();
    }

    public function createFileSystemApi(): string {
        $fs = FileServer::getInstance();
        return Html::wrap(
            'div',
            '',
            [
                'id' => 'fs-data',
                'data-file-url' => $fs->createFileUrl(),
                'data-download-url' => $fs->createDownloadUrl(),
                'data-info-url' => $fs->createInfoUrl(),
                'data-variant-query' => RouteChasmEnvironment::QUERY_FS_VARIANT,
                'data-file-type-query' => RouteChasmEnvironment::QUERY_FS_FILE_TYPE,
                'data-directory-url' => $fs->createDirectoryUrl(),
                'data-image-variant-url' => $fs->createVariantUrl(ImageVariant::getInstance()),
            ]
        );
    }

    public function createLocalizationApi(): string {
        $releaseDate = new DateTime($this->localization->getPage()->getReleaseDate());

        return Html::wrap(
            'div',
            '',
            [
                'id' => 'localization-data',
                'data-title' => $this->localization->title,
                'data-release-date' => $this->localization
                    ->getLanguage()
                    ->getLocale()
                    ->formatDateTime($releaseDate->getTimestamp())
            ]
        );
    }



    // Action
    public function perform(Request $request, Response $response): void {
        switch ($request->getHttpMethod()) {
            case "GENERATE": {
                $client = OpenAi::fromEnv();
                $aiRequest = new AiRequest('gpt-4o-mini');

                $schema = new Schema(
                    'webpage_component_generation',
                    (new ObjectSchema())
                        ->add('html', new StringSchema())
                        ->add('css', new StringSchema())
                        ->add('js', new StringSchema())
                        ->setRequired(['html', 'css', 'js'])
                );

                $prompt = $request->getBody()->get("prompt");

                $aiRequest
                    ->setSchema($schema)
                    ->add(new PageGeneration(InputMessage::ROLE_SYSTEM, $prompt))
                    ->add(new PageGeneration(InputMessage::ROLE_USER, $prompt));

                if (is_null($aiResponse = $client->parseResponse($client->chat($aiRequest)))) {
                    $response->sendMessage(
                        $this->tr("Unable to generate widget based on the prompt."),
                        HttpCode::CE_BAD_REQUEST
                    );

                    return;
                }

                $response->json([
                    'html' => $aiResponse['html'],
                    'css' => $aiResponse['css'],
                    'js' => $aiResponse['js'],
                ]);
            }

            case HttpMethod::GET: {
                parent::perform($request, $response);
                return;
            }

            case HttpMethod::POST: {
                $this->storage->write($request->getBodyReader()->readAll());
                $response->setStatus(HttpCode::S_OK);
                $response->flush();
            }

            default: {
                $response->sendMessage(
                    'Invalid HTTP method ' . $request->getHttpMethod(),
                    HttpCode::CE_BAD_REQUEST
                );
            }
        }
    }
}