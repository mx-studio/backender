<?php
namespace adjai\backender\models;

use adjai\backender\core\DBModel;
use adjai\backender\core\ModelCRUDTrait;

class Comment extends DBModel {
    use ModelCRUDTrait;

    public static function getItems($where = [], $fields = '*', $numRows = null, $orderBy = [], $groupBy = [], $ifCalcTotalRows = false) {
        $items = self::traitGetItems($where, $fields, $numRows, $orderBy, $groupBy, $ifCalcTotalRows);
        $items = self::addRelatedModel($items, 'user', User::class, 'user_id');
        return $items;
    }

    public static function getItemsTree($where = [], $fields = '*', $numRows = null, $orderBy = [], $groupBy = [], $ifCalcTotalRows = false) {
        $items = self::getItems($where, $fields, $numRows, $orderBy, $groupBy, $ifCalcTotalRows);

        $itemRefs = [];
        for ($index = 0; $index < count($items); $index++) {
            $itemRefs[$items[$index]['id']] = &$items[$index];
        }

        foreach ($items as $item) {
            $parentId = $item['parent_comment_id'];
            if (!is_null($parentId)) {
                $itemRefs[$parentId]['comments'][] = $item;
                $itemRefs[$item['id']] = &$itemRefs[$parentId]['comments'][count($itemRefs[$parentId]['comments']) - 1];
            }

        }

        $items = array_filter($items, function($item) {
            return is_null($item['parent_comment_id']);
        });

        $items = array_values($items);

        return $items;
    }

    public static function get($id) {
        $item = self::_getOne(['id' => $id]);
        $item = self::addRelatedModel([$item], 'user', User::class, 'user_id')[0];
        return $item;
    }

    public static function clear() {
        self::_remove([]);
    }

}
