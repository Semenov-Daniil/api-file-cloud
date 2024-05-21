<?php

namespace app\controllers;

use app\models\Accesses;
use app\models\Files;
use app\models\Roles;
use app\models\Users;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rbac\Role;
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
                'Access-Control-Request-Method' => ['OPTIONS', 'POST', 'GET', 'DELETE', 'PATH'],
                'Access-Control-Request-Headers' => ['content-type', 'Authorization'],
            ],
            'actions' => [
                'upload-files' => [
                    'Access-Control-Allow-Credentials' => true,
                ],
                'edit-file' => [
                    'Access-Control-Allow-Credentials' => true,
                ],
                'delete-file' => [
                    'Access-Control-Allow-Credentials' => true,
                ],
                'download-file' => [
                    'Access-Control-Allow-Credentials' => true,
                ],
                'add-access' => [
                    'Access-Control-Allow-Credentials' => true,
                ],
                'delete-access' => [
                    'Access-Control-Allow-Credentials' => true,
                ],
                'get-files' => [
                    'Access-Control-Allow-Credentials' => true,
                ],
                'get-shared-files' => [
                    'Access-Control-Allow-Credentials' => true,
                ],
            ]
        ];

        $auth = [
            'class' => HttpBearerAuth::class,
            'except' => []
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
        $identity = Yii::$app->user->identity;

        foreach ($files as $file) {
            $model = new Files();
            $model->file = $file;

            if ($model->validate()) {
                $result[] = $this->saveFile($file, $identity->id);
            } else {
                $result[] = [
                    "success" => false,
                    "message" => $model->errors['file'],
                    "name" => $file->baseName,
                ];
            }
        }

        Yii::$app->response->statusCode = 200;
        return $this->asJson($result);
    }

    public function saveFile($file, $user_id)
    {
        $answer = [];
        $modelFile = new Files();
        $filename = $file->baseName;

        $fileCount = Accesses::find()
            ->select([
                '{{%files}}.title'
            ])
            ->innerJoin(Files::tableName(), '{{%files}}.id = {{%accesses}}.files_id')
            ->innerJoin(Roles::tableName(), '{{%roles}}.id = {{%accesses}}.roles_id')
            ->where(['{{%accesses}}.users_id' => $user_id, '{{%files}}.extension' => $file->extension, '{{%roles}}.title' => 'author'])
            ->andWhere(['regexp', '{{%files}}.title', $file->baseName . '(\s*\(\d+\))?'])
            ->count();

        if ($fileCount) {
            $filename = trim($filename) . ' (' . $fileCount+1 . ')';
        }

        $modelFile->title = $filename;
        $modelFile->extension = $file->extension;
        $modelFile->file_id = Yii::$app->security->generateRandomString(10);
        while(!$modelFile->validate()) {
            $modelFile->file_id = Yii::$app->security->generateRandomString(10);
        }
        $modelFile->url = Yii::$app->request->getHostInfo() . '/api-file/files/' . $modelFile->file_id;

        if ($modelFile->save()) {
            $modelAccesses = new Accesses();
            $modelAccesses->users_id = $user_id;
            $modelAccesses->files_id = $modelFile->id;
            $modelAccesses->roles_id = (Roles::findOne(['title' => 'author']))->id;
    
            if ($modelAccesses->save()) {
                $dir = Yii::getAlias('@app/uploads/');

                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }

                if (($file->saveAs(Yii::getAlias('@app/uploads/') . $modelFile->file_id . '.' . $modelFile->extension))) {
                    $answer = [
                        "success" => true,
                        "code" => 200,
                        "message" => "Success",
                        "name" => $modelFile->title,
                        "url" => $modelFile->url,
                        "file_id" => $modelFile->file_id
                    ];
                }
            }
        }

        if (!$answer) {
            $answer = [
                "success" => false,
                "message" => ['File not loader'],
                "name" => $file->baseName,
            ];
        }

        return $answer;
    }

    public function actionEditFile($file_id = null)
    {
        $identity = Yii::$app->user->identity;
        $file = Files::findOne(['file_id' => $file_id]);
        
        if (!empty($file)) {
            $accesse = Accesses::find()->where(['files_id' => $file->id, 'users_id' => $identity->id, 'roles_id' => (Roles::findOne(['title' => 'author']))->id])->one();
            if (!empty($accesse)) {
                $file->scenario = Files::SCENARIO_EDIT;
                $file->load(Yii::$app->request->post(), '');
                $file->validate();
                if (!$file->hasErrors()) {
                    $file->title = $file->name;
                    $file->save(false);

                    Yii::$app->response->statusCode = 200;
                    $answer = [
                        "success" => true,
                        'code' => 200,
                        'message' => 'Renamed',
                    ];
                } else {
                    Yii::$app->response->statusCode = 422;
                    $answer = [
                        "success" => false,
                        'code' => 422,
                        'message' => $file->errors,
                    ];
                }
            } else {
                Yii::$app->response->statusCode = 401;
                return false;
            }
        } else {
            Yii::$app->response->statusCode = 404;
            $answer = [
                "message" => "Not found",
                "code" => 404
            ];
        }

        return $answer;
    }

    public function actionDeleteFile($file_id = null)
    {
        $identity = Yii::$app->user->identity;
        $file = Files::findOne(['file_id' => $file_id]);
        
        if (!empty($file)) {
            $accesse = Accesses::find()->where(['files_id' => $file->id, 'users_id' => $identity->id, 'roles_id' => (Roles::findOne(['title' => 'author']))->id])->one();
            if (!empty($accesse)) {
                unlink(Yii::getAlias('@app/uploads/') . $file->file_id . '.' . $file->extension);
                $file->delete();

                Yii::$app->response->statusCode = 200;
                $answer = [
                    "success" => true,
                    'code' => 200,
                    'message' => 'File deleted',
                ];
            } else {
                Yii::$app->response->statusCode = 401;
                return false;
            }
        } else {
            Yii::$app->response->statusCode = 404;
            $answer = [
                "message" => "Not found",
                "code" => 404
            ];
        }

        return $answer;
    }

    public function actionDownloadFile($file_id = null)
    {
        $identity = Yii::$app->user->identity;
        $file = Files::findOne(['file_id' => $file_id]);
        
        if (!empty($file)) {
            $accesse = Accesses::find()->where(['files_id' => $file->id, 'users_id' => $identity->id])->one();
            if (!empty($accesse)) {
                $path_file = Yii::getAlias('@app/uploads/') . $file->file_id . '.' . $file->extension;

                if (file_exists($path_file)) {
                    // return Yii::$app->response->sendStreamAsFile(fopen($path_file, 'r'), $file->title)->send();
                    return Yii::$app->response->sendFile($path_file)->send();
                } else {
                    Yii::$app->response->statusCode = 404;
                    $answer = [
                        "message" => "Not found",
                        "code" => 404
                    ];
                }
            } else {
                Yii::$app->response->statusCode = 401;
                return false;
            }
        } else {
            Yii::$app->response->statusCode = 404;
            $answer = [
                "message" => "Not found",
                "code" => 404
            ];
        }

        return $answer;
    }

    public function actionAddAccess($file_id = null)
    {
        $identity = Yii::$app->user->identity;
        $file = Files::findOne(['file_id' => $file_id]);
        
        if (!empty($file)) {
            $accesse = Accesses::find()->where(['files_id' => $file->id, 'users_id' => $identity->id, 'roles_id' => (Roles::findOne(['title' => 'author']))->id])->one();
            if (!empty($accesse)) {
                if (isset((Yii::$app->request->post())['email'])) {
                    $email = (Yii::$app->request->post())['email'];

                    $user = Users::findOne(['email' => $email]);
    
                    if (!empty($user)) {
                        if (empty(Accesses::findOne(['users_id' => $user->id, 'files_id' => $file->id]))) {
                            $modelAccesses = new Accesses();
                            $modelAccesses->users_id = $user->id;
                            $modelAccesses->files_id = $file->id;
                            $modelAccesses->roles_id = (Roles::findOne(['title' => 'co-author']))->id;
                            $modelAccesses->save(false);
                        }
    
                        $all_user = Accesses::find()
                            ->select(['first_name', 'last_name', 'email', '{{%roles}}.title as type'])
                            ->innerJoin('{{%users}}', '{{%users}}.id = {{%accesses}}.users_id')
                            ->innerJoin('{{%roles}}', '{{%roles}}.id = {{%accesses}}.roles_id')
                            ->where(['files_id' => $file->id])
                            ->asArray()
                            ->all();
    
                        foreach ($all_user as $one_user) {
                            $answer[] = [
                                "fullname" => $one_user['first_name'] . ' ' . $one_user['last_name'],
                                "email" => $one_user['email'],
                                "type" => $one_user['type'],
                                "code" => 200
                            ];
                        }
    
                        Yii::$app->response->statusCode = 200;
                    } else {
                        Yii::$app->response->statusCode = 404;
                        $answer = [
                            "message" => "Not found",
                            "code" => 404
                        ];
                    }
                } else {
                    Yii::$app->response->statusCode = 422;
                    $answer = [
                        "success" => false,
                        'code' => 422,
                        'message' => ["email" => ["Email cannot be blank."]],
                    ];
                }

            } else {
                Yii::$app->response->statusCode = 401;
                return false;
            }
        } else {
            Yii::$app->response->statusCode = 404;
            $answer = [
                "message" => "Not found",
                "code" => 404
            ];
        }

        return $answer;
    }

    public function actionDeleteAccess($file_id = null)
    {
        $identity = Yii::$app->user->identity;
        $file = Files::findOne(['file_id' => $file_id]);
        
        if (!empty($file)) {
            $is_author = Accesses::find()->where(['files_id' => $file->id, 'users_id' => $identity->id, 'roles_id' => (Roles::findOne(['title' => 'author']))->id])->one();
            if (!empty($is_author)) {
                if (isset((Yii::$app->request->post())['email'])) {
                    $email = (Yii::$app->request->post())['email'];

                    $user = Users::findOne(['email' => $email]);
    
                    if (!empty($user)) {
                        if ($modelAccesses = Accesses::findOne(['users_id' => $user->id])) {
                            if ($modelAccesses->users_id != $identity->id) {
                                $modelAccesses->delete();
                            } else {
                                Yii::$app->response->statusCode = 401;
                                return false;
                            }
                        }
    
                        $all_user = Accesses::find()
                            ->select(['first_name', 'last_name', 'email', '{{%roles}}.title as type'])
                            ->innerJoin('{{%users}}', '{{%users}}.id = {{%accesses}}.users_id')
                            ->innerJoin('{{%roles}}', '{{%roles}}.id = {{%accesses}}.roles_id')
                            ->where(['files_id' => $file->id])
                            ->asArray()
                            ->all();
    
                        foreach ($all_user as $one_user) {
                            $answer[] = [
                                "fullname" => $one_user['first_name'] . ' ' . $one_user['last_name'],
                                "email" => $one_user['email'],
                                "type" => $one_user['type'],
                                "code" => 200
                            ];
                        }
    
                        Yii::$app->response->statusCode = 200;
                    } else {
                        Yii::$app->response->statusCode = 404;
                        $answer = [
                            "message" => "Not found",
                            "code" => 404
                        ];
                    }
                } else {
                    Yii::$app->response->statusCode = 422;
                    $answer = [
                        "success" => false,
                        'code' => 422,
                        'message' => ["email" => ["Email cannot be blank."]],
                    ];
                }

            } else {
                Yii::$app->response->statusCode = 401;
                return false;
            }
        } else {
            Yii::$app->response->statusCode = 404;
            $answer = [
                "message" => "Not found",
                "code" => 404
            ];
        }

        return $answer;
    }

    public function actionGetFiles()
    {
        $identity = Yii::$app->user->identity;
        $answer = [];

        $files = Accesses::find()
            ->select([
                'title', 'file_id', 'url', '{{%files}}.id'
            ])
            ->innerJoin('{{%files}}', '{{%files}}.id = {{%accesses}}.files_id')
            ->where(['users_id' => $identity->id, 'roles_id' => (Roles::findOne(['title' => 'author']))->id])
            ->asArray()
            ->all();

        foreach ($files as $file) {
            $accesses = Accesses::find()
                ->select([
                    'CONCAT(first_name, " ", last_name) as fullname', 'email', '{{%roles}}.title as type'
                ])
                ->innerJoin('{{%users}}', '{{%users}}.id = {{%accesses}}.users_id')
                ->innerJoin('{{%roles}}', '{{%roles}}.id = {{%accesses}}.roles_id')
                ->where(['files_id' => $file['id']])
                ->asArray()
                ->all();
            
            $answer[] = [
                'file_id' => $file['file_id'],
                'name' => $file['title'],
                'code' => 200,
                'url' => $file['url'],
                'accesses' => $accesses
            ];
        }

        Yii::$app->response->statusCode = 200;
        return $answer;
    }

    public function actionGetSharedFiles()
    {
        $identity = Yii::$app->user->identity;
        $answer = [];

        $files = Accesses::find()
            ->select([
                'title', 'file_id', 'url'
            ])
            ->innerJoin('{{%files}}', '{{%files}}.id = {{%accesses}}.files_id')
            ->where(['users_id' => $identity->id, 'roles_id' => (Roles::findOne(['title' => 'co-author']))->id])
            ->asArray()
            ->all();

        foreach ($files as $file) {
            $answer[] = [
                'file_id' => $file['file_id'],
                'name' => $file['title'],
                'code' => 200,
                'url' => $file['url']
            ];
        }

        Yii::$app->response->statusCode = 200;
        return $answer;
    }
}
