<?php

namespace common\models\aifin;

use common\helpers\IfapHelper;
use Yii;

/**
 * This is the model class for table "user_people".
 *
 * @property integer $id
 * @property integer $iduser
 * @property integer $idpeople
 * @property integer $primary_account
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 * @property integer $status
 *
 * @property User $user
 * @property People $people
 */
class UserPeople extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'user_people';
    }

    public static function getDb() {
        return Yii::$app->get('dbMaster');
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['iduser', 'idpeople', 'primary_account'], 'required'],
            [['iduser', 'idpeople', 'primary_account', 'created_by', 'updated_by', 'deleted_by', 'status'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['iduser'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['iduser' => 'id']],
            [['idpeople'], 'exist', 'skipOnError' => true, 'targetClass' => People::className(), 'targetAttribute' => ['idpeople' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'iduser' => 'Iduser',
            'idpeople' => 'Idpeople',
            'primary_account' => 'Primary Account',
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
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'iduser'])
                        ->onCondition(['user.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeople() {
        return $this->hasOne(People::className(), ['id' => 'idpeople'])
                        ->from(People::tableName() . ' p')
                        ->onCondition(['p.status' => 1/* , 'primary_account' => 1 */]);
    }

    /**
     * getAuthorisedPeople
     * Get authorised Agent 1-10 only, excluding client, internal user types, EXCLUDING herself
     * @param integer
     * @return array
     */
    public static function getAuthorisedPeople($iduser) {
        return self::find()
                        ->select(["up.idpeople"])
                        ->from("user_people up")
                        ->innerJoin("people_role pr", "pr.idpeople=up.idpeople AND pr.status=1 AND pr.idpeople_role_type IN (1,2,3,4,5,6,7,8,9,10)")
                        ->where([
                            'iduser' => $iduser,
                            'up.status' => 1,
                        ])
                        ->column();
    }

    /**
     *
     * @param integer $iduser PK of user table
     * @return array A flat array of idpeople, not associative, including herself
     */
    public static function getCrossTreePeopleByIduser($iduser) {
        $peopleTree = array();
        $people = self::getAuthorisedPeople($iduser);

        if ($people && count($people) > 0) {
            foreach ($people as $person) {
                $currentPeopleTree = AgencyRelationshipAdvance::getConsultantWholeTree($person);

                if ($currentPeopleTree && count($currentPeopleTree) > 0) {
                    $peopleTree = array_merge($peopleTree, $currentPeopleTree);
                }
            }
        } else {
            Yii::trace("No authorised people for user $iduser");
            return NULL;
        }
        /* Himself should be included?? */
        $peopleTree[] = UserPeople::getPrimaryPeopleByIduser($iduser);

        /* Remove duplicated elements */
        $peopleTree = array_unique($peopleTree);
        return $peopleTree;
    }

    public static function getPrimaryPeopleByIduser($iduser) {
        return self::find()
                        ->select(['idpeople'])
                        ->where([
                            'status' => 1,
                            'primary_account' => 1,
                            'iduser' => $iduser
                        ])
                        ->scalar();
    }

    public static function getIduserByIdpeople($idpeople) {
        return self::find()
                        ->select(['iduser'])
                        ->where([
                            'status' => 1,
                            'primary_account' => 1,
                            'idpeople' => $idpeople
                        ])
                        ->scalar();
    }

    public static function checkExist($iduser, $idpeople, $primary_account) {
        $result = self::find()
                ->where([
                    'iduser' => $iduser,
                    'idpeople' => $idpeople,
                    'primary_account' => $primary_account,
                    'status' => 1,
                ])
                ->one();

        if ($result != NULL)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * This considers people_role_type = PT_STAFF only (ignores PT_CONTACTPOINT)
     * @param $idpeople
     * @param string $mode
     * @return mixed
     */
    public static function getAssistantUsersByIdpeople($idpeople, $mode = "object") {
        $query = self::find()
                ->select(["user_people.iduser"])
                ->innerJoin("user_people up1", "up1.iduser = user_people.iduser AND up1.status=1 AND up1.primary_account=1")
                ->innerJoin("people_role pr", "pr.idpeople=up1.idpeople AND pr.status=1 AND pr.idpeople_role_type=" . IfapHelper::PT_STAFF)
                ->where([
            "user_people.idpeople" => $idpeople,
            "user_people.status" => 1,
            "user_people.primary_account" => 0
        ]);

        if ($mode == "object") {
            $result = $query->all();
        } else {
            $result = $query->column();
        }
        return $result;
    }

}
