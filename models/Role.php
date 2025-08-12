<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Role".
 *
 * @property int $id
 * @property string $role
 *
 * @property User[] $users
 */
class Role extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Role';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role'], 'required'],
            [['role'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role' => 'Role',
        ];
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['role_id' => 'id']);
    }

    public static function getRoleId($role) {
        return self::findOne(['role' => $role])->id;
    }

    public static function getRoleName($id) {
                return self::findOne($id)->role;

    }

}
