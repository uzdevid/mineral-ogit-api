<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\Device;
use app\modules\v1\models\User;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\rest\Controller;

class SigninController extends Controller {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => Cors::className()
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['POST'],
            ],
        ];

        $behaviors['bearerAuth'] = [
            'class' => HttpBearerAuth::class,
            'only' => ['confirm'],
            'except' => ['index'],
        ];

        return $behaviors;
    }

    public function actionIndex() {
        $body = json_decode(Yii::$app->request->rawBody, true);

        if ($body === null) {
            return ['ok' => false, 'body' => ['message' => Yii::t('app', 'request_body_not_found')]];
        }

        if (empty($body['user']['phone'])) {
            return ['ok' => false, 'body' => ['message' => Yii::t('app', 'phone_is_empty')]];
        }

        if (empty($body['user']['password'])) {
            return ['ok' => false, 'body' => ['message' => Yii::t('app', 'password_is_empty')]];
        }

        if (empty($body['device']['id'])) {
            return ['ok' => false, 'body' => ['message' => Yii::t('app', 'device_id_is_empty')]];
        }

        $user = User::findOne(['phone' => $body['user']['phone']]);

        if ($user == null || ($user != null && !password_verify($body['user']['password'], $user->password))) {
            return ['ok' => false, 'body' => ['message' => Yii::t('app', 'incorrect_phone_or_password')]];
        }

        $user->authorization_time = date('Y-m-d H:i:s');
        if ($user->update(['authorization_time'])) {
            $device = Device::find()->where(['user_id' => $user->id, 'device_id' => $body['device']['id']])->one();

            if ($device == null) {
                $device = new Device();
                $device->user_id = $user->id;
                $device->device_id = @$body['device']['id'];
                $device->name = @$body['device']['name'];
                $device->fcm_token = @$body['device']['fcm_token'];

                if (!$device->save()) {
                    return ['ok' => false, 'body' => ['message' => $device->firstErrorsString, 'errors' => $device->errors]];
                }
            } else {
                $device->authorization_time = date('Y-m-d H:i:s');
                $device->update(['authorization_time']);
            }

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
            return ['ok' => false, 'body' => ['message' => $user->firstErrorsString, 'errors' => $user->errors]];
        }
    }

}
