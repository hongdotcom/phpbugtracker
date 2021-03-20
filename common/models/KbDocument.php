<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "kb_document".
 *
 * @property int $id
 * @property int $idpost
 * @property string $orig_file_name
 * @property string $file_name
 * @property string $file_type
 * @property string $file_size
 * @property string $token
 * @property string $uuid
 * @property int $status
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property string $deleted_at
 * @property int $deleted_by
 */
class KbDocument extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'kb_document';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idpost', 'status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['orig_file_name', 'file_name', 'file_type', 'file_size'], 'required'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['orig_file_name', 'file_name', 'file_type', 'file_size'], 'string', 'max' => 255],
            [['token'], 'string', 'max' => 64],
            [['uuid'], 'string', 'max' => 40],
            [['token'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idpost' => 'Idpost',
            'orig_file_name' => 'Orig File Name',
            'file_name' => 'File Name',
            'file_type' => 'File Type',
            'file_size' => 'File Size',
            'token' => 'Token',
            'uuid' => 'Uuid',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }
}
