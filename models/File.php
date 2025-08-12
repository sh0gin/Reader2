<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "File".
 *
 * @property int $id
 * @property string $file_url
 * @property string $data_uploads
 * @property int $book_id
 *
 * @property Book $book
 */
class File extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'File';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file_url', 'book_id'], 'required'],
            [['data_uploads'], 'safe'],
            [['book_id'], 'integer'],
            [['file_url'], 'string', 'max' => 255],
            [['book_id'], 'exist', 'skipOnError' => true, 'targetClass' => Book::class, 'targetAttribute' => ['book_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'file_url' => 'File Url',
            'data_uploads' => 'Data Uploads',
            'book_id' => 'Book ID',
        ];
    }

    /**
     * Gets query for [[Book]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBook()
    {
        return $this->hasOne(Book::class, ['id' => 'book_id']);
    }

}
