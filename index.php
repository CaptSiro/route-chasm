<?php

use components\core\Admin\Home\AdminHome;
use components\core\HttpMessage\HttpMessage;
use components\core\WebPage\WebPage;
use components\layout\Accordion\Accordion;
use components\resources\Cards\Cards;
use components\Tabs\Tabs;
use core\AdminRouter;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\config\AppConfig;
use core\config\EnvConfig;
use core\database_v3\sql\connections\MySqlDriver;
use core\database_v3\sql\Sql;
use core\endpoints\Procedure;
use core\http\Http;
use core\http\HttpCode;
use core\http\HttpMethod;
use entities\core\ModuleDefinition;
use modules\forms\controls\Checkbox\Checkbox;
use modules\forms\controls\File\File;
use modules\forms\controls\MultiSubmit\MultiSubmit;
use modules\forms\controls\PasswordField\PasswordField;
use modules\forms\controls\TextArea\TextArea;
use modules\forms\controls\TextField;
use modules\forms\Form;
use modules\forms\FormAction;
use modules\forms\layout\Column\Column;
use modules\forms\layout\Row\Row;
use modules\SideLoader\Javascript;

require_once __DIR__ ."/src/autoload.php";


$config = new EnvConfig(App::getEnvStatic());
AppConfig::getInstance()
    ->set($config);

Sql::connect("app", new MySqlDriver(
    $config->getSqlConfigV3()
));

$app = App::getInstance();
$app->getOptions()->set(App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH, true);
$app->getOptions()->set(App::OPTION_DO_ADD_HOME_TO_URL_PATH, true);

$router = $app->getMainRouter();



$router->bind('/admin', AdminRouter::getInstance(new AdminHome()));



$router->expose("/public", (new \core\endpoints\Directory(__DIR__ . "/public"))
    ->onDirectory(\core\endpoints\Directory::showExplorer()));

$router->use(
    "/error",
    new HttpMessage("I'm a teapot", HttpCode::CE_IM_A_TEAPOT)
);

$router->use(
    '/ping',
    Procedure::middleware(fn() => Javascript::import(Cards::getInstance()->getResource('ping.js'))),
    fn(Request $request, Response $response) => $response->send(
        '<h2 style="color: whitesmoke" x-swap="outer" x-get="'. App::getInstance()->prependHome('/dong?s') .'">pong</h2>'
    )
);
$router->use(
    '/dong',
    fn(Request $request, Response $response) => $response->send('<button x-swap="outer" x-get="'. App::getInstance()->prependHome('/ping?s') .'">Back to ping</button>')
);

$user = new Accordion(
    'User info',
    (new Row(.50))
        ->add(new TextField('Name', 'Name', 'Tonda'))
        ->add(new TextField('Surname', 'Surname', 'Maly'))
        ->add(new PasswordField('Password', 'Password', 'foo123'))
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
        new FormAction(FormAction::TYPE_SUBMIT, 'Delete'),
        FormAction::submit(),
    ]));

$form = (new Form(HttpMethod::DELETE))
    ->add(new Tabs([
        "User" => $c0,
        "Description" => $c1
    ]));
$router->use("/form",
    Http::get((new WebPage())->addContent($form)),
    Http::delete(function(Request $request, Response $response) {
        $body = $request->getBody()->asArray();
        $body['files'] = $request->getFiles()->asArray();

        $response->json($body);
    })
);



$router->resource("/cards", Cards::getInstance());
$router->use("/map", fn(Request $request, Response $response) => $response->send($router->map()));



$router->use('/modules', fn(Request $request, Response $response) => $response->json(ModuleDefinition::fetchAll()));


$menu = new \components\core\Menu\Menu();
$menu
    ->add('Admin', 'admin-item')
    ->add('/Item', 'item')
    ->add('/Item/Sub Item', 'sub-item')
    ->add('/Item 2', 'item-2')
    ->add('/Item 3/Sub Item', 'sub-item')
    ->add('/Item 3/Sub Item', 'change')
    ->add('/Item 3/Sub Item 2', 'sub-item-2')
;
$router->use('/menu', fn(Request $request, Response $response) => $response->render($menu));



$router->use('/db', function(Request $request, Response $response) {
    var_dump(
        \core\database_v3\entities\Setting::all()
    );

    $domain = \core\database_v3\entities\Domain::fromId(18);
    var_dump($domain);
    $domain?->delete();

    $response->flush();
});



$app->serve();