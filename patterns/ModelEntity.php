<?php
namespace app\models;

use adjai\backender\core\DBModel;

class _REPLACE_NAME_ extends DBModel {

    public static function getItems() {
        return self::_get();
    }
    
    public static function remove($id) {
        self::_remove(['id' => $id]);
    }

}
