# BACKENDER

## INSTALLATION

```
composer require adjai/backender
```

Then run installation:

```
php vendor/adjai/backender/install.php
```

After installation all required directories and files will be created. If you had your main **index.php** file before, then add content to it:
```
include_once 'vendor/autoload.php';
$backend = new \adjai\backender\core\Backender();
```

## USING

Add controller ```TestController.php``` to ```controllers``` directory. Fill file with following content:
```
<?php
namespace app\controllers;

class TestController extends \adjai\backender\core\Controller {

    public function actionDo() {
        die('do action');
    }

}
```

## USING CLI

If your backend is located in "backend" directory and you want to execute the "CONTROLLER"'s "METHOD" run it the following way:
```
php PATH_TO_INDEX/index.php /backend/CONTROLLER/METHOD/
```
