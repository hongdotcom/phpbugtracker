<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\helpers;

use common\models\WebLang;
use Yii;
use kartik\checkbox\CheckboxX;

//use yii\bootstrap\Dropdown;

/**
 * Description of IfapFormHelper
 */
class IfapFormHelper {

// generate bootstrap form widgets
    public static function genCountryRadio($element_id, $element_name, $array, $default_value, $required = 0, $disabled = 0) {
        foreach ($array as $item) {
            echo '<div class="radio m-b-15">';
            echo '<label>';
            echo "<input type='radio' id='$element_id' name='$element_name' value='$item->id'";

            if ($item == $default_value) {
                echo ' checked';
            }

            if ($required) {
                echo " required";
            }
            if ($disabled) {
                echo " disabled";
            }
            echo ">";
            echo " <i class='input-helper'></i>";
            echo $item->catName;
            if ($required) {
                echo " <span class='text-danger'>*</span>";
            }
            echo "</label>";
            echo "</div>";
        }
    }

    /**
     * 
     * @param type $label
     * @param type $element_id
     * @param type $element_name
     * @param type $object
     * @param type $default_value
     * @param type $required
     * @param type $disabled
     * @param type $readonly
     * @param type $onChange
     * @param type $class
     */
    public static function genSelectField($label, $element_id, $element_name, $object, $default_value, $required = 0, $disabled = 0, $readonly = 0, $onChange = "", $class = null) {
        // assume $object is always [0]=id, [1]=itemname

        echo '<div class="form-group fg-line">';

        if (!empty($label)) {
            echo '    <label class="control-label" for="' . $element_id . '">' . $label;
            if ($required) {
                echo " <span class='text-danger'>*</span>";
            }
            echo '</label>';
        }
        echo '        <div class="select" ';
        echo '>';
        echo '            <select id="' . $element_id . '" name="' . $element_name . '" class="form-control';
        if ($class) {
            echo " $class";
        }
        echo '"';

        if ($required) {
            echo " required";
        }
        if ($disabled) {
            echo " disabled";
        }
        if ($readonly) {
            echo " readonly";
        }
        if (!empty($onChange)) {
            echo " onChange=\"" . $onChange . "\"";
        }
        echo '>';
        echo '<option value="0">' . WebLang::t('select') . '</option>';

        foreach ((array) $object as $index => $item) {

            $itemname = (isset($item) ? $item : 'invalid itemname');

            echo "<option value='$index'";

            if ($index == $default_value) {
                echo ' selected';
            }
            echo '>' . $itemname . '</option>';
        }
        echo '</select>';
        echo '</div>';
        echo '</div>';
    }

    public static function genSelectFieldWithExclude($label, $element_id, $element_name, $object, $arrExclude, $default_value, $required = 0, $disabled = 0, $readonly = 0) {
// assume $object is always [0]=id, [1]=itemname
        echo '<div class="form-group fg-line">';
        echo '    <label class="control-label" for="' . $element_id . '">' . $label;
        if ($required) {
            echo " <span class='text-danger'>*</span>";
        }
        echo '</label>';
        echo '        <div class="select">';
        echo '            <select id="' . $element_id . '" name="' . $element_name . '" class="form-control"';

        if ($required) {
            echo " required";
        }
        if ($disabled) {
            echo " disabled";
        }
        if ($readonly) {
            echo " readonly";
        }
        echo '>';
        echo '                <option value="">' . WebLang::$t['select'] . '</option>';

        foreach ($object as $key => $item) {

            if (in_array($item['id'], $arrExclude) == FALSE) {
                echo '<option value="' . $item['id'] . '"';

                if ($item['id'] == $default_value) {
                    echo ' selected';
                }
                echo '>' . $item['itemname'] . '</option>';
            }
        }
        echo '</select>';
        echo '</div>';
        echo '</div>';
    }

    public static function genDatePicker($form, $model, $field) {
        $template = '<div class="fg-line"><label class="control-label for="' . $field . '"></label>{input}</div>';
        return $form->field($model, $field)->textInput(['template' => $template]);
    }

