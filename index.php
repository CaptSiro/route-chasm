<?php

use components\core\HttpError\HttpError;
use components\core\WebPage\WebPage;
use components\resources\Cards\Cards;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\config\EnvConfig;
use core\http\Http;
use core\http\HttpCode;
use core\http\HttpMethod;
use modules\forms\controls\Checkbox\Checkbox;
use modules\forms\controls\File;
use modules\forms\controls\Submit\Submit;
use modules\forms\controls\Text;
use modules\forms\controls\TextArea\TextArea;
use modules\forms\Form;
use modules\forms\layout\Row\Row;
use modules\SideLoader\Javascript;
use sptf\Sptf;

require_once __DIR__ ."/src/autoload.php";



$app = App::getInstance();
$config = new EnvConfig($app->getEnv());
$app->setConfig($config);
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



$userRow = (new Row(2))
    ->add(new Text('Name', 'Name', 'Tonda'))
    ->add(new Text('Surname', 'Surname', 'Maly'));
$text = "My text 
with line";
$form = (new Form(HttpMethod::DELETE))
    ->add($userRow)
    ->add(new TextArea("Area", "Description", $text))
    ->add((new File("Image", 'Image'))->addAttribute('multiple'))
    ->add(new Checkbox('Read', 'I have read TOS'))
    ->add(Form::note("Submitting form you are giving us consent to get all your money"))
    ->add(Form::hr())
    ->add(new Submit());
$router->use("/form",
    Http::get(new WebPage(content: $form)),
    Http::delete(function(Request $request, Response $response) {
        $response->json($request->getBody());
    })
);



$router->resource("/cards", Cards::getInstance());
$router->use("/map", fn(Request $request, Response $response) => $response->send($router->map()));



$app->serve();