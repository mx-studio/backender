<?php
namespace adjai\backender\core;

trait ControllerCRUDTrait {

    public function actionItems($where = [], $fields = '*', $count = null, $orderBy = [], $page = 1) {
        $this->traitActionItems($where, $fields, $count, $orderBy, $page);
    }

    protected function traitActionItems($where = [], $fields = '*', $count = null, $orderBy = [], $page = 1) {
        $model = $this->getRelatedModel();
        $numRows = $count === null ? null : [($page - 1) * $count, $count];
        $this->outputData($model::getItems($where, $fields, $numRows, $orderBy));
    }

    public function actionRemove($id) {
        $this->traitActionRemove($id);
    }

    protected function traitActionRemove($id) {
        $model = $this->getRelatedModel();
        $model::remove($id);
        $this->outputData();
    }

    public function actionUpdate($model) {
        $this->traitActionUpdate($model);
    }

    protected function traitActionUpdate($model) {
        $model = $this->getRelatedModel();
        $model::update($model);
        $this->outputData();
    }

}
