<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%files}}".
 *
 * @property int $id
 * @property string $title
 * @property string $extension
 * @property string $file_id
 * @property string $url
 *
 * @property Accesses[] $accesses
 */
class Files extends \yii\db\ActiveRecord
{
    const SCENARIO_EDIT = 'edit';

    public $file;
    public $name;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%files}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'extension', 'file_id', 'url'], 'string', 'max' => 255],
            ['file_id', 'unique'],

            ['file', 'file', 'extensions' => 'doc, pdf, docx, zip, jpeg, jpg, png', 'maxSize' => 2*1024*1024],

            ['name', 'string', 'max' => 255, 'on' => static::SCENARIO_EDIT],
            ['name', 'required', 'on' => static::SCENARIO_EDIT],
            ['name', 'uniqueForUser', 'on' => static::SCENARIO_EDIT],
        ];
    }

    public function uniqueForUser($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $existingRecord = Accesses::find()
                ->innerJoin(Files::tableName(), 'fc_files.id = fc_accesses.files_id')
                ->innerJoin(Roles::tableName(), 'fc_roles.id = fc_accesses.roles_id')
                ->where(['fc_accesses.users_id' => Yii::$app->user->identity->id, 'fc_roles.title' => 'author', 'fc_files.title' => $this->$attribute])
                ->one();

            if ($existingRecord !== null) {
                $this->addError($attribute, 'This value already exists for this user.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'extension' => 'extension',
            'file_id' => 'File ID',
            'url' => 'Url',
        ];
    }

    /**
     * Gets query for [[Accesses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccesses()
    {
        return $this->hasMany(Accesses::class, ['files_id' => 'id']);
    }
}
