<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "bbs_post_meta".
 *
 * @property integer $id
 * @property integer $idpost
 * @property string $meta_key
 * @property string $meta_value
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $deleted_at
 * @property integer $deleted_by
 *
 * @property BbsPost $idpost0
 */
class BbsPostMeta extends \common\models\BaseAR
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bbs_post_meta';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idpost'], 'required'],
            [['idpost', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['meta_key', 'meta_value'], 'string', 'max' => 50],
            [['idpost'], 'exist', 'skipOnError' => true, 'targetClass' => BbsPost::className(), 'targetAttribute' => ['idpost' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'idpost' => Yii::t('app', 'Idpost'),
            'meta_key' => Yii::t('app', 'Meta Key'),
            'meta_value' => Yii::t('app', 'Meta Value'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'deleted_at' => Yii::t('app', 'Deleted At'),
            'deleted_by' => Yii::t('app', 'Deleted By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdpost0()
    {
        return $this->hasOne(BbsPost::className(), ['id' => 'idpost']);
    }
}
