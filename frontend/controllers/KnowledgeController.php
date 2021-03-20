<?php

namespace frontend\controllers;

use common\helpers\ApiHandler;
use common\models\WebLang;
use common\models\BbsCategory;
use common\models\BbsBoard;
use common\models\BbsPost;
use common\models\KbDocument;
use common\models\KbDmsModel;
use Yii;
use common\controllers\MasterController;
use yii\filters\VerbFilter;
use yii\db\Expression;

class KnowledgeController extends MasterController {

    public $kbs;
    public $limit = 20;

    public function behaviors() {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }


    public function actionIndex() {
        $lang = Yii::$app->language;

        $models = BbsCategory::find()->where(['type' => 'kb', 'status' => 1])->orderBy(['seq' => 'aec'])->all();
        $popularArticles = BbsPost::find()
                ->joinWith(['bbsBoard.bbsCategory'])
                ->where(['bbs_category.type' => 'kb', 'bbs_category.status' => 1, 'bbs_board.status' => 1, 'bbs_post.status' => 1, 'bbs_post.idparent' => null])
                ->orderBy('bbs_post.views desc')
                ->limit(10)
                ->all();

        $recentArticles = BbsPost::find()
                ->joinWith(['bbsBoard.bbsCategory'])
                ->where(['bbs_category.type' => 'kb', 'bbs_category.status' => 1, 'bbs_board.status' => 1, 'bbs_post.status' => 1, 'bbs_post.idparent' => null])
                ->orderBy('created_at desc')
                ->limit(10)
                ->all();

        return $this->render('index', [
                    'models' => $models,
                    'popularArticles' => $popularArticles,
                    'recentArticles' => $recentArticles,
        ]);
    }

    public function actionCategory($id) {
        $models = BbsBoard::find()
                ->joinWith('bbsCategory', true, "inner join")
                ->where(['idcategory' => $id, 'type' => 'kb', 'bbs_board.status' => 1])
                ->orderBy(['seq' => SORT_ASC])
                ->all();

        $selectedCategoryModel = BbsCategory::find()
                ->where(['id' => $id, 'type' => 'kb', 'status' => 1])
                ->one();

        $categoryModels = BbsCategory::find()
                ->where(['type' => 'kb', 'status' => 1])
                ->orderBy(['seq' => SORT_ASC])
                ->all();

        if (!$selectedCategoryModel) {
            $this->notFoundError();
        }
        return $this->render('categories', [
                    'selectedCategoryModel' => $selectedCategoryModel,
                    'categoryModels' => $categoryModels,
                    'models' => $models,
        ]);
    }

    public function actionBoard($id) {
//        $page = Yii::$app->request->get("p");
//        $idpost = Yii::$app->request->get("pid");

        if (Yii::$app->request->isPost) {
            $token = Yii::$app->request->post("token", null);

            if ($id > 0) {
                $model = $this->findModel($id);
                if (!$model) {
                    $this->notFoundError();
                }
            } else {
                $model = new BbsBoard;
            }
            $transaction = Yii::$app->db->beginTransaction();

            if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
                $transaction->commit();
                ApiHandler::success();
            } else {
                $transaction->rollBack();
                ApiHandler::failure(\common\helpers\IfapHelper::modelErrorHtml($model->getErrors()));
            }
        }

        $selectedBoardModel = BbsBoard::findActiveOne($id);
        $categoryModels = BbsCategory::find()->where(['type' => 'kb', 'status' => 1])->orderBy(['seq' => 'asc'])->all();

        if ($selectedBoardModel) {
            $boardModels = $selectedBoardModel->bbsCategory->bbsBoards;
            $postModels = BbsPost::find()
                    ->where(['idboard' => $id, 'status' => 1, 'idparent' => null])
                    ->orderBy(["sticky" => SORT_DESC, "updated_at" => SORT_DESC, "created_at" => SORT_DESC])
                    ->all();
        } else {
            $this->notFoundError();
        }

