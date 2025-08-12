<?php

namespace app\controllers;

use app\models\Role;
use app\models\User;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\validators\EmailValidator;

class UserController extends  \yii\rest\ActiveController
{

    public $modelClass = '';
    public $enableCsrfValidation = '';


    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => [isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'http ://' . $_SERVER['REMOTE_ADDR']],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
            'actions' => [
                'login' => [
                    'Access-Control-Allow-Credentials' => true,
                ]
            ],
        ];


        $auth = [
            'class' => HttpBearerAuth::class,
            'only' => ['logout'],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete" and "create" actions
        unset($actions['delete'], $actions['create']);

        // customize the data provider preparation with the "prepareDataProvider()" method
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionRegister()
    {
        $model = new User();

        $model->load(Yii::$app->request->post(), '');
        $model->role_id = 1;
        $model->gender_id = 1;
        if ($model->validate()) {
            $model->password = Yii::$app->security->generatePasswordHash($model->password);
            $model->save();
            return $this->asJson([
                'data' => [
                    'user' => [
                        'id' => $model->id,
                        'name' => $model->name,
                        'email' => $model->email,
                    ]
                ]
            ]);
        } else {
            return $this->asJson([
                'errors' => [
                    'code' => 422,
                    'message' => 'Validation Error',
                    'errors' => $model->errors,
                ]
            ]);
        }
    }

    public function actionLogin()
    {
        $post = Yii::$app->request->post();
        $validator = new EmailValidator();
        if ($validator->validate($post['email'], $error)) {
            $model = User::findOne(['email' => $post['email']]);
            if ($model) {
                if ($model->validatePassword($post['password'])) {
                    $model->token = Yii::$app->security->generateRandomString();
                    $model->save(false);
                    return $this->asJson([
                        'data' => [
                            'token' =>  $model->token,
                            'user' => [
                                'id' => $model->id,
                                'name' => $model->name,
                                'email' => $model->email,
                                'role' => Role::getRoleName($model->role_id),
                            ],
                            'code' => 200,
                            'message' => 'Успешна авторизация',
                        ]
                    ]);
                } else {
                    Yii::$app->response->statusCode = 401;
                }
            } else {
                Yii::$app->response->statusCode = 401;
            }
        } else {
            return $this->asJson([
                'errors' => [
                    'code' => 422,
                    'message' => 'Validation Error',
                    'errors' => [
                        'email' => $error,
                    ]
                ]
            ]);
        }
    }
}
