<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\User;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\helpers\Json;
use yii\rest\Controller;

class ForgotPasswordController extends Controller {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => Cors::className()
        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => ['POST'],
                'confirm' => ['POST'],
                'set-password' => ['POST'],
            ],
        ];

        $behaviors['bearerAuth'] = [
            'class' => HttpBearerAuth::class,
            'only' => [],
            'except' => ['index', 'confirm', 'set-password'],
        ];

        return $behaviors;
    }

    public function actionIndex() {
        $body = Json::decode(Yii::$app->request->rawBody, true);

        if ($body === null) {
            return ['ok' => false, 'message' => Yii::t('app', 'request_body_not_found')];
        }

        if (empty(@$body['user']['phone'])) {
            return ['ok' => false, 'message' => Yii::t('app', 'user_phone_is_empty')];
        }

        $model = User::find()->where(['phone' => $body['user']['phone']])->one();

        if ($model == null) {
            return ['ok' => false, 'message' => Yii::t('app', 'user_not_found')];
        }

        $model->recover_password = (string)rand(1000, 9999);
        $model->sendConfirmCode($model->recover_password, false);

        if ($model->save()) {
            return [
                'ok' => true,
                'body' => [
                    'confirm_code_is_send' => true
                ]
            ];
        }

        return ['ok' => false, 'body' => ['message' => $model->firstErrorsString, 'errors' => $model->errors]];
    }

    public function actionConfirm() {
        $body = Json::decode(Yii::$app->request->rawBody, true);

        if ($body === null) {
            return ['ok' => false, 'message' => Yii::t('app', 'request_body_not_found')];
        }

        if (empty(@$body['user']['phone'])) {
            return ['ok' => false, 'message' => Yii::t('app', 'user_phone_is_empty')];
        }

        if (empty(@$body['user']['code'])) {
            return ['ok' => false, 'message' => Yii::t('app', 'user_code_is_empty')];
        }

        $model = User::find()->where(['phone' => $body['user']['phone']])->one();

        if ($model == null) {
            return ['ok' => false, 'message' => Yii::t('app', 'user_not_found')];
        }

        if ($model->recover_password != $body['user']['code']) {
            return ['ok' => false, 'message' => Yii::t('app', 'invalid_confirm_code')];
        }

        $model->recover_password = Yii::$app->security->generateRandomString();
        if ($model->save()) {
            return [
                'ok' => true,
                'body' => [
                    'temp_token' => $model->recover_password,
                ]
            ];
        }

        return ['ok' => false, 'body' => ['message' => $model->firstErrorsString, 'errors' => $model->errors]];
    }

    public function actionSetPassword() {
        $body = Json::decode(Yii::$app->request->rawBody, true);

        if ($body === null) {
            return ['ok' => false, 'message' => Yii::t('app', 'request_body_not_found')];
        }

        if (empty(@$body['user']['temp_token'])) {
            return ['ok' => false, 'message' => Yii::t('app', 'temp_token_is_empty')];
        }

        if (empty(@$body['user']['password'])) {
            return ['ok' => false, 'message' => Yii::t('app', 'password_is_empty')];
        }

        $model = User::find()->where(['recover_password' => $body['user']['temp_token']])->one();

        if ($model == null) {
            return ['ok' => false, 'message' => Yii::t('app', 'incorrect_temp_token')];
        }

        $model->password = password_hash($body['user']['password'], PASSWORD_DEFAULT);
        $model->recover_password = null;

        if ($model->save()) {
            return [
                'ok' => true,
                'body' => [
                    'password_reset' => true,
                ]
            ];
        }

        return ['ok' => false, 'body' => ['message' => $model->firstErrorsString, 'errors' => $model->errors]];
    }
}
