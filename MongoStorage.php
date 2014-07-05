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
        $this->db->getCollection($name)->insert($row);
    }
    
    protected function doBatchSave($name, $rows)
    {
        $this->db->getCollection($name)->batchInsert($rows);
    }
}