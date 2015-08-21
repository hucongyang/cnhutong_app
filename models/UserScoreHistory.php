<?php

/**
 * 用户积分变动历史
 * Class UserScoreHistory
 */
class UserScoreHistory extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{user_score_history}}";
    }

    /**
     * 用户在App中获取用户积分等相关信息
     * @param $userId
     * @return array|int
     */
    public function pointInfo($userId)
    {
        $data = array();
        try {
            $data['point'] = self::getPoint($userId);
            $flag = UserDailySign::model()->flag($userId);
            $data['signDays']   = $flag['signDays'];
            $data['signFlag']   = $flag['signFlag'];
            $data['prizeFlag']  = $flag['prizeFlag'];

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 用户在app中获得积分历史
     *
     * @param $userId
     * @param $historyId
     * @return array
     */
    public function userPointHistory($userId, $historyId)
    {
        $data = array();
        try {
            $history = array();
            if($historyId == 0) {
                $history = Yii::app()->cnhutong_user->createCommand()
                    ->select('id, reason, change, create_ts')
                    ->from('user_score_history')
                    ->where('user_id = :userId', array(':userId' => $userId))
                    ->order('id desc')
                    ->limit('10')
                    ->queryAll();
            } else {
                $history = Yii::app()->cnhutong_user->createCommand()
                    ->select('id, reason, change, create_ts')
                    ->from('user_score_history')
                    ->where('user_id = :userId And id > :historyId', array(':userId' => $userId, ':historyId' => $historyId))
                    ->order('id desc')
                    ->limit('10')
                    ->queryAll();
            }

            if(!$history) {
                return 20030;       // MSG_ERR_NULL_HISTORY
            }

            foreach( $history as $row ) {
                $result = array();
                $result['historyId']        = $row['id'];
                $result['item']              = self::scoreChangeByReason($row['reason']);
                if( $row['change'] >= 0 ) {
                    $row['change'] = '+' . $row['change'];
                }
                $result['change']           = $row['change'];
                $result['changeTs']         = $row['create_ts'];

                $data['history'][] = $result;
            }

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 根据用户ID获得用户当前所拥有的积分值   (积分总值从user中score中获取)
     * 输入：$userId 用户id
     * 输出：$point 用户当前积分值
     * @param $userId
     * @return int
     */
    public function getPoint($userId)
    {
        $point = 0;
        try {
            $point = Yii::app()->cnhutong_user->createCommand()
                ->select('score')
                ->from('user')
                ->where('id = :userId', array(':userId' => $userId))
                ->queryScalar();
        } catch (Exception $e) {
            error_log($e);
        }
        return $point;
    }

    /**
     * 用户积分变动历史记录
     * @param $userId
     * @param $change
     * @param $reason
     * @param $scoreRest
     * @param $createTs
     * @param $memo
     * @return int
     */
    public function insertScoreHistory($userId, $change, $reason, $scoreRest, $createTs, $memo)
    {
        $result = 0;
        try {
            $result = Yii::app()->cnhutong_user->createCommand()
                ->insert('user_score_history',
                    array(
                        'user_id'       => $userId,
                        'change'        => $change,
                        'reason'        => $reason,
                        'score_rest'   => $scoreRest,
                        'create_ts'    => $createTs,
                        'memo'          => $memo
                    )
                );
        } catch (Exception $e) {
            error_log($e);
        }
        return $result;
    }

    /**
     * 用户积分总分更新
     * @param $userId
     * @param $score
     * @return int
     */
    public function updateUserScore($userId, $score)
    {
        $result = 0;
        try {
            $result = Yii::app()->cnhutong_user->createCommand()
                ->update('user',
                    array('score' => $score),
                    'id = :userId',
                    array(':userId' => $userId)
                );
        } catch (Exception $e) {
            error_log($e);
        }
        return $result;
    }

    /**
     * 输入 状态 reason 1-2
     * 输出 对应的积分变化原因 签到，抽奖等等
     * @param $reason
     * @return string
     */
    public function scoreChangeByReason($reason)
    {
        switch ($reason) {
            case "1":
                return "签到";
            case "2":
                return "抽奖";
            default:
                return "";
        }
    }
}