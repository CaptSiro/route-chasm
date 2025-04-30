<?php

namespace components\core\Admin\Login;

use components\core\Message\Message;
use components\core\WebPage\WebPage;
use components\layout\Spotlight\Spotlight;
use components\layout\Spotlight\Switch\SpotlightSwitchLink;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\forms\controls\HiddenField;
use core\forms\controls\PasswordField\PasswordField;
use core\forms\controls\Submit\Submit;
use core\forms\controls\TextField;
use core\forms\Form;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\http\HttpMethod;
use core\url\UrlBuilder;
use core\view\ContainerContent;
use core\view\View;
use models\core\User\User;

class AdminLogin extends ContainerContent {
    public const PASSWORD = 'ADMIN_LOGIN_PASSWORD';
    public const QUERY_LOGOUT = '__logout';

    private const METHOD_USER = 'user';
    private const METHOD_ENV = 'env';

    private const FIELD_METHOD = 'method';
    private const FIELD_TAG = 'tag';
    private const FIELD_PASSWORD = 'password';

    public static function createLogoutUrl(UrlBuilder $builder): string {
        $builder->setQuery(self::QUERY_LOGOUT);
        return $builder->build();
    }



    protected WebPage $page;

    public function __construct() {
        parent::__construct($this->page = new WebPage());
    }



    public function isMiddleware(): bool {
        return true;
    }

    public function createLoginForm(): View {
        $userLogin = new Form(HttpMethod::POST, namespace: self::METHOD_USER);

        $userLogin->add(Form::title('Admin Login'));
        $userLogin->add(new TextField(self::FIELD_TAG, 'Tag'));
        $userLogin->add(new PasswordField(self::FIELD_PASSWORD, 'Password'));
        $userLogin->add(new HiddenField(self::FIELD_METHOD, self::METHOD_USER));
        $userLogin->add(new SpotlightSwitchLink('Login via .env password ', 'env', 'here'));
        $userLogin->add(new Submit());

        $envLogin = new Form(HttpMethod::POST, namespace: self::METHOD_ENV);

        $envLogin->add(Form::title('.env Admin Login'));
        $envLogin->add(new PasswordField(self::FIELD_PASSWORD, 'Password'));
        $envLogin->add(new HiddenField(self::FIELD_METHOD, self::METHOD_ENV));
        $envLogin->add(new SpotlightSwitchLink('Login via user account ', 'user', 'here'));
        $envLogin->add(new Submit());

        return new Spotlight([
            self::METHOD_USER => $userLogin,
            self::METHOD_ENV => $envLogin
        ]);
    }

    public function execute(Request $request, Response $response): void {
        // todo
        // change to User::fromSession($request->session())->inGroup(Admin);
        if ($request->getSession()->exists(App::KEY_USER)) {
            $url = $request->getUrl();
            $logout = $url->getQuery()->exists(self::QUERY_LOGOUT);
            if ($logout) {
                $url->getQuery()->remove(self::QUERY_LOGOUT);
                $request->getSession()->remove(App::KEY_USER);
                $response->redirect($url->full());
            }

            return;
        }

        $this->page
            ->getHead()
            ->setTitle('Login');

        switch ($request->httpMethod) {
            case HttpMethod::GET: {
                parent::execute($request, $response);
                break;
            }

            case HttpMethod::POST: {
                $body = $request->getBody();
                $method = $body->getStrict(self::FIELD_METHOD);
                $password = $body->getStrict(self::FIELD_PASSWORD);

                if ($method === self::METHOD_ENV) {
                    if (App::getInstance()->getEnv()->get(self::PASSWORD) !== $password) {
                        $response->setStatus(HttpCode::CE_BAD_REQUEST);
                        $response->renderRoot(new Message('The password is wrong'));
                    }

                    $user = User::fromTag(User::TAG_ROOT);
                    $request->getSession()->set(App::KEY_USER, $user->id);

                    $response->setStatus(HttpCode::S_OK);
                    $response->setHeader(HttpHeader::X_NEXT, $request->getUrl()->full());
                    $response->flush();
                }

                if ($method === self::METHOD_USER) {
                    $tag = $body->getStrict(self::FIELD_TAG);
                    $user = User::fromTag($tag);

                    if (!password_verify($password, $user->password)) {
                        $response->setStatus(HttpCode::CE_BAD_REQUEST);
                        $response->renderRoot(new Message('The password is wrong or the user is not admin'));
                    }

                    if (!$user->isAdmin()) {
                        $response->setStatus(HttpCode::CE_BAD_REQUEST);
                        $response->renderRoot(new Message('The password is wrong or the user is not admin'));
                    }

                    $request->getSession()->set(App::KEY_USER, $user->id);

                    $response->setStatus(HttpCode::S_OK);
                    $response->setHeader(HttpHeader::X_NEXT, $request->getUrl()->full());
                    $response->flush();
                }

                $response->setStatus(HttpCode::CE_BAD_REQUEST);
                $response->flush();
                break;
            }

            default: {
                $response->sendMessage(
                    'Invalid HTTP method ' . $request->httpMethod,
                    HttpCode::CE_BAD_REQUEST
                );
            }
        }
    }
}