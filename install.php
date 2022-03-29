<?php

function randomString($length) {
    $randomChars = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'));
    $randomString = implode('', array_map(function() use ($randomChars) {
        return $randomChars[rand(0, count($randomChars))];
    }, range(0, $length - 1)));
    return $randomString;
}

function getInput($promptMessage, $defaultValue = '', $isRequired = true) {
    do {
        echo "$promptMessage" . ($defaultValue === '' ? '' : " [$defaultValue]") . ":";
        $value = trim(fgets(STDIN));
        if ($value === '') {
            $value = $defaultValue;
        }
    } while ($isRequired && $value === '');
    return $value;
}

function fillFiles($filename, $replacements) {
    $content = file_get_contents($filename);
    $content = str_replace(array_keys($replacements), array_values($replacements), $content);
    file_put_contents($filename, $content);
}

$rootDirectory = getInput('Define app root directory', dirname(dirname(dirname(__DIR__))));

$appMode = getInput('Define app mode ("development" or "production")', 'development');
$dbHost = getInput('Define DB host', 'localhost');
$dbUser = getInput('Define DB name', 'root');
$dbName = getInput('Define DB name', 'app' . uniqid());
$dbPassword = getInput('Define DB password', 'root', false);

//echo "Defined: $definedRootDirectory";

$rootFiles = ['.htaccess.example', 'config/config.example.php', 'config/config.development.example.php', 'config/config.production.example.php'];
//$rootFiles = ['config/config.example.php'];

foreach ($rootFiles as $rootFile) {
    $destinationFileName = basename(str_replace('.example', '', $rootFile));
    $rootFileDestination = $rootDirectory . "/$destinationFileName";
    if (!file_exists($rootFileDestination)) {
        copy($rootFile, $rootFileDestination);
        if ($destinationFileName === 'config.php') {
            fillFiles($rootFileDestination, [
                "define('MODE', '');" => "define('MODE', '$appMode');",
            ]);
        } elseif ($destinationFileName === "config.$appMode.php") {
            fillFiles($rootFileDestination, [
                "define('JWT_SECRET_KEY', '');" => "define('JWT_SECRET_KEY', '" . randomString(24) . "');",
                "define('DB_HOST', '');" => "define('DB_HOST', '$dbHost');",
                "define('DB_NAME', '');" => "define('DB_NAME', '$dbName');",
                "define('DB_USER', '');" => "define('DB_USER', '$dbUser');",
                "define('DB_PASSWORD', '');" => "define('DB_PASSWORD', '$dbPassword');",
            ]);
        }
        echo "\t$rootFileDestination has been created\n";
    } else {
        echo "\tfailed to create $rootFileDestination\n";
    }
}

chdir($rootDirectory);

include_once $rootDirectory . '/vendor/autoload.php';
include_once 'config.php';

$createDirectories = [
    ABSPATH . "/controllers/",
    ABSPATH . "/models/",
    ABSPATH . "/views/",
    LOG_DIRECTORY,
    TMP_DIRECTORY,
    FILES_UPLOAD_PATH,
    EMAIL_TEMPLATES_DIRECTORY
];
foreach ($createDirectories as $directory) {
    if (!file_exists($directory)) {
        mkdir($directory);
    }
}

$db = new \MysqliDb(DB_HOST, DB_USER, DB_PASSWORD, null, null, DB_CHARSET);
$db->rawQuery("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
$db->disconnect();
$db = new \MysqliDb(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, null, DB_CHARSET);

$sqlFiles = [__DIR__ . '/schemes/user.sql'];
foreach ($sqlFiles as $sqlFile) {
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

