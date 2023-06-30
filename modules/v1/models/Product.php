<?php

namespace app\modules\v1\models;

use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property string $title
 * @property int $status
 * @property string $create_time
 * @property string $update_time
 * @property string|null $image1
 * @property string|null $image2
 * @property string|null $image3
 * @property string|null $image4
 * @property int $category_id
 * @property int|null $price_1 ton
 * @property int|null $price_2 bag
 * @property int|null $price_3 kg
 * @property string|null $description
 * @property int|null $available_1 ton
 * @property int|null $available_2 bag
 * @property int|null $available_3 kg
 * @property int|null $delivery_type -1 0 1
 * @property int|null $location_type_1 0 1 in_shop
 * @property int|null $location_type_2 0 1 at_factory
 * @property int|null $location_type_3 0 1 at_stock
 * @property int|null $production_date_month
 * @property string|null $production_date_year
 * @property int|null $payment_type_1 cash
 * @property int|null $payment_type_2 transfer
 * @property int|null $payment_type_3 card
 * @property int|null $vat -1 0 1
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int|null $views_count
 * @property int $user_id
 * @property int|null $contact_type
 * @property int $region_id
 * @property int|null $type
 *
 * @property string $translatedTitle
 * @property UploadedFile[] $imageFiles
 *
 * @property Category $category
 * @property Region $region
 * @property User $user
 *
 * @property array $attributeNames
 */
class Product extends BaseModel {
    const TYPE_0 = 0;
    const TYPE_1 = 1;

