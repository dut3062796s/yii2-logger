<?php

namespace mdm\logger;

use yii\helpers\ArrayHelper;
use yii\db\Connection;

/**
 * Description of Bootstrap
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class Bootstrap implements \yii\base\BootstrapInterface
{

    /**
     * 
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        $dbs = ArrayHelper::getValue($app->params, 'mdm.logger.attach', 'db');
        if (!empty($dbs)) {
            foreach ((array) $dbs as $db) {
                if (is_string($db) && strpos($db, '\\') === false) {
                    $db = $app->get($db, false);
                } elseif (!($db instanceof Connection)) {
                    $db = Yii::createObject($db);
                }
                if ($db !== null) {
                    $db->on(Connection::EVENT_BEGIN_TRANSACTION, ['mdm\logger\RecordLogger', 'begin']);
                    $db->on(Connection::EVENT_COMMIT_TRANSACTION, ['mdm\logger\RecordLogger', 'commit']);
                    $db->on(Connection::EVENT_ROLLBACK_TRANSACTION, ['mdm\logger\RecordLogger', 'rollback']);
                }
            }
        }
    }
}