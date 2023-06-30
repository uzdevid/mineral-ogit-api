<?php

namespace app\modules\v1\models;

use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Html;

/**
 * This is the model class for table "region".
 *
 * @property int $id
 * @property string $title_ru
 * @property string|null $title_uz
 * @property string|null $title_oz
 * @property string $create_time
 * @property string $update_time
 * @property int $user_id
 *
 * @property int $translatedTitle
 *
 * @property Product[] $products
 * @property User $user
 */
class Region extends BaseModel {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'region';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['title_ru', 'create_time', 'update_time', 'user_id'], 'required'],
            [['create_time', 'update_time'], 'safe'],
            [['user_id'], 'integer'],
            [['title_ru', 'title_uz', 'title_oz'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'title_ru' => Yii::t('app', 'Title Ru'),
            'title_uz' => Yii::t('app', 'Title Uz'),
            'title_oz' => Yii::t('app', 'Title Oz'),
            'create_time' => Yii::t('app', 'Create Time'),
            'update_time' => Yii::t('app', 'Update Time'),
            'user_id' => Yii::t('app', 'User ID'),
        ];
    }

    /**
     * Gets query for [[Products]].
     *
     * @return ActiveQuery
     */
    public function getProducts() {
        return $this->hasMany(Product::class, ['region_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getTranslatedTitle() {
        switch (Yii::$app->language) {
            case 'oz':
                return Html::decode($this->title_oz);
            case 'ru':
                return Html::decode($this->title_ru);
            default:
                return Html::decode($this->title_uz);
        }
    }
}
