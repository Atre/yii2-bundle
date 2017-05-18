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
    public $dbOptions;
    protected $tableName = 'air_rates';
    public function init()
    {
        parent::init();

        $this->dbOptions = $this->generateDbOptions();
    }

    public function generateDbOptions()
    {
        $list = self::getDbOptionsList();
        $driverName = $this->db->driverName;

        return isset($list[$driverName]) ? $list[$driverName] : null;
    }

    public static function getDbOptionsList()
    {
        return [
            'mysql' => 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB',
        ];
    }

    public function tinyint() {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BOOLEAN, 4);
    }
}