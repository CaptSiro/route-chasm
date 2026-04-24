<?php

namespace components\core\Admin\Login;

use components\core\Message\Message;
use components\core\WebPage\WebPage;
use components\layout\Spotlight\Spotlight;
use components\layout\Spotlight\Switch\SpotlightSwitchLink;
use core\actions\UnexpectedHttpMethod;
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
use core\RouteChasmEnvironment;
use core\url\Url;
use core\view\ContainerContent;
use core\view\View;
use models\core\Setting\Setting;
use models\core\User\User;

class AdminLogin extends ContainerContent {
    use UnexpectedHttpMethod;



    public const LEXICON_GROUP = 'admin.login';

    private const METHOD_USER = 'user';
    private const METHOD_ENV = 'env';

    private const FIELD_METHOD = 'method';
    private const FIELD_TAG = 'tag';
    private const FIELD_PASSWORD = 'password';



    public static function createLogoutUrl(Url $url): string {
        return $url
            ->copy()
            ->setQueryArgument(RouteChasmEnvironment::QUERY_LOGOUT)
            ->toString();
    }



    protected WebPage $page;

    public function __construct() {
        parent::__construct($this->page = new WebPage());
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function isMiddleware(): bool {
        return true;
    }

    public function useEnvPasswordMethod(): bool {
        $setting = Setting::fromName(RouteChasmEnvironment::SETTING_ENV_PASSWORD);

        if (is_null($setting)) {
            $setting = new Setting();

            $setting->name = RouteChasmEnvironment::SETTING_ENV_PASSWORD;
            $setting->value = 'yes';
            $setting->editable = true;

            $setting->save();
        }

        return $setting->toBoolean();
    }

    public function createLoginForm(): View {
        $useEnvPasswordMethod = $this->useEnvPasswordMethod();

        $userLogin = new Form(HttpMethod::POST, namespace: self::METHOD_USER);

        $userLogin->add(Form::title($this->tr('Admin Login')));
        $userLogin->add(new TextField(self::FIELD_TAG, $this->tr('Tag')));
        $userLogin->add(new PasswordField(self::FIELD_PASSWORD, $this->tr('Password')));
        $userLogin->add(new HiddenField(self::FIELD_METHOD, self::METHOD_USER));

        if ($useEnvPasswordMethod) {
            $userLogin->add(new SpotlightSwitchLink(
                $this->tr('Login with .env password '),
                'env',
                $this->tr('here')
            ));
        }

        $userLogin->add(new Submit());

        if (!$useEnvPasswordMethod) {
            return $userLogin;
        }

        $envLogin = new Form(HttpMethod::POST, namespace: self::METHOD_ENV);

        $envLogin->add(Form::title($this->tr('.env Admin Login')));
        $envLogin->add(new PasswordField(self::FIELD_PASSWORD, $this->tr('Password')));
        $envLogin->add(new HiddenField(self::FIELD_METHOD, self::METHOD_ENV));
        $envLogin->add(new SpotlightSwitchLink(
            $this->tr('Login with user account '),
            'user',
            $this->tr('here')
        ));
        $envLogin->add(new Submit());

        return new Spotlight([
            self::METHOD_USER => $userLogin,
            self::METHOD_ENV => $envLogin
        ]);
    }

    public function perform(Request $request, Response $response): void {
        $url = $request->getUrl();
        $logout = $url->getQuery()->exists(RouteChasmEnvironment::QUERY_LOGOUT);
        if ($logout) {
            $url->getQuery()->remove(RouteChasmEnvironment::QUERY_LOGOUT);
            User::logout();
            $response->redirect($url->toString());
        }

        if (User::fromRequest($request)->isAdmin()) {
            return;
        }

        $this->page
            ->getHead()
            ->setTitle($this->tr('Login'));

        switch ($request->getHttpMethod()) {
            case HttpMethod::GET: {
                $response->renderRoot($this);
                break;
            }

            case HttpMethod::POST: {
                $body = $request->getBody();
                $method = $body->getStrict(self::FIELD_METHOD);
                $password = $body->getStrict(self::FIELD_PASSWORD);

                if ($method === self::METHOD_ENV) {
                    if (!$this->useEnvPasswordMethod()) {
                        $response->setStatus(HttpCode::CE_METHOD_NOT_ALLOWED);
                        $response->renderRoot(new Message(
                            $this->tr('.env password method is not allowed')
                        ));
                    }

                    if (App::getInstance()->getEnv()->get(RouteChasmEnvironment::ENV_ADMIN_LOGIN_PASSWORD) !== $password) {
                        $response->setStatus(HttpCode::CE_BAD_REQUEST);
                        $response->renderRoot(new Message($this->tr('The password is wrong')));
                    }

                    User::fromTag(User::TAG_ROOT)?->login();

                    $response->setStatus(HttpCode::S_OK);
                    $response->setHeader(HttpHeader::X_RELOAD, 'Reload');
                    $response->flush();
                }

                if ($method === self::METHOD_USER) {
                    $tag = $body->getStrict(self::FIELD_TAG);
                    $user = User::fromTag($tag);

                    if (!password_verify($password, $user->password)) {
                        $response->setStatus(HttpCode::CE_BAD_REQUEST);
                        $response->renderRoot(new Message($this->tr('The password is wrong')));
                    }

                    if (!$user->isAdmin()) {
                        $response->setStatus(HttpCode::CE_BAD_REQUEST);
                        $response->renderRoot(new Message(
                            $this->tr('The user does not have adequate privilege to login as Admin')
                        ));
                    }

                    $user->login();

                    $response->setStatus(HttpCode::S_OK);
                    $response->setHeader(HttpHeader::X_RELOAD, 'Reload');
                    $response->flush();
                }

                $response->setStatus(HttpCode::CE_BAD_REQUEST);
                $response->flush();
                break;
            }

            default: {
                $this->handleUnexpectedMethod($request, $response);
                break;
            }
        }
    }
}