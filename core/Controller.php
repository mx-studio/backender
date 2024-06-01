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

    protected function includeScript($name, $section = 'inline', $safe = false) {
        $filename = ABSPATH . "/views/$name.min.js";
        if (!file_exists($filename)) {
            $filename = ABSPATH . "/views/$name.js";
        }
        if (file_exists($filename)) {
            Router::getInstance()->addScript(str_replace(ABSPATH, '', $filename), $section);
        } else if (!$safe) {
            throw new \Exception("Не найден js-файл шаблона $name");
        }
    }

    protected function includeStyle($name, $section = 'inline', $safe = false) {
        $filename = ABSPATH . "/views/$name.min.css";
        if (!file_exists($filename)) {
            $filename = ABSPATH . "/views/$name.css";
        }
        if (file_exists($filename)) {
            Router::getInstance()->addStyle(str_replace(ABSPATH, '', $filename), $section);
        } else if (!$safe) {
            throw new \Exception("Не найден css-файл шаблона $name");
        }
    }

    protected function renderPartial($name, $data = []) {
        $filename = ABSPATH . "/views/$name.php";
        if (file_exists($filename)) {
            extract($data);
            $this->includeStyle($name, 'footer', true);
            include $filename;
        } else {
            throw new \Exception("Не найден шаблон $name");
        }
    }

    protected function render($name, $data = [], $isPartial = false) {
        Router::getInstance()->setIsPartialOutput($isPartial);
        $filename = ABSPATH . "/views/$name.php";
        if (file_exists($filename)) {
            extract($data);
            $this->includeStyle($name . '-header', 'header', true);
            $this->includeStyle($name . '-footer', 'footer', true);
            $this->includeStyle($name, 'inline', true);
            $this->includeScript($name . '-header', 'header', true);
            $this->includeScript($name . '-footer', 'footer', true);
            $this->includeScript($name, 'inline', true);
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
            if (defined('SIMULATE_ACCESS')) {
                $authorizedData = (object) SIMULATE_ACCESS;
            }
        }
        if ($authorizedData instanceof Error) {
            $this->outputError($authorizedData->getMessage());
        } else {
            $this->authorizedData = $authorizedData;
            if (count($roles) && count(array_diff($roles, $this->getAuthorizedData('roles'))) === count($roles)) {
                self::outputError('no_access');
            }
        }
    }

    protected function getRelatedModel() {
        $className = get_class($this);
        preg_match('|\\\([^\\\]+)Controller$|', $className, $matches);
        $modelName = "app\\models\\" . $matches[1];
        return $modelName;
    }

    protected function getParam($name, $default = null) {
        return Router::getInstance()->getInputData($name, $default);
    }

}
