<?php
namespace mx\CLIUtils;

class CLIUtils {

    private static $includeSQLFiles = [];

    private static function getInput($promptMessage, $defaultValue = '', $isRequired = true) {
        do {
            echo "$promptMessage" . ($defaultValue === '' ? '' : " [$defaultValue]") . ": ";
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
        $rootDirectory = dirname($vendorDirectory);
        $webappDirectorySuggest = preg_match('/(\\\\|\/)backend$/', $rootDirectory) ? '/backend/' : '/';
        $webappDirectory = self::getInput('Define web app base directory (trailing slash included)', $webappDirectorySuggest);
        $appMode = self::getInput('Define app mode ("development" or "production")', 'development');
        $ifUseDb = in_array(self::getInput('Do you want to use DB ("yes" or "no")?', 'yes'), ['yes', 'y']);
        if ($ifUseDb) {
            $dbNameSuggest = basename($rootDirectory);
            if ($dbNameSuggest === 'backend') {
                $dbNameSuggest = basename(dirname($rootDirectory));
                $dbNameSuggest = preg_replace('~^local.~', '', $dbNameSuggest);
                $dbNameSuggest = preg_replace('~\.(com|ru|net|io)$~', '', $dbNameSuggest);
            }
            $dbHost = self::getInput('Define DB host', 'localhost');
            $dbUser = self::getInput('Define DB user', 'root');
            $dbName = self::getInput('Define DB name', $dbNameSuggest);
            $dbPrefix = self::getInput('Define DB tables prefix', '', false);
            $dbPassword = self::getInput('Define DB password', '', false);
        } else {
            $dbHost = "";
            $dbUser = "";
            $dbName = "";
            $dbPrefix = "";
            $dbPassword = "";
        }

        $ifUseAuthModule = in_array(self::getInput('Do you want to use Auth Module ("yes" or "no")?', 'yes'), ['yes', 'y']);
        $ifUseCommentsModule = $ifUseAuthModule && in_array(self::getInput('Do you want to use Comments Module ("yes" or "no")?', 'yes'), ['yes', 'y']);
        $ifUseTagsModule = in_array(self::getInput('Do you want to use Tags Module ("yes" or "no")?', 'yes'), ['yes', 'y']);

        $sampleFiles = ['.htaccess.example', 'config/config.example.php', 'config/config.development.example.php', 'config/config.production.example.php'];

        foreach ($sampleFiles as $sampleFile) {
            $exampleFile = "samples/" . $sampleFile;
            $destinationFileName = basename(str_replace('.example', '', $sampleFile));
            $destinationFile = $rootDirectory . "/$destinationFileName";
            if (!file_exists($destinationFile)) {
                copy($vendorDirectory . "/adjai/backender/samples/" . $sampleFile, $destinationFile);
                if ($destinationFileName === 'config.php') {
                    self::fillFiles($destinationFile, [
                        "define('BACKEND_BASE_URL', '/');" => "define('BACKEND_BASE_URL', '$webappDirectory');",
                        "define('MODE', '');" => "define('MODE', '$appMode');",
                    ]);
                } elseif ($destinationFileName === "config.$appMode.php") {
                    self::fillFiles($destinationFile, [
                        "define('JWT_SECRET_KEY', '');" => "define('JWT_SECRET_KEY', '" . self::randomString(24) . "');",
                        "define('DB_HOST', '');" => "define('DB_HOST', '$dbHost');",
                        "define('DB_NAME', '');" => "define('DB_NAME', '$dbName');",
                        "define('DB_PREFIX', '');" => "define('DB_PREFIX', '$dbPrefix');",
                        "define('DB_USER', '');" => "define('DB_USER', '$dbUser');",
                        "define('DB_PASSWORD', '');" => "define('DB_PASSWORD', '$dbPassword');",
                    ]);
                } elseif ($destinationFileName === ".htaccess") {
                    self::fillFiles($destinationFile, [
                        "RewriteRule . index.php [L]" => "RewriteRule . {$webappDirectory}index.php [L]",
                        "RewriteRule ^index\.php$ - [L]" => "RewriteRule ^{$webappDirectory}index\.php$ - [L]",
                    ]);
                }
                echo "\t$destinationFile has been created\n";
            } else {
                echo "\tfailed to create $destinationFile (file already exists)\n";
            }
        }

        if ($ifUseDb) {
            chdir($rootDirectory);
            include_once $rootDirectory . '/vendor/autoload.php';
            include_once 'config.php';

            if ($ifUseAuthModule) {
                self::$includeSQLFiles[] = $vendorDirectory . '/adjai/backender/schemes/user.sql';
                if ($ifUseCommentsModule) {
                    self::$includeSQLFiles[] = $vendorDirectory . '/adjai/backender/schemes/comment.sql';
                }
            }
            if ($ifUseTagsModule) {
                self::$includeSQLFiles[] = $vendorDirectory . '/adjai/backender/schemes/tags.sql';
            }

            self::processSQL();

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

    public static function createControllerScript($event) {
        $name = self::getInput('Enter the name of the controller (or names, separated by comma)');
        $names = array_map('trim', explode(',', $name));
        foreach ($names as $name) {
            self::createFromPattern($name, 'Controller', "/controllers/_REPLACE_NAME_Controller.php");
        }
    }

    public static function createModelScript($event) {
        $name = self::getInput('Enter the name of the model (or names, separated by comma)');
        $names = array_map('trim', explode(',', $name));
        foreach ($names as $name) {
            self::createFromPattern($name, 'Model', "/models/_REPLACE_NAME_.php");
        }
    }

    public static function createEntityScript($event) {
        $name = self::getInput('Enter the name of the entity (or names, separated by comma)');
        $names = array_map('trim', explode(',', $name));
        foreach ($names as $name) {
            self::createFromPattern($name, 'ControllerEntity', '/controllers/_REPLACE_NAME_Controller.php');
            self::createFromPattern($name, 'ModelEntity', '/models/_REPLACE_NAME_.php');
        }
    }

    public static function createFromPattern($name, $patternName, $destinationFile) {
        $name = ucfirst($name);
        $rootDirectory = dirname(dirname(dirname(dirname(__DIR__))));
        $destinationFile = $rootDirectory . str_replace('_REPLACE_NAME_', $name, $destinationFile);
        copy(dirname(__DIR__) . "/patterns/$patternName.php", $destinationFile);
        self::fillFiles($destinationFile, ['_REPLACE_NAME_' => $name]);
    }

    private static function processSQL() {
        $db = new \MysqliDb(DB_HOST, DB_USER, DB_PASSWORD, null, null, DB_CHARSET);
        $db->rawQuery("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        $db->disconnect();
        $db = new \MysqliDb(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, null, DB_CHARSET);

        foreach (self::$includeSQLFiles as $sqlFile) {
            echo "\trun $sqlFile\n";
            $commands = file($sqlFile);
            $commands = array_filter($commands, function($line) {
                $line = trim($line);
                return $line && strlen($line) >= 2 && substr($line, 0, 2) !== '--';
            });
            $commands = array_map(function($line) {
                return trim($line);
            }, $commands);
            $commands = explode(';', implode(' ', $commands));
            $commands = array_filter($commands);

            foreach ($commands as $command) {
                $db->query($command);
            }
        }
    }

}
