<?php

namespace mdm\logger;

use yii\base\Behavior;
use yii\db\BaseActiveRecord;
use \Yii;
use yii\mongodb\Collection;
use yii\helpers\ArrayHelper;
use yii\mongodb\Connection;
use yii\di\Instance;
use yii\base\InvalidConfigException;

/**
 * Description of Logger
 *
 * @author MDMunir
 * 
 * @property Collection $collection Description
 * @property Connection $connection Description
 */
class RecordLogger extends Behavior
{
    /**
     *
     * @var Collection[]
     */
    private static $_collection = [];
    private static $_user_id;
    public $logParams = [];
    public $attributes = [];
    public $collectionName;
    public $connection = 'mongodb';

    public function init()
    {
        if($this->collectionName === null){
            throw new InvalidConfigException("RecordLogger::collectionName must be set.");
        }
        $this->connection = Instance::ensure($this->connection, Connection::className());
        if (self::$_user_id === null) {
            $user = Yii::$app->user;
            self::$_user_id = $user->getIsGuest() ? 0 : $user->getId();
        }
    }

    public function events()
    {
        return[
            BaseActiveRecord::EVENT_AFTER_INSERT => 'insertLog',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'insertLog',
        ];
    }

    /**
     * @return Collection Description
     */
    public function getCollection()
    {
        if (!isset(self::$_collection[$this->collectionName])) {
            self::$_collection[$this->collectionName] = $this->connection->getCollection($this->collectionName);
        }
        return self::$_collection[$this->collectionName];
    }

    /**
     * 
     * @param \yii\base\Event $event
     */
    public function insertLog($event)
    {
        $model = $event->sender;
        $logs = ArrayHelper::merge([
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
        try {
            $this->collection->insert($data);
        } catch (\Exception $exc) {
            
        }
    }
}