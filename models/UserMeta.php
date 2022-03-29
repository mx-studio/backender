<?php
namespace adjai\backender\models;

class UserMeta extends \adjai\backender\core\DBModel {

    public static function update($userId, $name, $value) {
        $id = self::_getValue('id', ['user_id' => $userId, 'name' => $name]);
        if (is_null($id)) {
           self::add($userId, $name, $value); 
        } else {
            self::_update(['id' => $id], ['value' => $value]);
        }
    }
    
    public static function add($userId, $name, $value) {
        return self::_insert([
            'user_id' => $userId,
            'name' => $name,
            'value' => $value,
        ]);
    }

    public static function get($userId, $name, $default = null) {
        $value = self::_getValue('value', ['user_id' => $userId, 'name' => $name]);
        return is_null($value) ? $default : $value;
    }

    public static function getUserId($name, $value) {
        return self::_getValue('user_id', ['name' => $name, 'value' => $value]);
    }

    public static function remove($userId, $name) {
        self::_remove(['user_id' => $userId, 'name' => $name]);
    }

    public static function removeByUserId($userId) {
        self::_remove(['user_id' => $userId]);
    }

}
