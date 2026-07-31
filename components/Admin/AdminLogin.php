<?php

namespace components\Admin;

use components\forms\controls\HiddenField;
use components\forms\controls\PasswordField;
use components\forms\controls\Submit;
use components\forms\controls\TextField;
use components\forms\Form;
use components\layout\Spotlight\Spotlight;
use components\layout\Spotlight\SpotlightSwitchLink;
use core\actions\UnexpectedHttpMethod;
use core\App;
use core\communication\body\DictionaryBody;
use core\communication\Request;
use core\communication\Response;
use core\http\HttpCode;
use core\http\HttpMethod;
use core\locale\LexiconUnit;
use core\RouteChasmEnvironment;
use core\url\Url;
use core\view\Component;
use core\view\Controller;
use core\view\PageView;
use core\view\Renderer;
use core\view\View;
use models\Setting\Setting;
use models\User\User;

class AdminLogin extends Controller {
    use UnexpectedHttpMethod, LexiconUnit;



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



    public function __construct(
        ?Renderer $renderer = null
    ) {
        parent::__construct($renderer);
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
        $useEnvPasswordMethod = $this->useEnvPasswordMethod()
            && !is_null(App::getInstance()->getEnv()->get(RouteChasmEnvironment::ENV_ADMIN_LOGIN_PASSWORD));

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

        Component::propagateSetRenderer($spotlight = new Spotlight([
            self::METHOD_USER => $userLogin,
            self::METHOD_ENV => $envLogin
        ]), $this->renderer);

        return $spotlight;
    }

    public function perform(Request $request, Response $response): void {
        $messageEnvMethodNotAllowed = $this->tr('.env password method is not allowed');
        $messageWrongPassword = $this->tr('The password is wrong');
        $messageUserNotFound = $this->tr('User not found');
        $messageForbidden = $this->tr('The user does not have adequate privilege to login as Admin');

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

        $this->setTitle($this->tr('Login'));

        switch ($request->getHttpMethod()) {
            case HttpMethod::GET: {
                $response->render(PageView::fromComponent($this));
                break;
            }

            case HttpMethod::POST: {
                $fields = $request
                    ->body(DictionaryBody::class)
                    ->getFields();

                $method = $fields->getStrict(self::FIELD_METHOD);
                $password = $fields->getStrict(self::FIELD_PASSWORD);

                if ($method === self::METHOD_ENV) {
                    if (!$this->useEnvPasswordMethod()) {
                        $response->sendMessage($messageEnvMethodNotAllowed, HttpCode::CE_METHOD_NOT_ALLOWED);
                    }

                    if (App::getInstance()->getEnv()->get(RouteChasmEnvironment::ENV_ADMIN_LOGIN_PASSWORD) !== $password) {
                        $response->sendMessage($messageWrongPassword, HttpCode::CE_BAD_REQUEST);
                    }

                    User::fromTag(User::TAG_ROOT)?->login();

                    $response
                        ->setStatus(HttpCode::S_OK)
                        ->addReloadHeader()
                        ->flush();
                }

                if ($method === self::METHOD_USER) {
                    $tag = $fields->getStrict(self::FIELD_TAG);
                    $user = User::fromTag($tag);

                    if (is_null($user)) {
                        $response->sendMessage($messageUserNotFound, HttpCode::CE_BAD_REQUEST);
                    }

                    if (!password_verify($password, $user->password)) {
                        $response->sendMessage($messageWrongPassword, HttpCode::CE_BAD_REQUEST);
                    }

                    if (!$user->isAdmin()) {
                        $response->sendMessage($messageForbidden, HttpCode::CE_FORBIDDEN);
                    }

                    $user->login();

                    $response
                        ->setStatus(HttpCode::S_OK)
                        ->addReloadHeader()
                        ->flush();
                }

                $response
                    ->setStatus(HttpCode::CE_BAD_REQUEST)
                    ->flush();

                break;
            }

            default: {
                $this->handleUnexpectedMethod($request, $response);
                break;
            }
        }
    }
}