<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'asd',
            'baseUrl' => '',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'multipart/form-data' => 'yii\web\MultipartFormDataParser'
            ]
        ],
        'response' => [
            'formatters' => [
                \yii\web\Response::FORMAT_JSON => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                    // ...
                ],
            ],
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->statusCode == 401) {
                    if (Yii::$app->user->isGuest) {
                        return $response->data = [
                            "message" => "Login failed"
                        ];
                    } else {
                        return $response->data = [
                            "message" => "Forbidden for you"
                        ];
                    }
                }
            },
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\Users',
            'enableAutoLogin' => true,
            'enableSession' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                'OPTIONS <prefix:.*>/authorization' => 'user/options',
                'POST <prefix:.*>/authorization' => 'user/login',

                'OPTIONS <prefix:.*>/registration' => 'user/options',
                'POST <prefix:.*>/registration' => 'user/register',

                'OPTIONS <prefix:.*>/logout' => 'user/options',
                'GET <prefix:.*>/logout' => 'user/logout',

                [
                    'pluralize' => true,
                    'prefix' => '<prefix:.*>',
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'file',
                    'extraPatterns' => [
                        'OPTIONS /' => 'upload-files',
                        'POST /' => 'upload-files',
                        
                        'OPTIONS <file_id>' => 'edit-file',
                        'POST <file_id>' => 'edit-file',

                        'OPTIONS <file_id>' => 'delete-file',
                        'DELETE <file_id>' => 'delete-file',

                        'GET disk' => 'get-files',

                        'GET shared' => 'get-shared-files',

                        'GET <file_id>' => 'download-file',

                        'OPTIONS <file_id>/accesses' => 'add-access',
                        'POST <file_id>/accesses' => 'add-access',

                        'OPTIONS <file_id>/accesses' => 'delete-access',
                        'DELETE <file_id>/accesses' => 'delete-access',

                    ],
                ],
            ],
        ]
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];
}

return $config;