    /**
     * 
     * @param type $label
     * @param type $element_id
     * @param type $element_name
     * @param type $default_value
     * @param type $required
     * @param type $disabled
     * @param type $readonly
     */
    public static function datePicker($label, $element_id, $element_name, $default_value, $required = 0, $disabled = 0, $readonly = 0) {
        echo '<div class = "form-group">';
        echo "<label class='control-label' for = '$element_id'>$label";
        if ($required) {
            echo " <span class='text-danger'>*</span>";
        }
        echo "</label>";
        echo '<div class="dtp-container fg-line">';
        echo "<input type='text' class='form-control date-picker' ";
        echo "id='$element_id' name='$element_name' value='$default_value'";
        if ($required) {
            echo " required";
        }
        if ($disabled) {
            echo " disabled";
        }
        if ($readonly) {
            echo " readonly";
        }
        echo ">";
        echo "</div></div>";
    }

    public static function genDateTimePicker($label, $element_id, $element_name, $default_value, $required = 0, $disabled = 0, $readonly = 0) {
        echo '<div class = "form-group">';
        echo "<label class='control-label' for = '$element_id'>$label";
        if ($required) {
            echo " <span class='text-danger'>*</span>";
        }
        echo "</label>";
        echo '<div class="dtp-container fg-line">';
        echo "<input type='text' class='form-control date-time-picker' ";
        echo "id='$element_id' name='$element_name' value='$default_value'";
        if ($required) {
            echo " required";
        }
        if ($disabled) {
            echo " disabled";
        }
        if ($readonly) {
            echo " readonly";
        }
        echo ">";
        echo "</div></div>";
    }

    public static function genInputText($form, $model, $field) {
        $template = '<div class="fg-line"><label class="control-label for="' . $field . '"></label>{input}</div>';
        return $form->field($model, $field)->textInput(['template' => $template]);
    }

//$form->field($item, 'id', ['checkboxTemplate' => "<div class=\"checkbox123\">\n{input}
//{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"])->checkbox(['value' => true])

    /**
     * 
     * @param type $form
     * @param type $model
     * @param type $field
     * @param type $haystackModel
     * @param type $options
     * @return type
     */
    public static function genDropDown($form, $model, $field, $haystackModel, $options = null, $label = null) {
        $template = '{label}<div class=""><div class="select">{input}</div></div>';
        $finalOptions = $options;
        $finalOptions['prompt'] = WebLang::t('select');

        if (!$label) {
            return $form->field($model, $field, ['template' => $template])->dropDownList($haystackModel, $finalOptions);
        } else {
            return $form->field($model, $field, ['template' => $template])->dropDownList($haystackModel, $finalOptions)->label($label);
        }
    }

    public static function genInputPassword($form, $model, $field) {
        $template = '<div class="fg-line"><label class="control-label for="' . $field . '"></label>{input}</div>';
        return $form->field($model, $field)->passwordInput(['template' => $template]);
    }

    /**
     * 
     * @param type $form
     * @param type $model
     * @param type $field
     * @param type $label
     * @param type $value
     * @return type
     */
    public static function genCheckbox($form, $model, $field, $label, $value) {
        return $form->field($model, $field)->checkbox(['label' => $label]);
    }

    /**
     * 
     * @param type $form
     * @param type $model
     * @param type $field
     * @param type $label
     * @param type $value
     * @param type $labelOptions
     * @return type
     */
    public static function genCheckboxAifin($form, $model, $field, $label, $value, $labelOptions = NULL) {

        $checkboxTemplate = '<div class="checkbox m-r-20 p-b-15 m-b-25">{beginLabel}{input}<i class="input-helper"></i>' .
//                ($cssClass ? "<span class='$cssClass'>" : '') .
                $label .
//                ($cssClass ? '</span>' : '') .
                '{error}{hint}{endLabel}</div>';
        return $form->field($model, $field, ['template' => $checkboxTemplate])->checkbox(['labelOptions' => $labelOptions], FALSE);
    }

