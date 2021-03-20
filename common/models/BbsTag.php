<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "bbs_tag".
 *
 * @property integer $id
 * @property string $tagname
 * @property string $description
 * @property string $icon
 * @property integer $status
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $deleted_at
 * @property integer $deleted_by
 */
class BbsTag extends \common\models\BaseAR {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'bbs_tag';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['tagname', 'icon'], 'string', 'max' => 50],
            [['description'], 'string', 'max' => 255],
        ];
    }

    public function getBbsPostTags() {
        return $this->hasMany(BbsPostTag::className(), ['idtag' => 'id'])
                        ->onCondition([
                            BbsPostTag::tableName() . '.status' => 1,
        ]);
    }

}