    public $imageFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        if ($this->type === self::TYPE_0) {
            return [
                [['category_id', 'user_id', 'region_id', 'price_1', 'price_2', 'available_1', 'available_2', 'production_date_month'], 'required'],
                [['status', 'category_id', 'delivery_type', 'location_type_1', 'location_type_2', 'location_type_3', 'production_date_month', 'payment_type_1', 'payment_type_2', 'payment_type_3', 'vat', 'views_count', 'user_id', 'contact_type', 'region_id', 'type'], 'integer'],
                [['price_1', 'price_2', 'price_3', 'available_1', 'available_2', 'available_3',], 'number'],
                [['create_time', 'update_time', 'production_date_year'], 'safe'],
                [['description'], 'string'],
                [['latitude', 'longitude'], 'number'],
                [['title', 'image1', 'image2', 'image3', 'image4'], 'string', 'max' => 255],
                [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
                [['region_id'], 'exist', 'skipOnError' => true, 'targetClass' => Region::class, 'targetAttribute' => ['region_id' => 'id']],
                [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
                //
                [['location_type_1', 'location_type_2', 'location_type_3', 'payment_type_1', 'payment_type_2', 'payment_type_3', 'vat', 'contact_type'], 'in', 'range' => [0, 1]],
                ['production_date_month', 'in', 'range' => range(1, 12)],
                ['latitude', 'match', 'pattern' => '/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/'],
                ['longitude', 'match', 'pattern' => '/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/'],
                // Upload photos
                [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'maxSize' => 1024 * 1024 * 6, 'on' => 'upload-photos'],
            ];
        } elseif ($this->type === self::TYPE_1) {
            return [
                [['title', 'category_id', 'user_id', 'region_id', 'price_2', 'price_3', 'available_2', 'available_3', 'production_date_month'], 'required'],
                [['status', 'category_id', 'delivery_type', 'location_type_1', 'location_type_2', 'location_type_3', 'production_date_month', 'payment_type_1', 'payment_type_2', 'payment_type_3', 'vat', 'views_count', 'user_id', 'contact_type', 'region_id', 'type'], 'integer'],
                [['price_1', 'price_2', 'price_3', 'available_1', 'available_2', 'available_3',], 'number'],
                [['create_time', 'update_time', 'production_date_year'], 'safe'],
                [['description'], 'string'],
                [['latitude', 'longitude'], 'number'],
                [['title', 'image1', 'image2', 'image3', 'image4'], 'string', 'max' => 255],
                [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
                [['region_id'], 'exist', 'skipOnError' => true, 'targetClass' => Region::class, 'targetAttribute' => ['region_id' => 'id']],
                [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
                //
                [['location_type_1', 'location_type_2', 'location_type_3', 'payment_type_1', 'payment_type_2', 'payment_type_3', 'vat', 'contact_type'], 'in', 'range' => [0, 1]],
                ['production_date_month', 'in', 'range' => range(1, 12)],
                ['latitude', 'match', 'pattern' => '/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/'],
                ['longitude', 'match', 'pattern' => '/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/'],
                // Upload photos
                [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'maxSize' => 1024 * 1024 * 6, 'on' => 'upload-photos'],
            ];
        }

        return [
            ['type', 'in', 'range' => [0, 1]]
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
            'image1' => Yii::t('app', 'Image1'),
            'image2' => Yii::t('app', 'Image2'),
            'image3' => Yii::t('app', 'Image3'),
            'image4' => Yii::t('app', 'Image4'),
            'category_id' => Yii::t('app', 'Category ID'),
            'price_1' => Yii::t('app', 'Price Ton'),
            'price_2' => Yii::t('app', 'Price Bag'),
            'price_3' => Yii::t('app', 'Price Kg'),
            'description' => Yii::t('app', 'Description'),
            'available_1' => Yii::t('app', 'Available Ton'),
            'available_2' => Yii::t('app', 'Available Bag'),
            'available_3' => Yii::t('app', 'Available Kg'),
            'delivery_type' => Yii::t('app', 'Delivery Type'),
            'location_type_1' => Yii::t('app', 'Location Type In Shop'),
            'location_type_2' => Yii::t('app', 'Location Type At Factory'),
            'location_type_3' => Yii::t('app', 'Location Type At Stock'),
            'production_date_month' => Yii::t('app', 'Production Date Month'),
            'production_date_year' => Yii::t('app', 'Production Date Year'),
            'payment_type_1' => Yii::t('app', 'Payment Type Cash'),
            'payment_type_2' => Yii::t('app', 'Payment Type Transfer'),
            'payment_type_3' => Yii::t('app', 'Payment Type Card'),
            'vat' => Yii::t('app', 'Vat'),
            'latitude' => Yii::t('app', 'Latitude'),
            'longitude' => Yii::t('app', 'Longitude'),
            'views_count' => Yii::t('app', 'Views Count'),
            'user_id' => Yii::t('app', 'User ID'),
            'contact_type' => Yii::t('app', 'Contact Type'),
            'region_id' => Yii::t('app', 'Region ID'),
            'type' => Yii::t('app', 'Type'),
        ];
    }

    /**
     * Gets query for [[Category]].
     *
     * @return ActiveQuery
     */
    public function getCategory() {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[Region]].
     *
     * @return ActiveQuery
     */
    public function getRegion() {
        return $this->hasOne(Region::class, ['id' => 'region_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getAttributeNames() {
        return ['title', 'description'];
    }

    public function beforeSave($insert) {
        if ($this->type == self::TYPE_0) {
            $this->title = $this->category->translatedTitle;
        }
        if ($insert) {
            $this->create_time = $this->update_time = date('Y-m-d H:i:s');
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

    public function upload($index) {
        $this->scenario = 'upload-photos';
        if (!$this->validate())
            return false;

        $this->uploadImage($index, $this->imageFile);

        return true;
    }

    public function uploadImage($index, $file) {
        $basename = uniqid();
        $filename = "{$basename}.{$file->extension}";
        $file->saveAs("upload_files/product/{$filename}");

        switch ($index) {
            case 0:
                $this->image1 = $filename;
                $this->deleteOldImage($index);
                break;
            case 1:
                $this->image2 = $filename;
                $this->deleteOldImage($index);
                break;
            case 2:
                $this->image3 = $filename;
                $this->deleteOldImage($index);
                break;
            case 3:
                $this->image4 = $filename;
                $this->deleteOldImage($index);
                break;
        }

        $this->update(false);
    }

    public function getImages() {
        $images = [];
        if ($this->image1 != null)
            $images[] = [
                'attribute' => 'image_1',
                'link' => Yii::$app->params['url'] . "/upload_files/product/{$this->image1}",
            ];
        if ($this->image2 != null)
            $images[] = [
                'attribute' => 'image_2',
                'link' => Yii::$app->params['url'] . "/upload_files/product/{$this->image2}",
            ];
        if ($this->image3 != null)
            $images[] = [
                'attribute' => 'image_3',
                'link' => Yii::$app->params['url'] . "/upload_files/product/{$this->image3}",
            ];
        if ($this->image4 != null)
            $images[] = [
                'attribute' => 'image_4',
                'link' => Yii::$app->params['url'] . "/upload_files/product/{$this->image4}",
            ];

        return $images;
    }

    public function deleteOldImage($index) {
        switch ($index) {
            case 0:
                @unlink("upload_files/product/{$this->oldAttributes['image1']}");
                break;
            case 1:
                @unlink("upload_files/product/{$this->oldAttributes['image2']}");
                break;
            case 2:
                @unlink("upload_files/product/{$this->oldAttributes['image3']}");
                break;
            case 3:
                @unlink("upload_files/product/{$this->oldAttributes['image4']}");
                break;
        }
    }

    public function deleteImage($index) {
        switch ($index) {
            case 0:
                return @unlink("upload_files/product/{$this->image1}");
            case 1:
                return @unlink("upload_files/product/{$this->image2}");
            case 2:
                return @unlink("upload_files/product/{$this->image3}");
            case 3:
                return @unlink("upload_files/product/{$this->image4}");
        }

        return false;
    }

    public function getTranslatedTitle() {
        return $this->type == Product::TYPE_0 ? $this->category->translatedTitle : $this->title;
    }

    public function beforeDelete() {
        $models = array_merge(
            Favourite::find()->where(['product_id' => $this->id])->all(),
            Exchange::find()->where(['product_id' => $this->id])->all()
        );

        foreach ($models as $model) {
            $model->delete();
        }

        if ($this->image1 != null)
            $this->deleteImage(0);
        if ($this->image2 != null)
            $this->deleteImage(1);
        if ($this->image3 != null)
            $this->deleteImage(2);
        if ($this->image4 != null)
            $this->deleteImage(3);

        return parent::beforeDelete();
    }
}
