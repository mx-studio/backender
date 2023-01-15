<?php
namespace adjai\backender\models;

use adjai\backender\core\Core;
use adjai\backender\core\DBModel;

class TagGroup extends DBModel {
    static string $defaultGroup = 'Default';

    public static function get($group, $ifAutoCreate = true) {
        $id = self::_getValue('id', ['name' => $group]);
        if (is_null($id)) {
            $id = self::_insert(['name' => $group]);
        }
        return $id;
    }
}
