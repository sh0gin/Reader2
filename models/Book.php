<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Book".
 *
 * @property int $id
 * @property string $title
 * @property string $author
 * @property string $description
 * @property int $is_public
 *
 * @property File[] $files
 * @property Progress[] $progresses
 */
class Book extends \yii\db\ActiveRecord
{
    public $file;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Book';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['file', 'file', 'extensions' => ['html'], 'maxSize' => 1024*512],
            [['title'], 'required'],
            [['author', 'description'], 'string', 'max' => 255],
            ['title', 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'author' => 'Author',
            'description' => 'Description',
            'is_public' => 'Is Public',
        ];
    }

    /**
     * Gets query for [[Files]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::class, ['book_id' => 'id']);
    }

    /**
     * Gets query for [[Progresses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProgresses()
    {
        return $this->hasMany(Progress::class, ['book_id' => 'id']);
    }

}
