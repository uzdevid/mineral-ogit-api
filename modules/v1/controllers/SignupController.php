<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\Device;
use app\modules\v1\models\User;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\Controller;

class SignupController extends Controller {

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
            ],
        ];

        $behaviors['bearerAuth'] = [
            'class' => HttpBearerAuth::class,
            'only' => [''],
            'except' => ['index', 'confirm'],
        ];

        return $behaviors;
    }

    public function actionIndex() {
        $body = json_decode(Yii::$app->request->rawBody, true);

        if ($body === null) {
            return ['ok' => false, 'body' => ['message' => Yii::t('app', 'request_body_not_found')]];
        }

        $user = new User();
        $user->surname = @$body['user']['surname'];
        $user->name = @$body['user']['name'];
        $user->phone = @$body['user']['phone'];
        $user->password = @$body['user']['password'];
        $user->lang = Yii::$app->language;

        if ($user->save()) {
            return [
                'ok' => true,
                'body' => [
                    'user' => [
                        'image' => $user->imageLink,
                        'surname' => $user->surname,
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'status' => $user->status,
                        'create_time' => $user->create_time,
                        'update_time' => $user->update_time,
                        'authorization_time' => $user->authorization_time,
                        'organization' => $user->organization,
                    ]
                ]
            ];
        } else {
            return ['ok' => false, 'body' => ['message' => $user->firstErrorsString, 'errors' => $user->errors]];
        }
    }

    public function actionConfirm() {
        $body = json_decode(Yii::$app->request->rawBody, true);

        if ($body === null) {
            return ['ok' => false, 'body' => ['message' => Yii::t('app', 'request_body_not_found')]];
        }

        if (empty($body['user'])) {
            return ['ok' => false, 'body' => ['message' => Yii::t('app', 'user_data_is_empty')]];
        }

        if (empty($body['user']['phone'])) {
            return ['ok' => false, 'body' => ['message' => Yii::t('app', 'user_phone_is_empty')]];
        }

        if (empty($body['user']['code'])) {
            return ['ok' => false, 'body' => ['message' => Yii::t('app', 'user_code_is_empty')]];
        }

        $user = User::find()->where(['phone' => $body['user']['phone']])->one();

        if ($user == null) {
            return ['ok' => false, 'body' => ['message' => Yii::t('app', 'user_not_found')]];
        }

        if ($user->status == 1) {
            return ['ok' => false, 'body' => ['message' => Yii::t('app', 'user_phone_already_confirmed')]];
        }

        if ($user->recover_password != $body['user']['code']) {
            return ['ok' => false, 'body' => ['message' => Yii::t('app', 'invalid_confirm_code')]];
        }

        $user->status = 1;
        $user->recover_password = null;
        if ($user->save()) {
            $device = new Device();
            $device->user_id = $user->id;
            $device->device_id = @$body['device']['id'];
            $device->name = @$body['device']['name'];
            $device->fcm_token = @$body['device']['fcm_token'];

            if ($device->save()) {
                return [
                    'ok' => true,
                    'body' => [
                        'token' => $device->access_token,
                        'user' => [
                            'image' => $user->imageLink,
                            'surname' => $user->surname,
                            'name' => $user->name,
                            'phone' => $user->phone,
                            'status' => $user->status,
                            'create_time' => $user->create_time,
                            'update_time' => $user->update_time,
                            'authorization_time' => $user->authorization_time,
                            'organization' => $user->organization,
                        ]
                    ]
                ];
            } else {
                return ['ok' => false, 'body' => ['message' => $device->firstErrorsString, 'errors' => $device->errors]];
            }
        }

        return ['ok' => false, 'body' => ['message' => Yii::t('app', 'cannot_update_user_model')]];
    }

}
