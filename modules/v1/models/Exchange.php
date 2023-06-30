<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "exchange".
 *
 * @property int $id
 * @property int $exchange_product_id
 * @property int $region_id
 * @property float $unit_value
 * @property string $unit
 * @property int $price_min
 * @property int|null $price_max
 * @property string $currency
 * @property int $status
 * @property string $publish_time
 * @property string $create_time
 * @property string $update_time
 *
 * @property ExchangeProduct $exchangeProduct
 * @property Region $region
 */
class Exchange extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'exchange';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['exchange_product_id', 'region_id', 'unit_value', 'unit', 'price_min', 'publish_time', 'create_time', 'update_time'], 'required'],
            [['exchange_product_id', 'region_id', 'price_min', 'price_max', 'status'], 'integer'],
            [['unit_value'], 'number'],
            [['publish_time', 'create_time', 'update_time'], 'safe'],
            [['unit'], 'string', 'max' => 45],
            [['currency'], 'string', 'max' => 3],
            [['exchange_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExchangeProduct::class, 'targetAttribute' => ['exchange_product_id' => 'id']],
            [['region_id'], 'exist', 'skipOnError' => true, 'targetClass' => Region::class, 'targetAttribute' => ['region_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'exchange_product_id' => Yii::t('app', 'Exchange Product ID'),
            'region_id' => Yii::t('app', 'Region ID'),
            'unit_value' => Yii::t('app', 'Unit Value'),
            'unit' => Yii::t('app', 'Unit'),
            'price_min' => Yii::t('app', 'Price Min'),
            'price_max' => Yii::t('app', 'Price Max'),
            'currency' => Yii::t('app', 'Currency'),
            'status' => Yii::t('app', 'Status'),
            'publish_time' => Yii::t('app', 'Publish Time'),
            'create_time' => Yii::t('app', 'Create Time'),
            'update_time' => Yii::t('app', 'Update Time'),
        ];
    }

    /**
     * Gets query for [[ExchangeProduct]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExchangeProduct() {
        return $this->hasOne(ExchangeProduct::class, ['id' => 'exchange_product_id']);
    }

    /**
     * Gets query for [[Region]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRegion() {
        return $this->hasOne(Region::class, ['id' => 'region_id']);
    }
}
