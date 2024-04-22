<?php
namespace adjai\backender\models;

use adjai\backender\core\DBModel;

class Uploads extends DBModel {

    // Upload all files from $_FILES by name $name
    public static function uploadFromServer($name) {
        $imagesIdA = [];
        if (count($_FILES) && isset($_FILES[$name])) {
            $files = array_map(function($index) use ($name) {
                return [
                    'name' => $_FILES[$name]['name'][$index],
                    'tmp_name' => $_FILES[$name]['tmp_name'][$index],
                    'error' => $_FILES[$name]['error'][$index],
                ];
            }, array_keys($_FILES[$name]['error']));
            $files = array_values(array_filter($files, function($file) {
                return $file['error'] === 0;
            }));
            foreach ($files as $file) {
                $imagesIdA[] = self::upload($file);
            }
        }
        return $imagesIdA;
    }

    public static function upload($file) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $id = self::_insert(['name' => $file['name'], 'ext' => $ext]);
        move_uploaded_file($file['tmp_name'], UPLOADS_DIRECTORY . "$id.$ext");
        return $id;
    }

    public static function getByIdsList($images)
    {
        $uploadsBaseUrl = SITE_BACKEND_URL . str_replace(ABSPATH . '/', '', UPLOADS_DIRECTORY);
        $idA = explode(',', $images);
        $images = self::_get(['id' => [$idA, 'IN']]);
        $images = array_map(function($item) use ($uploadsBaseUrl) {
            $item['url'] = $uploadsBaseUrl . $item['id'] . '.' . $item['ext'];
            return $item;
        }, $images);
        return $images;
    }

    public static function removeIdA($idA)
    {
        $items = self::_get(['id' => [$idA, 'IN']]);
        foreach ($items as $item) {
            unlink(UPLOADS_DIRECTORY . $item['id'] . '.' . $item['ext']);
        }
        self::_remove(['id' => [$idA, 'IN']]);
    }
}