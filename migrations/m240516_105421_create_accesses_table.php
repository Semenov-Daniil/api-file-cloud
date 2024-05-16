<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%accesses}}`.
 */
class m240516_105421_create_accesses_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%accesses}}', [
            'id' => $this->primaryKey(),
            'users_id' => $this->integer()->notNull(),
            'files_id' => $this->integer()->notNull(),
            'roles_id' => $this->integer()->notNull(),
        ]);

        $this->createIndex('accesses-users_id', '{{%accesses}}', 'users_id');
        $this->addForeignKey('fk-accesses-users_id', '{{%accesses}}', 'users_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('accesses-files_id', '{{%accesses}}', 'files_id');
        $this->addForeignKey('fk-accesses-files_id', '{{%accesses}}', 'files_id', '{{%files}}', 'id', 'CASCADE', 'CASCADE');

        $this->createIndex('accesses-roles_id', '{{%accesses}}', 'roles_id');
        $this->addForeignKey('fk-accesses-roles_id', '{{%accesses}}', 'roles_id', '{{%roles}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-accesses-users_id', '{{%accesses}}');
        $this->dropIndex('accesses-users_id', '{{%accesses}}');

        $this->dropForeignKey('fk-accesses-files_id', '{{%accesses}}');
        $this->dropIndex('accesses-files_id', '{{%accesses}}');
        
        $this->dropForeignKey('fk-accesses-roles_id', '{{%accesses}}');
        $this->dropIndex('accesses-roles_id', '{{%accesses}}');

        $this->dropTable('{{%accesses}}');
    }
}
