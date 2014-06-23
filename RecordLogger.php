<?php

namespace mdm\logger;

use \Yii;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;
use yii\mongodb\Connection;
use yii\di\Instance;
use yii\base\InvalidConfigException;

/**
 * Description of Logger
 *
 * @author MDMunir
 * 
 */
class RecordLogger extends Behavior
{
    /**
     *
     * @var array 
     */
    public $logParams = [];

    /**
     *
     * @var array 
     */
    public $attributes = [];

    /**
     *
     * @var string 
     */
    public $collectionName;

    /**
     *
     * @var Connection 
     */
    public static $connection = 'mongodb';

    /**
     *
     * @var mixed 
     */
    private static $_user_id = false;
    private static $_data = [];
    private static $_level = 0;

    public function init()
    {
        if ($this->collectionName === null) {
            throw new InvalidConfigException("RecordLogger::collectionName must be set.");
        }
        if (self::$_user_id === false) {
            $user = Yii::$app->user;
            self::$_user_id = $user->getId();
        }
    }

    public function events()
    {
        return[
            BaseActiveRecord::EVENT_AFTER_INSERT => 'insertLog',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'insertLog',
        ];
    }

    public static function begin()
    {
        static::$_data[static::$_level] = [];
        static::$_level++;
    }

    public static function commit()
    {
        try {
            static::$_level--;
            static::$connection = Instance::ensure(static::$connection, Connection::className());
            foreach (static::$_data[static::$_level] as $collections) {
                foreach ($collections as $name => $rows) {
                    static::$connection->getCollection($name)->batchInsert($rows);
                }
            }
        } catch (\Exception $exc) {
            
        }
        unset(static::$_data[static::$_level]);
    }

    public static function rollback()
    {
        static::$_level--;
        unset(static::$_data[static::$_level]);
    }

    /**
     * 
     * @param \yii\base\Event $event
     */
    public function insertLog($event)
    {
        $model = $event->sender;
        $logs = array_merge([
            'log_time1' => new \MongoDate(),
            'log_time2' => time(),
            'log_by' => self::$_user_id,
            ], $this->logParams);
        $data = [];
        foreach ($this->attributes as $attribute) {
            if ($model->hasAttribute($attribute)) {
                $data[$attribute] = $model->{$attribute};
            } elseif (isset($logs[$attribute]) || array_key_exists($attribute, $logs)) {
                $data[$attribute] = $logs[$attribute];
            }
        }

        if (static::$_level > 0) {
            static::$_data[static::$_level - 1][$this->collectionName][] = $data;
        } else {
            try {
                static::$connection = Instance::ensure(self::$connection, Connection::className());
                static::$connection->getCollection($this->collectionName)->insert($data);
            } catch (\Exception $exc) {
                
            }
        }
    }
}