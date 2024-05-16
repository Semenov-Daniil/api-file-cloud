<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%files}}`.
 */
class m240516_102925_create_files_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%files}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'extension' => $this->string(255)->notNull(),
            'file_id' => $this->string(255)->notNull()->unique(),
            'url' => $this->string(255)->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%files}}');
    }
}
