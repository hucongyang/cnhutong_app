<?php

/**
 * 用户提交意见反馈记录表
 * Class ComFeedback
 */
class ComFeedback extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{com_feedback}}';
    }

    /**
     * 用户在App中提交反馈意见等相关信息
     * @param $userId
     * @param $feedback
     * @return int
     */
    public function postFeedback($userId, $feedback)
    {
        $result = 0;
        $nowTime = date("Y-m-d H-i-s");              //当前时间
        try {
            $result = Yii::app()->cnhutong_user->createCommand()
                ->insert('com_feedback',
                    array(
                        'user_id' => $userId,
                        'feedback' => $feedback,
                        'create_ts' => $nowTime
                    )
                );
        } catch (Exception $e) {
            error_log($e);
        }
        return $result;
    }
}