<?php

namespace common\models\aifin;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "people_role".
 *
 * @property integer $id
 * @property integer $idpeople
 * @property integer $idpeople_role_type
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 * @property integer $status
 *
 * @property People $idpeople0
 * @property PeopleRoleType $peopleRoleType
 */
class PeopleRole extends \yii\db\ActiveRecord {

    const HOLDER = 1;
    const WRITING = 2;
    const SERVICING = 3;
    const SIGNATURE = 4;
    const INSURED = 5;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'people_role';
    }

    public static function getDb() {
        return Yii::$app->get('dbMaster');
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['idpeople', 'idpeople_role_type'], 'required'],
            [['idpeople', 'idpeople_role_type', 'created_by', 'updated_by', 'deleted_by', 'status'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['idpeople'], 'exist', 'skipOnError' => true, 'targetClass' => People::className(), 'targetAttribute' => ['idpeople' => 'id']],
            [['idpeople_role_type'], 'exist', 'skipOnError' => true, 'targetClass' => PeopleRoleType::className(), 'targetAttribute' => ['idpeople_role_type' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'idpeople' => 'Idpeople',
            'idpeople_role_type' => 'Idpeople Role Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_by' => 'Deleted By',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeople() {
        return $this->hasOne(People::className(), ['id' => 'idpeople']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeopleRoleType() {
        return $this->hasOne(PeopleRoleType::className(), ['id' => 'idpeople_role_type']);
    }

    public static function getAllConsultant($level) {
        return self::find()
                        ->select([
                            "e.id",
                            new Expression("COALESCE(NULLIF(co_name,''), concat(lastname_eng, ' ', firstname_eng)) as name_eng"),
                            new Expression("COALESCE(NULLIF(co_name_locale,''), concat(lastname_locale, ' ', firstname_locale)) as name_locale"),
                        ])
                        ->from(self::tableName() . ' me')
                        ->leftJoin("people e", "e.id=me.idpeople AND idpeople_role_type=$level")
                        ->where([
                            "e.status" => 1,
                            "me.status" => 1,
                        ])
                        ->orderBy([
                            'name_eng' => SORT_ASC,
                            'name_locale' => SORT_ASC,
                        ])
                        ->asArray()
                        ->all();
    }

    public static function checkAgentExist($idpeople) {
        return self::find()
                        ->select(['idpeople'])
                        ->where(['idpeople' => $idpeople])
                        ->andWhere(['>=', 'idpeople_role_type', '1'])
                        ->andWhere(['<=', 'idpeople_role_type', '10'])
                        ->scalar();
    }

    public static function findPeopleRoleType($idpeople) {
        return self::find()
                        ->select(['idpeople_role_type'])
                        ->where([
                            'idpeople' => $idpeople,
                            'status' => 1
                        ])
                        ->scalar();
    }

}
