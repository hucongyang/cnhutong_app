<?php

class LogUserShare extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className = __CLASS__);
    }

    public function tableName()
    {
        return '{{log_user_share}}';
    }

    /**
     * 获取APP中所有分享成功的相关数据
     * @param $userId
     * @param $shareType
     * @param $shareId
     * @param $platform
     * @return array
     */
    public function postShareInfo($userId, $shareType, $shareId, $platform)
    {
        $data = array();
        $nowTime = date("Y-m-d H-i-s");              //当前时间
        try {
            Yii::app()->cnhutong_user->createCommand()
                ->insert('log_user_share',
                    array(
                        'user_id' => $userId,
                        'share_type' => $shareType,
                        'share_id' => $shareId,
                        'platform' => $platform,
                        'create_ts' => $nowTime
                    )
                );
            // 当前日期分享的次数
            $count = count(self::IssetShare($userId));
            if($count > 5) {
                $pointChange = 0;
            } else {
                $pointChange = 1;
            }

            $change         = $pointChange;
            $reason         = 7;           // 积分变化类型 scoreChangeByReason($reason) 获得类型
            $scoreRest      = UserScoreHistory::model()->getPoint($userId) + $pointChange;
            $createTs       = $nowTime;
            $memo           = null;

            // 积分变化记录历史
            $scoreHistory = UserScoreHistory::model()->insertScoreHistory($userId, $change, $reason, $scoreRest, $createTs, $memo);
            $scoreUpdate = UserScoreHistory::model()->updateUserScore($userId, $scoreRest);

            $data['point'] = $pointChange;

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 当前日期是否分享
     * @param $userId
     * @return array
     */
    public function IssetShare($userId)
    {
        $id = array();
        $nowTime = date("Y-m-d");
        try {
            $id = Yii::app()->cnhutong_user->createCommand()
                ->select('id')
                ->from('log_user_share')
                ->where('user_id = :userId And DATE(create_ts) = :nowTime', array(':userId' => $userId, ':nowTime' => $nowTime))
                ->queryAll();
        } catch (Exception $e) {
            error_log($e);
        }
        return $id;
    }
}