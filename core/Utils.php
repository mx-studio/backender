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

    static function unsetArrayElementByPath(&$ar, $pathA) {
        if (!is_array($pathA)) {
            $pathA = explode('/', $pathA);
        }
        $pathA = array_filter($pathA);
        $last = array_pop($pathA);
        $elementParent = self::getArrayElementRefByPath($ar, $pathA);
        unset($elementParent[$last]);
    }

    static function &getArrayElementParentRefByPath(&$ar, $pathA) {
        if (!is_array($pathA)) {
            $pathA = explode('/', $pathA);
        }
        $pathA = array_filter($pathA);
        $element = array_pop($pathA);
        $parentElement = self::getArrayElementRefByPath($ar, $pathA);
        $null = null;
        return $parentElement !== null && isset($parentElement[$element]) ? $parentElement : $null;
    }

    static function &getArrayElementRefByPath(&$ar, $pathA) {
        if (!is_array($pathA)) {
            $pathA = explode('/', $pathA);
        }
        $pathA = array_filter($pathA);
        $current = &$ar;
        foreach ($pathA as $pathItem) {
            if (!isset($current[$pathItem])) {
                $null = null;
                return $null;
            }
            $current = &$current[$pathItem];
        }
        return $current;
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

    /**
     * Возвращает текущую дату для вставки в mysql
     * @return array|\string[][]
     * @throws \Exception
     */
    public static function nowSQL() {
        return Core::$db->now();
    }

    public static function printObjectSmart($obj) {
        function printObjectSmartHierarchy($obj, $level = 0, $path = '') {
            if (is_object($obj) || is_array($obj)) {
                foreach ($obj as $key => $item) {
                    echo "<div data-level='$level' style='margin-left: " . ($level * 10) . "px;'>";
                    if (is_object($item) || is_array($item)) {
                        echo "<div class='group-caption collapsed' title='$path'>$key</div>";
                        echo "<div>";
                        printObjectSmartHierarchy($item, $level + 1, $path . $key . '/');
                        echo "</div>";
                    } else {
                        echo "<div title='$path'>$key: $item</div>";
                    }
                    echo "</div>";
                }
            }
        }
        echo "<div class='smart-tree'>";
        printObjectSmartHierarchy($obj);
        echo "</div>";
        ?>
        <style>
            .smart-tree .group-caption {
                cursor: pointer;
                color: #55f;
                font-weight: bold;
            }
            .smart-tree .group-caption.collapsed:before {
                content: "-";
                margin-right: 4px;
            }
            .smart-tree .group-caption:not(.collapsed):before {
                content: "+";
                margin-right: 4px;
            }
        </style>
        <script>
            for (element of document.getElementsByClassName('group-caption')) {
                element.addEventListener('click', e => {
                    if (e.target.nextElementSibling.style.display !== 'none') {
                        e.target.nextElementSibling.style.display = 'none'
                        e.target.classList.remove('collapsed')
                    } else {
                        e.target.nextElementSibling.style.display = 'block'
                        e.target.classList.add('collapsed')
                    }
                })
            }
        </script>
        <?php
    }
}
