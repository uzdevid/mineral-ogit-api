<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\User;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\helpers\Json;
use yii\rest\Controller;
use yii\web\UploadedFile;

class CabinetController extends Controller {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => Cors::className()
        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'update' => ['POST'],
                'create-update-organization' => ['POST'],
                'change-language' => ['POST'],
                'upload-photo' => ['POST'],
                'upload-organization-photo' => ['POST'],
                'reset-password' => ['POST'],
                'delete' => ['POST'],
            ],
        ];

        $behaviors['bearerAuth'] = [
            'class' => HttpBearerAuth::class,
            'only' => ['index', 'update', 'create-update-organization', 'change-language', 'upload-photo', 'upload-organization-photo', 'reset-password', 'delete'],
            'except' => [],
        ];

        return $behaviors;
    }

    public function actionIndex() {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        return [
            'ok' => true,
            'body' => [
                'user' => [
                    'image' => $user->imageLink,
                    'surname' => $user->surname,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'lang' => $user->lang,
                    'organization' => $user->organization
                ]
            ]
        ];
    }

    public function actionUpdate() {
        $body = Json::decode(Yii::$app->request->rawBody, true);

        /** @var User $user */
        $user = Yii::$app->user->identity;

        $user->surname = @$body['user']['surname'];
        $user->name = @$body['user']['name'];

        if (!$user->save()) {
            return ['ok' => false, 'body' => ['message' => $user->firstErrorsString, 'errors' => $user->errors]];
        }

        return [
            'ok' => true,
            'body' => [
                'user' => [
                    'surname' => $user->surname,
                    'name' => $user->name,
                ]
            ]
        ];
    }

    public function actionCreateUpdateOrganization() {
        $body = Json::decode(Yii::$app->request->rawBody, true);

        /** @var User $user */
        $user = Yii::$app->user->identity;

        $user->scenario = 'create-organization';
        $user->organization_name = @$body['user']['organization']['name'];
        $user->organization_phone_number = @$body['user']['organization']['phone'];
        $user->organization_about = @$body['user']['organization']['about'];
        $user->inn = @$body['user']['organization']['inn'];
        $user->mfo = @$body['user']['organization']['mfo'];
        $user->bank_info = @$body['user']['organization']['bank_info'];

        if (!$user->save()) {
            return ['ok' => false, 'body' => ['message' => $user->firstErrorsString, 'errors' => $user->errors]];
        }

        return [
            'ok' => true,
            'body' => [
                'user' => [
                    'organization' => $user->organization
                ]
            ]
        ];
    }

    public function actionChangeLanguage() {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $user->updateAttributes(['lang' => Yii::$app->language]);

        return [
            'ok' => true,
            'body' => [
                'user' => [
                    'lang' => $user->lang,
                ]
            ]
        ];
    }

    public function actionUploadPhoto() {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $user->file = UploadedFile::getInstanceByName('file');

        if ($user->uploadPhoto()) {
            return [
                'ok' => true,
                'body' => [
                    'user' => [
                        'image' => $user->imageLink
                    ]
                ]
            ];
        }

        return ['ok' => false, 'body' => ['message' => $user->firstErrorsString, 'errors' => $user->errors]];
    }

    public function actionUploadOrganizationPhoto() {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $user->file = UploadedFile::getInstanceByName('file');

        if ($user->uploadPhoto('organization')) {
            return [
                'ok' => true,
                'body' => [
                    'user' => [
                        'organization' => [
                            'image' => $user->organizationImageLink
                        ]
                    ]
                ]
            ];
        }

        return ['ok' => false, 'body' => ['message' => $user->firstErrorsString, 'errors' => $user->errors]];
    }

    public function actionResetPassword() {
        $body = Json::decode(Yii::$app->request->rawBody, true);

        /** @var User $user */
        $user = Yii::$app->user->identity;

        if ($body === null) {
            return ['ok' => false, 'message' => Yii::t('app', 'request_body_not_found')];
        }

        if (empty(@$body['user']['password'])) {
            return ['ok' => false, 'message' => Yii::t('app', 'password_is_empty')];
        }

        $user->password = password_hash($body['user']['password'], PASSWORD_DEFAULT);

        if ($user->save()) {
            return [
                'ok' => true,
                'body' => [
                    'password_reset' => true,
                ]
            ];
        }

        return ['ok' => false, 'body' => ['message' => $user->firstErrorsString, 'errors' => $user->errors]];
    }

    public function actionDelete() {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        return [
            'ok' => true,
            'body' => [
                'deleted' => (bool)$user->delete(),
            ]
        ];
    }
}
