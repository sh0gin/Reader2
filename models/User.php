<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property int $age
 * @property int $role_id
 * @property int|null $gender_id
 * @property string|null $token
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'email', 'password', 'age', 'role_id'], 'required'],

            [['name', 'email'], 'string', 'max' => 255],
            ['name' , 'match', 'pattern' => '/[A-ZА-Я].*/'],
            ['email' , 'email'],
            ['email' , 'unique'],
            ['age', 'integer', 'min' => 2, 'max' => 150],
            ['gender_id', 'boolean', 'trueValue' => 1, 'falseValue' => 2],
            ['password', 'match', 'pattern' => '/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])[0-9a-zA-Z!@#$%^&*]{4}/'],

            // [['gender_id'], 'default', 'value' => null],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => Role::class, 'targetAttribute' => ['role_id' => 'id']],
            [['gender_id'], 'exist', 'skipOnError' => true, 'targetClass' => Gender::class, 'targetAttribute' => ['gender_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'age' => 'Age',
            'role_id' => 'Role ID',
            'gender_id' => 'Gender ID',
            'token' => 'Token',
        ];
    }
    
// книги // облако файлов // встречи // 
    public function getIsAdmin()
    {
        return $this->role_id == Role::getRoleId('admin');
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {   
        return static::findOne(['token' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->authKey;
    }

    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    public function validatePassword($password) {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

}
