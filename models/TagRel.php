<?php
namespace app\models;

use adjai\backender\core\DBModel;

class TagRel extends DBModel {

    public static function update($userId, $objectId, $tags, $group) {
        //$existTags = self::getTagIds($subscriptionId);
        $allTags = array_column(
            array_map(function($item) {
                $item['name'] = mb_strtolower($item['name']);
                return $item;
            }, Tag::getAll($userId, $group))
            , 'id', 'name');

        $tagIds = array_map(function($tag) use ($allTags, $userId) {
            return $allTags[mb_strtolower($tag)] ?? Tag::add($userId, $group, $tag);
        }, $tags);

        /*$newTagIds = array_diff($tagIds, $existTags);
        $removeTagIds = array_diff($existTags, $tagIds);*/

        self::_remove(['object_id' => $objectId]);
        foreach ($tagIds as $id) {
            self::add($userId, $objectId, $group, $id);
        }
        Tag::removeUnusedTags($userId);
    }

    public static function getTagsByUser($userId, $group, $ifOnlyNames = false) {
        $items = self::getByUser($userId, $group);
        $tags = Tag::getAll($userId, $group);
        $tags = array_column($tags, 'name', 'id');
        $resultItems = [];
        foreach ($items as $item) {
            $resultItems[$item['object_id']][] = $ifOnlyNames ? $tags[$item['tag_id']] : [
                'id' => $item['tag_id'],
                'name' => $tags[$item['tag_id']],
            ];
        }
        return $resultItems;
    }

    public static function getObjectsByTags($userId, $tags, $group) {
        $tagIds = Tag::getTagIds($userId, $group, $tags);
        return array_column(self::_get([
            'tag_id' => [$tagIds, 'IN'],
        ], 'distinct object_id'), 'object_id');
    }

    public static function removeByObject($userId, $objectId, $group) {
        $where = [
            'object_id' => $objectId,
        ];
        if ($group !== null) {
            $where['tag_group_id'] = TagGroup::get($group);
        }
        self::_remove($where);
        Tag::removeUnusedTags($userId);
    }

    public static function getByObjectId($objectId, $group) {
        $items = self::_get([
            'object_id' => $objectId,
            'group_id' => TagGroup::get($group),
        ], 'tag_id, id', null, ['tag_id' => 'asc']);
        if (count($items)) {
            $ids = array_column($items, 'id');
            $tagIds = array_column($items, 'tag_id');
            $tags = Tag::getByIds($tagIds);
            array_multisort($ids, $tags);
            return $tags;
        } else {
            return [];
        }
    }

    private static function getTagIdsByObject($objectId, $group) {
        return array_column(self::_get([
            'object_id' => $objectId,
            'group_id' => TagGroup::get($group),
        ], 'tag_id'), 'tag_id');
    }

    static function getTagIdsByUser($userId, $group) {
        $where = [
            'user_id' => $userId,
        ];
        if ($group !== null) {
            $where['tag_group_id'] = TagGroup::get($group);
        }
        return array_column(self::_get($where, 'distinct tag_id'), 'tag_id');
    }

    private static function add($userId, $objectId, $group, $tagId) {
        return self::_insert([
            'object_id' => $objectId,
            'tag_id' => $tagId,
            'tag_group_id' => TagGroup::get($group),
            'user_id' => $userId,
        ]);
    }

    private static function getByUser($userId, $group) {
        return self::_get(['user_id' => $userId, 'tag_group_id' => TagGroup::get($group)]);
    }

}
