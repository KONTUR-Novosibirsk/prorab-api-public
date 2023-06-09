<?php

use yii\db\Migration;

/**
 * Class m220901_021532_add_image_to_category_table
 */
class m220901_021532_add_image_to_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('category', 'image', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('category', 'image');
    }
}
