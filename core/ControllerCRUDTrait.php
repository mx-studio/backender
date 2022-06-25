<?php
namespace adjai\backender\core;

trait ControllerCRUDTrait {

    public function actionItems() {
        $model = $this->getRelatedModel();
        $this->outputData($model::getItems());
    }

    public function actionRemove($id) {
        $model = $this->getRelatedModel();
        $model::remove($id);
        $this->outputData();
    }

    public function actionUpdate($model) {
        $model = $this->getRelatedModel();
        $model::update($model);
        $this->outputData();
    }

}
