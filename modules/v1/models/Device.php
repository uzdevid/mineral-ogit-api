<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "device".
 *
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property string $device_id
 * @property string|null $fcm_token
 * @property string $access_token
 * @property string $authorization_time
 * @property string $create_time
 * @property string $update_time
 *
 * @property User $user
 */
class Device extends BaseModel {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'device';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name', 'user_id', 'device_id'], 'required'],
            [['user_id'], 'integer'],
            [['authorization_time', 'create_time', 'update_time'], 'safe'],
            [['name', 'device_id', 'fcm_token'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'user_id' => Yii::t('app', 'User ID'),
            'access_token' => Yii::t('app', 'Access Token'),
            'device_id' => Yii::t('app', 'Device ID'),
            'fcm_token' => Yii::t('app', 'Fcm Token'),
            'authorization_time' => Yii::t('app', 'Authorization Time'),
            'create_time' => Yii::t('app', 'Create Time'),
            'update_time' => Yii::t('app', 'Update Time'),
        ];
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
            $this->access_token = Yii::$app->security->generateRandomString();
            $this->create_time = $this->update_time = $this->authorization_time = date('Y-m-d H:i:s');
        } else {
            $this->access_token = Yii::$app->security->generateRandomString();
            $this->update_time = date('Y-m-d H:i:s');
        }
        return parent::beforeSave($insert);
    }
}
