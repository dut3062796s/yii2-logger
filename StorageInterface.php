<?php
namespace mdm\logger;

/**
 * Description of BaseStorage
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
interface StorageInterface
{
    public function save($name,$row);
    
    public function batchSave($name,$rows);
}