<?php
namespace adjai\backender\controllers;

use adjai\backender\core\Controller;
use adjai\backender\models\Journal;

class JournalController extends Controller {

    public function actionSave($juid, $action, $params = null) {
        Journal::save($juid, $action, $params, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');
    }

}