<?php
namespace adjai\backender\core;

// todo реализовать мултиязычность

class Language {
    private static $baseLanguage = 'english';
    private static $translations;

    private static function readTranslations() {
        $translations = [];
        $languages = [];
        foreach (glob(ABSPATH . '/languages/*') as $file) {
            $language = preg_replace('|\.[^.]+$|', '', basename($file));
            $languages[] = $language;
            $translations[$language] = array_filter(array_map('trim', file($file)));
            /*echo "<p>$language</p>";
            echo "<p>$file</p>";
            echo "<pre>";var_dump($translations);echo "</pre>";*/
        }
        foreach ($translations[self::$baseLanguage] as $index => $baseText) {
            self::$translations[$baseText] = array_combine($languages, array_map(function($language) use ($translations, $index) {
                return $translations[$language][$index];
            }, $languages));
        }
    }

    public static function text($text, $destinationLanguage = LANGUAGE) {
        if (is_null(self::$translations)) {
            self::readTranslations();
        }
        //echo "<pre>";var_dump(self::$translations);echo "</pre>";
        return self::$translations[$text][$destinationLanguage] ?? $text;
    }

}
