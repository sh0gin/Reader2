<?php

namespace app\controllers;

use app\models\Book;
use app\models\File;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\web\UploadedFile;
use yii\filters\auth\HttpBearerAuth;


class BookController extends \yii\rest\ActiveController
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
                'Origin' => [isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'http://' . $_SERVER['REMOTE_ADDR']],
                // 'Origin' => ["*"],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
            'actions' => [
                'logout' => [
                    'Access-Control-Allow-Credentials' => true,
                ]
            ],
        ];
        $auth = [
            'class' => HttpBearerAuth::class,
            'only' => ['create', 'get-books', 'get-books-info-all', 'delete-books'],
            'optional' => ['get-books', 'get-books-info-all'],
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

    public function actionCreate()
    {
        $model = new Book();
        $model->load(Yii::$app->request->post(), '');
        $model->file = UploadedFile::getInstanceByName('file');
        $model->user_id = Yii::$app->user->identity->id;
        if ($model->save()) {

            $model_book = new File();
            $random_string = Yii::$app->security->generateRandomString(6);
            $model_book->file_url = $_SERVER['REQUEST_SCHEME'] . "::/" . $_SERVER['HTTP_HOST'] . "/books/$random_string-$model->file";
            $model_book->book_id = $model->id;
            $model_book->save();
            $model->file->saveAs(__DIR__ . "/../books/$random_string-$model->file");

            return $this->asJson([
                'data' => [
                    'book' => [
                        'id' => $model->id,
                        'title' => $model->title,
                        'author' => $model->author,
                        'description' => $model->description,
                        'file_url' => $model_book->file_url,
                    ],
                    'code' => 201,
                    'message' => 'Книга успешно загружена',
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

    public function actionGetBooks()
    {
        $query = new Query();

        $query->select('book.id, title, author, description, file_url')
            ->from('book')
            ->innerJoin('file', 'book.id=file.book_id')
            ->innerJoin('user', 'user.id = book.user_id');



        if (Yii::$app->user->id) {
            return $this->asJson([
                'data' => [
                    'books' => $query->where(['user.id' => Yii::$app->user->id])->all(),
                    'code' => 200,
                    'message' => 'Список книг получен',
                ]
            ]);
        }


        $post = Yii::$app->request->post();
        if ($post) {
            $provider = new ActiveDataProvider([
                'query' => $query->where(['role_id' => 2]),
                'pagination' => [
                    'pageSize' => $post['count'],
                    'page' => $post['page'] - 1
                ],
            ]);

            return $this->asJson([
                'data' => [
                    'books' => $provider->getModels(),
                    'code' => 200,
                    'message' => 'Список книг для указанной странницы получен',
                    'total_books' => $provider->totalCount,
                ]
            ]);
        }

        return $this->asJson([
            'data' => [
                'books' => $query->where(['role_id' => 2])->all(),
                'code' => 200,
                'message' => 'Список книг получен',
            ]
        ]);
    }

    public function actionGetBooksInfoAll($id)
    {
        $query = new Query();

        $query->select('book.id, title, author, description, file_url')
            ->from('book')
            ->innerJoin('file', 'book.id=file.book_id')
            ->innerJoin('user', 'user.id = book.user_id');

        if (Yii::$app->user->id) {
            $query1 = $query->where(['user.id' => Yii::$app->user->id, 'book.id' => $id])->one();
            if (!$query1) {
                $query1 = $query->where(['is_public' => 1, 'book.id' => $id])->one();
                if (!$query1) {
                    Yii::$app->response->statusCode = 403;
                    return;
                } else {
                    return $this->asJson([
                        'data' => [
                            'books' => $query1,
                            'code' => 200,
                            'message' => 'Информация о книге полученна',
                        ]
                    ]);
                }
                Yii::$app->response->statusCode = 404;
                return;
            }

            return $this->asJson([
                'data' => [
                    'books' => $query1,
                    'code' => 200,
                    'message' => 'Информация о книге полученна',
                ]
            ]);
        }


        $query1 = $query->where(['book.id' => $id, 'role_id' => 2])->one();
        if ($query1) {
            return $this->asJson([
                $query1,
            ]);
        } else {
            if ($query->where(['book.id' => $id, 'role_id' => 1])->one()) {
                Yii::$app->response->statusCode = 403;
            } else {
                Yii::$app->response->statusCode = 404;
            }
        };
    }

    public function actionDeleteBooks($id)
    {
        $model = Book::findOne($id);
        if ($model) {
            if ($model->user_id == Yii::$app->user->identity->id) {
                $file = __DIR__ . "/../books" . str_split(File::findOne(['book_id' => $id])->file_url, strripos(File::findOne(['book_id' => $id])->file_url, '/'))[1];
                unlink($file);
                $model->delete();
                return $this->asJson([
                    'data' => [
                        'code' => 200,
                        'message' => "Книга успешно удаленна",
                    ]
                ]);
            } else {
                Yii::$app->response->statusCode = 403;
                return;
            }
        } else {
            Yii::$app->response->statusCode = 403;
            return;
        }
    }
}
