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
            return $randomChars[rand(0, count($randomChars) - 1)];
        }, range(0, $length - 1)));
        return $randomString;
    }

    public static function postInstallCmd($event) {
        $vendorDirectory = $event->getComposer()->getConfig()->get('vendor-dir');
        //$rootDirectory = dirname($vendorDirectory);
        $rootDirectory = self::getInput('Define app root directory', dirname($vendorDirectory));
        $webappDirectory = preg_match('/(\\\\|\/)backend$/', $rootDirectory) ? '/backend/' : '/';
        $appMode = 'development';

        $exampleFiles = ['.htaccess.example', 'config/config.example.php', 'config/config.development.example.php', 'config/config.production.example.php'];

        foreach ($exampleFiles as $exampleFile) {
            $destinationFileName = basename(str_replace('.example', '', $exampleFile));
            $rootFileDestination = $rootDirectory . "/$destinationFileName";
            if (!file_exists($rootFileDestination)) {
                copy($vendorDirectory . "/adjai/backender/" . $exampleFile, $rootFileDestination);
                if ($destinationFileName === 'config.php') {
                    self::fillFiles($rootFileDestination, [
                        "define('BACKEND_BASE_URL', '/');" => "define('BACKEND_BASE_URL', '$webappDirectory');",
                        "define('MODE', '');" => "define('MODE', '$appMode');",
                    ]);
                } elseif ($destinationFileName === "config.$appMode.php") {
                    self::fillFiles($rootFileDestination, [
                        "define('JWT_SECRET_KEY', '');" => "define('JWT_SECRET_KEY', '" . self::randomString(24) . "');",
                    ]);
                } elseif ($destinationFileName === ".htaccess") {
                    self::fillFiles($rootFileDestination, [
                        "RewriteRule . index.php [L]" => "RewriteRule . {$webappDirectory}index.php [L]",
                        "RewriteRule ^index\.php$ - [L]" => "RewriteRule ^{$webappDirectory}index\.php$ - [L]",
                    ]);
                }
                echo "\t$rootFileDestination has been created\n";
            } else {
                echo "\tfailed to create $rootFileDestination\n";
            }
        }

        //file_put_contents('backender.log', $event->getComposer()->getConfig()->get('vendor-dir'), FILE_APPEND);
        // file_put_contents('backender.log', date('d.m.y H:i:s') . " post-install-cmd\n", FILE_APPEND);
    }

    public static function postUpdateCmd() {
        // file_put_contents('backender.log', date('d.m.y H:i:s') . " post-update-cmd\n", FILE_APPEND);
    }

    public static function postRootPackageInstall() {
        // file_put_contents('backender.log', date('d.m.y H:i:s') . " post-root-package-install\n", FILE_APPEND);
    }

    public static function postPackageInstall() {
        // file_put_contents('backender.log', date('d.m.y H:i:s') . " post-package-install\n", FILE_APPEND);
    }

    public static function postPackageUpdate() {
        // file_put_contents('backender.log', date('d.m.y H:i:s') . " post-package-update\n", FILE_APPEND);
    }

    public static function enableDB() {

    }

}
