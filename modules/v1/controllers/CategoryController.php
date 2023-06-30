<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\Category;
use yii\db\Expression;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\Controller;

class CategoryController extends Controller {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => Cors::className()
        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => ['GET', 'all'],
            ],
        ];

        $behaviors['bearerAuth'] = [
            'class' => HttpBearerAuth::class,
            'only' => [],
            'except' => ['index', 'all'],
        ];

        return $behaviors;
    }

    public function actionIndex($parent_id = null) {
        if ($parent_id == null) {
            $models = Category::find()
                ->where(['is', 'category_id', new Expression('null')])
                ->andWhere(['status' => 1])
                ->orderBy(['sort' => SORT_ASC])
                ->all();
        } else {
            $models = Category::find()
                ->where(['category_id' => (int)$parent_id])
                ->andWhere(['status' => 1])
                ->orderBy(['sort' => SORT_ASC])
                ->all();
        }

        $categories = [];

        foreach ($models as $model) {
            $categories[] = [
                'id' => $model->id,
                'title' => $model->translatedTitle,
                'type' => $model->type,
                'product_count' => count($model->allProducts),
                'end' => !Category::find()->where(['category_id' => $model->id])->exists(),
            ];
        }

        return [
            'ok' => true,
            'body' => [
                'categories' => $categories
            ]
        ];
    }

    public function actionAll() {
        $models = Category::find()
            ->where(['is', 'category_id', new Expression('null')])
            ->andWhere(['status' => 1])
            ->orderBy(['sort' => SORT_ASC])
            ->all();

        $categories = [];

        foreach ($models as $model) {
            $categories[] = [
                'id' => $model->id,
                'title' => $model->translatedTitle,
                'type' => $model->type,
                'product_count' => count($model->allProducts),
                'end' => !Category::find()->where(['category_id' => $model->id])->exists(),
                'catalogs' => $this->getSubCatalogs($model)
            ];
        }

        return [
            'ok' => true,
            'body' => [
                'categories' => $categories
            ]
        ];
    }

    private function getSubCatalogs($parent) {
        $categories = [];

        foreach ($parent->categories as $model) {
            $categories[] = [
                'id' => $model->id,
                'title' => $model->translatedTitle,
                'type' => $model->type,
                'product_count' => count($model->allProducts),
                'end' => !Category::find()->where(['category_id' => $model->id])->exists(),
                'catalogs' => $this->getSubCatalogs($model)
            ];
        }

        return $categories;
    }
}
