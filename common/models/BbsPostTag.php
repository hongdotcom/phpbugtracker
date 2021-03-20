<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "bbs_post_tag".
 *
 * @property integer $id
 * @property integer $idpost
 * @property integer $status
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $deleted_at
 * @property integer $deleted_by
 *
 * @property BbsPost $idpost0
 */
class BbsPostTag extends \common\models\BaseAR {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'bbs_post_tag';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['idpost'], 'required'],
            [['idpost', 'status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['idpost'], 'exist', 'skipOnError' => true, 'targetClass' => BbsPost::className(), 'targetAttribute' => ['idpost' => 'id']],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBbsPost() {
        return $this->hasOne(BbsPost::className(), ['id' => 'idpost'])
                        ->onCondition([BbsPost::tableName() . '.status' => 1]);
    }

    public function getBbsTag() {
        return $this->hasOne(BbsTag::className(), ['id' => 'idtag'])
                        ->onCondition([BbsTag::tableName() . '.status' => 1]);
    }

}
