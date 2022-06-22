<?php
namespace adjai\backender\core;

class Controller {
    private $authorizedData = [];

    function simulateAccess($userId, $roles = ['user'], $expire = null) {
        if (!isset($_SERVER['HTTP_AUTHORIZATION']) || $_SERVER['HTTP_AUTHORIZATION'] === '') {
            $payload = [
                'user_id' => $userId,
                'roles' => $roles,
                'exp' => $expire === null ? time() + JWT_TOKEN_EXPIRE : $expire,
            ];
            $token = \Firebase\JWT\JWT::encode($payload, JWT_SECRET_KEY, 'HS256');
            $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $token";
        }
    }


    protected function getAuthorizedData($name = null, $default = null) {
        if ($name === null) {
            return $this->authorizedData;
        } else {
            return $this->authorizedData->$name ?? null;
        }
    }

    protected function getAuthorizesUserId() {
        return $this->getAuthorizedData('user_id', null);
    }

    protected function getAuthorizedUserRoles() {
        return $this->getAuthorizedData('roles', null);
    }

    protected function outputResponse(Response $response) {
        http_response_code($response->getCode());
        header('Content-Type: application/json');
        echo json_encode($response->getOutput());
        die();
    }

    protected function includeScript($name, $safe = false) {
        $filename = ABSPATH . "/views/$name.min.js";
        if (!file_exists($filename)) {
            $filename = ABSPATH . "/views/$name.js";
        }
        if (file_exists($filename)) {
            echo "<script src='" . str_replace(ABSPATH, '', $filename) . "'></script>";
        } else if (!$safe) {
            throw new \Exception("Не найден js-файл шаблона $name");
        }
    }

    protected function render($name, $data = [], $isPartial = false) {
        Router::getInstance()->setIsPartialOutput($isPartial);
        $filename = ABSPATH . "/views/$name.php";
        if (file_exists($filename)) {
            extract($data);
            $this->includeScript($name, true);
            include $filename;
        } else {
            throw new \Exception("Не найден шаблон $name");
        }
    }

    protected function outputData($data = []) {
        self::outputResponse(new Response(true, '', $data));
    }

    protected function outputJSON($data = []) {
        header('Content-Type: application/json');
        self::render('common/json', $data, true);
    }

    protected function outputError($errorMessage) {
        self::outputResponse(new Response(false, $errorMessage));
    }

    protected function restrictAccess($roles = []) {
        if (PHP_SAPI === 'cli') {
            $this->authorizedData = (object) CLI_ACCESS;
            if (count($roles) && count(array_diff($roles, $this->getAuthorizedData('roles'))) === count($roles)) {
                self::outputError('no_access');
            }
            return;
        }
        $authorizedData = Core::getAuthorizationData();
        if ($authorizedData instanceof Error) {
            $this->outputError($authorizedData->getMessage());
        } else {
            $this->authorizedData = $authorizedData;
            if (count($roles) && count(array_diff($roles, $this->getAuthorizedData('roles'))) === count($roles)) {
                self::outputError('no_access');
            }
        }
    }
}
