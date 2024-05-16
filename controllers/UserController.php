<?php

namespace app\controllers;

use app\models\Users;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;

class UserController extends ActiveController
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
                'logout' => [
                    'Access-Control-Allow-Credentials' => true,
                ],
            ]
        ];

        $auth = [
            'class' => HttpBearerAuth::class,
            'only' => ['logout']
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

    public function actionLogin()
    {
        $data = Yii::$app->request->post();

        $model = new Users();
        $model->load($data, '');
        $model->validate();

        if (!$model->hasErrors()) {
            $user = Users::findOne(['email' => $model->email]);
            if ($user->validatePassword($model->password)) {
                $model = $user;
                $model->token = Yii::$app->security->generateRandomString();

                while(!$model->save()) {
                    $model->token = Yii::$app->security->generateRandomString();
                }
            }

            if ($model->token) {
                Yii::$app->response->statusCode = 200;
                $answer = [
                    "success" => true,
                    "code" => 200,
                    "message" => "Success",
                    "token" => $model->token
                ];
            } else {
                Yii::$app->response->statusCode = 401;
                $answer = [
                    "success" => false,
                    "code" => 401,   
                    "message" => "Authorization failed"                 
                ];
            }
        } else {
            Yii::$app->response->statusCode = 422;
            $answer = [
                "success" => false,
                'code' => 422,
                'message' => $model->errors,
            ];
        }
        return $this->asJson($answer);
    }

    public function actionRegister()
    {
        $data = Yii::$app->request->post();

        $model = new Users();
        $model->scenario = Users::SCENARIO_REGISTER;
        $model->load($data, '');
        $model->validate();

        if (!$model->hasErrors()) {
            $model->token = Yii::$app->security->generateRandomString();
            while(!$model->validate()) {
                $model->token = Yii::$app->security->generateRandomString();
            }
            
            $model->password = Yii::$app->getSecurity()->generatePasswordHash($model->password);

            $model->save(false);

            Yii::$app->response->statusCode = 201;
            $answer = [
                "success" => true,
                "code" => 201,
                "message" => "Success",
                "token" => $model->token
            ];
        } else {
            Yii::$app->response->statusCode = 422;
            $answer = [
                "success" => false,
                'code' => 422,
                'message' => $model->errors,
            ];
        }
        return $this->asJson($answer);
    }

    public function actionLogout()
    {
        $identity = Yii::$app->user->identity;

        $user = Users::findOne($identity->id);
        $user->token = null;
        $user->save(false);
        Yii::$app->response->statusCode = 204;
        return true;
    }
}
