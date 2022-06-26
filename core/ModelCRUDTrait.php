<?php
namespace adjai\backender\core;

trait ModelCRUDTrait {

    public static function getItems($where = [], $fields = '*', $numRows = null, $orderBy = []) {
        return self::traitGetItems($where, $fields, $numRows, $orderBy);
    }

    protected static function traitGetItems($where = [], $fields = '*', $numRows = null, $orderBy = []) {
        return self::_get($where, $fields, $numRows, $orderBy);
    }

    public static function remove($id) {
        self::traitRemove($id);
    }

    protected static function traitRemove($id) {
        self::_remove(['id' => $id]);
    }

    public static function update($fields) {
        return self::traitUpdate($fields);
    }

    protected static function traitUpdate($fields) {
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
