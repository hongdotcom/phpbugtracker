<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "repo_record".
 *
 * @property int $id
 * @property string $repo_name
 * @property string $update_date
 * @property int $status
 */
class RepoRecord extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'repo_record';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['update_date'], 'safe'],
            [['status'], 'integer'],
            [['repo_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'repo_name' => 'Repo Name',
            'update_date' => 'Update Date',
            'status' => 'Status',
        ];
    }
}
