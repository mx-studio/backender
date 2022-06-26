<?php
namespace adjai\backender\core;

class DBModel {

    protected static function _batchPrepare($items, $func) {
        if (is_null($items)) {
            return $items;
        }
        $isSingle = !Utils::isIndexedArray($items);
        if ($isSingle) {
            $items = [$items];
        }
        $items = array_map($func, $items);
        return $isSingle ? $items[0] : $items;
    }

    protected static function _getTableName() {
        $className = get_called_class();
        $className = preg_replace('|^.+\\\([^\\\]+)$|', '$1', $className);
        return strtolower(preg_replace('|([A-Z])|', '_$1', lcfirst($className)));
    }

    protected static function _applyOrderBy($orderBy) {
        foreach ($orderBy as $field => $direction) {
            Core::$db->orderBy($field, $direction);
        }
    }

    protected static function _applyGroupBy($groupBy) {
        foreach ($groupBy as $field) {
            Core::$db->groupBy($field);
        }
    }

    protected static function _applyWhere($where) {
        foreach ($where as $whereKey => $whereValue) {
            if ($whereValue === 'RAW_QUERY') {
                Core::$db->where($whereKey);
                continue;
            } elseif (is_array($whereValue) && count($whereValue) === 2 && array_keys($whereValue) === range(0, count($whereValue) - 1)) {
                $whereOperator = $whereValue[1];
                $whereValue = $whereValue[0];
            } else {
                $whereOperator = '=';
            }
            Core::$db->where($whereKey, $whereValue, $whereOperator);
        }
    }

    protected static function _query($query, $params = []) {
        return Core::$db->rawQuery($query, $params);
    }

    protected static function _update($where, $data) {
        self::_applyWhere($where);
        Core::$db->update(self::_getTableName(), $data);
    }

    protected static function _insert($data) {
        $id = Core::$db->insert(self::_getTableName(), $data);
        return $id;
    }

    protected static function _remove($where) {
        self::_applyWhere($where);
        Core::$db->delete(self::_getTableName());
    }

    protected static function _get($where = [], $fields = '*', $numRows = null, $orderBy = [], $groupBy = []) {
        self::_applyWhere($where);
        self::_applyOrderBy($orderBy);
        self::_applyGroupBy($groupBy);
        return Core::$db->get(self::_getTableName(), $numRows, $fields);
    }

    protected static function _getOne($where = [], $fields = '*', $orderBy = [], $groupBy = []) {
        self::_applyOrderBy($orderBy);
        self::_applyGroupBy($groupBy);
        $result = self::_get($where, $fields, 1);
        return is_null($result) || !count($result) ? null : $result[0];
    }

    protected static function _getValue($field, $where = [], $orderBy = [], $groupBy = []) {
        self::_applyOrderBy($orderBy);
        self::_applyGroupBy($groupBy);
        $result = self::_getOne($where, $field);
        if (!is_null($result) && isset($result[$field])) {
            return $result[$field];
        }
        return null;
    }

    protected static function _insertOrUpdate($data, $insertExtraData = [], $id = null) {
        if (is_null($id)) {
            $id = self::_insert(array_merge($data, $insertExtraData));
        } else {
            self::_update(['id' => $id], $data);
        }
        return $id;
    }

    protected static function addRelatedModel($items, $relationName, $relatedClassName, $foreignKey, $privateKey = 'id') {
        $relatedItems = $relatedClassName::getItems();
        $relatedItems = array_combine(array_column($relatedItems, $privateKey), $relatedItems);
        $items = array_map(function($item) use ($relatedItems, $relationName, $foreignKey) {
            $item[$relationName] = $relatedItems[$item[$foreignKey]];
            return $item;
        }, $items);
        return $items;
    }
}
