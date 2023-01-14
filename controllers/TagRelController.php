<?php
namespace app\controllers;
use adjai\backender\core\Controller;
use app\models\TagGroup;
use app\models\Video;
use app\models\Tag;
use app\models\TagRel;

class TagRelController extends Controller {

    public function actionUpdate($objectId, $tags, $group = '') {
        $this->restrictAccess();
        $group = $group ? : TagGroup::$defaultGroup;
        TagRel::update($this->getAuthorizesUserId(), $objectId, $tags, $group);
        $this->outputData(compact('objectId', 'tags'));
    }

    public function actionGetByUser($group = '') {
        $this->restrictAccess();
        $group = $group ? : TagGroup::$defaultGroup;
        //$items = array_combine(array_column($items, 'subscription_id'), array_column($items, 'tag_id'));
        /*echo "<pre>";var_dump($subscriptionIds);echo "</pre>";
        echo "<pre>";var_dump($items);echo "</pre>";
        echo "<pre>";var_dump($tags);echo "</pre>";
        echo "<pre>";var_dump($resultItems);echo "</pre>";*/
        $this->outputData(TagRel::getTagsByUser($this->getAuthorizesUserId(), $group, true));
    }

}
