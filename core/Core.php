<?php
namespace adjai\backender\core;

//use MysqliDb;

class Core {
    static \MysqliDb $db;
    private $template = TEMPLATE;
    private static $instance;

    public function __construct() {
        $this->init();
        $this->initDebug();
    }

    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new Core();
        }
        return self::$instance;
    }

    private function init() {
        if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASSWORD') && defined('DB_NAME') && defined('DB_CHARSET')) {
            $this->initDB();
        }
    }

    private function initDebug() {
        if (isset($_REQUEST['debug'])) {
            $debugFile = ABSPATH . '/debug/' . $_REQUEST['debug'] . '.request';
            if (file_exists($debugFile)) {
                $f = fopen($debugFile, 'r');
                while (!feof($f)) {
                    $line = trim(fgets($f));
                    if ($line === '') {
                        continue;
                    }
                    $firstChar = substr($line, 0, 1);
                    if ($firstChar === '#') {
                        continue;
                    } elseif ($firstChar === '{') {
                        $_REQUEST = json_decode($line, true);
                    } else {
                        if (Router::getInstance()->getPath() === false) {
                            Router::getInstance()->setPath($line);
                        }
                        $parts = explode(':', $line);
                        $_SERVER[trim($parts[0])] = trim($parts[1]);
                    }
                }
                fclose($f);
            }
        }
    }

    private function initDB() {
        self::$db = new \MysqliDb(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, null, DB_CHARSET);
        self::$db->setPrefix(DB_PREFIX);
        self::$db->query('SET NAMES ' . DB_CHARSET);
        self::$db->query("SET time_zone='".TIMEZONE_OFFSET."'");
        date_default_timezone_set(TIMEZONE_LOCATION);
    }

    /**
     * Transforming string containing hyphens into camelCase string
     * @param $str
     */
    public static function transformHyphensToCamelCase($str) {
        return preg_replace_callback('|-(.)|', function($matches) {
            return strtoupper($matches[1]);
        }, $str);
    }
    
    public static function getDb() {
        return self::$db;
    }

    public function setTemplate($name) {
        $this->template = $name;
    }

    public function outputTemplate() {
        include_once ABSPATH . "/templates/$this->template.php";
    }
}