    public static function genCheckboxMaterial($label, $element_id, $element_name, $default_value = 1, $required = 0, $checked = 0) {
        return '<div class="checkbox m-b-25">' .
                    '<label class="control-label">' .
                        "<input type=\"checkbox\" name=\"$element_name\" id=\"$element_id\" value=\"$default_value\"" .
                        " class='checkbox-item' " .
                        ($checked == 1 ? ' checked' : '') . 
                        ($required == 1 ? ' required' : '') . ">" .
                        "<i class=\"input-helper\"></i>$label<br>" .
                        '<small class="text-primary"></small>' .
                    '</label>' .
                '</div>';
    }
    /**
     * 
     * @param type $label
     * @param type $element_id
     * @param type $element_name
     * @param type $default_value
     * @param type $required
     * @param type $disabled
     * @return type
     */
    public static function inputText($label, $element_id, $element_name, $default_value, $required = 0, $disabled = 0, $inputClass = null) {
        echo "<div class='form-group fg-line";
        if($required) {
            echo " required";
        }
        echo "'>";
        echo "<label class='control-label'";
        echo " for='" . $element_name . "'> " . WebLang::t($label) . "</label>";

        echo "<input type='text' class='form-control" .
        ($inputClass ? " " . $inputClass : '')
        . "' ";

        if ($element_id)
            echo " id='$element_id'";

        if ($element_name)
            echo " name='$element_name'";

        if ($default_value) {
            echo " value='$default_value'";
        }
        if ($required) {
            echo " required";
        }
        if ($disabled) {
            echo " disabled";
        }
        echo ">";
        echo '</div>';
        return;
    }

    /**
     * 
     * @param type $form
     * @param type $model
     * @param type $field
     * @param type $label
     * @param type $rows
     * @return type
     */
    public static function genTextArea($form, $model, $field, $label = null, $rows = 2) {
        $template = '<div class="fg-line"><label class="control-label for="' . $field . '"></label>' . '{input}' . '</div>';
        return $form->field($model, $field)->textarea(['rows' => $rows, 'template' => $template, 'class' => 'form-control auto-size'])->label($label);
    }

    public static function genRadioButton($form, $model, $field) {
        $template = '<label class="radio radio-inline m-r-20 control-label">{input}<i class="input-helper"></i></label>';
        return $form->field($model, $field)->radioInput(['template' => $template]);
    }

    public static function genRadioList($form, $model, $field) {
        $template = '<label class="radio radio-inline m-r-20 control-label">{input}<i class="input-helper"></i></label>';
        return $form->field($model, $field)->radioList(['template' => $template]);
    }

    /**
     *
     * @global type WebLang::$t['select']
     * @param type $label
     * @param type $element_id
     * @param type $element_name
     * @param type $object
     * @param type $default_value
     * @param type $required
     * @param type $disabled
     * @param type $live_search
     */
    public static function genSelectFieldOrdinary($label, $element_id, $element_name, $object, $default_value, $required = 0, $disabled = 0, $live_search = 0) {
        echo "<div class='m-t-10'>";
        if ($required) {
            echo "<span class='text-danger'>* </span>";
        }
        echo '<span class="text-primary">' . $label . '</span>';
        echo '            <select id="' . $element_id . '" name="' . $element_name . '"';
//        echo ' style="width: 100%" ';

        if ($required) {
            echo " required";
        }
        if ($disabled) {
            echo " disabled";
        }
        if ($live_search == 1) {
            echo ' class="form-control selectpicker" data-live-search="true"';
        } else {
            echo ' class="btn"';
        }
        echo '>';
        echo '<option value="">' . WebLang::$t['select'] . '</option>';

        foreach ($object as $key => $item) {
            echo '<option value="' . $item['id'] . '"';

            if ($item['id'] == $default_value) {
                echo ' selected';
            }
            if (isset($item['active'])) {
                if ($item['active'] == 0) {
                    echo " disabled";
                }
            }
            echo '>' . $item['itemname'] . '</option>';
        }
        echo '</select>';
        echo "</div>";
    }

