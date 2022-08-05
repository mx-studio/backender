<?php
namespace adjai\backender\controllers;

use adjai\backender\core\Controller;
use adjai\backender\models\Comment;

class CommentController extends Controller {

    public function actionAdd($objectId, $text, $parentCommentId = null) {
        $this->restrictAccess();
        $id = Comment::update([
            'object_id' => $objectId,
            'user_id' => $this->getAuthorizesUserId(),
            'text' => $text,
            'parent_comment_id' => $parentCommentId,
        ]);
        $this->outputData(Comment::get($id));
    }

    public function actionItems($objectId) {
        $items = Comment::getItemsTree(['object_id' => $objectId], '*', null, ['created' => 'desc']);
        $this->outputData($items);
    }

}