        return $this->render('boards', [
                    'selectedBoardModel' => $selectedBoardModel,
                    'categoryModels' => $categoryModels,
                    'boardModels' => $boardModels,
                    'postModels' => $postModels,
        ]);
    }

    public function actionArticle() {

        $id = Yii::$app->request->get('id', 1);
        $model = BbsPost::findActiveOne($id);

        $lang = Yii::$app->language;

        if (!$model) {
            $this->notFoundError();
        }
        // increast read counter
        $model->views = $model->views + 1;
        $model->save(false);

        $postTagModels = $model->bbsPostTags;

        return $this->render('article', [
                    'model' => $model,
                    'postTagModels' => yii\helpers\ArrayHelper::map($postTagModels, 'id', 'bbsTag.tagname'),
        ]);
    }

    /**
     * 
     * @return type
     */
    public function actionSlug($slug) {

        $slug = Yii::$app->request->get('slug');
        $model = BbsPost::find()->where(['slug' => $slug, 'status' => 1])->one();

        $lang = Yii::$app->language;

        if (!$model) {
            $this->notFoundError();
        }
        // increast read counter
        $model->views = $model->views + 1;
        $model->save(false);

        $postTagModels = $model->bbsPostTags;

        return $this->render('article', [
                    'model' => $model,
                    'postTagModels' => yii\helpers\ArrayHelper::map($postTagModels, 'id', 'bbsTag.tagname'),
        ]);
    }

    protected function findModel($id, $ajax = FALSE) {
        return BbsBoard::find()
                        ->joinWith(['bbsPosts'])
                        ->select([
                            new Expression('count(bbs_post.id) as count'),
                            'bbs_board.*'
                        ])
                        ->where(['bbs_board.status' => 1, 'bbs_board.id' => $id])
                        ->one();
    }

    public function actionArticleSave() {
        if (!Yii::$app->request->isAjax)
            $this->internalServerError();

        $id = Yii::$app->request->post('id');
        $content = Yii::$app->request->post('content');
        $token = Yii::$app->request->post('token');
        $slug = Yii::$app->request->post('slug');

        if (\common\helpers\IfapHelper::verifySingingToken(null, $token) != TRUE) {
            ApiHandler::unauthorised();
        }

        if ($id > 0)
            $model = BbsPost::findActiveOne($id);

        if (!$model) {
            ApiHandler::failure(WebLang::$t['invalid_request']);
        } else {
            $model->content = $content;
            if ($slug) {
                $model->slug = $slug;
            }

            if ($model->save()) {
                ApiHandler::success(['id' => $model->id]);
            }
            ApiHandler::failure(WebLang::$t['internal_error']);
        }
    }

    public function actionSearch() {

        if (!Yii::$app->request->isPost)
            $this->notFoundError();

        $s = Yii::$app->request->post("s");
        $s = stripslashes(strtolower($s));

        $searchStringArray = explode(" ", $s);

        foreach ($searchStringArray as $item) {
            $item = strtolower($item);
            $item = trim($item);

            if (!empty($item)) {
                $this->searchKb($item);
            }
        }

        return $this->render('search-result', [
                    'items' => $this->kbs,
                    'searchText' => $s,
        ]);
    }

    private function searchKb($searchPhrase) {
        $lang = Yii::$app->language;

        if (empty(trim($searchPhrase)))
            return FALSE;

        $this->kbs = BbsPost::find()
                ->joinWith(['bbsBoard'])
                ->where(['bbs_post.status' => 1])
                ->andFilterWhere([
                    'OR',
                    ["LIKE", "subject", $searchPhrase],
                    ["LIKE", "content", $searchPhrase],
                ])
                ->limit($this->limit)
                ->asArray()
                ->all();

        return;
    }

    public function actionArticleComment() {

        $this->isAjax();

        $id = Yii::$app->request->post('id');
        $token = Yii::$app->request->post('token');
        $comments = Yii::$app->request->post('comments');

        if (!\common\helpers\IfapHelper::verifySingingToken(['id' => (int) $id], $token))
            ApiHandler::unauthorised();

        $bbsPostParent = BbsPost::findActiveOne($id);

        if (!$bbsPostParent) {
            ApiHandler::unauthorised();
        }

        $bbsPost = new BbsPost;

        $bbsPost->status = 1;
        $bbsPost->idparent = $id;
        $bbsPost->idboard = $bbsPostParent->idboard;
        $bbsPost->content = $comments;

        if (!$bbsPost->validate() || !$bbsPost->save()) {
            ApiHandler::unauthorised(\common\helpers\IfapHelper::modelErrorHtml($bbsPost->getErrors()));
        }
        ApiHandler::success();
    }

    /**
     * 
     * @param int $id parent id
     * @return mixed
     */
    public function actionPost() {
        $lang = Yii::$app->language;

        if (Yii::$app->request->isPost) {
            $id = Yii::$app->request->post("id");
            $idparent = Yii::$app->request->post("idparent");
            $idboard = Yii::$app->request->post("idboard");
            $subject = Yii::$app->request->post("subject");
            $content = Yii::$app->request->post("post_content");
            $progress = Yii::$app->request->post("progress");
//            $uuid = Yii::$app->request->post("uuid", null);
            $tags = Yii::$app->request->post("BbsPostTag", null);
            $slug = Yii::$app->request->post('slug');

            if ($id > 0) {
                if (!$bbsPost = BbsPost::findActiveOne($id)) {
                    ApiHandler::failure(1);
                }
            } else {
                $bbsPost = new BbsPost;
            }

            if ($idparent) { // reply
                $parentPost = BbsPost::findActiveOne($idparent);

                if (!$parentPost) {
                    ApiHandler::failure(2);
                }
                $bbsPost->idparent = $idparent;
                $bbsPost->idboard = $parentPost->idboard;
            } else {// new or UPDATE post 
                if (!$subject) {
                    ApiHandler::failure("Subject is required");
                }
            }
            if ($idboard)
                $bbsPost->idboard = $idboard;

            if ($subject)
                $bbsPost->subject = $subject;

            $bbsPost->views = 0;
            $bbsPost->answers = 0;
            $bbsPost->votes = 0;
            $bbsPost->content = $content;
            $bbsPost->progress = $progress;
            $bbsPost->status = 1;

            if ($slug) {
                $bbsPost->slug = $slug;
            }

            $transaction = Yii::$app->db->beginTransaction();

            if ($bbsPost->validate() && $bbsPost->save()) {
                if ($idparent) {
                    if ($parentPost) { // a reply
                        // update parent answer count
                        $parentPost->answers += 1;
                        $parentPost->save();
                    }
                }

                if (isset($tags['id'])) {
                    foreach ((array) $tags['id'] as $key => $value) {

                        if (!empty($value)) {
                            $bbsPostTag = new BbsPostTag();
                            $bbsPostTag->idpost = $bbsPost->id;
                            $bbsPostTag->status = 1;

                            // try to find existing BbsTag
                            if ($tagModel = BbsTag::find()->where(['status' => 1, 'id' => $value])->one()) {
                                $bbsPostTag->idtag = $value;
                            } else {
                                // new tag
                                $tagModel = new BbsTag();
                                $tagModel->tagname = $value;
                                $tagModel->status = 1;
                                if (($tagModel->validate() && $tagModel->save()) !== TRUE) {
                                    $transaction->rollBack();
                                    ApiHandler::failure();
                                }
                                $bbsPostTag->idtag = $tagModel->id;
                            }
                            if (($bbsPostTag->validate() && $bbsPostTag->save()) !== TRUE) {
                                $transaction->rollBack();
                                ApiHandler::failure();
                            }
                            // increase tag usage counter
                            $tagModel->hitcount += 1;
                            $tagModel->save();
                        }
                    }
                }

                // Extract all images and update document table idpost
                $pattern = '/token=[0-9A-Za-z]*/'; //$pattern = '/id=[0-9]*/'; if it is only numeric.
                preg_match($pattern, $content, $matches);

                if ($matches) {
                    foreach ($matches as $match) {
                        $iddoc = explode('=', $match);
                        if (count($iddoc) == 2) {
                            // Find document
                            Yii::error("Trying to find KbDocument token={$iddoc[1]}");
                            
                            if ($document = KbDocument::find()->where(['token' => $iddoc[1]])->one()) {
                                $document->idpost = $bbsPost->id;
                                $document->save(false);
                                
                                // If cant locate the file in kbDocument, skip it
//                            } else {
//                                $transaction->rollBack();
//                                ApiHandler::failure("Unable to link attachment {$iddoc[1]} to Post");
                            }
                        } else {
                            $transaction->rollBack();
                            ApiHandler::failure("Unable to find attachment in DMS vault");
                        }
                    }
                }

                $transaction->commit();
                ApiHandler::success();
            } else {
                $transaction->rollBack();
                ApiHandler::failure(\common\helpers\IfapHelper::modelErrorHtml($bbsPost->getErrors()));
            }
            $transaction->rollBack();
            ApiHandler::failure();
        }


        $this->isAjax();

        $id = Yii::$app->request->get("id");
        $model = $this->findPostModel($id);

        // Increase view counter
        if ($model) {
            $model->views = $model->views + 1;
            $model->save();
        } else {
            $this->internalServerError();
        }

        ApiHandler::custom([
            'success' => true,
            'data' => $model->attributes,
        ]);
    }

    public function actionPostDelete($id) {
        $this->isAjax();

        $token = Yii::$app->request->post('token');

        if (!\common\helpers\IfapHelper::verifySingingToken(['id' => (int) $id], $token)) {
            Yii::error("Invalid token");
            ApiHandler::unauthorised(WebLang::t('invalid_request'));
        }
        $model = $this->findPostModel($id);

        if (!$model) {
            ApiHandler::failure(WebLang::t('internal_error'));
        }

        $transaction = Yii::$app->db->beginTransaction();

        if ($model->softDelete()) {
            // delete all related document and FS files
            $documents = KbDocument::find()->where(["idpost" => $id])->all();

            if ($documents) {
                foreach ($documents as $document) {
                    $dmsModel = new DmsModel();

                    if (!$dmsModel->delete($document->id, $document->token)) {
                        // even unable to delete the document, still continue?
//                        $transaction->rollBack();
//                        ApiHandler::failure(\common\helpers\IfapHelper::modelErrorHtml($dmsModel->getErrors()));
                    }
                }
            }
            // check if tags used in post.  Delete if orphaned
            $bbsPostTags = BbsPostTag::find()->where(['idpost' => $id, 'status' => 1])->all();

            if (count($bbsPostTags) > 0) {
                foreach ($bbsPostTags as $bbsPostTag) {
                    if (BbsPostTag::find()->where(['idtag' => $bbsPostTag->id, 'status' => 1])->all() == NULL) {
                        BbsTag::deleteAll(['id' => $bbsPostTag, 'status' => 1]);
                    }
                }
            }

            // delete post tags if any
            BbsPostTag::softDeleteAll(['idpost' => $id]);

            $transaction->commit();
            ApiHandler::success();
        } else {
            ApiHandler::failure(WebLang::$t['internal_error']);
        }
    }

    public function actionArticleSticky() {
        if (!Yii::$app->request->isAjax)
            $this->internalServerError();

        $id = Yii::$app->request->post('id');
        $token = Yii::$app->request->post('token');

        if (\common\helpers\IfapHelper::verifySingingToken(null, $token) != TRUE) {
            ApiHandler::unauthorised();
        }

        if ($id > 0)
            $model = BbsPost::findActiveOne($id);

        if (!$model)
            ApiHandler::failure(WebLang::$t['invalid_request']);
        else {
            $model->sticky = !$model->sticky;

            if ($model->save()) {
                ApiHandler::success(['id' => $model->id]);
            }
            ApiHandler::failure(WebLang::$t['internal_error']);
        }
    }

    /**
     * For summernote inline attach file
     * Need to consider if the KB was prepared at backend and shared to frontend
     */
    public function actionPostUpload() {

        $model = new KbDmsModel();

        if (!$model->upload()) {
            ApiHandler::failure("Some of the uploads failed");
        }

        foreach ($model->documents as $document) {
            // No need to return document id for security reason
            $fileUrls[] = "/backend/knowledge/download?token={$document->token}";
        }
        echo json_encode([
            'success' => true,
            'filename' => $fileUrls,
        ]);
    }

    public function actionDownload() {
        $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);

        $model = new KbDmsModel();
        return $model->download($token);
    }

}
