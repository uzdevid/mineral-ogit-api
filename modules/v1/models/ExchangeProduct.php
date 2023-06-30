<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "exchange_product".
 *
 * @property int $id
 * @property string $title
 * @property int $status
 * @property string $create_time
 * @property string $update_time
 * @property int $user_id
 *
 * @property Exchange[] $exchanges
 * @property User $user
 */
class ExchangeProduct extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'exchange_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['title', 'create_time', 'update_time', 'user_id'], 'required'],
            [['status', 'user_id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'status' => Yii::t('app', 'Status'),
            'create_time' => Yii::t('app', 'Create Time'),
            'update_time' => Yii::t('app', 'Update Time'),
            'user_id' => Yii::t('app', 'User ID'),
        ];
    }

    /**
     * Gets query for [[Exchanges]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExchanges() {
        return $this->hasMany(Exchange::class, ['exchange_product_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
