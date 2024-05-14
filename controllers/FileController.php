<?php

namespace app\controllers;

use app\models\Files;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\UploadedFile;

class FileController extends ActiveController
{
    public $enableCsrfValidation = false;
    public $modelClass = '';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                // restrict access to
                'Origin' => [(isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'http://' . $_SERVER['REMOTE_ADDR'])],
                'Access-Control-Request-Method' => ['OPTIONS', 'POST', 'GET'],
                'Access-Control-Request-Headers' => ['content-type', 'Authorization'],
            ],
            'actions' => [
                'upload-files' => [
                    'Access-Control-Allow-Credentials' => true,
                ],
            ]
        ];

        $auth = [
            'class' => HttpBearerAuth::class,
            'only' => ['upload-files']
        ];

        $behaviors['authenticator'] = $auth;
    
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['index'], $actions['view'], $actions['update']);
        return $actions;
    }

    public function actionUploadFiles()
    {
        $result = [];
        $files = UploadedFile::getInstancesByName('files');

        foreach ($files as $file) {
            $model = new Files();
            // $model->scenario = Files::SCENARIO_UPLOAD;
            $model->file = $file;

            if ($model->validate()) {
                $model = $model::saveFile();
                $result[] = [
                    "success" => true,
                    "code" => 200,
                    "message" => "Success",
                    "name" => $model->file->baseName,
                    "url" => "{{host}}/files/qweasd1234",
                    "file_id" => "qweasd1234"
                ];
            }
        }
    }
}
