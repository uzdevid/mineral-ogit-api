<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use Yii;

class EskizController extends Controller {
    public function actionRefreshToken() {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://notify.eskiz.uz/api/auth/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('email' => Yii::$app->params['eskiz']['email'], 'password' => Yii::$app->params['eskiz']['password']),
        ));

        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);

        if ($response['message'] == 'token_generated') {
            file_put_contents(__DIR__ . '/../config/eskiz_auth_token.txt', $response['data']['token']);
        }

        return ExitCode::OK;
    }
}