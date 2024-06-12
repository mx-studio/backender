<?php
namespace adjai\backender\core;

class Log {
    static function logRequest() {
        $userInfo = '';
        $authorizedData = Core::getAuthorizationData();
        if (!$authorizedData instanceof Error) {
            $user = \adjai\backender\models\User::get($authorizedData->user_id);
            $userInfo = "{$user['name']} (id: {$user['id']}, email: {$user['email']}, role: {$user['roles'][0]})";
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
