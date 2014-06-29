<?php

namespace mdm\logger;

use yii\mongodb\Connection;
use yii\di\Instance;

/**
 * Description of MongoStorage
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class MongoStorage extends \yii\base\Object implements StorageInterface
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

    public function save($name, $row)
    {
        $this->db->getCollection($name)->insert($row);
    }
    
    public function batchSave($name, $rows)
    {
        $this->db->getCollection($name)->batchInsert($rows);
    }
}