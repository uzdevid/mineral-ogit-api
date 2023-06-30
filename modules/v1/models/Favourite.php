<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "favourite".
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property string $create_time
 * @property string $update_time
 *
 * @property Product $product
 * @property User $user
 */
class Favourite extends BaseModel {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'favourite';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['user_id', 'product_id'], 'required'],
            [['user_id', 'product_id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'create_time' => Yii::t('app', 'Create Time'),
            'update_time' => Yii::t('app', 'Update Time'),
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct() {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function beforeSave($insert) {
        if ($insert) {
            $this->create_time = $this->update_time = date('Y-m-d H:i:s');
        } else {
            $this->update_time = date('Y-m-d H:i:s');
        }
        return parent::beforeSave($insert);
    }
}
