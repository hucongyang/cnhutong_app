<?php

class LogUserLoginHistory extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{log_user_login_history}}';
    }

    /**
     * 登录LOG记录         log_user_login_history
     *
     * @param $userId
     * @param $loginIp
     * @param $version
     * @param $deviceId
     * @param $platform
     * @param $channel
     * @param $appVersion
     * @param $osVersion
     * @param $appId
     * @param $nowTime
     */
    public function log_user_login_history($userId, $loginIp, $version, $deviceId, $platform, $channel, $appVersion, $osVersion, $appId, $nowTime)
    {
        try {
            Yii::app()->cnhutong_user->createCommand()
                ->insert('log_user_login_history',
                    array(
                        'user_id' => $userId,
                        'login_ip' => $loginIp,
                        'version' => $version,
                        'device_id' => $deviceId,
                        'platform' => $platform,
                        'channel' => $channel,
                        'app_version' => $appVersion,
                        'os_version' => $osVersion,
                        'app_id' => $appId,
                        'login_time' => $nowTime
                    )
                );
        } catch(Exception $e) {
            error_log($e);
        }
    }

}