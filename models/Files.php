<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%files}}".
 *
 * @property int $id
 * @property string $title
 * @property string $file_id
 * @property string $url
 *
 * @property Accesses[] $accesses
 */
class Files extends \yii\db\ActiveRecord
{
    // const SCENARIO_UPLOAD = 'upload';
    // const SCENARIO_SAVE = 'save';

    public $file;

    public static function saveFile()
    {
        $dir = Yii::getAlias('@app/web/uploads/');

        $model = new Files();
        // $model->title = $filename;

        $model->file_id = Yii::$app->security->generateRandomString();
        while(!$model->validate()) {
            $model->file_id = Yii::$app->security->generateRandomString();
        }

        $model->url = Yii::$app->security->generateRandomString();

        $model->save(false);

        return $model;
    }

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
            [['title', 'file_id', 'url'], 'string', 'max' => 255],
            ['file_id', 'unique'],

            ['file', 'file', 'extensions' => 'doc, pdf, docx, zip, jpeg, jpg, png', 'maxSize' => 2*1024*1024],
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
