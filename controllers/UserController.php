<?php
namespace adjai\backender\controllers;

use adjai\backender\core\Core;
use adjai\backender\core\Error;
use adjai\backender\core\Language;
use adjai\backender\core\Mail;
use adjai\backender\core\Response;
use adjai\backender\core\Router;
use adjai\backender\core\Utils;
use adjai\backender\models\User;
use adjai\backender\models\UserMeta;
use WpOrg\Requests\Requests;

class UserController extends \adjai\backender\core\Controller {

    public function actionAuth($email, $password) {
        $result = User::auth($email, $password);
        if (!$result instanceof Error) {
            $this->outputData($result);
        } else {
            $this->outputError($result->getMessage());
        }
    }

    public function actionRegister($userRegisterInfo) {
        $authorizationData = Core::getAuthorizationData();
        if ($authorizationData instanceof Error) {
            $allowedRoles = [REGISTER_DEFAULT_USER_ROLE];
        } else {
            $allowedRoles = [];
            $roles = $authorizationData->roles;
            foreach ($roles as $role) {
                if (isset(REGISTER_ALLOWED_ROLES[$role])) {
                    $allowedRoles = array_merge($allowedRoles, REGISTER_ALLOWED_ROLES[$role]);
                }
            }
            $allowedRoles = array_unique($allowedRoles);
        }

        $userRegisterInfo['roles'] = array_map(function($role) {
            return $role === '' ? REGISTER_DEFAULT_USER_ROLE : $role;
        }, $userRegisterInfo['roles']);

        if (count(array_diff($userRegisterInfo['roles'], $allowedRoles))) {
            $this->outputError('Недостаточно прав доступа');
        }

        $result = User::create($userRegisterInfo['email'], $userRegisterInfo['password'], $userRegisterInfo['roles'], $userRegisterInfo['name'], $userRegisterInfo['network'] ?? null, $userRegisterInfo['network_user_id'] ?? null, $userRegisterInfo['meta'] ?? []);
        if ($result instanceof Error) {
            $this->outputError($result->getMessage());
        } else {
            $id = $result;
            if (NEED_ACTIVATION) {
                User::sendActivation($id);
            } else {
                User::activate($id);
            }
            $this->outputData(User::get($id));
        }
    }

    public function actionSocialAuth() {
        $accessCodeInfo = Router::getInstance()->getInputData();
        if (isset($accessCodeInfo['error'])) {
            $this->outputError($accessCodeInfo['error']);
        }
        $requestUserInfoUri = $accessCodeInfo['requestUserInfoUri'];
        $requestUserInfoUri = preg_replace_callback('|(\{([^}]+)\})|', function($matches) use ($accessCodeInfo) {
            return $accessCodeInfo[$matches[2]] ?? $matches[1];
        }, $requestUserInfoUri);
        if ($accessCodeInfo['provider'] === 'MailRu') {
            /*$signature = md5("app_id=" . $accessCodeInfo['clientId'] . "method=users.getInfosecure=1session_key=" . $accessCodeInfo['access_token'] . $accessCodeInfo['clientSecret']);
            $requestUserInfoUri = str_replace('{signature}', $signature, $requestUserInfoUri);*/
        } elseif ($accessCodeInfo['provider'] === 'Ok') {
            preg_match('/(?:\?|&)fields=([^&]+)(?:$|&)/', $requestUserInfoUri, $matches);
            $sessionSecretKey = $accessCodeInfo['session_secret_key'];
            $params = [
                'application_key' => $accessCodeInfo['publicKey'],
                'access_token' => $accessCodeInfo['access_token'],
                'fields' => $matches[1],
            ];
            ksort($params);
            $signature = strtolower(md5(implode('', array_map(function($key) use ($params) {
                    return "$key=" . $params[$key];
                }, array_filter(array_keys($params), function($key) {
                    return $key !== 'access_token';
                }))) . $sessionSecretKey));
            $requestUserInfoUri = str_replace('{signature}', $signature, $requestUserInfoUri);
        }
        $response = Requests::get($requestUserInfoUri);
        $info = json_decode($response->body, true);
        $registrationInfo = null;

        if ($accessCodeInfo['provider'] === 'VK') {
            $info = $info['response'][0];
            $registrationInfo = [
                'name' => trim($info['last_name'] . ' ' . $info['first_name']),
                'email' => $accessCodeInfo['email'],
                'network_user_id' => $accessCodeInfo['user_id'],
                'meta' => [
                    'city' => $info['city']['title'],
                    'country' => $info['country']['title'],
                    'first_name' => $info['first_name'],
                    'last_name' => $info['last_name'],
                ],
            ];
        } elseif ($accessCodeInfo['provider'] === 'Google') {
            $registrationInfo = [
                'name' => $info['name'],
                'email' => $info['email'],
                'network_user_id' => $info['sub'],
                'meta' => [
                    'first_name' => $info['given_name'],
                    'last_name' => $info['family_name'],
                ],
            ];
        } elseif ($accessCodeInfo['provider'] === 'MailRu') {
            $info = $info[0];
            $registrationInfo = [
                'name' => trim($info['last_name'] . ' ' . $info['first_name']),
                'email' => $info['email'],
                'network_user_id' => $info['uid'],
                'meta' => [
                    'first_name' => $info['first_name'],
                    'last_name' => $info['last_name'],
                    'age' => $info['age'],
                    'sex' => $info['sex'],
                    'birthday' => $info['birthday'],
                ],
            ];
            if ($info['has_pic']) {
                $requestUserInfoUri['meta']['picture'] = $info['pic_big'];
            }
        } elseif ($accessCodeInfo['provider'] === 'Ok') {
            $registrationInfo = [
                'name' => trim($info['last_name'] . ' ' . $info['first_name']),
                'email' => $info['uid'] . '@ok.ru', // todo Написать в техподдержку Одноклассников, т.к. email они выдают через api только по письму https://apiok.ru/ext/oauth/permissions
                'network_user_id' => $info['uid'],
                'meta' => [
                    'first_name' => $info['first_name'],
                    'last_name' => $info['last_name'],
                    'age' => $info['age'],
                    'gender' => $info['gender'],
                    'birthday' => $info['birthday'],
                    'picture' => $info['pic_full'],
                    'url_profile' => $info['url_profile'],
                    'city' => $info['location']['city'],
                    'country' => $info['location']['countryName'],
                ],
            ];
        }
        $info['token_info'] = $accessCodeInfo;
        if (!is_null($registrationInfo)) {
            $user = User::getByNetwork($accessCodeInfo['provider'], $registrationInfo['network_user_id']);
            if (is_null($user)) {
                $userId = User::create($registrationInfo['email'], uniqid(), [REGISTER_DEFAULT_USER_ROLE], $registrationInfo['name'], $accessCodeInfo['provider'], $registrationInfo['network_user_id'], $registrationInfo['meta'] ?? []);
                //echo "<pre>";var_dump(Core::$db->getLastError());echo "</pre>";
                User::activate($userId);
            }
            //exit;
            $this->outputData(User::auth($registrationInfo['email'], null, $accessCodeInfo['provider']));
        }
        //$this->outputData($inputData);
    }

