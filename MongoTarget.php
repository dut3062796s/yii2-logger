<?php

namespace mdm\logger;

use yii\mongodb\Connection;
use \Yii;
use yii\di\Instance;

/**
 * Description of MongoTarget
 *
 * @author MDMunir
 */
class MongoTarget extends \yii\log\Target
{

    /**
     *
     * @var Connection 
     */
    public $connection = 'mongodb';
    public $collectionName = 'app_log';
    private $_idLog;

    public function init()
    {
        parent::init();
        $this->connection = Instance::ensure($this->connection, Connection::className());
        $str = microtime(true);
        if (($session = Yii::$app->getSession()) !== null) {
            $str .= $session->id;
        }
        $this->_idLog = time() . ':' . md5($str);
    }

    /**
     * Generates the context information to be logged.
     * The default implementation will dump user information, system variables, etc.
     * @return mixed the context information. If an empty string, it means no context information.
     */
    protected function getContextMessage()
    {
        $context = [];
        if (($user = Yii::$app->getUser()) !== null) {
            /** @var \yii\web\User $user */
            $context['user'] = $user->getId();
        }

        foreach ($this->logVars as $name) {
            if (!empty($GLOBALS[$name])) {
                $context[$name] = $GLOBALS[$name];
            }
        }

        return empty($context) ? '' : $context;
    }

    public function export()
    {
        try {
            $collection = $this->connection->getCollection($this->collectionName);

            foreach ($this->messages as $message) {
                $collection->insert([
                    'log_id' => $this->_idLog,
                    'level' => $message[1],
                    'category' => $message[2],
                    'log_time' => $message[3],
                    'message' => $message[0],
                ]);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
