<?php
namespace mx\CLIUtils;

class CLIUtils {

    private static function getInput($promptMessage, $defaultValue = '', $isRequired = true) {
        do {
            echo "$promptMessage" . ($defaultValue === '' ? '' : " [$defaultValue]") . ":";
            $value = trim(fgets(STDIN));
            if ($value === '') {
                $value = $defaultValue;
            }
        } while ($isRequired && $value === '');
        return $value;
    }

    private static function fillFiles($filename, $replacements) {
        $content = file_get_contents($filename);
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        file_put_contents($filename, $content);
    }

    private static function randomString($length) {
        $randomChars = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'));
        $randomString = implode('', array_map(function() use ($randomChars) {
            return $randomChars[rand(0, count($randomChars))];
        }, range(0, $length - 1)));
        return $randomString;
    }

    public static function postInstall() {
        file_put_contents('test.log', 'test');
    }
}
