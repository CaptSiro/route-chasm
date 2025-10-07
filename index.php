<?php

use components\core\Admin\Home\AdminHome;
use components\core\HttpMessage\HttpMessage;
use components\core\WebPage\WebPage;
use components\Home\Home;
use components\layout\Accordion\Accordion;
use components\layout\Column\Column;
use components\layout\Row\Row;
use components\layout\Tabs\Tabs;
use core\actions\Assets\Assets;
use core\actions\Assets\policy\ShowExplorerPolicy;
use core\admin\AdminRouter;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\configs\AppConfig;
use core\configs\EnvConfig;
use core\database\sql\connections\MySqlDriver;
use core\database\sql\Sql;
use core\forms\controls\Checkbox\Checkbox;
use core\forms\controls\File\File;
use core\forms\controls\MultiSelect\MultiSelect;
use core\forms\controls\MultiSubmit\MultiSubmit;
use core\forms\controls\PasswordField\PasswordField;
use core\forms\controls\Select\Select;
use core\forms\controls\Submit\Submit;
use core\forms\controls\TextArea\TextArea;
use core\forms\controls\TextField;
use core\forms\Form;
use core\forms\FormAction;
use core\http\Http;
use core\http\HttpCode;
use core\http\HttpMethod;
use core\navigation\Navigator;
use core\pages\Pages;
use core\RouteChasmEnvironment;
use core\sideloader\SideLoader;

require_once __DIR__ ."/src/autoload.php";


$config = new EnvConfig(App::getEnvStatic());
AppConfig::getInstance()->set($config);

Sql::connect(App::DATABASE, new MySqlDriver(
    $config->getSqlConfig()
));

Pages::load();

$app = App::getInstance();

$router = $app->getMainRouter();



$router->bind('/import', SideLoader::getInstance()->initRouter($app));
$router->bind('/admin', AdminRouter::getInstance(new AdminHome()));



$router->expose('public', (new Assets(__DIR__ .'/public'))
    ->setDirectoryPolicy(new ShowExplorerPolicy()));


$router->use('/', new Home());

$router->use(
    "/error",
    new HttpMessage("I'm a teapot", HttpCode::CE_IM_A_TEAPOT)
);



$user = new Accordion(
    'User info',
    (new Row(.75))
        ->add(new TextField('Name', 'Name', 'Tonda'))
        ->add(new TextField('Surname', 'Surname', 'Maly'))
        ->add(new PasswordField('Password', 'Password', 'foo123', addVisibilityControl: true))
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
        $body = $request->getBody()->toArray();
        $body['files'] = $request->getFiles()->toArray();

        $response->json($body);
    })
);



$router->use('/exc', fn() => throw new Exception('Test exception'));
$router->use('/err', fn() => trigger_error("Test error", E_USER_ERROR));
//$router->use('/select',
//    Http::get(function(Request $request, Response $response) {
//        $countryList = [
//            'cz' => 'The Czech Republic',
//            'uk' => 'The United Kingdom',
//            'au' => 'Australia',
//            'ca' => 'Canada',
//            'me' => 'Mexico',
//            'aa' => 'Andorra',
//            'fr' => 'France',
//            'ch' => 'China',
//            'jp' => 'Japan',
//            'sk' => 'South Korea',
//            'nk' => 'North Korea',
//            'tw' => 'Taiwan',
//            'ge' => 'Germany',
//            'it' => 'Italy',
//            'us' => 'USA',
//            'ph' => 'Philippines',
//            'np' => 'Nepal',
//            'th' => 'Thailand',
//            'vn' => 'Vietnam'
//        ];
//
//        $selectForm = new Form(HttpMethod::POST);
//
//        $selectForm->add(new Select('country', 'Country', $countryList, 'jp'));
//        $selectForm->add(new MultiSelect('countries', 'Countries', $countryList, ['jp', 'sk', 'nk']));
//        $selectForm->add(new Submit());
//
//        $response->renderRoot((new WebPage())->addContent($selectForm));
//    }),
//    Http::post(fn(Request $request, Response $response) => $response->json(
//        $request->getBody()->toArray()
//    ))
//);

// $router->resource("/cards", Cards::getInstance());

// todo
//  - Router::map()
//  $router->use("/map", fn(Request $request, Response $response) => $response->send($router->map()));



// $router->use('/modules', fn(Request $request, Response $response) => $response->json(ModuleDefinition::fetchAll()));


//$menu = new \components\core\Menu\Menu();
//$menu
//    ->add('Admin', 'admin-item')
//    ->add('/Item', 'item')
//    ->add('/Item/Sub Item', 'sub-item')
//    ->add('/Item 2', 'item-2')
//    ->add('/Item 3/Sub Item', 'sub-item')
//    ->add('/Item 3/Sub Item', 'change')
//    ->add('/Item 3/Sub Item 2', 'sub-item-2')
//;
//$router->use('/menu', fn(Request $request, Response $response) => $response->render($menu));

//$router->use('/nav-bind', function (Request $request, Response $response) {
//    $lang = $request->getLanguage();
//    Navigator::addSlug($lang, \core\route\Path::from('my-custom-page'), \core\pages\PageFactory::getInstance(), 'My custom page');
//    $response->send('ok');
//});

Navigator::register(\core\pages\PageFactory::getInstance());

$router->bind(
    Navigator::mount(RouteChasmEnvironment::DEFAULT_CONTEXT_MOUNT, '/'),
    new Navigator()
);



//if (isset($_GET['_test'])) {
//    echo (new SptfTests(__DIR__ .'/src/tests/cases/RouteChasm'))->getRoot()->render();
//    exit();
//}

$app->serve();