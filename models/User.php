<?php
namespace adjai\backender\models;

use adjai\backender\core\Core;

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
            'deleted_time' => [null, 'IS'],
            'blocked_time' => [null, 'IS'],
            'network' => is_null($network) ? [null, 'IS'] : $network,
        ];
        if (!is_null($password)) {
            $where['password'] = md5($password);
        }
        $userId = self::_getValue('id', $where);
        if (is_null($userId)) {
            return false;
        } else {
            $refreshToken = self::updateRefreshToken($userId);
            list($token, $tokenExpire) = self::getToken($userId);
            $user = self::get($userId);
            return compact('token', 'refreshToken', 'user', 'tokenExpire');
        }
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
        $roles = json_encode($roles);
        $password = md5($password);
        $id = self::_insert(compact('name', 'email', 'password', 'roles', 'network', 'network_user_id'));
        return $id;
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
        UserMeta::update($id, 'reset_password_code', $code);
        UserMeta::update($id, 'reset_password_expire', $expire);
        return SITE_FRONTEND_URL . "reset-password/$expire/$code";
    }

}
