<?php
namespace adjai\backender\controllers;

use adjai\backender\core\Controller;
use adjai\backender\models\Journal;

class JournalController extends Controller {

    public function actionSave($juid, $action, $params = null) {
        $ip = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];
        Journal::save($juid, $action, $params, $ip, $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->outputData();
    }

}