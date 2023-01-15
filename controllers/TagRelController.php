<?php
namespace adjai\backender\controllers;
use adjai\backender\core\Controller;
use adjai\backender\models\TagGroup;
use adjai\backender\models\Tag;
use adjai\backender\models\TagRel;

class TagRelController extends Controller {

    public function actionUpdate($objectId, $tags, $group = '') {
        // $this->restrictAccess();
        $group = $group ? : TagGroup::$defaultGroup;
        TagRel::update($this->getAuthorizesUserId(), $objectId, $tags, $group);
        $this->outputData(compact('objectId', 'tags'));
    }

    public function actionGetByUser($group = '') {
        // $this->restrictAccess();
        $group = $group ? : TagGroup::$defaultGroup;
        //$items = array_combine(array_column($items, 'subscription_id'), array_column($items, 'tag_id'));
        /*echo "<pre>";var_dump($subscriptionIds);echo "</pre>";
        echo "<pre>";var_dump($items);echo "</pre>";
        echo "<pre>";var_dump($tags);echo "</pre>";
        echo "<pre>";var_dump($resultItems);echo "</pre>";*/
        $this->outputData(TagRel::getTagsByUser($this->getAuthorizesUserId(), $group, true));
    }

    public function actionGetByObject($objectId, $group = '') {
        // $this->restrictAccess();
        $group = $group ? : TagGroup::$defaultGroup;
        $this->outputData(TagRel::getByObject($this->getAuthorizesUserId(), $objectId, $group, true));
    }

}
