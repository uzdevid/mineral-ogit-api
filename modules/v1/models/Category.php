<?php

namespace app\modules\v1\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property string $title_system
 * @property string|null $title_ru
 * @property string|null $title_uz
 * @property string|null $title_oz
 * @property string $url
 * @property string $create_time
 * @property string $update_time
 * @property int|null $status
 * @property int|null $product_count
 * @property int|null $views_count
 * @property int|null $product_views_count
 * @property string|null $image
 * @property int|null $sort
 * @property string|null $seo_title_ru
 * @property string|null $seo_title_uz
 * @property string|null $seo_title_oz
 * @property string|null $seo_description_ru
 * @property string|null $seo_description_uz
 * @property string|null $seo_description_oz
 * @property string|null $seo_keywords_ru
 * @property string|null $seo_keywords_uz
 * @property string|null $seo_keywords_oz
 * @property int|null $category_id
 * @property int $user_id
 * @property int|null $type
 *
 * @property Category[] $categories
 * @property Category $category
 * @property Product[] $products
 * @property Product[] $allProducts
 * @property User $user
 *
 * @property string|null $translatedTitle
 * @property string|null $imageLink
 */
class Category extends ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['title_system', 'url', 'create_time', 'update_time', 'user_id'], 'required'],
            [['create_time', 'update_time'], 'safe'],
            [['status', 'product_count', 'views_count', 'product_views_count', 'sort', 'category_id', 'user_id', 'type'], 'integer'],
            [['title_system', 'title_ru', 'title_uz', 'title_oz', 'url', 'seo_title_ru', 'seo_title_uz', 'seo_title_oz', 'seo_description_ru', 'seo_description_uz', 'seo_description_oz', 'seo_keywords_ru', 'seo_keywords_uz', 'seo_keywords_oz'], 'string', 'max' => 255],
            [['image'], 'string', 'max' => 18],
            [['title_system'], 'unique'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'title_system' => Yii::t('app', 'Title System'),
            'title_ru' => Yii::t('app', 'Title Ru'),
            'title_uz' => Yii::t('app', 'Title Uz'),
            'title_oz' => Yii::t('app', 'Title Oz'),
            'url' => Yii::t('app', 'Url'),
            'create_time' => Yii::t('app', 'Create Time'),
            'update_time' => Yii::t('app', 'Update Time'),
            'status' => Yii::t('app', 'Status'),
            'product_count' => Yii::t('app', 'Product Count'),
            'views_count' => Yii::t('app', 'Views Count'),
            'product_views_count' => Yii::t('app', 'Product Views Count'),
            'image' => Yii::t('app', 'Image'),
            'sort' => Yii::t('app', 'Sort'),
            'seo_title_ru' => Yii::t('app', 'Seo Title Ru'),
            'seo_title_uz' => Yii::t('app', 'Seo Title Uz'),
            'seo_title_oz' => Yii::t('app', 'Seo Title Oz'),
            'seo_description_ru' => Yii::t('app', 'Seo Description Ru'),
            'seo_description_uz' => Yii::t('app', 'Seo Description Uz'),
            'seo_description_oz' => Yii::t('app', 'Seo Description Oz'),
            'seo_keywords_ru' => Yii::t('app', 'Seo Keywords Ru'),
            'seo_keywords_uz' => Yii::t('app', 'Seo Keywords Uz'),
            'seo_keywords_oz' => Yii::t('app', 'Seo Keywords Oz'),
            'category_id' => Yii::t('app', 'Category ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'type' => Yii::t('app', 'Type'),
        ];
    }

    /**
     * Gets query for [[Categories]].
     *
     * @return ActiveQuery
     */
    public function getCategories() {
        return $this->hasMany(Category::class, ['category_id' => 'id']);
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
     * Gets query for [[Products]].
     *
     * @return ActiveQuery
     */
    public function getProducts() {
        return $this->hasMany(Product::class, ['category_id' => 'id']);
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
                return $this->title_oz;
            case 'ru':
                return $this->title_ru;
            default:
                return $this->title_uz;
        }
    }

    public function getImageLink() {
        return $this->image ? "/web/upload_files/category/{$this->image}" : null;
    }

    public function getAllProducts() {
        $models = $this->products;
        foreach ($this->categories as $category) {
            $models[] = $category->products;
        }
        return $models;
    }
}
