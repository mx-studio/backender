<?php
namespace adjai\backender\core;

trait ControllerCRUDTrait {

    public function actionItems($where = [], $fields = '*', $count = null, $orderBy = [], $groupBy = [], $ifCalcTotalRows = false, $page = 1) {
        $this->traitActionItems($where, $fields, $count, $orderBy, $groupBy, $ifCalcTotalRows, $page);
    }

    protected function traitActionItems($where = [], $fields = '*', $count = null, $orderBy = [], $groupBy = [], $ifCalcTotalRows = false, $page = 1) {
        $model = $this->getRelatedModel();
        $numRows = $count === null ? null : [($page - 1) * $count, $count];
        $result = $model::getItems($where, $fields, $numRows, $orderBy, $groupBy, $ifCalcTotalRows);
        $this->outputData($ifCalcTotalRows ? ['total_count' => $result[1], 'items' => $result[0]] : $result);
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

    protected function traitActionUpdate($modelData) {
        $model = $this->getRelatedModel();
        $model::update($modelData);
        $this->outputData();
    }

    public function actionItem($id) {
        $this->traitActionItem($id);
    }

    protected function traitActionItem($id) {
        $model = $this->getRelatedModel();
        $result = $model::get($id);
        $this->outputData($result);
    }

}
