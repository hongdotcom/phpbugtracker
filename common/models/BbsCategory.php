<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "bbs_category".
 *
 * @property int $id
 * @property string $type
 * @property int $seq
 * @property string $eng
 * @property string $cht
 * @property string $chs
 * @property string $jap
 * @property string $icon
 * @property int $status
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property string $deleted_at
 * @property int $deleted_by
 */
class BbsCategory extends \common\models\BaseAR {

    const SOCIAL = 16;
    
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'bbs_category';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['type'], 'required'],
            [['seq', 'status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['type'], 'string', 'max' => 20],
            [['eng', 'cht', 'chs', 'jap', 'icon'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'seq' => 'Seq',
            'eng' => 'Eng',
            'cht' => 'Cht',
            'chs' => 'Chs',
            'jap' => 'Jap',
            'icon' => 'Icon',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }

    public function getBbsBoards() {
        return $this->hasMany(BbsBoard::className(), ['idcategory' => 'id'])
                        ->onCondition([
                            BbsBoard::tableName() . '.status' => 1,
//                            BbsBoard::tableName() . '.idparent' => null
        ]);
    }

}
