<?php

namespace components\Admin\Phrase;

use components\Admin\AdminPageView;
use components\forms\controls\FileControl;
use components\forms\controls\MultiSelect;
use components\forms\controls\Submit;
use components\forms\Form;
use core\communication\body\DictionaryBody;
use core\communication\Request;
use core\communication\Response;
use core\locale\LexiconUnit;
use core\storage\Temporary;
use core\http\HttpCode;
use core\http\HttpMethod;
use core\io\ContentType;
use core\route\Route;
use core\route\RouteNode;
use core\route\Router;
use core\utils\Arrays;
use core\utils\Files;
use core\utils\Models;
use core\view\Controller;
use core\view\Renderer;
use core\view\renderers\HtmlRenderer;
use models\Language\Language;
use models\Language\Lexicon\LexiconGroup;
use models\Language\Lexicon\Phrase;
use models\Language\Lexicon\Rule;
use models\Language\Lexicon\Translation;
use models\Privilege\Privilege;
use ZipArchive;

class AdminPhrasePacks extends Controller {
    use LexiconUnit;

    public const NAME_LANGUAGE_SELECT = 'languageSelect';

    public const NAME_IMPORT_FILES = 'importFiles';

    public const JSON_LANGUAGE = 'language';
    public const JSON_PHRASES = 'phrases';
    public const JSON_IS_DYNAMIC = 'isDynamic';
    public const JSON_GROUP = 'group';
    public const JSON_TRANSLATIONS = 'translations';
    public const JSON_ITEM_RULE = 'rule';
    public const JSON_ITEM_TRANSLATION = 'translation';

    public const LEXICON_GROUP = 'admin.phrase.translation-packs';



    public function __construct(
        ?Renderer $renderer = null
    ) {
        parent::__construct($renderer);
        $this->setLexiconGroup(self::LEXICON_GROUP);

        $this->setTitle($this->tr('Admin - Translation Packs'));
    }



    public function createImportForm(): Form {
        $form = new Form(HttpMethod::POST, $this->createUrl('/import'));

        $form->noStyles();
        $form->setOnSubmitSuccess('translationPacks_onImportSuccess');

        $form->add((new FileControl(self::NAME_IMPORT_FILES, multiple: true))
            ->accept('.json,.zip'));
        $form->add(new Submit($this->tr('Import Packs')));

        return $form;
    }

    public function createExportForm(): Form {
        $form = new Form(HttpMethod::POST, $this->createUrl('/export'));

        $form->noStyles();
        $form->setOnSubmitSuccess('translationPacks_onExportSuccess');

        $languages = [];

        foreach (Language::all() as $language) {
            $languages[$language->code] = $language
                ->getLocale()
                ->getName();
        }

        $form->add(new MultiSelect(
            self::NAME_LANGUAGE_SELECT,
            $this->tr('Select Language'),
            $languages
        ));

        $form->add(new Submit($this->tr('Generate Export')));
        return $form;
    }



    public function setRouter(Route $route, Router $router): bool {
        if (!$this->hasRequestAccess(Privilege::fromName(Privilege::READ))) {
            return false;
        }

        $router->use($route, AdminPageView::fromComponent($this));
        return true;
    }

