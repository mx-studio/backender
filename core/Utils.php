<?php
namespace adjai\backender\core;

class Utils {

    /**
     * Из многомерного массива получаем одномерный массив с сохранением иерархии в названиях ключей
     *
     * @param array $ar
     * @param string $prefix
     * @param string $delimiter Разделитель в ключах результирующего массива между элементами иерархии
     * @return array
     */
    public static function arrayFlatten($ar, $prefix = '', $delimiter = '_') {
        $flattenAr = [];
        foreach ($ar as $key => $value) {
            if (is_object($value)) {
                $value = (array) $value;
            }
            if (is_array($value)) {
                $flattenAr = array_merge($flattenAr, self::arrayFlatten($value, $prefix . $key . $delimiter));
            } else {
                $flattenAr[$prefix . $key] = $value;
            }
        }
        return $flattenAr;
    }

    /**
     * Проверка - является ли массив индексным или ассоциативным
     *
     * @param array $ar Массив для проверки
     * @param bool $emptyArrayIsIndexed Если передан пустой массив для проверки, то он считается индексным (если true)
     * @return bool True - если это индексный массив, False - если ассоциативный
     */
    public static function isIndexedArray($ar, $emptyArrayIsIndexed = true) {
        if (!count($ar)) {
            return $emptyArrayIsIndexed;
        }
        return array_keys($ar) === range(0, count($ar) - 1);
    }

    /**
     * Преобразует путь файловой системы в url
     * @param $path
     * @return string
     */
    public static function localPathToUrl($path) {
        $path = str_replace('\\', '/', $path);
        $path = SITE_BACKEND_URL . substr(str_replace(str_replace('\\', '/', ABSPATH), '', $path), 1);
        return $path;
    }
}
