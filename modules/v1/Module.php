<?php

namespace app\modules\v1;

use Yii;
use yii\console\Exception;
use yii\web\Response;

class Module extends \yii\base\Module {

    public $controllerNamespace = 'app\modules\v1\controllers';
    public $securityKeys = [
        "SBnbCdAOtrfo0PzExMqFwcrf_hcVv7JF0vY9LXl6SyHgH3YYACpxvvrhQbfXvPw"
    ];

    public function init() {
        parent::init();

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->request->headers->has('Security-Key')) {
            if (!in_array(Yii::$app->request->headers->get('Security-Key'), $this->securityKeys)) {
                throw new Exception('Forbidden', 403);
            }
        } else {
            throw new Exception('Forbidden', 403);
        }

        if (Yii::$app->request->headers->has('Accept-Language')) {
            $lang = Yii::$app->request->headers->get('Accept-Language');

            if (!in_array($lang, ['uz', 'oz', 'ru'])) {
                throw new Exception('Incorrect language', 400);
            }

            Yii::$app->language = $lang;
            Yii::$app->response->headers->add('Content-Language', Yii::$app->language);
        }
    }
}
