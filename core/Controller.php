<?php
namespace adjai\backender\core;

class Controller {
    private $authorizedData = [];

    protected function getAuthorizedData($name = null, $default = null) {
        if ($name === null) {
            return $this->authorizedData;
        } else {
            return $this->authorizedData->$name ?? null;
        }
    }

    protected function outputResponse(Response $response) {
        http_response_code($response->getCode());
        header('Content-Type: application/json');
        echo json_encode($response->getOutput());
        die();
    }

    protected function render($name, $data = [], $isPartial = false) {
        Router::getInstance()->setIsPartialOutput($isPartial);
        $filename = ABSPATH . "/views/$name.php";
        if (file_exists($filename)) {
            extract($data);
            include $filename;
        } else {
            throw new Exception("Не найден шаблон $name");
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

    protected function restrictAccessForRoles($roles = []) {
        if (PHP_SAPI === 'cli') {
            $this->authorizedData = (object) CLI_ACCESS;
            if (count($roles) && count(array_diff($roles, $this->getAuthorizedData('roles'))) === count($roles)) {
                self::outputError('no_access');
            }
            return;
        }
        if (isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('|^Bearer\s(\S+)$|', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            $token = $matches[1];
            try {
                $this->authorizedData = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key(JWT_SECRET_KEY, 'HS256'));
                if (count($roles) && count(array_diff($roles, $this->getAuthorizedData('roles'))) === count($roles)) {
                    self::outputError('no_access');
                }
            } catch (\Firebase\JWT\ExpiredException $exception) {
                self::outputError('expired_token');
            }
        } else {
            self::outputError('anauthorized_access');
        }
    }
}
