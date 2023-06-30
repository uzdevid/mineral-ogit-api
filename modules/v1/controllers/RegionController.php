<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\Region;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\Controller;

class RegionController extends Controller {

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

    public function actionIndex($parent_id = null) {
        $models = Region::find()->all();

        $regions = [];
        foreach ($models as $model) {
            $regions[] = [
                'id' => $model->id,
                'title' => $model->translatedTitle,
                'create_time' => $model->create_time,
                'update_time' => $model->update_time
            ];
        }

        return [
            'ok' => true,
            'body' => [
                'regions' => $regions
            ]
        ];
    }
}
