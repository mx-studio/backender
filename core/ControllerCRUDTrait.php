<?php
namespace adjai\backender\core;

use adjai\backender\models\Tag;

trait ControllerCRUDTrait {

    public function actionItems($where = [], $fields = '*', $count = null, $orderBy = [], $groupBy = [], $ifCalcTotalRows = false, $page = 1) {
        $this->traitActionItems($where, $fields, $count, $orderBy, $groupBy, $ifCalcTotalRows, $page);
    }

    protected function traitActionItems($where = [], $fields = '*', $count = null, $orderBy = [], $groupBy = [], $ifCalcTotalRows = false, $page = 1) {
        /** @var ModelCRUDTrait $model */
        $model = $this->getRelatedModel();
        $numRows = $count === null ? null : [($page - 1) * $count, $count];
        $result = $model::getItems($where, $fields, $numRows, $orderBy, $groupBy, $ifCalcTotalRows);
        $this->outputData($ifCalcTotalRows ? ['total_count' => $result[1], 'items' => $result[0]] : $result);
    }

    public function actionRemove($id) {
        $this->traitActionRemove($id);
    }

    protected function traitActionRemove($id) {
        /** @var ModelCRUDTrait $model */
        $model = $this->getRelatedModel();
        $model::remove($id);
        $this->outputData();
    }

    public function actionUpdate($model) {
        $this->traitActionUpdate($model);
    }

    protected function traitActionUpdate($modelData) {
        /** @var ModelCRUDTrait $model */
        $model = $this->getRelatedModel();
        $id = $model::update($modelData);
        $this->actionItem($id);
    }

    public function actionItem($id) {
        $this->traitActionItem($id);
    }

    protected function traitActionItem($id) {
        /** @var ModelCRUDTrait $model */
        $model = $this->getRelatedModel();
        $result = $model::get($id);
        $this->outputData($result);
    }

}
