<?php

use yii\db\Migration;

/**
 * Class m240516_104636_add_roles
 */
class m240516_104636_add_roles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%roles}}', 
        [
            'id',
            'title'
        ], 
        [
            [
                1,
                'author'
            ],
            [
                2,
                'co-author'
            ]
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%roles}}', ['id' => 1]);
        $this->delete('{{%roles}}', ['id' => 2]);
    }
}
