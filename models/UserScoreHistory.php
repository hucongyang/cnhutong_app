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
     * @param $token
     * @return array|int
     */
    public function pointInfo($userId)
    {
        $data = array();
        $nowTime = date("Y-m-d H:i:s");
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
}