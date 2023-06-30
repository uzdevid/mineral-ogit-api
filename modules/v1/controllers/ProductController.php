<?php

namespace app\modules\v1\controllers;

use app\components\Translit;
use app\modules\v1\models\Product;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\helpers\Json;
use yii\rest\Controller;
use yii\web\UploadedFile;

class ProductController extends Controller {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => Cors::className()
        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'view' => ['GET'],
                'create' => ['POST'],
                'update' => ['POST'],
                'upload-photos' => ['POST'],
                'update-price-available' => ['POST'],
                'search' => ['GET'],
                'my-products' => ['GET'],
                'delete' => ['POST'],
            ],
        ];

        $behaviors['bearerAuth'] = [
            'class' => HttpBearerAuth::class,
            'only' => ['create', 'update', 'upload-photos', 'update-price-available', 'my-products', 'delete', 'delete-photo'],
            'except' => ['index', 'view', 'search'],
        ];

        return $behaviors;
    }

    public function actionIndex($category_id, $page = null, $region_id = null) {
        $query = Product::find()
            ->where(['category_id' => (int)$category_id])
            ->andWhere(['status' => 1])
            ->orderBy(['update_time' => SORT_DESC]);

        if($page !== null){
            $limit = Yii::$app->params['limits']['products'];
            $query->limit($limit)->offset(($page - 1) * $limit);
        }

        if ($region_id != null) {
            $query->andWhere(['region_id' => $region_id]);
        }

        /** @var Product[] $models */
        $models = $query->all();

        $products = [];

        foreach ($models as $model) {
            $products[] = [
                'id' => $model->id,
                'title' => $model->translatedTitle,
                'type' => $model->type,
                'seller' => [
                    'id' => $model->user->id,
                    'fullname' => $model->user->fullname,
                    'phone' => $model->user->phone,
                    'organization' => $model->user->organization,
                ],
                'region' => [
                    'id' => $model->region_id,
                    'district' => $model->region->translatedTitle
                ],
                'price' => [
                    'ton' => $model->price_1,
                    'bag' => $model->price_2,
                    'kg' => $model->price_3,
                ],
                'views_count' => (int)$model->views_count,
                'create_time' => $model->create_time,
                'update_time' => $model->update_time
            ];
        }

        return [
            'ok' => true,
            'body' => [
                'products' => $products
            ]
        ];
    }

    public function actionView($id) {
        $model = Product::findOne((int)$id);

        if ($model == null) {
            return ['ok' => false, 'body' => ['message' => Yii::t('app', 'product_not_found')]];
        }

        $product = [
            'id' => $model->id,
            'title' => $model->translatedTitle,
            'description' => $model->description,
            'type' => $model->type,
            'images' => $model->images,
            'category' => [
                'id' => $model->category_id,
                'title' => $model->category->translatedTitle,
                'category' => $model->category->category == null ? null : [
                    'id' => $model->category->category_id,
                    'title' => $model->category->category->translatedTitle,
                ]
            ],
            'seller' => [
                'id' => $model->user->id,
                'fullname' => $model->user->fullname,
                'phone' => $model->user->phone,
                'organization' => $model->user->organization,
            ],
            'region' => [
                'id' => $model->region_id,
                'district' => $model->region->translatedTitle
            ],
            'price' => [
                'ton' => $model->price_1,
                'bag' => $model->price_2,
                'kg' => $model->price_3,
            ],
            'available' => [
                'ton' => $model->available_1,
                'bag' => $model->available_2,
                'kg' => $model->available_3,
            ],
            'delivery' => $model->delivery_type,
            'location' => [
                'in_shop' => $model->location_type_1,
                'at_factory' => $model->location_type_2,
                'at_stock' => $model->location_type_3,
            ],
            'production_date' => [
                'month' => $model->production_date_month,
                'year' => (int)$model->production_date_year,
            ],
            'payment' => [
                'cash' => $model->payment_type_1,
                'transfer' => $model->payment_type_2,
                'card' => $model->payment_type_3,
            ],
            'vat' => $model->vat,
            'contact_type' => $model->contact_type,
            'latitude' => $model->latitude == null ? null : (float)$model->latitude,
            'longitude' => $model->longitude == null ? null : (float)$model->longitude,
            'views_count' => (int)$model->views_count,
            'create_time' => $model->create_time,
            'update_time' => $model->update_time
        ];

        return [
            'ok' => true,
            'body' => [
                'product' => $product
            ]
        ];
    }

    public function actionCreate() {
        $body = Json::decode(Yii::$app->request->rawBody, true);

        if ($body === null) {
            return ['ok' => false, 'message' => Yii::t('app', 'request_body_not_found')];
        }

        $model = new Product();
        $model->user_id = Yii::$app->user->id;
        $model->category_id = (int)@$body['product']['category_id'];
        $model->type = (int)@$body['product']['type'];
        $model->title = @$body['product']['title'];

        $model->price_1 = @$body['product']['price']['ton'];
        $model->price_2 = @$body['product']['price']['bag'];
        $model->price_3 = @$body['product']['price']['kg'];

        $model->description = @$body['product']['description'];

        $model->available_1 = @$body['product']['available']['ton'];
        $model->available_2 = @$body['product']['available']['bag'];
        $model->available_3 = @$body['product']['available']['kg'];

        $model->delivery_type = @$body['product']['delivery'];

        $model->location_type_1 = (int)@$body['product']['location']['in_shop'];
        $model->location_type_2 = (int)@$body['product']['location']['at_factory'];
        $model->location_type_3 = (int)@$body['product']['location']['at_stock'];

        $model->production_date_month = @$body['product']['production_date']['month'];
        $model->production_date_year = @$body['product']['production_date']['year'];

        $model->payment_type_1 = (int)@$body['product']['payment_type']['cash'];
        $model->payment_type_2 = (int)@$body['product']['payment_type']['transfer'];
        $model->payment_type_3 = (int)@$body['product']['payment_type']['card'];

        $model->vat = @$body['product']['vat'];
        $model->latitude = @$body['product']['latitude'];
        $model->longitude = @$body['product']['longitude'];
        $model->contact_type = @$body['product']['contact_type'];
        $model->region_id = @$body['product']['region_id'];

        if ($model->save()) {
            return [
                'ok' => true,
                'body' => [
                    'product' => [
                        'id' => $model->id,
                        'title' => $model->translatedTitle,
                        'description' => $model->description,
                        'type' => $model->type,
                        'images' => $model->images,
                        'category' => [
                            'id' => $model->category_id,
                            'title' => $model->category->translatedTitle,
                            'category' => $model->category->category == null ? null : [
                                'id' => $model->category->category_id,
                                'title' => $model->category->category->translatedTitle,
                            ]
                        ],
                        'seller' => [
                            'id' => $model->user->id,
                            'fullname' => $model->user->fullname,
                            'phone' => $model->user->phone,
                            'organization' => $model->user->organization,
                        ],
                        'region' => [
                            'id' => $model->region_id,
                            'district' => $model->region->translatedTitle
                        ],
                        'price' => [
                            'ton' => $model->price_1,
                            'bag' => $model->price_2,
                            'kg' => $model->price_3,
                        ],
                        'available' => [
                            'ton' => $model->available_1,
                            'bag' => $model->available_2,
                            'kg' => $model->available_3,
                        ],
                        'delivery' => $model->delivery_type,
                        'location' => [
                            'in_shop' => $model->location_type_1,
                            'at_factory' => $model->location_type_2,
                            'at_stock' => $model->location_type_3,
                        ],
                        'production_date' => [
                            'month' => $model->production_date_month,
                            'year' => $model->production_date_year,
                        ],
                        'payment' => [
                            'cash' => $model->payment_type_1,
                            'transfer' => $model->payment_type_2,
                            'card' => $model->payment_type_3,
                        ],
                        'vat' => $model->vat,
                        'latitude' => $model->latitude == null ? null : (float)$model->latitude,
                        'longitude' => $model->longitude == null ? null : (float)$model->longitude,
                        'views_count' => (int)$model->views_count,
                        'create_time' => $model->create_time,
                        'update_time' => $model->update_time
                    ]
                ]
            ];
        }

        return ['ok' => false, 'body' => ['message' => $model->firstErrorsString, 'errors' => $model->errors]];
    }

    public function actionUploadPhotos($product_id) {
        $model = Product::findOne((int)$product_id);

        if ($model === null || $model != null && $model->user_id != Yii::$app->user->id) {
            return ['ok' => false, 'message' => Yii::t('app', 'product_not_found')];
        }

        $model->imageFile = UploadedFile::getInstanceByName('image');

        $attrs = [
            'image_1' => 0,
            'image_2' => 1,
            'image_3' => 2,
            'image_4' => 3,
        ];

        $attr = $_POST['attribute'];

        if (!isset($attrs[$attr])) {
            return ['ok' => false, 'message' => Yii::t('app', 'image_attribute_incorrect')];
        }

        if ($model->upload($attrs[$attr])) {
            return [
                'ok' => true,
                'body' => [
                    'product' => [
                        'images' => $model->images
                    ]
                ]
            ];
        }

        return ['ok' => false, 'body' => ['message' => $model->firstErrorsString, 'errors' => $model->errors]];
    }

    public function actionUpdate($product_id) {
        $body = Json::decode(Yii::$app->request->rawBody, true);

        if ($body === null) {
            return ['ok' => false, 'message' => Yii::t('app', 'request_body_not_found')];
        }

        $model = Product::findOne((int)$product_id);

        if ($model === null || $model != null && $model->user_id != Yii::$app->user->id) {
            return ['ok' => false, 'message' => Yii::t('app', 'product_not_found')];
        }

        $model->category_id = @$body['product']['category_id'];
        $model->type = (int)@$body['product']['type'];
        $model->title = @$body['product']['title'];

        $model->price_1 = @$body['product']['price']['ton'];
        $model->price_2 = @$body['product']['price']['bag'];
        $model->price_3 = @$body['product']['price']['kg'];

        $model->description = @$body['product']['description'];

        $model->available_1 = @$body['product']['available']['ton'];
        $model->available_2 = @$body['product']['available']['bag'];
        $model->available_3 = @$body['product']['available']['kg'];

        $model->delivery_type = @$body['product']['delivery'];

        $model->location_type_1 = (int)@$body['product']['location']['in_shop'];
        $model->location_type_2 = (int)@$body['product']['location']['at_factory'];
        $model->location_type_3 = (int)@$body['product']['location']['at_stock'];

        $model->production_date_month = @$body['product']['production_date']['month'];
        $model->production_date_year = @$body['product']['production_date']['year'];

        $model->payment_type_1 = (int)@$body['product']['payment_type']['cash'];
        $model->payment_type_2 = (int)@$body['product']['payment_type']['transfer'];
        $model->payment_type_3 = (int)@$body['product']['payment_type']['card'];

        $model->vat = @$body['product']['vat'];
        $model->latitude = @$body['product']['latitude'];
        $model->longitude = @$body['product']['longitude'];
        $model->contact_type = @$body['product']['contact_type'];
        $model->region_id = @$body['product']['region_id'];

        if ($model->save()) {
            return [
                'ok' => true,
                'body' => [
                    'product' => [
                        'id' => $model->id,
                        'title' => $model->translatedTitle,
                        'description' => $model->description,
                        'type' => $model->type,
                        'images' => $model->images,
                        'category' => [
                            'id' => $model->category_id,
                            'title' => $model->category->translatedTitle,
                            'category' => $model->category->category == null ? null : [
                                'id' => $model->category->category_id,
                                'title' => $model->category->category->translatedTitle,
                            ]
                        ],
                        'seller' => [
                            'id' => $model->user->id,
                            'fullname' => $model->user->fullname,
                            'phone' => $model->user->phone,
                            'organization' => $model->user->organization,
                        ],
                        'region' => [
                            'id' => $model->region_id,
                            'district' => $model->region->translatedTitle
                        ],
                        'price' => [
                            'ton' => $model->price_1,
                            'bag' => $model->price_2,
                            'kg' => $model->price_3,
                        ],
                        'available' => [
                            'ton' => $model->available_1,
                            'bag' => $model->available_2,
                            'kg' => $model->available_3,
                        ],
                        'delivery' => $model->delivery_type,
                        'location' => [
                            'in_shop' => $model->location_type_1,
                            'at_factory' => $model->location_type_2,
                            'at_stock' => $model->location_type_3,
                        ],
                        'production_date' => [
                            'month' => $model->production_date_month,
                            'year' => $model->production_date_year,
                        ],
                        'payment' => [
                            'cash' => $model->payment_type_1,
                            'transfer' => $model->payment_type_2,
                            'card' => $model->payment_type_3,
                        ],
                        'vat' => $model->vat,
                        'latitude' => $model->latitude == null ? null : (float)$model->latitude,
                        'longitude' => $model->longitude == null ? null : (float)$model->longitude,
                        'views_count' => (int)$model->views_count,
                        'create_time' => $model->create_time,
                        'update_time' => $model->update_time
                    ]
                ]
            ];
        }

        return ['ok' => false, 'body' => ['message' => $model->firstErrorsString, 'errors' => $model->errors]];
    }

    public function actionUpdatePriceAvailable($product_id) {
        $body = Json::decode(Yii::$app->request->rawBody, true);

        if ($body === null) {
            return ['ok' => false, 'message' => Yii::t('app', 'request_body_not_found')];
        }

        $model = Product::findOne((int)$product_id);

        if ($model === null || $model != null && $model->user_id != Yii::$app->user->id) {
            return ['ok' => false, 'message' => Yii::t('app', 'product_not_found')];
        }

        $model->price_1 = @$body['product']['price']['ton'];
        $model->price_2 = @$body['product']['price']['bag'];
        $model->price_3 = @$body['product']['price']['kg'];

        $model->available_1 = @$body['product']['available']['ton'];
        $model->available_2 = @$body['product']['available']['bag'];
        $model->available_3 = @$body['product']['available']['kg'];

        if ($model->save()) {
            return [
                'ok' => true,
                'body' => [
                    'product' => [
                        'id' => $model->id,
                        'price' => [
                            'ton' => $model->price_1,
                            'bag' => $model->price_2,
                            'kg' => $model->price_3,
                        ],
                        'available' => [
                            'ton' => $model->available_1,
                            'bag' => $model->available_2,
                            'kg' => $model->available_3,
                        ],
                    ]
                ]
            ];
        }

        return ['ok' => false, 'body' => ['message' => $model->firstErrorsString, 'errors' => $model->errors]];
    }

    public function actionSearch($query) {
        $query_lat = Translit::oz2uz($query);
        $models = Product::find()
            ->where(['like', 'title', "%{$query}%", false])
            ->orWhere(['like', 'title', "%{$query_lat}%", false])
            ->orderBy(['update_time' => SORT_DESC])
            ->all();

        $products = [];

        foreach ($models as $model) {
            $products[] = [
                'id' => $model->id,
                'title' => $model->translatedTitle,
                'type' => $model->type,
                'seller' => [
                    'id' => $model->user->id,
                    'fullname' => $model->user->fullname,
                    'phone' => $model->user->phone,
                    'organization' => $model->user->organization,
                ],
                'region' => [
                    'id' => $model->region_id,
                    'district' => $model->region->translatedTitle
                ],
                'price' => [
                    'ton' => $model->price_1,
                    'bag' => $model->price_2,
                    'kg' => $model->price_3,
                ],
                'views_count' => (int)$model->views_count,
                'create_time' => $model->create_time,
                'update_time' => $model->update_time
            ];
        }

        return [
            'ok' => true,
            'body' => [
                'products' => $products
            ]
        ];
    }

    public function actionMyProducts() {
        /** @var Product[] $models */
        $models = Product::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->orderBy(['update_time' => SORT_DESC])
            ->all();

        $products = [];

        foreach ($models as $model) {
            $products[] = [
                'id' => $model->id,
                'title' => $model->translatedTitle,
                'type' => $model->type,
                'seller' => [
                    'id' => $model->user->id,
                    'fullname' => $model->user->fullname,
                    'phone' => $model->user->phone,
                    'organization' => $model->user->organization,
                ],
                'region' => [
                    'id' => $model->region_id,
                    'district' => $model->region->translatedTitle
                ],
                'price' => [
                    'ton' => $model->price_1,
                    'bag' => $model->price_2,
                    'kg' => $model->price_3,
                ],
                'views_count' => (int)$model->views_count,
                'create_time' => $model->create_time,
                'update_time' => $model->update_time
            ];
        }

        return [
            'ok' => true,
            'body' => [
                'products' => $products
            ]
        ];
    }

    public function actionDelete($product_id) {
        $model = Product::findOne((int)$product_id);

        if ($model === null || $model != null && $model->user_id != Yii::$app->user->id) {
            return ['ok' => false, 'message' => Yii::t('app', 'product_not_found')];
        }

        if ($model->delete()) {
            return [
                'ok' => true,
                'body' => [
                    'is_deleted' => true
                ]
            ];
        }

        return ['ok' => false, 'message' => Yii::t('app', 'cannot_delete_product')];
    }

    public function actionDeletePhoto($product_id, $attribute) {
        $model = Product::findOne((int)$product_id);

        if ($model === null || $model != null && $model->user_id != Yii::$app->user->id) {
            return ['ok' => false, 'message' => Yii::t('app', 'product_not_found')];
        }

        switch ($attribute) {
            case 'image_1':
                $attr = 'image1';
                $index = 0;
                break;
            case 'image_2':
                $attr = 'image2';
                $index = 1;
                break;
            case 'image_3':
                $attr = 'image3';
                $index = 2;
                break;
            case 'image_4':
                $attr = 'image4';
                $index = 3;
                break;
            default:
                return ['ok' => false, 'message' => Yii::t('app', 'image_attribute_incorrect')];
        }

        if ($model->$attr == null) {
            return ['ok' => false, 'message' => Yii::t('app', 'product_image_does_not_exist')];
        }

        if ($model->deleteImage($index)) {
            $model->$attr = null;
            if ($model->save()) {
                return [
                    'ok' => true,
                    'body' => [
                        'is_deleted' => true
                    ]
                ];
            }
        }

        return ['ok' => false, 'message' => Yii::t('app', 'cannot_delete_product_image'), 'errors' => $model->errors];
    }
}
