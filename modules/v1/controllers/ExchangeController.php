<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\Exchange;
use app\modules\v1\models\ExchangeProduct;
use app\modules\v1\models\Product;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\Controller;

class ExchangeController extends Controller {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => Cors::className()
        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
            ],
        ];

        $behaviors['bearerAuth'] = [
            'class' => HttpBearerAuth::class,
            'only' => [],
            'except' => ['index'],
        ];

        return $behaviors;
    }

    public function actionIndex() {
        $product_models = ExchangeProduct::find()->all();
        $products = [];

        foreach ($product_models as $product) {
            $models = Exchange::find()
                ->where(['exchange_product_id' => $product->id])
                ->orderBy(['publish_time' => SORT_DESC])
                ->all();

            if (empty($models)) continue;

            $exchange = [];
            foreach ($models as $model) {
                $exchange[] = [
                    'id' => $model->id,
                    'region' => [
                        'id' => $model->region_id,
                        'district' => $model->region->translatedTitle
                    ],
                    'unit' => [
                        'unit' => $model->unit,
                        'value' => $model->unit_value,
                    ],
                    'price' => [
                        'fixed' => $model->price_max == null ? $model->price_min : null,
                        'min' => $model->price_min,
                        'max' => $model->price_max,
                    ],
                    'currency' => $model->currency,
                    'status' => $model->status,
                    'publish_time' => date('Y-m-d H:i', strtotime($model->publish_time))
                ];
            }

            $products[] = [
                'id' => $product->id,
                'title' => $product->title,
                'exchanges' => $exchange
            ];
        }

        return [
            'ok' => true,
            'body' => [
                'products' => $products
            ]
        ];
    }
}
