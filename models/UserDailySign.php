<?php

/**
 * 用户签到天数/抽奖标识
 * Class UserDailySign
 */
class UserDailySign extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{user_daily_sign}}';
    }

    /**
     * 用户app签到，获得积分
     * @param $userId
     * @param $signType
     * @return array
     */
    public function dailySign($userId, $signType)
    {
        $data = array();
        $nowTime = date("Y-m-d H:i:s");
        try {
            // 调用此方法说明用户当前签到,更新user_daily_sign数据

            // 验证用户当前是否签到
            $last_sign_ts = self::flag($userId);
//            var_dump($last_sign_ts['signFlag']);exit;
            if( $last_sign_ts['signFlag'] == 0 ) {
                return 20026;
            }

            $signDays = Yii::app()->cnhutong_user->createCommand()
                ->select('sign_days')
                ->from('user_daily_sign')
                ->where('user_id = :userId', array(':userId' => $userId))
                ->queryScalar();
            $signDays++;      // 用户签到天数 +1

            if($signType == 1) {
                $pointChange = 2;                   // 用户正常签到后获取的积分值
            } elseif ($signType == 2) {
                $pointChange = rand(-1, 5);         // 用户随机签到后获得的积分值
            } else {
                $pointChange = 0;
            }

            if($pointChange >= 0) {
                $pointChange = '+' . $pointChange;
            }
//            var_dump($signDays);exit;
            if(self::IssetUser($userId)) {
                $prizeFlag = 0;
                if( $signDays >= 7 ) {
                    $prizeFlag = 1;
                }
                Yii::app()->cnhutong_user->createCommand()
                    ->update('user_daily_sign',
                        array(
                            'last_sign_ts' => $nowTime,
                            'sign_days' => $signDays,
                            'prize_flag' => $prizeFlag
                        ),
                        'user_id = :userId',
                        array(':userId' => $userId)
                    );

            } else {
                Yii::app()->cnhutong_user->createCommand()
                    ->insert('user_daily_sign',
                        array(
                            'user_id' => $userId,
                            'last_sign_ts' => $nowTime,
                            'sign_days' => 1,
                            'prize_flag' => 0,
                            'last_prize_ts' => null
                        )
                    );
            }


            $change         = $pointChange;
            $reason         = 1;           // 积分变化类型 scoreChangeByReason($reason) 获得类型
            $scoreRest      = UserScoreHistory::model()->getPoint($userId) + $pointChange;
            $createTs       = $nowTime;
            $memo           = null;

            // 积分变化记录历史
            $scoreHistory = UserScoreHistory::model()->insertScoreHistory($userId, $change, $reason, $scoreRest, $createTs, $memo);
            $scoreUpdate = UserScoreHistory::model()->updateUserScore($userId, $scoreRest);

            $data['point']  = UserScoreHistory::model()->getPoint($userId);

            $data['pointChange']    = $pointChange;
            $sign = self::flag($userId);
            $data['signDays']   = $sign['signDays'];
            $data['signFlag']   = $sign['signFlag'];
            $data['prizeFlag']  = $sign['prizeFlag'];

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 用户在app中进行抽奖，以获取相关积分
     * @param $userId
     * @return array|int
     */
    public function prizeSign($userId)
    {
        $data = array();
        $nowTime = date("Y-m-d H:i:s");
        try {
            // 调用此方法说明用户当前抽奖，更新user_daily_sign数据

            // 验证是否可以抽奖
            $prizeFlag = self::flag($userId)['prizeFlag'];
//            var_dump($prizeFlag);exit;
            if( $prizeFlag == 0 ) {
                return 20027;
            }

            $change = rand(10, 50);       // 用户抽奖后获取的积分值
            $reason = 2;                  // 积分变化类型 scoreChangeByReason($reason) 获得类型
            $scoreRest      = UserScoreHistory::model()->getPoint($userId) + $change;
            $createTs       = $nowTime;

            // 抽奖后改变抽奖状态为0，即不可抽奖; 修改签到天数为1；记录抽奖日期
            Yii::app()->cnhutong_user->createCommand()
                ->update('user_daily_sign',
                    array(
                        'sign_days' => 1,
                        'prize_flag' => 0,
                        'last_prize_ts' => $nowTime
                    ),
                    'user_id = :userId',
                    array(':userId' => $userId)
                );

            // 积分变化记录历史
            $scoreHistory = UserScoreHistory::model()->insertScoreHistory($userId, $change, $reason, $scoreRest, $createTs, $memo = null);
            $scoreUpdate = UserScoreHistory::model()->updateUserScore($userId, $scoreRest);

            $data['point']  = UserScoreHistory::model()->getPoint($userId);
            if($change >= 0) {
                $change = '+' . $change;
            } else {
                $change = '-' . $change;
            }
            $data['pointChange']    = $change;
            $sign = self::flag($userId);
            $data['signDays']   = $sign['signDays'];
            $data['signFlag']   = $sign['signFlag'];
            $data['prizeFlag']  = $sign['prizeFlag'];

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 签到/抽奖标识
     * @param $userId
     * @return array
     */
    public function flag($userId)
    {
        $data = array();
        try {
            $result = Yii::app()->cnhutong_user->createCommand()
                ->select('last_sign_ts, sign_days, prize_flag')
                ->from('user_daily_sign')
                ->where('user_id = :userId', array(':userId' => $userId))
                ->queryRow();

            $signDay = strtotime(date("Y-m-d", strtotime($result['last_sign_ts'])));
            $now = strtotime(date("Y-m-d", strtotime("now")));
            if( $signDay === $now ) {
                $data['signFlag'] = '0';          // signFlag 签到标记 0: 已签到,不可签到  1：未签到，可签到
            } else {
                $data['signFlag'] = '1';
            }

            if( $result['sign_days'] == '' ) {
                $data['signDays'] = '0';
            } else {
                $data['signDays']   =  $result['sign_days'];
            }

            if( $result['prize_flag'] == '' ) {
                $data['prizeFlag'] = '0';
            } else {
                $data['prizeFlag']  =  $result['prize_flag'];
            }

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 用户是否首次签到
     * @param $userId
     * @return int
     */
    public function IssetUser($userId)
    {
        $id = null;
        try {
            $id = Yii::app()->cnhutong_user->createCommand()
                ->select('user_id')
                ->from('user_daily_sign')
                ->where('user_id = :userId', array(':userId' => $userId))
                ->queryScalar();
        } catch (Exception $e) {
            error_log($e);
        }
        return $id;
    }
}