<?php
namespace adjai\backender\core;

class Log {
    static function logRequest() {
        $userInfo = '';
        if (isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('|^Bearer\s(\S+)$|', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            $token = $matches[1];
            try {
                $authorizedData = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key(JWT_SECRET_KEY, 'HS256'));
                $user = \adjai\backender\models\User::get($authorizedData->user_id);
                $userInfo = "{$user['name']} (id: {$user['id']}, email: {$user['email']}, role: {$user['roles'][0]})";
            } catch (\Firebase\JWT\ExpiredException $exception) {

            } catch (\Exception $e) {

            }
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        $uri = $_SERVER['REQUEST_URI'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $dataPost = (isset($_POST) && count($_POST)) ? json_encode($_POST) : '';
        $dataGet = (isset($_GET) && count($_GET)) ? json_encode($_GET) : '';
        $files = (isset($_FILES) && count($_FILES)) ? json_encode($_FILES) : '';
        $dataInput = file_get_contents('php://input');
        $date = date('d.m.y H:i:s');

        $columns = ['Date & Time', 'IP', 'User Info', 'URI', 'JSON Input', 'POST', 'GET', 'FILES', 'User Agent'];
        $output = [$date, $ip, $userInfo, $uri, $dataInput, $dataPost, $dataGet, $files, $userAgent];
        $logFile = LOG_DIRECTORY . 'requests_' . date('d.m.y') . '.log';
        if (!file_exists($logFile)) {
            file_put_contents($logFile, implode("\t", $columns) . "\n", FILE_APPEND);
        }
        file_put_contents($logFile, implode("\t", $output) . "\n", FILE_APPEND);
    }


    public static function write($text, $section = 'app') {
        file_put_contents(LOG_DIRECTORY . $section . ".log", date('d.m.y H:i:s') . " " . $text . PHP_EOL, FILE_APPEND);
    }
}
