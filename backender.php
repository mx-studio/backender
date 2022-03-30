<?php

namespace adjai\backender\core;

class Backender {

    public function __construct() {
        if (!file_exists('config.php')) {
            throw new \Exception('Error: config.php' . " doesn't exist. Use config example " . __DIR__ . '/config/config.sample.php');
        }
        require_once 'config.php';

        if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], ALLOWED_HTTP_ORIGINS)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        }

        spl_autoload_register(function($className) {
            $directories = [
                dirname(__DIR__) . '/',
                ABSPATH .'/controllers/',
                ABSPATH .'/models/',
            ];
            $className = str_replace('\\', '/', $className) . '.php';
            if (strpos($className, 'adjai/backender/') === 0) {
                $classPath = dirname(dirname(__DIR__)) . '/' . $className;
            } elseif (strpos($className, 'app/') === 0) {
                $classPath = ABSPATH . '/' . preg_replace('|^app/|', '', $className);
            }
            //echo "<pre>";var_dump($classPath);echo "</pre>";
            if (isset($classPath) && file_exists($classPath)) {
                require_once $classPath;
            }

            /*foreach ($directories as $directory) {
                if (file_exists($directory . $classPath)) {
                    require_once $directory . $classPath;
                    break;
                }
            }*/
            //echo "<pre>";var_dump($classPath);echo "</pre>";
            /*echo "<pre>";var_dump($className);echo "</pre>";
            echo "<pre>";var_dump($classPath);echo "</pre>";
            exit;*/
        });

        //include_once 'core/Core.php';
        Core::getInstance();
        Router::getInstance();
        Router::getInstance()->parseUri();
    }

}
