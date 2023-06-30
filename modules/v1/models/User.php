<?php

namespace app\modules\v1\models;

use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Html;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $phone
 * @property string $password
 * @property string|null $name
 * @property string|null $surname
 * @property string $lang
 * @property string $role
 * @property int $status
 * @property string $create_time
 * @property string $update_time
 * @property string $authorization_time
 * @property string|null $recover_password
 * @property string|null $image
 * @property string|null $organization_image
 * @property string|null $organization_name
 * @property string|null $organization_phone_number
 * @property string|null $inn
 * @property string|null $mfo
 * @property string|null $bank_info
 * @property string|null $organization_about
 *
 * @property string|null $fullname
 * @property string|null $imageLink
 * @property string|null $organizationImageLink
 * @property array|null $organization
 * @property UploadedFile $file
 *
 * @property Category[] $categories
 * @property Product[] $products
 * @property Favourite[] $favourites
 * @property Region[] $regions
 *
 * @property array $attributeNames
 */
class User extends BaseModel implements IdentityInterface {

    public $file;

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            ['phone', 'required', 'message' => Yii::t('app', 'phone_is_required')],
            ['password', 'required', 'message' => Yii::t('app', 'password_is_required')],
            ['surname', 'required', 'message' => Yii::t('app', 'surname_is_required')],
            ['name', 'required', 'message' => Yii::t('app', 'name_is_required')],
            [['status'], 'integer'],
            [['create_time', 'update_time', 'authorization_time'], 'safe'],
            [['phone'], 'string', 'length' => 9],
            [['password'], 'string', 'max' => 60],
            [['name', 'surname', 'organization_name', 'organization_phone_number', 'organization_about', 'bank_info'], 'string', 'max' => 255],
            [['lang'], 'string', 'max' => 2],
            [['role', 'recover_password', 'inn', 'mfo'], 'string', 'max' => 45],
            [['image', 'organization_image'], 'string', 'max' => 18],
            [['phone'], 'unique', 'message' => Yii::t('app', 'user_with_this_phone_already_exist')],
            // Create Organization
            [['organization_name', 'organization_phone_number', 'organization_about', 'inn', 'mfo', 'bank_info'], 'required', 'on' => 'create-organization'],
            // Upload photo
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'maxSize' => 1024 * 1024 * 2, 'on' => 'upload-photo'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'phone' => Yii::t('app', 'Phone'),
            'password' => Yii::t('app', 'Password'),
            'name' => Yii::t('app', 'Name'),
            'surname' => Yii::t('app', 'Surname'),
            'lang' => Yii::t('app', 'Lang'),
            'role' => Yii::t('app', 'Role'),
            'status' => Yii::t('app', 'Status'),
            'create_time' => Yii::t('app', 'Create Time'),
            'update_time' => Yii::t('app', 'Update Time'),
            'authorization_time' => Yii::t('app', 'Authorization Time'),
            'recover_password' => Yii::t('app', 'Recover Password'),
            'image' => Yii::t('app', 'Image'),
            'organization_image' => Yii::t('app', 'Organization Image'),
            'organization_name' => Yii::t('app', 'Organization Name'),
            'organization_phone_number' => Yii::t('app', 'Organization Phone Number'),
            'inn' => Yii::t('app', 'Inn'),
            'mfo' => Yii::t('app', 'Mfo'),
            'bank_info' => Yii::t('app', 'Bank Info'),
            'organization_about' => Yii::t('app', 'Organization About'),
        ];
    }

    /**
     * Gets query for [[Categories]].
     *
     * @return ActiveQuery
     */
    public function getCategories() {
        return $this->hasMany(Category::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Products]].
     *
     * @return ActiveQuery
     */
    public function getProducts() {
        return $this->hasMany(Product::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Favourites]].
     *
     * @return ActiveQuery
     */
    public function getFavourites() {
        return $this->hasMany(Favourite::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Regions]].
     *
     * @return ActiveQuery
     */
    public function getRegions() {
        return $this->hasMany(Region::class, ['user_id' => 'id']);
    }

    public static function findIdentity($id) {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null) {
        $device = Device::findOne(['access_token' => $token]);
        return @$device->user;
    }

    public function getId() {
        return $this->id;
    }

    public function getAuthKey() {
        return null;
    }

    public function validateAuthKey($authKey) {
        return false;
    }

    public function validatePassword($password) {
        return password_verify($password, $this->password);
    }

    public function getAttributeNames() {
        return ['name', 'surname', 'organization_name', 'inn', 'mfo', 'bank_info', 'organization_about'];
    }

    public function beforeSave($insert) {
        if ($insert) {
            $this->status = 0;
            $this->recover_password = rand(1000, 9999);
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
            $this->create_time = $this->update_time = $this->authorization_time = date('Y-m-d H:i:s');

            $this->sendConfirmCode($this->recover_password);
        } else {
            $this->update_time = date('Y-m-d H:i:s');
        }

        foreach ($this->attributeNames as $attribute) {
            $this->$attribute = Html::encode($this->$attribute);
        }

        return parent::beforeSave($insert);
    }

    public function afterFind() {
        foreach ($this->attributeNames as $attribute) {
            $this->$attribute = Html::decode($this->$attribute);
        }

        parent::afterFind();
    }

    public function sendConfirmCode($code, $is_register = true) {
        $curl = curl_init();

        if ($is_register) {
            $text = "Mineralogit.uz - Ro'yhatdan o'tish uchun sms kod: {$code}";
        } else {
            $text = "Mineralogit.uz - Akkauntni tasdiqlash kodi: {$code}";
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'notify.eskiz.uz/api/message/sms/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'mobile_phone' => "998{$this->phone}",
                'message' => $text,
                'from' => Yii::$app->params['eskiz']['from']
            ],
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . Yii::$app->params['eskiz']['auth_token']
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }

    public function getFirstErrorsString() {
        $errors = parent::getFirstErrors();
        $message = '';
        foreach ($errors as $error) {
            $message .= $error . ' | ';
        }
        return trim($message, ' | ');
    }

    public function getFullname() {
        if ($this->surname != null || $this->name != null) {
            return $this->surname . ' ' . $this->name;
        }

        return preg_replace("/(\d{2})(\d{3})(\d{4})/m", "+998 ($1) $2 $3", $this->phone);
    }

    public function getImageLink() {
        return $this->image ? Yii::$app->params['url'] . "/upload_files/user/{$this->image}" : null;
    }

    public function getOrganizationImageLink() {
        return $this->organization_image ? Yii::$app->params['url'] . "/upload_files/organization/{$this->organization_image}" : null;
    }

    public function uploadPhoto($attribute = 'user') {
        $this->scenario = 'upload-photo';
        if (!$this->validate())
            return false;

        $basename = uniqid();
        $filename = "{$basename}.{$this->file->extension}";

        if ($attribute == 'user') {
            $this->file->saveAs("upload_files/user/{$filename}");
            @unlink("upload_files/user/{$this->oldAttributes['image']}");
            $this->image = $filename;
        } elseif ($attribute == 'organization') {
            $this->file->saveAs("upload_files/organization/{$filename}");
            @unlink("upload_files/organization/{$this->oldAttributes['organization_image']}");
            $this->organization_image = $filename;
        }

        $this->save(false);
        return true;
    }

    public function deleteProfileImage() {
        return @unlink("upload_files/user/{$this->oldAttributes['image']}");
    }

    public function deleteOrganizationImage() {
        return @unlink("upload_files/organization/{$this->oldAttributes['image']}");
    }

    public function getOrganization() {
        if ($this->organization_name != null) {
            return [
                'image' => $this->organizationImageLink,
                'name' => $this->organization_name,
                'phone' => $this->organization_phone_number,
                'about' => $this->organization_about,
                'inn' => $this->inn,
                'mfo' => $this->mfo,
                'bank_info' => $this->bank_info,
                'create_time' => $this->create_time
            ];
        }

        return null;
    }

    public function beforeDelete() {
        $models = array_merge(
            Device::find()->where(['user_id' => $this->id])->all(),
            Favourite::find()->where(['user_id' => $this->id])->all(),
            Product::find()->where(['user_id' => $this->id])->all()
        );

        foreach ($models as $model) {
            $model->delete();
        }

        $this->deleteProfileImage();
        $this->deleteOrganizationImage();

        return parent::beforeDelete();
    }
}