    public static function genSelectCityAjax($label, $element_id, $element_name, $object, $default_value, $required = 0, $disabled = 0) {
        echo '<p class="f-500 m-b-15 c-black">' . $label . '</p>';
        echo '            <select id="' . $element_id . '" name="' . $element_name . '"';
        echo ' style="width: 100%" ';

        if ($required) {
            echo " required";
        }
        if ($disabled) {
            echo " disabled";
        }
        echo ' class="selectpicker" data-live-search="true"';
        echo " data-url='/plan/ajax_search_city.php'";
        echo '>';
        echo '<option value="0">' . WebLang::$t['select'] . '</option>';

        foreach ($object as $key => $item) {
            echo '<option value="' . $item['id'] . '"';

            if ($item['id'] == $default_value) {
                echo ' selected';
            }
            echo '>' . $item['itemname'] . '</option>';
        }
        echo '</select>';
    }

    /**
     * @param $label
     * @param $element_id
     * @param $element_name
     * @param $object
     * @param $default_value
     * @param int $required
     * @param int $disabled
     * @param int $live_search
     * @return string
     */
    public static function genMultiSelectField($label, $element_id, $element_name, $object, $default_value) {

        $returnStr = "";
        $returnStr .= '<p class="f-500 c-black m-b-15">' . $label . "</p>";
        $returnStr .= '<select id="' . $element_id . '" name="' . $element_name . '[]" class="select2 form-control" multiple';
        $returnStr .= '>';

        foreach ((array) $object as $key => $item) {
            $returnStr .= '<option value="' . $item['id'] . '"';

            if (is_array($default_value)) {
                foreach ($default_value as $default) {
                    if ($item['id'] == $default) {
                        $returnStr .= ' selected';
                    }
                }
            } else {
                if ($item['id'] == $default_value) {
                    $returnStr .= ' selected';
                }
            }
            $returnStr .= '>' . $item['itemname'] . '</option>';
        }
        $returnStr .= '</select>';

        return $returnStr;
    }

    public static function genSelectFieldWithAddon($object, $params, $echo = true) {
        $returnStr = "";

        // assume $object is always [0]=id, [1]=itemname
        $returnStr .= '<div class="form-inline">';
        $returnStr .= '<div class="form-group">';

        if (isset($params['label'])) {
            $label = $params['label'];
        } else {
            $label = "";
        }
        if (isset($params['fieldname'])) {
            $fieldname = $params['fieldname'];
        } else {
            $fieldname = "";
        }
        if (isset($params['default'])) {
            $default = $params['default'];
        } else {
            $default = "";
        }

        $returnStr .= '<div class="input-group">';
        $returnStr .= '<select id="' . $fieldname . '" name="' . $fieldname . '" class="form-control">';
        $returnStr .= '<option value="">' . WebLang::t($label) . '</option>';

        foreach ($object as $k => $item) {

            if (is_array($item)) {
                foreach ($item as $key => $value) {
                    $returnStr .= '<option value="' . $key . '"';

                    if ($value == $default) {
                        $returnStr .= ' selected';
                    }
                    $returnStr .= '>' . WebLang::t($value) . '</option>';
                }
            } else {
                $returnStr .= '<option value="' . $k . '"';
//var_dump($item);
                if ($k == $default) {
                    $returnStr .= ' selected';
                }
                $returnStr .= '>' . WebLang::t($item) . '</option>';
            }
        }
        $returnStr .= '</select>';

        /* inline element if any */
        if (isset($params['addon'])) {
            $returnStr .= "<div class='input-group-addon'>";
            $returnStr .= $params['addon'];
            $returnStr .= "</div>";
        }
        $returnStr .= '</div>';
        $returnStr .= '</div>';
        $returnStr .= '</div>';

        if ($echo) {
            echo $returnStr;
        } else {
            return $returnStr;
        }
    }

