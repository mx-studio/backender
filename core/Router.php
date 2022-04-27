<?php
namespace adjai\backender\core;

class Router {
    private static $instance;
    private $controller;
    private $controllerMethod;
    private $controllerMethodArguments = [];
    private $content = '';
    private $isPartialOutput;
    private $path = false;
    private $inputData = [];

    public function __construct() {
        $this->init();
    }

    private function init() {
        $this->setIsPartialOutput(true);
    }

    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new Router();
        }
        return self::$instance;
    }

    public function setPath(string $path) {
        $this->path = $path;
    }

    public function getPath() {
        return $this->path;
    }

    /**
     * looking for path in URI and appropriate controller
     */
    public function parseUri() {
        global $argc, $argv;
        if (php_sapi_name() === 'cli' && $argc > 1) {
            $path = trim($argv[1]);
            foreach (array_slice($argv, 2) as $cliParam) {
                if (preg_match('|^(.+)=(.+)$|', $cliParam, $matches)) {
                    $_REQUEST[$matches[1]] = str_replace('__', ' ', $matches[2]);
                }
            }
        } else {
            $path = $this->getPath();
            if ($path === false) {
                $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            }
        }

        if (defined('LOG_REQUESTS') && LOG_REQUESTS) {
            if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
                Log::logRequest();
            }
        }
        if (preg_match('|^' . BACKEND_BASE_URL . '(.+)/|', $path, $matches)) {
            $pathItems = explode('/', $matches[1]);
            if (count($pathItems) > 1) {
                $shortClassName = ucfirst(Core::transformHyphensToCamelCase($pathItems[0])) . 'Controller';
                $className = 'app\\controllers\\'. $shortClassName;
                if (!class_exists($className)) {
                    $className = 'adjai\\backender\\controllers\\'. $shortClassName;
                }
                $methodName = 'action' . ucfirst(Core::transformHyphensToCamelCase($pathItems[1]));
                if (class_exists($className)) {
                    $classObject = new $className();
                    if (method_exists($classObject, $methodName)) {
                        $reflectionMethod = new \ReflectionMethod($className, $methodName);
                        $arguments = [];
                        $isJSONInput = isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json';
                        if (!$isJSONInput) {
                            $sourceInput = $_REQUEST;
                        } else {
                            $input = file_get_contents('php://input');
                            $sourceInput = $input ? json_decode($input, true) : [];
                        }
                        $this->inputData = $sourceInput;
                        foreach ($reflectionMethod->getParameters() as $parameter) {
                            if (isset($sourceInput[$parameter->name])) {
                                $arguments[] = $sourceInput[$parameter->name];
                            } elseif ($parameter->isDefaultValueAvailable()) {
                                $arguments[] = $parameter->getDefaultValue();
                            } else {
                                break;
                            }
                        }
                        if ($reflectionMethod->getNumberOfRequiredParameters() > count($arguments)) {
                            throw new \Exception('Missing required parameters');
                        }
                        $this->controller = $classObject;
                        $this->controllerMethod = $methodName;
                        $this->controllerMethodArguments = $arguments;
                        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                            die();
                        }
                        ob_start();
                        $this->controller->{$this->controllerMethod}(...$this->controllerMethodArguments);
                        $this->content = ob_get_contents();
                        ob_end_clean();

                        if ($this->getIsPartialOutput()) {
                            $this->outputContent();
                        } else {
                            Core::getInstance()->outputTemplate();
                        }


                        return;
                        //$classObject->$methodName(...$arguments);
                        //die();
                    }
                }
            }
        }
        header('HTTP/1.1 404 Not Found');
        die();
    }

    public function outputContent() {
        echo $this->content;
        /*if (!is_null($this->controller) && !is_null($this->controllerMethod)) {
            $this->controller->{$this->controllerMethod}(...$this->controllerMethodArguments);
        }*/
    }

    /**
     * @param bool $isPartialOutput
     */
    public function setIsPartialOutput(bool $isPartialOutput): void
    {
        $this->isPartialOutput = $isPartialOutput;
    }

    /**
     * @return mixed
     */
    public function getIsPartialOutput()
    {
        return $this->isPartialOutput;
    }

    public function getInputData() {
        return $this->inputData;
    }

}
