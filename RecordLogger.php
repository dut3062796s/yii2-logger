<?php

namespace mdm\logger;

use \Yii;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

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

    /**
     *
     * @var BaseStorage 
     */
    private static $_storage = false;

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
            Yii::trace(strtr('log value at {level} are {value}', [
                '{level}' => static::$_level + 1,
                '{value}' => \yii\helpers\VarDumper::dumpAsString(static::$_data[static::$_level])
                ]), __METHOD__);
            foreach (static::$_data[static::$_level] as $name => $rows) {
                static::batchSave($name, $rows);
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
                static::save($this->name, $data);
                Yii::trace(strtr('log value name \'{name}\' are {value}', [
                    '{name}' => $this->name,
                    '{value}' => \yii\helpers\VarDumper::dumpAsString($data)
                    ]), __METHOD__);
            } catch (\Exception $exc) {
                
            }
        }
    }

    protected static function initStorage()
    {
        if (static::$_storage === false) {
            $config = ArrayHelper::getValue(Yii::$app->params, 'mdm.logger.storage');
            if ($config === null || $config instanceof BaseStorage) {
                static::$_storage = $config;
            } else {
                static::$_storage = Yii::createObject($config);
            }
        }
    }

    protected static function save($name, $row)
    {
        static::initStorage();
        if (static::$_storage !== null) {
            static::$_storage->save($name, $row);
        }
    }

    protected static function batchSave($name, $rows)
    {
        static::initStorage();
        if (static::$_storage !== null) {
            static::$_storage->batchSave($name, $rows);
        }
    }
}