    public static function genSelectFieldStatic($label, $element_id, $element_name, $object, $default_value, $required = 0, $disabled = 0, $readonly = 0, $onChange = "") {
        if (!empty($label)) {
            echo '    <label class="control-label" for="' . $element_id . '">' . $label;
            if ($required) {
                echo " <span class='text-danger'>*</span>";
            }
            echo '</label> ';
        }
        echo '<select id="' . $element_id . '" name="' . $element_name . '" class="form-control-static"';

        if ($required) {
            echo " required";
        }
        if ($disabled) {
            echo " disabled";
        }
        if ($readonly) {
            echo " readonly";
        }
        if (!empty($onChange)) {
            echo " onChange=\"" . $onChange . "\"";
        }
        echo '>';
        echo '                <option value="">' . WebLang::$t['select'] . '</option>';

        foreach ((array) $object as $key => $item) {
            echo '<option value="' . $item['id'] . '"';

            if ($item['id'] == $default_value) {
                echo ' selected';
            }
            echo '>' . $item['itemname'] . '</option>';
        }
        echo '</select>';
    }

    public static function genYearSelect($label, $element_id, $element_name, $default_value, $required = 0, $disabled = 0, $readonly = 0, $onChange = "") {
        global $t_select;

        // assume $object is always [0]=id, [1]=itemname
        echo '<div class="form-group fg-line">';

        if (!empty($label)) {
            echo '    <label class="control-label" for="' . $element_id . '">' . $label;
            if ($required) {
                echo " <span class='text-danger'>*</span>";
            }
            echo '</label>';
        }
        echo '        <div class="select">';
        echo '            <select id="' . $element_id . '" name="' . $element_name . '" class="form-control"';

        if ($required) {
            echo " required";
        }
        if ($disabled) {
            echo " disabled";
        }
        if ($readonly) {
            echo " readonly";
        }
        if (!empty($onChange)) {
            echo " onChange=\"" . $onChange . "\"";
        }
        echo '>';
        echo '<option value="">' . $t_select . '</option>';

        $year = date("Y");
        $object = [];

        for ($i = 0; $i < 25; $i++) {
            $object[] = [
                'id' => $year - $i,
                'itemname' => $year - $i
            ];
        }

        foreach ($object as $key => $item) {
            echo '<option value="' . $item['id'] . '"';

            if ($item['id'] == $default_value) {
                echo ' selected';
            }
            echo '>' . $item['itemname'] . '</option>';
        }
        echo '</select>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * 
     * @param type $label
     * @param type $element_id
     * @param type $element_name
     * @param type $value
     * @param type $checked
     * @param type $disabled
     */
    public static function genCheckboxCustomValue($label, $element_id, $element_name, $value, $checked, $disabled = 0, $onClick = null, $class = null) {

        echo '<div class="checkbox m-b-0">';
        echo "<label class='control-label'>";
        echo "<input type='checkbox'";

        if ($element_id)
            echo " id='$element_id'";

        if ($element_name)
            echo " name='$element_name'";

        if ($value)
            echo " value='$value'";

        if ($checked) {
            echo " checked";
        }
        if ($disabled) {
            echo " disabled";
        }
        if ($onClick) {
            echo " onclick='$onClick';";
        }
        if ($class) {
            echo " class='$class'";
        }
        echo ">";
        echo " <i class='input-helper'></i>";
        echo $label;
        echo "</label>";
        echo "</div>";
    }

    /**
     * 
     * @param type $label local_id of WebLang
     * @param type $element_id
     * @param type $element_name
     * @param type $value
     * @param type $checked default is 0
     * @param type $disabled default is 0
     */
    public static function checkbox($label, $element_id, $element_name, $value, $checked = 0, $disabled = 0, $marginBottom = 39) {
        ?>
        <div class="form-group">
            <div class='m-r-15' style='display: inline-block;'>
                <div class="checkbox" style="margin-top: 0px; margin-bottom: <?= $marginBottom ?>px;">
                    <label class='control-label'>
                        <input type='checkbox'

                               <?php
                               if ($element_id)
                                   echo " id='$element_id'";

                               if ($element_name)
                                   echo " name='$element_name'";

                               if ($value) {
                                   echo " value='$value'";
                               }
                               if ($checked) {
                                   echo " checked";
                               }
                               if ($disabled) {
                                   echo " disabled";
                               }
                               ?>
                               >
                        <i class='input-helper'></i>
                        <?= WebLang::t($label) ?>
                    </label>
                </div>
            </div>
        </div>
        <?php
    }

    public static function genStaticInputText($label, $element_id, $element_name, $default_value, $required = 0, $disabled = 0, $readonly = 0, $type = "text", $onChangeEvent = null) {

        $validateClass = "";

        if ($type == "digits") {
            $validateClass .= " digits";
        }
        if ($type == "number") {
            $validateClass .= " number";
        }

        echo "<div class='form-group fg-line' for='$element_id'>";
        echo "<label class='control-label' for='$element_id'>" . $label;
        if ($required) {
            echo ' <span class="text-danger">*</span>';
        }
        echo "</label>";
        echo "<input type='text' class='form-control $validateClass' ";
        echo 'id="' . $element_id . '" name="' . $element_name . '" value="' . $default_value . '"';
        if ($required) {
            echo " required";
        }
        if ($disabled) {
            echo " disabled";
        }
        if ($readonly) {
            echo " readonly";
        }
        if ($onChangeEvent) {
            echo " onchange=\"$onChangeEvent\"";
        }
        echo ">";
        echo "</div>";
    }

    public static function genSelect2Field($label, $element_id, $element_name, $object, $default_value, $onChange = null) {
        $returnStr =  '<div class="m-b-15">';
        $returnStr .= '<p class="f-500 c-black m-b-15">' . $label . "</p>";
        $returnStr .= '<select id="' . $element_id . '" name="' . $element_name . '" class="select2 form-control"';
        
        if ($onChange) {
            $returnStr .= " onChange='$onChange'";
        }
        $returnStr .= '>';
        $returnStr .= "<option></option>";

        foreach ($object as $key => $value) {
            $returnStr .= '<option value="' . $key . '"';

            if (is_array($default_value)) {
                foreach ($default_value as $default) {
                    if ($key == $default) {
                        $returnStr .= ' selected';
                    }
                }
            } else {
                if ($key == $default_value) {
                    $returnStr .= ' selected';
                }
            }
            $returnStr .= '>' . $value . '</option>';
        }
        $returnStr .= '</select></div>';

        return $returnStr;
    }

    public static function genDatePickerOldFashion($label, $element_id, $element_name, $default_value, $required = 0, $disabled = 0, $readonly = 0) {
        echo '<div class = "form-group">';
        echo "<label class='control-label' for = '$element_id'>$label";
        if ($required) {
            echo " <span class='text-danger'>*</span>";
        }
        echo "</label>";
        echo '<div class="dtp-container fg-line">';
        echo "<input type='text' class='form-control date-picker' autocomplete='false' ";
        echo "id='$element_id' name='$element_name' value='$default_value'";
        if ($required) {
            echo " required";
        }
        if ($disabled) {
            echo " disabled";
        }
        if ($readonly) {
            echo " readonly";
        }
        echo ">";
        echo "</div></div>";
    }

    public static function checkboxWithHidden($label, $element_id, $element_name, $value, $checked = 0, $disabled = 0, $marginBottom = 39) {
        ?>
        <input type="hidden" name="<?= $element_name ?>" value="0">
        <div class="form-group">
            <div class='m-r-15' style='display: inline-block;'>
                <div class="checkbox" style="margin-top: 0px; margin-bottom: <?= $marginBottom ?>px;">
                    <label class='control-label'>
                        <input type='checkbox'

                               <?php
                               if ($element_id)
                                   echo " id='$element_id'";

                               if ($element_name)
                                   echo " name='$element_name'";

                               if ($value) {
                                   echo " value='$value'";
                               }
                               if ($checked) {
                                   echo " checked";
                               }
                               if ($disabled) {
                                   echo " disabled";
                               }
                               ?>
                               >
                        <i class='input-helper'></i>
                        <?= WebLang::t($label) ?>
                    </label>
                </div>
            </div>
        </div>
        <?php
    }

}
