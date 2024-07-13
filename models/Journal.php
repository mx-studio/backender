<?php
namespace adjai\backender\models;

use adjai\backender\core\DBModel;

class Journal extends DBModel {

    public static function save($juid, $action, $referer, $params, $ip, $userAgent) {
        self::_insert([
            'juid' => $juid,
            'datetime' => date('Y-m-d H:i:s'),
            'ip' => $ip,
            'referer' => $referer,
            'useragent' => $userAgent,
            'action' => $action,
            'params' => $params === null ? null : json_encode($params),
        ]);
    }

}
