<?php

use app\modules\v1\models\Translation;
use yii\helpers\ArrayHelper;

$models = (new Translation())->find()->all();
$translations = ArrayHelper::map($models, 'title', 'translation_uz');
return $translations;