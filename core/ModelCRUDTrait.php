<?php
namespace adjai\backender\core;

trait ModelCRUDTrait {

    public static function getItems() {
        return self::_get();
    }

    public static function remove($id) {
        self::_remove(['id' => $id]);
    }

    public static function update($fields) {
        if (isset($fields['id'])) {
            $id = $fields['id'];
            unset($fields['id']);
        } else {
            $id = false;
        }
        if ($id !== false) {
            self::_update(['id' => $id], $fields);
        } else {
            $id = self::_insert($fields);
        }
        return $id;
    }

}
