<?php
namespace adjai\backender\models;

use adjai\backender\core\Core;
use adjai\backender\core\Error;
use adjai\backender\core\Mail;
use adjai\backender\core\Utils;

class User extends \adjai\backender\core\DBModel {

    public static function prepare($users) {
        return self::_batchPrepare($users, function($user) {
            $removeKeys = ['password', 'refresh_token', 'refresh_token_expire'];
            return array_diff_key($user, array_flip($removeKeys));
        });
    }

    public static function get($id, $ifSecure = true) {
        $user = self::_getOne(['id' => $id]);
        $user['roles'] = json_decode($user['roles']);
        return $ifSecure ? self::prepare($user) : $user;
    }

    public static function auth($email, $password, $network = null) {
        $where = [
            'email' => $email,
            'network' => is_null($network) ? [null, 'IS'] : $network,
        ];
        if (!is_null($password)) {
            $where['password'] = md5($password);
        }
        $user = self::_getOne($where);
        if (is_null($user) || !is_null($user['deleted_time'])) {
            return new Error('Пользователь не найден');
        } else {
            if (!is_null($user['blocked_time'])) {
                return new Error('Пользователь заблокирован');
            } elseif (is_null($user['activated_time'])) {
                return new Error('Аккаунт неактивирован');
            }
            return self::getAuthById($user['id']);
        }
    }

    public static function getAuthById($id) {
        $refreshToken = self::updateRefreshToken($id);
        list($token, $tokenExpire) = self::getToken($id);
        $user = self::get($id);
        $user['registration_incomplete'] = !is_null(UserMeta::get($id, '__registration_incomplete', null));
        return compact('token', 'refreshToken', 'user', 'tokenExpire');
    }

    public static function refreshToken($refreshToken) {
        $userId = self::_getValue('id', [
            'deleted_time' => [null, 'IS'],
            'blocked_time' => [null, 'IS'],
            'refresh_token' => $refreshToken,
            'refresh_token_expire > NOW()' => 'RAW_QUERY',
        ]);
        if (is_null($userId)) {
            return false;
        } else {
            $refreshToken = self::updateRefreshToken($userId);
            list($token, $tokenExpire) = self::getToken($userId);
            $user = self::get($userId);
            return compact('token', 'refreshToken', 'user', 'tokenExpire');
        }
    }

    private static function updateRefreshToken($id) {
        $refreshToken = md5(random_bytes(10));
        self::_update(['id' => $id], [
            'refresh_token' => $refreshToken,
            'refresh_token_expire' => date('Y-m-d H:i:s', time() + JWT_REFRESH_TOKEN_EXPIRE),
        ]);
        return $refreshToken;
    }

    private static function getToken($id) {
        $expire = time() + JWT_TOKEN_EXPIRE;
        $user = self::get($id, false);
        $payload = [
            'user_id' => $id,
            'roles' => $user['roles'],
            'exp' => $expire,
        ];
        $token = \Firebase\JWT\JWT::encode($payload, JWT_SECRET_KEY, 'HS256');
        return [$token, $expire];
    }

    public static function create($email, $password, $roles, $name = '', $network = null, $network_user_id = null, $meta = []) {
        $user = self::_getOne([
            'email' => $email,
            'network' => is_null($network) ? [null, 'IS'] : $network,
        ]);
        if (is_null($user)) {
            $roles = json_encode($roles);
            $password = md5($password);
            $created = date('Y-m-d H:i:s');
            $id = self::_insert(compact('name', 'email', 'password', 'roles', 'network', 'network_user_id', 'created'));
            foreach ($meta as $key => $value) {
                UserMeta::add($id, $key, $value);
            }
            return $id;
        } else {
            return new Error('Пользователь с данным email уже зарегистрирован в системе');
        }
    }

    public static function blockUser($id) {
        self::_update(['id' => $id], ['blocked_time' => Core::$db->now()]);
    }

    public static function unblockUser($id) {
        self::_update(['id' => $id], ['blocked_time' => null]);
    }

    public static function removeUser($id) {
        self::_update(['id' => $id], ['deleted_time' => Core::$db->now()]);
    }

    public static function updatePassword($userId, $password) {
        self:self::_update(['id' => $userId], ['password' => $password]);
    }

    public static function getByNetwork($network, $networkUserId) {
        return self::_getOne([
            'network' => $network,
            'network_user_id' => $networkUserId,
        ]);
    }

    public static function getByEmail($email) {
        return self::_getOne(['email' => $email]);
    }

    public static function list() {
        return self::_get();
    }

    public static function getResetPasswordLink($id) {
        $code = md5(uniqid());
        $expire = time() + RESET_PASSWORD_EXPIRE;
        UserMeta::update($id, '__reset_password_code', $code);
        UserMeta::update($id, '__reset_password_expire', $expire);
        return SITE_FRONTEND_URL . "reset-password/$id/$code";
    }

    public static function activate($id, $activationCode = false) {
        if ($activationCode === false) {
            self::_update(['id' => $id], ['activated_time' => date('Y-m-d H:i:s')]);
            return true;
        }
        $user = self::get($id);
        if (!is_null($user) && self::checkAccountActivationCode($id, $activationCode)) {
            UserMeta::remove($id, '__account_activation_code');
            UserMeta::remove($id, '__account_activation_code_expire');
            self::_update(['id' => $id], ['activated_time' => date('Y-m-d H:i:s')]);
            return true;
        } else {
            return false;
        }
    }

    public static function sendActivation($userId) {
        $user = self::get($userId);
        if (!is_null($user)) {
            $accountActivationCode = uniqid('', true);
            UserMeta::update($user['id'], '__account_activation_code', $accountActivationCode);
            UserMeta::update($user['id'], '__account_activation_code_expire', time() + ACCOUNT_ACTIVATION_LINK_LIFETIME);
            $user['activationLink'] = SITE_FRONTEND_URL . "auth/activation/$userId/" . $accountActivationCode;
            Mail::sendUsingTemplate('user-activation', $user['email'], null, Utils::arrayFlatten($user));
        }
    }

    public static function checkAccountActivationCode($userId, $accountActivationCode) {
        $correctResetPasswordCode = UserMeta::get($userId, '__account_activation_code');
        //echo "<pre>";var_dump($correctResetPasswordCode, $accountActivationCode);echo "</pre>";exit;
        if ($correctResetPasswordCode === $accountActivationCode) {
            $expire = UserMeta::get($userId, '__account_activation_code_expire');
            return time() <= $expire;
        }
        return false;
    }

    public static function checkResetPasswordCode($userId, $resetPasswordCode) {
        $correctResetPasswordCode = UserMeta::get($userId, '__reset_password_code');
        if ($correctResetPasswordCode === $resetPasswordCode) {
            $expire = UserMeta::get($userId, '__reset_password_expire');
            return time() <= $expire;
        }
        return false;
    }

}
