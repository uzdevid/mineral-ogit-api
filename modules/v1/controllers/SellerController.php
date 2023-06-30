<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\User;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\Controller;

class SellerController extends Controller {

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

    public function actionIndex($seller_id) {
        /** @var User $seller */

        $seller = User::find()->where(['id' => $seller_id])->one();

        if ($seller == null) {
            return ['ok' => false, 'body' => ['message' => Yii::t('app', 'Seller not found')]];
        }

        $products = [];
        foreach ($seller->products as $model) {
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

        return ['ok' => true, 'body' => ['seller' => [
            'data' => [
                'fullname' => $seller->fullname,
                'phone' => $seller->phone,
                'organization' => $seller->organization,
            ],
            'products' => $products
        ]]];
    }
}
