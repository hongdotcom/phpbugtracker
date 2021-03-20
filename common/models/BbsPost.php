<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "bbs_post".
 *
 * @property integer $id
 * @property integer $idparent
 * @property integer $idboard
 * @property string $subject
 * @property string $content
 * @property integer $views
 * @property integer $answers
 * @property integer $votes
 * @property integer $progress
 * @property integer $status
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $deleted_at
 * @property integer $deleted_by
 *
 * @property BbsBoard $idboard0
 * @property BbsPostMeta[] $bbsPostMetas
 * @property BbsPostTag[] $bbsPostTags
 */
class BbsPost extends \common\models\BaseAR {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'bbs_post';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['idparent', 'idboard', 'eng', 'cht', 'chs', 'jap', 'views', 'answers', 'votes', 'progress', 'status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['idboard', 'status'], 'required'],
            [['content'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['subject', 'slug'], 'string', 'max' => 255],
            [['idboard'], 'exist', 'skipOnError' => true, 'targetClass' => BbsBoard::className(), 'targetAttribute' => ['idboard' => 'id']],
        ];
    }

    public function increaseView() {
        $this->views += 1;
        $this->save(false);
        return TRUE;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBbsBoard() {
        return $this->hasOne(BbsBoard::className(), ['id' => 'idboard'])
                        ->onCondition([BbsBoard::tableName() . '.status' => 1]);
    }

    public function getCreatedBy() {
        return $this->hasOne(User::className(), ['id' => 'created_by'])
                        ->onCondition([User::tableName() . '.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBbsPostMetas() {
        return $this->hasMany(BbsPostMeta::className(), ['idpost' => 'id'])
                        ->onCondition([BbsPostMeta::tableName() . '.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBbsPostTags() {
        return $this->hasMany(BbsPostTag::className(), ['idpost' => 'id'])
                        ->onCondition([BbsPostTag::tableName() . '.status' => 1]);
    }

    public function getReplies() {
        return $this->hasMany(BbsPost::className(), ['replies.idparent' => 'id'])
                        ->from(BbsPost::tableName() . ' replies')
                        ->onCondition(['replies.status' => 1]);
    }

    public function getRelatedPosts() {
        $postTags = BbsPostTag::find()->select('idtag')->where(['idpost' => $this->id, 'status' => 1])->createCommand()->queryColumn();

        return $this->find()
                        ->joinWith(['bbsPostTags', 'bbsBoard.bbsCategory'])
                        ->where(
                                [
                                    'in', 'bbs_post_tag.idtag', $postTags
                                ]
                        )
                        ->andWhere(['bbs_post_tag.status' => 1, 'bbs_post.status' => 1])
                        ->andWhere(['bbs_category.type' => 'bbs', 'bbs_post.status' => 1])
                        ->andWhere(['<>', 'bbs_post.id', $this->id])
                        ->distinct('bbs_post_tag.idpost')
                        ->orderBy('bbs_post.created_at DESC')
                        ->limit(10)
                        ->all();
    }

    public function getRelatedKb() {
        $postTags = BbsPostTag::find()->select('idtag')->where(['idpost' => $this->id, 'status' => 1])->createCommand()->queryColumn();

        return $this->find()
                        ->joinWith(['bbsPostTags', 'bbsBoard.bbsCategory'])
                        ->where(
                                [
                                    'in', 'bbs_post_tag.idtag', $postTags
                                ]
                        )
                        ->andWhere(['bbs_post_tag.status' => 1, 'bbs_post.status' => 1])
                        ->andWhere(['bbs_category.type' => 'kb', 'bbs_post.status' => 1])
                        ->distinct('bbs_post_tag.idpost')
                        ->orderBy('bbs_post.created_at DESC')
                        ->limit(10)
                        ->all();
    }

}
