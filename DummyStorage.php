<?php

namespace mdm\logger;

/**
 * Description of DummyStorage
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class DummyStorage implements StorageInterface
{

    public function batchSave($name, $rows)
    {
        // dont do anything
    }

    public function save($name, $row)
    {
        // dont do anything
    }
}