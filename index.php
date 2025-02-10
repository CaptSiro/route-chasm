<?php

use components\Accordion\Accordion;
use components\core\HttpError\HttpError;
use components\core\WebPage\WebPage;
use components\resources\Cards\Cards;
use components\Tabs\Tabs;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\config\AppConfig;
use core\config\EnvConfig;
use core\http\Http;
use core\http\HttpCode;
use core\http\HttpMethod;
use modules\forms\controls\Checkbox\Checkbox;
use modules\forms\controls\File\File;
use modules\forms\controls\MultiSubmit\MultiSubmit;
use modules\forms\controls\TextField;
use modules\forms\controls\TextArea\TextArea;
use modules\forms\Form;
use modules\forms\FormAction;
use modules\forms\layout\Column\Column;
use modules\forms\layout\Row\Row;
use modules\SideLoader\Javascript;
use sptf\Sptf;
use tables\core\ModuleDefinition;

require_once __DIR__ ."/src/autoload.php";


AppConfig::getInstance()
    ->set(new EnvConfig(App::getEnvStatic()));

$app = App::getInstance();
$app->getOptions()->set(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH, true);
$app->getOptions()->set(App::OPTION_DO_ADD_HOME_TO_URL_PATH, true);

$router = $app->getMainRouter();



$router->expose("/public", (new \core\endpoints\Directory(__DIR__ . "/public"))
    ->onDirectory(\core\endpoints\Directory::showExplorer()));

$router->use(
    "/error",
    new HttpError("I'm a teapot", HttpCode::CE_IM_A_TEAPOT)
);

$router->use(
    '/ping',
    fn() => Javascript::import(Cards::getInstance()->getSource('ping.js')),
    fn(Request $request, Response $response) => $response->send(
        '<h2 style="color: whitesmoke" x-swap="outer" x-get="'. App::getInstance()->prependHome('/dong?s') .'">pong</h2>'
    )
);
$router->use(
    '/dong',
    fn(Request $request, Response $response) => $response->send('<button x-swap="outer" x-get="'. App::getInstance()->prependHome('/ping?s') .'">Back to ping</button>')
);

$router->use(
    "/",
    Http::get(
        fn() => Sptf::testDirectory(__DIR__ . "/src/tests/cases"),
        fn(Request $request, Response $response) => $response->flush()
    )->query("_test")
);

$user = new Accordion(
    'User info',
    (new Row(.50))
        ->add(new TextField('Name', 'Name', 'Tonda'))
        ->add(new TextField('Surname', 'Surname', 'Maly'))
);

$c0 = (new Column())
    ->add($user)
    ->add(Form::hr())
    ->add((new File("Image", 'Image', ['champs.txt', 'docs.pdf']))
        ->addAttribute('multiple'));
$c1 = (new Column())
    ->add(new TextArea("Area", "Description"))
    ->add(new Checkbox('Read', 'I have read TOS'))
    ->add(Form::note("Submitting form you are giving us consent to get all your money"))
    ->add(new MultiSubmit([
        new FormAction(FormAction::TYPE_RESET, 'Reset'),
        new FormAction(FormAction::TYPE_SUBMIT, 'Delete', 'delete'),
        FormAction::submit(),
    ]));

$form = (new Form(HttpMethod::DELETE))
    ->add(new Tabs([
        "User" => $c0,
        "Description" => $c1
    ]));
$router->use("/form",
    Http::get(new WebPage(content: $form)),
    Http::delete(function(Request $request, Response $response) {
        $response->json($request->getBody());
    })
);



$router->resource("/cards", Cards::getInstance());
$router->use("/map", fn(Request $request, Response $response) => $response->send($router->map()));



$router->use('/modules', fn(Request $request, Response $response) => $response->json(ModuleDefinition::fetchAll()));



$app->serve();