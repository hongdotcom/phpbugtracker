<?php

namespace common\models\aifin;

use common\helpers\IfapHelper;
use Yii;
use yii\base\NotSupportedException;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;
use yii\helpers\Url;

class User extends \yii\db\ActiveRecord implements IdentityInterface {

    public $idpeople;
    public $username;
    public $lang;
    public $idpeople_role_type;
    public $people_role_type_name;
    public $idrole;
    public $login_session;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by',
            'email_validation_date', 'reset_password_datetime', 'email_validation_token_expiry', 'data_control',
            'auth_key', 'password_hash', 'password_ot_hash'], 'safe'],
            [['data_control', 'status', 'email_validated', 'reset_password_required', 'email_fake', 'acl_role', 'g2fa_enable', 'profilepic'], 'integer'],
            [['email_fake'], 'default', 'value' => 0],
            //['email', 'email'],
            [['password_hash', 'password_ot_hash'], 'string', 'min' => 6],
            [['email'], 'required'],
            [['email'], 'filter', 'filter' => 'trim'],
            [['email', 'password_hash', 'email_address_pending', 'email_validation_token',
            'password_ot_hash', 'reset_password_token'], 'string', 'max' => 255],
            [['password', 'password_ot', 'g2fa_secret'], 'string', 'max' => 40],
            [['auth_key'], 'string', 'max' => 32],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],
        ];
    }

    public static function getDb() {
        return Yii::$app->get('dbMaster');
    }

    /**
     * @param int|string $id
     * @return array|null|User
     */
    public static function findIdentity($id) {
        if (Yii::$app->language == 'eng')
            $username = 'CONCAT_WS(" ", p.co_name, p.lastname_eng, p.firstname_eng)';
        else
            $username = 'CONCAT_WS("", p.co_name_locale, p.lastname_locale, p.firstname_locale)';

        /** @var User $user */
        $user = self::find()
                ->select([
                    'u.*',
                    new Expression($username) . ' as username',
                    'p.lang as lang',
                    'p.id as idpeople',
                    'pr.idpeople_role_type',
                    'prt.role_type_name_' . Yii::$app->language . ' as people_role_type_name',
                    'urr.idrole as idrole',
                    'ls.session as login_session'
                        //'landing_page'
                ])
                ->from(self::tableName() . ' u')
                ->innerJoin(UserPeople::tableName() . ' up', 'u.id=up.iduser')
                ->innerJoin(People::tableName() . ' p', 'up.idpeople=p.id')
                ->innerJoin(PeopleRole::tableName() . ' pr', 'p.id=pr.idpeople')
                ->innerJoin(PeopleRoleType::tableName() . ' prt', 'pr.idpeople_role_type=prt.id')
                ->innerJoin(UserRoleRelationship::tableName() . ' urr', 'u.id=urr.iduser')
                ->innerJoin(LoginSession::tableName() . ' ls', 'u.id=ls.iduser')
                //->innerJoin(UserRole::tableName() . ' ur', 'ur.id=urr.idrole')
                //join user_role ? if so, need to change json format to one to many table
                //one or multiple user role ?
                ->where([
                    'u.id' => $id,
                    'u.status' => 1,
                    'primary_account' => 1,
                    'ls.interface' => Yii::$app->params['interface']
                ])
                ->one();

        /**
         * Check DB login_session is equal to current user's
         */
        if ($user != NULL && Yii::$app->session->get('loginSession') != $user->login_session) {
            Yii::$app->session->set('loginSession', NULL);
            return NULL;
        }

        return $user;
    }

    public function logout() {
        LoginSession::clearSession($this->id);
        Yii::$app->user->logout();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return mixed
     */
    public static function findByEmail($email) {
        return self::find()
                        ->where([
                            'email' => $email,
                            'status' => 1
                        ])
                        ->one();
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token) {
        return static::findOne([
                    'email_validation_token' => $token,
                    'status' => 1,
        ]);
    }

    /**
     * Check if password reset token is valid
     *
     * @param $expiryTime
     * @return bool
     */
    public static function isPasswordResetTokenValid($expiryTime) {
        if (empty($expiryTime)) {
            return false;
        }

        return $expiryTime >= date("Y-m-d H:i:s");
    }

    public static function getAllActiveUsers() {
        return self::find()
                        ->where(['status' => 1])
                        ->orderBy(['email' => SORT_ASC])
                        ->all();
    }

    public static function getActiveUserByIds($userIds) {
        return self::find()
                        ->where([
                            'status' => 1
                        ])
                        ->andWhere(['IN', 'id', $userIds])
                        ->all();
    }

//    /**
//     * @inheritdoc
//     */
//    public function attributeLabels() {
//        return [
//            'id' => 'ID',
//            'email' => 'Email',
//            'password_hash' => 'Password Hash',
//            'auth_key' => 'Auth Key',
//            'email_validated' => 'Email Validated',
//            'email_address_pending' => 'Email Address Pending',
//            'email_validation_token' => 'Email Validation Token',
//            'email_validation_token_expiry' => 'Email Validation Token Expiry',
//            'email_validation_date' => 'Email Validation Date',
//            'email_fake' => 'Email Fake',
//            'acl_role' => 'Acl Role',
//            'data_control' => 'Data Control',
//            'status' => 'Status',
//            'password' => 'Password',
//            'password_ot' => 'Password Ot',
//            'password_ot_hash' => 'Password Ot Hash',
//            'reset_password_required' => 'Reset Password Required',
//            'reset_password_datetime' => 'Reset Password Datetime',
//            'g2fa_enable' => 'G2fa Enable',
//            'g2fa_secret' => 'G2fa Secret',
//            'profilepic' => 'Profilepic',
//            'created_at' => 'Created At',
//            'created_by' => 'Created By',
//            'updated_at' => 'Updated At',
//            'updated_by' => 'Updated By',
//            'deleted_at' => 'Deleted At',
//            'deleted_by' => 'Deleted By',
//        ];
//    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * @inheritdoc
     */
    /* public function getId()
      {
      return $this->getPrimaryKey();
      } */

    /**
     * @inheritdoc
     */
    public function getAuthKey() {
        return $this->auth_key;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password) {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function setPasswordOt($password) {
        $this->password_ot_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey() {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken() {
        $this->reset_password_required = 1;
        $this->reset_password_datetime = date("Y-m-d H:i:s");
        $this->email_validation_token = Yii::$app->security->generateRandomString();
        $this->email_validation_token_expiry = date('Y-m-d H:i:s', strtotime('+' . (int) Settings::getByKey('reset_password_expire_days') . ' day', time()));
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken() {
        $this->email_validation_token = $this->email_validation_token_expiry = NULL;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeoples() {
        return $this->hasMany(People::className(), ['id' => 'idpeople'])
                        ->from(People::tableName() . ' p')
                        ->onCondition(['p.status' => 1])
                        ->viaTable(UserPeople::tableName(), ['iduser' => 'id'], function ($query) {
                            return $query->from(UserPeople::tableName() . ' up')
                                    ->onCondition(['up.status' => 1]);
                        });
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserPeoples() {
        return $this->hasMany(UserPeople::className(), ['iduser' => 'id'])
                        ->from(UserPeople::tableName() . ' up')
                        ->onCondition(['up.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrimaryUserPeople() {
        return $this->hasOne(UserPeople::className(), ['iduser' => 'id'])
                        ->onCondition([UserPeople::tableName() . '.primary_account' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrimaryPeopleRole() {
        return $this->hasOne(PeopleRole::className(), ['idpeople' => 'idpeople'])
                        ->via('primaryUserPeople');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRoleRelationship() {
        return $this->hasOne(UserRoleRelationship::className(), ['iduser' => 'id']);
    }

    public function getAssistantOfPeople() {
        return UserPeople::getAuthorisedPeople($this->id);
    }

    public function getPeopleTree() {
        return UserPeople::getCrossTreePeopleByIduser($this->id);
    }

    /**
     * @param $localeId
     * @return bool
     */
    public function checkPermission($localeId) {
        $permission = Permission::find()
                ->from(Permission::tableName() . ' p')
                ->innerJoin(RolePermission::tableName() . ' rp', 'p.id=rp.permission_id')
                ->innerJoin(UserRoleRelationship::tableName() . ' urr', 'rp.role_id=urr.idrole')
                ->where([
                    'p.locale_id' => $localeId,
                    'urr.iduser' => $this->id
                ])
                ->one();

        return $permission != NULL;
    }

    public function getId() {
//        if ($BOUser = $this->isBOUser()) {
//            return $BOUser->api_user_id;
//        } else return $this->getPrimaryKey();
        return $this->getPrimaryKey();
    }

    public function isBOUser() {
        $BOUser = Yii::$app->session->get('BOUser');

        if (empty($BOUser) || $BOUser == NULL)
            return FALSE;
        else
            return $BOUser;
    }

    public function getUsername() {
        if ($BOUser = $this->isBOUser()) {
            return $BOUser->login_name;
        } else
            return $this->username;
    }

    public function getStatusTranslate() {
        return $this->hasOne(FieldTranslate::className(), ['status' => 'status'])
                        ->where(['table' => self::tableName()])
                        ->andWhere(['field' => 'status']);
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'user';
    }

    public function softDelete() {
        /* Delete User */
        // softDelete leads to weird behavior.  Cannot save User record 2017-12-14
        $result = parent::delete();

        if (!$result) {
            return false;
        }

        $this->deleteUserPeople();

        $this->deleteUserRole();

        return TRUE;
    }

    public function deleteUserPeople() {
        return UserPeople::deleteAll(['iduser' => $this->id]);
    }

    public function deleteUserRole() {
        return UserRoleRelationship::deleteAll(['iduser' => $this->id]);
    }

    public function updatePassword($password) {
        $this->setPassword($password);
        $this->password = $password;
        $this->reset_password_datetime = date('Y-m-d H:i:s');
        return $this->save();
    }

    public function updatePasswordOneTime($password_ot) {
        $this->setPasswordOt($password_ot);
        $this->password_ot = $password_ot;
        return $this->save();
    }

    public function checkEmailInUse($email) {
        $idpeople = self::find()
                ->select(['up.idpeople'])
                ->from(User::tableName() . ' u')
                ->leftJoin("user_people up", "up.iduser=u.id")
                ->where([
                    'email' => $email,
                    'u.status' => 1,
                    'up.status' => 1,
                ])
                ->scalar();

        if ($idpeople != NULL) {
            return $idpeople;
        } else {
            return FALSE;
        }
    }

    public function updateEmail($email) {
        if ($this->checkEmailInUse($email) == FALSE) {
            $this->email = $email;
            return $this->save();
        }
    }

    public function updateUserRoleType($user_role_type) {
        $this->deleteUserRole();

        return $this->addRole($user_role_type);
    }

    public static function getAvailableUsers($arrExcludeUsers) {
        $lang = Yii::$app->language;
        if ($lang == "eng") {
            $itemName = new Expression("concat_ws(' ', e.co_name, e.firstname_eng, e.lastname_eng) as itemname");
        } else {
            $itemName = new Expression("concat_ws('', e.co_name_locale, e.lastname_locale, e.firstname_locale) as itemname");
        }

        $query = self::find()
                ->select([
                    "u.id",
                    new Expression("1 as active"), // for live-search widget
                    $itemName
                ])
                ->from(self::tableName() . ' u')
                ->innerJoin("user_people up", "up.iduser=u.id AND up.status=1")
                ->innerJoin("people e", "e.id=up.idpeople AND e.status=1")
                ->innerJoin("people_role pr", "pr.idpeople=e.id AND pr.status=1 AND pr.idpeople_role_type=" . IfapHelper::PT_STAFF);

        if ($arrExcludeUsers && count($arrExcludeUsers) > 0) {
            $query->where([
                        "u.status" => 1
                    ])
                    ->andWhere(['NOT IN', "u.id", $arrExcludeUsers]);
        }

        $query->orderBy(["itemname" => SORT_ASC]);

        return $query->asArray()->all();
    }

    public function addRole($idrole) {
        $userRole = UserRole::findActiveOne(['id' => $idrole]);

        if ($userRole == NULL)
            return FALSE;

        $urr = new UserRoleRelationship();
        $urr->iduser = $this->id;
        $urr->idrole = $idrole;
        return $urr->save();
    }

    public function primaryUserPeople() {
        if ($this->primaryUserPeople == NULL || $this->primaryUserPeople->people == NULL)
            return FALSE;

        return $this->primaryUserPeople->people;
    }

    public static function create($idpeople, $email, $idrole, $aclRole) {
        $user = new self;
        $password = IfapHelper::genRandomKey(8);
        $user->password = $password;
        $password_ot = IfapHelper::genRandomKey(8);
        $user->password_ot = $password_ot;

        /*
         * If People has no email_primary, generate a temp
         */
        if ($email == NULL || trim($email) == "")
            $email = IfapHelper::genRandomKey(8) . "@" . date("Ymd");

        $user->email = $email;
        $user->auth_key = IfapHelper::genRandomKey(32);
        $user->email_validated = 0;
        $user->email_fake = 1;
        $user->reset_password_required = 0;
        $user->status = 1;
        $user->acl_role = $aclRole; // 0 = frontend only, 1 = backend only.
        $user->setPassword($password);
        $user->setPasswordOt($password_ot);

        if ($user->save() && $user->addRole($idrole)) {
            $existing = UserPeople::checkExist($user->id, $idpeople, 1);

            $flag = TRUE;

            if (!$existing) {
                // Create UserPeople
                $up = new UserPeople();
                $up->iduser = $user->id;
                $up->idpeople = $idpeople;
                $up->primary_account = 1; // New user always primary

                if (!$up->save()) {
                    $flag = FALSE;
                }
            }

            if ($flag) {
                // Create default agent1 assistant record
                $agent1 = Settings::getByKey('agent1');

                if (!$agent1) {
                    Yii::error("Agent1 not found in Settings");
                    return FALSE;
                }
                $up = new UserPeople();
                $up->iduser = $user->id;
                $up->idpeople = $agent1;
                $up->primary_account = 0;

                if (!$up->save()) {
                    $flag = FALSE;
                }

                return TRUE;
            }
        } else
            return FALSE;
    }

    /**
     * @return People[]
     */
    public static function getRelatedSds() {
        return People::find()
                        ->distinct()
                        ->joinWith([
                            'peopleRole',
                            'relationship'
                        ])
                        ->where([
                            People::tableName() . '.status' => 1,
                            People::tableName() . '.active' => 1,
                            PeopleRole::tableName() . '.status' => 1,
                            PeopleRole::tableName() . '.idpeople_role_type' => 2,
                            Relationship::tableName() . '.agent_level' => 2
                        ])
                        ->andWhere(['IN', People::tableName() . '.id', ArrayHelper::getColumn(Yii::$app->user->identity->userPeoples, 'idpeople')])
                        ->all();
    }

    /**
     * @return array
     */
    public static function getDownlinePeopleIds() {
        $relatedSds = Yii::$app->user->identity->relatedSds;

        $downlines = [];
        foreach ($relatedSds as $sd) {
            self::downlines($sd->relationship->downlines, $downlines);
        }

        return $downlines;
    }

    /**
     * @param Relationship[] $introducers
     * @param array $downlines
     */
    private static function downlines(array $introducers, array &$downlines) {
        if (empty($introducers))
            return;

        foreach ($introducers as $introducer) {
            if (!in_array($introducer->idpeople, $downlines))
                $downlines[] = $introducer->idpeople;

            self::downlines($introducer->downlines, $downlines);
        }
    }

    public function getProfilePicDocument() {
        return $this->hasOne(Document::className(), ['id' => 'profilepic'])
                        ->onCondition([Document::tableName() . '.status' => 1]);
    }

    public function showProfilePic($originalSize = false, $class = "", $style = "") {
        if ($this->profilepic > 0 && $this->profilePicDocument) {
            if (!$class) {
                return '<img class="' . ($originalSize ? 'user-icon-large' : 'user-icon') . " " . $class . ' pull-left" src="' .
                        Url::to(['/dms/download']) . "?a=5&id=" . $this->profilepic . "&token=" . $this->profilePicDocument->token .
                        '" style="' . $style . '">';
            } else {
                return '<img class="' . $class . '" src="' .
                        Url::to(['/dms/download']) . "?a=5&id=" . $this->profilepic . "&token=" . $this->profilePicDocument->token .
                        '" style="' . $style . '">';
            }
        } else {
            return $this->getNameTag();
        }
    }

    public function getNameTag() {
        $nameStr = "";

        if (Yii::$app->language == "eng") {
//            var_dump($this->primaryUserPeople); die();
            $initials = $this->primaryUserPeople->people->name();
            $initials = explode(" ", $initials);

            $count = 0;
            foreach ($initials as $item) {
                $nameStr .= strtoupper(mb_substr($item, 0, 1));
                $count++;

                // Max 2 initial letters
                if ($count > 2) {
                    break;
                }
            }
        } else {
            $nameStr = mb_substr($this->primaryUserPeople->people->name(), 0, 1);
        }

        return '<span class="btn btn-icon btn-primary">' . $nameStr . '</span>';
    }

    public function name() {
        return $this->primaryUserPeople->people->name();
    }

    public function getAuditTrailActivities() {
        return $this->hasMany(AuditTrail::className(), ['id' => 'created_by'])
                        ->onCondition(['audit_trail.status' => 1]);
    }

    /**
     * Return TRUE if idpeople is authorised to view idplan OR user is agent1 assistant
     * Consider internal user assistants
     * @param type $idplan
     * @param type $idpeople
     */
    public function checkPlanIsAuthorised($idplan) {

        $userPeoples = $this->userPeoples;

        if (!$userPeoples) {
            Yii::error("NO userPeoples found");
            return false;
        }

        foreach ($userPeoples as $userPeople) {
            // always return true if user is assistant of agent1
            if ($userPeople->idpeople == Settings::$key['agent1']) {
                return TRUE;
            }
            Yii::error("checking idpeople {$userPeople->idpeople}");
            $downlines = AgencyRelationshipAdvance::getWholeDownlineTree($userPeople->idpeople);


            if (!$downlines) {
                Yii::error("user has NO downlines");
            } else {
                $downlineIdpeoples = array_column($downlines, 'idpeople');
            }

//            // append logged in user primary idpeople
            $downlineIdpeoples[] = Yii::$app->user->identity->idpeople;

            if (PlanPeople::find()
                            ->where(['in', 'idpeople', $downlineIdpeoples])
                            ->andWhere(['idplan' => $idplan, 'role_type' => PeopleRole::SERVICING, 'status' => 1, 'end_date' => '9999-12-31'])
                            ->exists()
            ) {

                return true;
            }
        }
        return false;
    }

}
