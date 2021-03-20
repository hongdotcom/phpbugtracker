<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "bbs_board_task".
 *
 * @property int $id
 * @property int $idboard
 * @property string $reminder_at
 * @property string $deadline
 * @property int $iduser_pic
 * @property string $content
 * @property int $progress
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $created_by
 * @property int $updated_by
 * @property int $deleted_by
 *
 * @property BbsBoard $board
 */
class BbsBoardTask extends \common\models\BaseAR {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'bbs_board_task';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['idboard', 'iduser_pic', 'progress', 'status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['reminder_at', 'deadline', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['content'], 'string'],
            [['idboard'], 'exist', 'skipOnError' => true, 'targetClass' => BbsBoard::className(), 'targetAttribute' => ['idboard' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'idboard' => 'Idboard',
            'reminder_at' => 'Reminder At',
            'deadline' => 'Deadline',
            'iduser_pic' => 'Iduser Pic',
            'content' => 'Content',
            'progress' => 'Progress',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_by' => 'Deleted By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBoard() {
        return $this->hasOne(BbsBoard::className(), ['id' => 'idboard']);
    }

    public function getPic() {
        return $this->hasOne(User::className(), ['id' => 'iduser_pic'])
                ->onCondition([
                    User::tableName() . '.status' => 1,
                ]);
    }

}
