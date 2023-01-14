<?php
namespace app\models;

use adjai\backender\core\Core;
use adjai\backender\core\DBModel;

class Tag extends DBModel {

    static function add($userId, $group, $name) {
        return self::_insert([
            'user_id' => $userId,
            'tag_group_id' => TagGroup::get($group),
            'name' => $name,
        ]);
    }

    static function getId($userId, $group, $name) {
        return self::_getValue('id', [
            'user_id' => $userId,
            'tag_group_id' => TagGroup::get($group),
            'name' => $name,
        ]);
    }

    public static function suggest($userId, $group, $part) {
        return array_column(self::_get([
            'user_id' => $userId,
            'tag_group_id' => TagGroup::get($group),
            'name' => ["$part%", 'LIKE'],
        ]), 'name');
    }

    public static function get($userId, $group, $name) {
        return self::_getOne([
            'user_id' => $userId,
            'tag_group_id' => TagGroup::get($group),
            'name' => $name,
        ]);
    }

    public static function getAll($userId, $group) {
        return self::_get(['user_id' => $userId, 'tag_group_id' => TagGroup::get($group)], '*', null, ['name' => 'ASC']);
    }

    public static function removeUnusedTags($userId) {
        $usedTagIds = TagRel::getTagIdsByUser($userId, null);
        if (count($usedTagIds)) {
            self::_remove([
                'user_id' => $userId,
                'id' => [$usedTagIds, 'NOT IN'],
            ]);
        } else {
            self::_remove([
                'user_id' => $userId,
            ]);
        }
    }

    public static function getTagIds($userId, $group, $tags) {
        $items = array_column(self::_get([
            'user_id' => $userId,
            'tag_group_id' => TagGroup::get($group),
            'name' => [$tags, 'IN'],
        ]), 'id');
        return $items;
    }

    public static function getByIds(array $tagIds) {
        return self::_get([
            'id' => [$tagIds, 'IN'],
        ], '*', null, ['id' => 'asc']);
    }

}
