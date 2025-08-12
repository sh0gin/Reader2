<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Params".
 *
 * @property int $id
 * @property string $font_family
 * @property string $text_color
 * @property string $background_color
 * @property int $font_size
 */
class Params extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Params';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['font_family', 'text_color', 'background_color', 'font_size'], 'required'],
            [['font_size'], 'integer'],
            [['font_family', 'text_color', 'background_color'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'font_family' => 'Font Family',
            'text_color' => 'Text Color',
            'background_color' => 'Background Color',
            'font_size' => 'Font Size',
        ];
    }

}
