<?php
namespace app\controllers;

use adjai\backender\core\Controller;
use app\models\_REPLACE_NAME_;

class _REPLACE_NAME_Controller extends Controller {
    
    public function actionItems() {
        $this->outputData(_REPLACE_NAME_::getItems());
    }

    public function actionRemove($id) {
        _REPLACE_NAME_::remove($id);
        $this->outputData();
    }
    
}
