<?php
namespace app\controllers;

use app\models\Tag;
use app\models\TagGroup;

class TagController extends \adjai\backender\core\Controller {

    public function actionGetList($group = '') {
        $this->restrictAccess();
        $group = $group ? : TagGroup::$defaultGroup;
        $this->outputData(array_column(Tag::getAll($this->getAuthorizesUserId(), $group), 'name'));
    }

    public function actionAdd($name, $group = '') {
        $this->restrictAccess();
        $group = $group ? : TagGroup::$defaultGroup;
        Tag::add($this->getAuthorizesUserId(), $group, $name);
        $this->outputData();
    }

    public function actionGetId($name, $group = '') {
        $this->restrictAccess();
        $group = $group ? : TagGroup::$defaultGroup;
        $this->outputData(Tag::getId($this->getAuthorizesUserId(), $group, $name));
    }

    public function actionSuggest($part, $group = '') {
        $this->restrictAccess();
        $group = $group ? : TagGroup::$defaultGroup;
        $this->outputData(Tag::suggest($this->getAuthorizesUserId(), $group, $part));
    }

}
