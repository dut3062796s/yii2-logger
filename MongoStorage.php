<?php

namespace mdm\logger;

use yii\mongodb\Connection;
use yii\di\Instance;

/**
 * Description of MongoStorage
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class MongoStorage extends BaseStorage
{
    /**
     *
     * @var Connection 
     */
    public $db = 'mongodb';

    public function init()
    {
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    protected function doSave($name, $row)
    {
        \Yii::trace("Save to '{$name}'", __METHOD__);
        $this->db->getCollection($name)->insert($row);
    }
    
    protected function doBatchSave($name, $rows)
    {
        \Yii::trace("Batch save to '{$name}'", __METHOD__);
        $this->db->getCollection($name)->batchInsert($rows);
    }
}