<?php
namespace adjai\backender\controllers;

use adjai\backender\core\Controller;
use adjai\backender\models\Backup;

class BackupController extends Controller {

    public function actionBackup() {
        $result = Backup::save();
        if (PHP_SAPI === 'cli') {die();}
        $this->outputData($result === null ? [
            'created' => false,
        ] : [
            'created' => true,
            'file' => $result,
        ]);
    }

    public function actionRestore($name) {
        Backup::restore($name);
        if (PHP_SAPI === 'cli') {die();}
        $this->outputData();
    }

}