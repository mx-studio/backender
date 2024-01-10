<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <?php \adjai\backender\core\Router::getInstance()->outputStyles('header'); ?>
    <?php \adjai\backender\core\Router::getInstance()->outputScripts('header'); ?>
</head>
<body>
<?php \adjai\backender\core\Router::getInstance()->outputStyles('inline'); ?>
<?php \adjai\backender\core\Router::getInstance()->outputScripts('inline'); ?>
<?=\adjai\backender\core\Router::getInstance()->outputContent()?>
</body>
</html>
<?php \adjai\backender\core\Router::getInstance()->outputStyles('footer'); ?>
<?php \adjai\backender\core\Router::getInstance()->outputScripts('footer'); ?>