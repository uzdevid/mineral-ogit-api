<?php

namespace app\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "translation".
 *
 * @property int $id
 * @property string $title
 * @property string|null $translation_ru
 * @property string|null $translation_uz
 * @property string|null $translation_oz
 */
class Translation extends ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'translation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['title'], 'required'],
            [['title', 'translation_ru', 'translation_uz', 'translation_oz'], 'string', 'max' => 255],
            [['title'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'translation_ru' => Yii::t('app', 'Translation Ru'),
            'translation_uz' => Yii::t('app', 'Translation Uz'),
            'translation_oz' => Yii::t('app', 'Translation Oz'),
        ];
    }
}
