<?php
namespace dezmont765\yii2bundle\db;

use yii\db\mysql\Schema;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 29.03.2016
 * Time: 15:27
 */
class Migration extends \yii\db\Migration
{
    public function tinyint() {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BOOLEAN, 4);
    }
}