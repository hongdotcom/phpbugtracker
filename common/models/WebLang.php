<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "weblang".
 *
 * @property integer $id
 * @property string $locale_id
 * @property string $eng
 * @property string $cht
 * @property string $chs
 * @property string $jap
 * @property string $kor
 * @property integer $order
 * @property string $link
 * @property string $column
 * @property string $column_format
 * @property integer $status
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $deleted_at
 * @property integer $deleted_by
 */
class WebLang extends \yii\db\ActiveRecord {

    public static $langs = [
        'eng',
        'cht',
        'chs',
        'jap',
            //'kor'
    ];
    public static $t = [];

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'weblang';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['eng', 'cht', 'chs', 'jap', 'kor'], 'string'],
            [['order', 'status', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['locale_id'], 'string', 'max' => 80],
            [['link', 'column', 'column_format'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'locale_id' => 'Locale ID',
            'eng' => 'Eng',
            'cht' => 'Cht',
            'chs' => 'Chs',
            'jap' => 'Jap',
            'kor' => 'Kor',
            'order' => 'Order',
            'link' => 'Link',
            'column' => 'Column',
            'column_format' => 'Column Format',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }

    public static function sp($lang = null) {
        if ($lang == null)
            $lang = Yii::$app->language;

        if ($lang == "eng") {
            return " ";
        } else {
            return "";
        }
    }

    public static function validateLang($lang) {
        if (in_array($lang, self::$langs))
            return $lang;
        else
            return 'eng';
    }

    public static function translateLang() {
        $lang = Yii::$app->language;
        $webLang = self::find()
                ->where(['IS NOT', 'locale_id', NULL])
                ->andWhere(['!=', 'locale_id', ''])
                ->andWhere(['status' => 1])
                ->orderBy(['locale_id' => SORT_ASC])
                ->asArray()
                ->all();
        self::$t = ArrayHelper::map($webLang, 'locale_id', $lang);

        if ($lang == "eng") {
            self::$t['sp'] = " ";
        } else {
            self::$t['sp'] = "";
        }
        Yii::trace("translateLang done with lang=$lang");
    }

    public static function getByLocaleId($localeId, $lang = null) {
        if (strtolower($localeId) == "sp") {
            if ($lang == "eng" || $lang == null) {
                return " ";
            } else {
                return "";
            }
        }

        if (isset(self::$t[$localeId]) && $lang == null) {
            return self::$t[$localeId];
        }
        return self::find()
                        ->select([$lang])
                        ->where([
                            'locale_id' => $localeId,
                            'status' => 1
                        ])
                        ->scalar();
    }

    public static function t($localeId, $lang = null) {

        if (empty($localeId) || !$localeId) {
            return '';
        }

        if (!$lang) {
            $lang = Yii::$app->language;
        }

        if(strstr($lang, 'en')) {
            $lang = "eng";
        }
        
        if (strtolower($localeId) == "sp") {
            if ($lang == "eng" || $lang == null) {
                return " ";
            } else {
                return "";
            }
        }

        // Sometimes the view needs to show specific lang DIFFERENT with Yii::$app->language
        if ($lang == Yii::$app->language) {
            if (isset(self::$t[$localeId])) {
//                Yii::error("weblang $lang, found $localeId, value=" . self::$t[$localeId] . ", no need SQL select");
                return trim(self::$t[$localeId]);
            }
        }

        $result = self::find()
                ->select([$lang])
                ->where([
                    'locale_id' => $localeId,
                    'status' => 1
                ])
                ->scalar();

        if (!$result) {
//        throw new \Exception("$localeId not defined in WebLang table");
            $return = "";
            $items = explode("_", $localeId);

            foreach ($items as $item) {
                $return .= $item . WebLang::sp();
            }

            if ($lang == "eng") {
                $return = ucfirst(trim($return));
            }

            return trim($return);
        }
        // only set the SQL selected to $t if same lang
        if ($lang == Yii::$app->language) {
            self::$t[$localeId] = $result;
        }
        return $result;
    }

}
