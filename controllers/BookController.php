<?php

namespace app\controllers;

use app\models\Book;
use app\models\File;
use app\models\Progress;
use app\models\User;
use Yii;
use yii\data\ActiveDataFilter;
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
            'only' => ['create', 'get-books', 'get-books-info-all', 'delete-books', 'change-books-info', 'set-progress', 'get-progress', 'get-books-progress', 'change-visibility'],
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
        // $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider']; // СНОСИТЬ

        return $actions;
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
            if (Yii::$app->user->identity->role_id == 2) {
                $query = new Query();
                $query->select('book.id, title, author, description, file_url, is_public')
                    ->from('book')
                    ->innerJoin('file', 'book.id=file.book_id')
                    ->innerJoin('user', 'user.id = book.user_id');
                return $this->asJson([
                    'data' => [
                        'books' => $query->all(),
                        'code' => 200,
                        'message' => 'Список книг получен',
                    ]
                ]);
            } else {
                return $this->asJson([
                    'data' => [
                        'books' => $query->where(['user.id' => Yii::$app->user->id])->all(),
                        'code' => 200,
                        'message' => 'Список книг получен',
                    ]
                ]);
            }
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
            Yii::$app->response->statusCode = 404;
            return;
        }
    }

    public function actionChangeBooksInfo($id)
    {
        $model_book = Book::findOne($id);
        if ($model_book) {
            if (Yii::$app->user->identity->id == $model_book->user_id) {
                $model_book->load(Yii::$app->request->post(), '');
                if ($model_book->validate()) {
                    $model_book->save(false);
                    $file = __DIR__ . "/../books" . str_split(File::findOne(['book_id' => $id])->file_url, strripos(File::findOne(['book_id' => $id])->file_url, '/'))[1];

                    return $this->asJson([
                        'data' => [
                            'book' => [
                                'id' => $model_book->id,
                                'title' => $model_book->title,
                                'author' => $model_book->author,
                                'description' => $model_book->description,
                                'file_url' => $file,
                            ],
                            'code' => 200,
                            'message' => 'Информация о книге обновленна',
                        ]
                    ]);
                } else {
                    return $this->asJson([
                        'errors' => [
                            'code' => 422,
                            'message' => 'Validation Error',
                            'errors' => $model_book->errors,
                        ]
                    ]);
                }
            } else {
                Yii::$app->response->statusCode = 403;
                return;
            }
        } else {
            Yii::$app->response->statusCode = 404;
            return;
        }
    }

    public function actionSetProgress($id)
    {
        $model_book = Book::findOne($id);

        if ($model_book) {
            if ($model_book->user_id == Yii::$app->user->id || $model_book->is_public) {
                $progress = Progress::findOne(['user_id' => Yii::$app->user->id, 'book_id' => $id]);
                if ($progress) {
                    $progress->load(Yii::$app->request->post(), '');
                } else {
                    $progress = new Progress();
                    $progress->user_id = Yii::$app->user->id;
                    $progress->book_id = $model_book->id;
                    $progress->load(Yii::$app->request->post(), '');
                }

                if ($progress->save()) {
                    return $this->asJson([
                        'data' => [
                            'book_id' => $model_book->id,
                            'progress' => $progress->progress,
                            'code' => 200,
                            'message' => 'Прогресс чтения сохранён',
                        ]
                    ]);
                } else {
                    return $this->asJson([
                        'errors' => [
                            'code' => 422,
                            'message' => 'Validation Error',
                            'errors' => $progress->errors,
                        ]
                    ]);
                }
            } else {
                Yii::$app->response->statusCode = 403;
            }
        } else {
            Yii::$app->response->statusCode = 404;
        }
    }

    public function actionGetProgress($id)
    {
        $model_book = Book::findOne($id);
        if ($model_book) {
            if ($model_book->user_id == Yii::$app->user->id || $model_book->is_public) {
                $progress = Progress::findOne(['book_id' => $id, 'user_id' => Yii::$app->user->id]);
                if ($progress) { // ???
                    $progress = $progress->progress;
                } else {
                    $progress = 0;
                }
                return $this->asJson([
                    'data' => [
                        'book_id' => $model_book->id,
                        'progress' => $progress,
                        'code' => 200,
                        'message' => 'Прогресс чтения получен',
                    ]
                ]);
            } else {
                Yii::$app->response->statusCode = 403;
            }
        } else {
            Yii::$app->response->statusCode = 404;
        }
    }

    public function actionGetBooksProgress()
    {
        $query = new Query();

        $query->select('book.id, title, description, author, file_url')
            ->from('book')
            ->where('progress.progress > 0')
            ->andWhere(['progress.user_id' => Yii::$app->user->id])
            ->innerJoin('progress', 'book.id=progress.book_id')
            ->innerJoin('file', 'book.id = file.book_id');

        $provider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->asJson([
            'data' => [
                'books' => $provider->getModels(),
            ],
            'code' => 200,
            'message' => 'Список книг, который читает пользователь получен',
            'total_books' => $provider->totalCount,
        ]);
    }

    public function actionChangeVisibility($id)
    {
        $model = Book::findOne($id);
        if ($model) {
            if (Yii::$app->user->identity->id == $model->user_id) {
                $model->is_public = Yii::$app->request->post()['is_public'];
                if ($model->save()) {
                    return $this->asJson([
                        'data' => [
                            'book' => [
                                'id' => $model->id,
                                'is_public' => $model->is_public,
                            ],
                            'code' => 200,
                            'message' => 'Доступность книг измененна'
                        ]
                        ]);
                } else {
                    return $this->asJson([
                        'errors' => [
                            'code' => 422,
                            'message' => 'Validation Error',
                            'errors' => $model->errors,
                        ]
                    ]);;
                }
            } else {
                Yii::$app->response->statusCode = 403;
            }
        } else {
            Yii::$app->response->statusCode = 404;
        }
    }
}
