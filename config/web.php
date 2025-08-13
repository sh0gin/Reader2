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
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'sdojfhsdio',
            'baseURL' => '',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'multipart/form-data' => 'yii\web\MultipartFormDataParser',
            ]
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->statusCode == 404) {
                    $response->data = [
                        'message' => 'not found',
                    ];
                }
                if ($response->statusCode == 403) {
                    $response->data = [
                        'message' => 'Forbidden for you',
                    ];
                }
                if ($response->statusCode == 401) {
                    Yii::$app->response->statusCode = 403;
                    $response->data = [
                        'message' => 'Login failed',
                        'code' => '403',
                    ];
                }
            },
            'formatters' => [
                \yii\web\Response::FORMAT_JSON => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                    // ...
                ],
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableSession' => false,
            'enableAutoLogin' => true,
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
                ['class' => 'yii\rest\UrlRule', 'controller' => 'user'],

                'POST api/registration' => 'user/register',
                'OPTIONS api/registration' => 'options',

                'POST api/login' => 'user/login',
                'OPTIONS api/login' => 'options',

                'POST api/books/upload' => 'book/create',
                'OPTIONS api/books/upload' => 'options',

                'GET api/books' => 'book/get-books',
                'OPTIONS api/books' => 'options',   

                'GET api/books/progress' => 'book/get-books-progress',
                'OPTIONS api/books/progress' => 'options',   

                'GET api/books/<id>' => 'book/get-books-info-all',
                'OPTIONS api/books/<id>' => 'options',   

                'DELETE api/books/<id>' => 'book/delete-books',
                'OPTIONS api/books/<id>' => 'options',   

                'PATCH api/books/<id>' => 'book/change-books-info',
                'OPTIONS api/books/<id>' => 'options',

                'POST api/books/<id>/progress' => 'book/set-progress',
                'OPTIONS api/books/<id>/progress' => 'options',   

                'GET api/books/<id>/progress' => 'book/get-progress',
                'OPTIONS api/books/<id>/progress' => 'options',   

                'POST api/user/settings' => 'user/set-users-config',
                'OPTIONS api/user/settings' => 'options',    

                'POST api/logout' => 'user/logout',
                'OPTIONS api/logout' => 'options',    

                'PUT api/books/<id>/change-visibility' => 'book/change-visibility',
                'OPTIONS api/logout' => 'options',    

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
