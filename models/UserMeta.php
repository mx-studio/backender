<?php
namespace adjai\backender\models;

class UserMeta extends \adjai\backender\core\DBModel {

    public static function get($userId, $name, $default = null) {
        $value = self::_getValue('value', [
            'name' => $name,
            'user_id' => $userId,
        ]);
        return is_null($value) ? $default : $value;
    }

    public static function getUserIdByMeta($name, $value) {
        return self::_getValue('user_id', ['name' => $name, 'value' => $value]);
    }

    public static function getByUserId($userId) {
        if (is_array($userId)) {
            $allMeta = self::_get([
                'user_id' => [$userId, 'IN'],
            ]);
            $meta = [];
            foreach ($allMeta as $metaItem) {
                $meta[$metaItem['user_id']][] = $metaItem;
            }
            $meta = array_map(function($userMeta) {
                return array_combine(array_column($userMeta, 'name'), array_column($userMeta, 'value'));
            }, $meta);
        } else {
            $meta = self::_get([
                'user_id' => $userId,
            ]);
            $meta = array_combine(array_column($meta, 'name'), array_column($meta, 'value'));
        }
        return $meta;
    }

    public static function update($userId, $name, $value) {
        if (is_array($value)) {
            $value = json_encode($value);
        }
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

    public static function remove($userId, $name) {
        self::_remove(['user_id' => $userId, 'name' => $name]);
    }

    public static function removeByUserId($userId) {
        self::_remove(['user_id' => $userId]);
    }

}
