<?php

namespace app\modules\v1\models;

use yii\db\ActiveRecord;

/**
 * This is the base model class for tables model.
 *
 * @property string|null $firstErrorsString
 * @property string|null $errors
 *
 */
class BaseModel extends ActiveRecord {
    public function getFirstErrorsString() {
        $errors = parent::getFirstErrors();
        $message = '';
        foreach ($errors as $error) {
            $message .= $error . ' | ';
        }
        return trim($message, ' | ');
    }

    public function getErrors($attribute = null) {
        $errors = parent::getErrors($attribute);

        $list = [];
        foreach ($errors as $attribute => $error) {
            $list[] = [
                'attribute' => $attribute,
                'messages' => $error,
            ];
        }

        return $list;
    }
}