    public function actionSocialAuthOld($network, $networkUserId, $accessToken, $extraInfo = []) {
        // https://www.googleapis.com/oauth2/v3/userinfo?access_token=
        if ($network === 'vk.com') {
            $url = "https://api.vk.com/method/users.get?user_ids=$networkUserId&fields=city,country&access_token=$accessToken&v=5.131";
            $response = Requests::get($url);
            $socialInfo = json_decode($response->body, true)['response'][0];
            $userInfo = [
                'name' => trim($socialInfo['last_name'] . ' ' . $socialInfo['first_name']),
                'email' => $extraInfo['email'],
            ];
        }

        $user = User::getByNetwork($network, $networkUserId);
        //$this->outputData($socialInfo);exit;
        if (is_null($user)) {
            $userId = User::create($userInfo['email'], uniqid(), [REGISTER_DEFAULT_USER_ROLE], $userInfo['name'], $network, $networkUserId);
            User::activate($userId);
            //$user = User::get($userId);
        }
        $this->outputData(User::auth($userInfo['email'], null, $network));
    }

    public function actionRefreshToken($refreshToken) {
        $result = User::refreshToken($refreshToken);
        if ($result !== false) {
            $this->outputData($result);
        } else {
            $this->outputError('refresh_token_not_found');
        }
    }

    public function actionRequestResetPassword($email) {
        $user = User::getByEmail($email);
        if (is_null($user)) {
            $this->outputError('REG_EMAIL_EXIST_MESSAGE');
        } else {
            $resetLink = User::getResetPasswordLink($user['id']);
            Mail::sendUsingTemplate('request-reset-password', $user['email'], null, compact('resetLink'));
            $this->outputData();
        }
    }

    public function actionResetPassword($password, $code, $userId) {
        $userId2 = UserMeta::getUserIdByMeta('__reset_password_code', $code);
        if (+$userId === $userId2 && !is_null($userId)) {
            $expire = +UserMeta::get($userId, '__reset_password_expire');
            if ($expire > time()) {
                UserMeta::remove($userId, '__reset_password_code');
                UserMeta::remove($userId, '__reset_password_expire');
                User::updatePassword($userId, md5($password));
                $this->outputData();
            } else {
                $this->outputError('RESET_PASSWORD_CODE_EXPIRED_MESSAGE');
            }
        } else {
            $this->outputError('RESET_PASSWORD_CODE_WRONG');
        }
    }

    public function actionActivate($id, $activationCode) {
        if (User::activate($id, $activationCode)) {
            $this->outputResponse(new Response(true, 'ACTIVATION_PROCESSED_SUCCESSFUL_MESSAGE', User::getAuthById($id)));
        } else {
            $this->outputError('WRONG_RESET_PASSWORD_LINK_MESSAGE');
        }
    }

    public function actionCheckResetPassword($id, $code) {
        if (User::checkResetPasswordCode($id, $code)) {
            $this->outputData();
        } else {
            $this->outputError('WRONG_RESET_PASSWORD_LINK_MESSAGE');
        }
    }

    public function actionFeedback() {
        if (defined('ADMIN_EMAIL')) {
            if (Mail::sendUsingTemplate('feedback', ADMIN_EMAIL)) {
                $this->outputData();
            } else {
                $this->outputError('Непредвиденная ошибка отправки сообщения');
            }
        } else {
            $this->outputError('Ошибка отправки: email администратора не задан');
        }
    }

}
