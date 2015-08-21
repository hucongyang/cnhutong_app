<?php

class LogUserAction extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{log_user_action}}';
    }

    /**
     * 用户操作动作记录log
     * @param $userId
     * @param $actionId
     * @param $params
     */
    public function userAction($userId, $actionId, $params)
    {
        $create_ts = date("Y-m-d H:i:s");
        try {
            Yii::app()->cnhutong_user->createCommand()
                ->insert('log_user_action',
                    array(
                        'user_id'       => $userId,
                        'action_id'     => $actionId,
                        'params'        => $params,
                        'create_ts'     => $create_ts,
                    )
                );
        } catch (Exception $e) {
            error_log($e);
        }
    }

    public function getUserAction($userId)
    {
        $data = array();
        try {
            $result = Yii::app()->cnhutong_user->createCommand()
                ->select('*')
                ->from('log_user_action')
                ->where('user_id = :userId', array(':userId' => $userId))
                ->order('create_ts desc')
                ->queryAll();

            foreach($result as $row) {
                $logs = array();
                $logs['id']              = $row['id'];
                $logs['userId']         = $row['user_id'];
                $logs['action']         = self::getActionById($row['action_id']);
                $logs['params']         = $row['params'];
                $logs['create_ts']      = $row['create_ts'];

                $data[] = $logs;
            }
        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    public function getActionById($actionId)
    {
        $action = '';
        try {
            $action = Yii::app()->cnhutong_user->createCommand()
                ->select('name')
                ->from('action')
                ->where('action_id = :actionId', array(':actionId' => $actionId))
                ->queryScalar();
        } catch (Exception $e) {
            error_log($e);
        }
        return $action;
    }
}