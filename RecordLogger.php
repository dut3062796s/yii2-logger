<?php

namespace mdm\logger;

use \Yii;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;
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
    public $name;

    /**
     *
     * @var mixed 
     */
    private static $_user_id = false;
    private static $_data = [];
    private static $_level = 0;

    public function init()
    {
        if ($this->name === null) {
            throw new InvalidConfigException("RecordLogger::name must be set.");
        }
        if (static::$_user_id === false) {
            $user = Yii::$app->user;
            static::$_user_id = $user->getId();
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
        static::$_level++;
    }

    public static function commit()
    {
        try {
            static::$_level--;
            if (!isset(static::$_data[static::$_level])) {
                return;
            }
            /* @var $storage StorageInterface */
            $storage = Yii::createObject('mdm\logger\BaseStorage');
            Yii::trace(strtr('log value at {level} ar {value}', [
                '{level}' => static::$_level + 1,
                '{value}' => \yii\helpers\VarDumper::dumpAsString(static::$_data[static::$_level])
                ]), __METHOD__);
            Yii::t($category, $message);
            foreach (static::$_data[static::$_level] as $logs) {
                foreach ($logs as $name => $rows) {
                    $storage->batchSave($name, $rows);
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
            'log_by' => static::$_user_id,
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
            static::$_data[static::$_level - 1][$this->name][] = $data;
        } else {
            try {
                /* @var $storage BaseStorage */
                $storage = Yii::createObject('mdm\logger\BaseStorage');
                $storage->save($this->name, $data);
            } catch (\Exception $exc) {
                
            }
        }
    }
}