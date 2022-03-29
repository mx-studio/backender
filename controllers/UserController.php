<?php
namespace adjai\backender\controllers;

use adjai\backender\core\Mail;
use adjai\backender\models\User;
use adjai\backender\models\UserMeta;

class UserController extends \adjai\backender\core\Controller {

    public function actionAuth($email, $password) {
        $result = User::auth($email, $password);
        if ($result !== false) {
            $this->outputData($result);
        } else {
            $this->outputError('Неверный логин или пароль');
        }
    }

    public function actionRegister($userRegisterInfo) {
        //echo "<pre>";var_dump($userRegisterInfo);echo "</pre>";
        $id = User::create($userRegisterInfo['email'], $userRegisterInfo['password'], $userRegisterInfo['roles'], $userRegisterInfo['name'], $userRegisterInfo['meta']);
        foreach ($userRegisterInfo['meta'] as $metaName => $metaValue) {
            UserMeta::add($id, $metaName, $metaValue);
        }
        $this->outputData(User::get($id));
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
            $this->outputError('Пользователь с указанным email не зарегистрирован в системе.');
        } else {
            $resetLink = User::getResetPasswordLink($user['id']);
            Mail::sendUsingTemplate('request-reset-password', $user['email'], null, compact('resetLink'));
            $this->outputData();
        }
    }

    public function actionResetPassword($password, $code, $expire) {
        $userId1 = UserMeta::getUserId('reset_password_code', $code);
        $userId2 = UserMeta::getUserId('reset_password_expire', $expire);
        if ($userId1 === $userId2 && !is_null($userId1) && $expire >= time()) {
            UserMeta::remove($userId1, 'reset_password_code');
            UserMeta::remove($userId1, 'reset_password_expire');
            User::updatePassword($userId1, md5($password));
            $this->outputData();
        } else {
            $this->outputError('Неверный или просроченный код для изменения пароля.');
        }
    }

    public function actionAuthGoogleRedirect() {

    }
}
