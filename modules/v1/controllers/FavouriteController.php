<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\Favourite;
use app\modules\v1\models\Product;
use app\modules\v1\models\User;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\Controller;

class FavouriteController extends Controller {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => Cors::className()
        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'toggle' => ['POST'],
            ],
        ];

        $behaviors['bearerAuth'] = [
            'class' => HttpBearerAuth::class,
            'only' => ['index', 'toggle'],
            'except' => [],
        ];

        return $behaviors;
    }

    public function actionIndex() {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $favourites = [];
        foreach ($user->favourites as $favourite) {
            $model = $favourite->product;
            $favourites[] = [
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

        return ['ok' => true, 'body' => ['favourites' => $favourites]];
    }

    public function actionToggle($product_id) {
        $product = Product::findOne((int)$product_id);

        if ($product === null) {
            return ['ok' => false, 'message' => Yii::t('app', 'product_not_found')];
        }

        $favourite = Favourite::find()->where(['user_id' => Yii::$app->user->id, 'product_id' => $product->id])->one();
        if ($favourite == null) {
            $model = new Favourite();
            $model->user_id = Yii::$app->user->id;
            $model->product_id = $product->id;

            if ($model->validate()) {
                return ['ok' => true, 'body' => ['product' => ['is_favourite' => $model->save()]]];
            } else {
                return ['ok' => false, 'body' => ['message' => $model->firstErrorsString, 'errors' => $model->errors]];
            }
        }

        return ['ok' => true, 'body' => ['product' => ['is_favourite' => !(bool)$favourite->delete()]]];
    }
}
