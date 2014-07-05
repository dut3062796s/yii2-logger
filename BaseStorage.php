<?php

namespace mdm\logger;

/**
 * Description of DummyStorage
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class BaseStorage extends \yii\base\Object
{

    public function batchSave($name, $rows)
    {
        $this->doBatchSave($name, $rows);
    }

    public function save($name, $row)
    {
        $this->doSave($name, $row);
    }
    
    protected function doBatchSave($name, $rows)
    {
        // dont do anything
    }
    
    protected function doSave($name, $row)
    {
        // dont do anything
    }
}