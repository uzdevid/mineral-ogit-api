<?php

namespace app\modules\v1\controllers;

use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\Controller;

class DefaultController extends Controller {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => Cors::className()
        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'check-auth' => ['GET'],
                'check-no-auth' => ['GET'],
            ],
        ];

        $behaviors['bearerAuth'] = [
            'class' => HttpBearerAuth::class,
            'only' => ['check-auth'],
            'except' => ['config'],
        ];

        return $behaviors;
    }

    public function actionCheckAuth() {
        return ['ok' => true];
    }

    public function actionCheckNoAuth() {
        return ['ok' => true];
    }

    public function actionConfig() {
        return [
            'ok' => true,
            'body' => [
                'units' => [
                    'bag' => [
                        'bag' => 1,
                        'kg' => 50
                    ]
                ]
            ]
        ];
    }
}