    public function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();
        $router->use('/export', function (Request $request, Response $response) {
            $messageCouldNotCreateZipArchive = $this->tr('Could not create ZIP archive');
            $messageEmptyLanguages = $this->tr('Languages for export are not specified');

            $fields = $request->body(DictionaryBody::class)
                ->getFields();

            $languages = MultiSelect::parse($fields->getStrict(self::NAME_LANGUAGE_SELECT));
            if (empty($languages)) {
                $response->sendMessage($messageEmptyLanguages, HttpCode::CE_BAD_REQUEST);
            }

            $languageModels = [];

            foreach (Language::all() as $language) {
                $languageModels[$language->code] = $language;
            }

            $packs = [];
            $phrases = Phrase::all();
            $groups = Models::identity(LexiconGroup::all());

            foreach ($languages as $languageCode) {
                if (!isset($languageModels[$languageCode])) {
                    continue;
                }

                $items = [];
                $language = $languageModels[$languageCode];

                foreach ($phrases as $phrase) {
                    $translations = array_filter(
                        $phrase->getTranslations(),
                        fn(Translation $x) => $x->languageId === $language->id
                    );

                    if (empty($translations)) {
                        continue;
                    }

                    $items[$phrase->default] = [
                        self::JSON_IS_DYNAMIC => $phrase->isDynamic ? 'template' : 'static',
                        self::JSON_GROUP => $groups[$phrase->groupId]?->name,
                        self::JSON_TRANSLATIONS => array_values(array_map(
                            fn(Translation $x) => [
                                self::JSON_ITEM_RULE => $x->getRule()?->rule,
                                self::JSON_ITEM_TRANSLATION => $x->translation
                            ],
                            $translations
                        ))
                    ];
                }

                $packs[] = [
                    self::JSON_LANGUAGE => $languageCode,
                    self::JSON_PHRASES => $items
                ];
            }

            if (count($packs) === 1) {
                $response->downloadContent(
                    json_encode($packs[0], JSON_PRETTY_PRINT),
                    $packs[0][self::JSON_LANGUAGE] . '.json'
                );
            }

            $zip = new ZipArchive();
            $zipFile = Temporary::file('zip_');
            unlink($zipFile);

            if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
                $response->sendMessage($messageCouldNotCreateZipArchive, HttpCode::SE_INTERNAL_SERVER_ERROR);
                return;
            }

            foreach ($packs as $pack) {
                $zip->addFromString(
                    $pack[self::JSON_LANGUAGE] . '.json',
                    json_encode($pack, JSON_PRETTY_PRINT)
                );
            }

            $zip->close();
            $response->download($zipFile, 'packs.zip', doFlush: false);

            unlink($zipFile);
        });

        $router->use('/import', function (Request $request, Response $response) {
            $files = $request->body(DictionaryBody::class)
                ->getFiles();

            foreach (Arrays::flatten($files->toArray()) as $file) {
                if ($file->getError() !== UPLOAD_ERR_OK) {
                    continue;
                }

                switch ($file->getType()) {
                    case ContentType::JSON: {
                        $this->importJson(
                            json_decode(file_get_contents($file->getPath()), associative: true)
                        );
                        break;
                    }

                    case ContentType::ZIP: {
                        $zip  = new ZipArchive();
                        if ($zip->open($file->getPath()) !== true) {
                            break;
                        }

                        for ($i = 0; $i < count($zip); $i++) {
                            if (Files::extension($zip->getNameIndex($i)) !== 'json') {
                                continue;
                            }

                            $this->importJson(
                                json_decode(
                                    $zip->getFromIndex($i),
                                    associative: true
                                )
                            );
                        }

                        $zip->close();
                        break;
                    }
                }
            }

            $response->sendStatus(HttpCode::S_OK);
        });
    }

    protected function importJson(?array $json): void {
        if (is_null($json) || is_null($language = Language::fromCode($json[self::JSON_LANGUAGE] ?? ''))) {
            return;
        }

        $phrases = Arrays::changeKeys(
            Phrase::all(),
            fn(Phrase $x) => $x->default
        );

        foreach (($json[self::JSON_PHRASES] ?? []) as $default => $phrase) {
            if (!isset($phrases[$default])) {
                if (!isset($record[self::JSON_GROUP]) || !isset($record[self::JSON_IS_DYNAMIC])) {
                    continue;
                }

                $model = Phrase::createPhrase(
                    $record[self::JSON_GROUP],
                    $default,
                    $language,
                    $record[self::JSON_IS_DYNAMIC]
                );
            } else {
                $model = $phrases[$default];
            }

            foreach ($phrase[self::JSON_TRANSLATIONS] as $record) {
                if (empty($record[self::JSON_ITEM_TRANSLATION])) {
                    continue;
                }

                $rule = null;
                if (isset($record[self::JSON_ITEM_RULE])) {
                    $rule = Rule::fromRule($record[self::JSON_ITEM_RULE]);
                }

                $model->addTranslation(
                    $language,
                    $record[self::JSON_ITEM_TRANSLATION],
                    $rule
                );
            }
        }
    }
}