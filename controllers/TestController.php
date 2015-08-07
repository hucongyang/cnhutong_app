<?php

/**
 * 题库功能部分
 * Class TestController
 */
class TestController extends ApiPublicController
{
    /**
     *在列表业内获取每个学科（类别）所进行过测试的人数
     * @userId $userId       -- 用户ID
     * @token $token        -- 用户验证token
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetTestUsers()
    {
        // 检查参数
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['token']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $token = Yii::app()->request->getParam('token', NULL);

        $version            = Yii::app()->request->getParam('version', NULL);
        $deviceId           = Yii::app()->request->getParam('deviceId', NULL);
        $platform           = Yii::app()->request->getParam('platform', NULL);
        $channel            = Yii::app()->request->getParam('channel', NULL);
        $appVersion         = Yii::app()->request->getParam('appVersion', NULL);
        $osVersion          = Yii::app()->request->getParam('osVersion', NULL);
        $appId              = Yii::app()->request->getParam('appId', NULL);

        $data = UserQuestionHistory::model()->getTestUsers($userId, $token);
//        var_dump($data);exit;
        if($data === 10010) {
            $this->_return('MSG_ERR_FAIL_USER');
        } elseif ($data === 10009) {
            $this->_return('MSG_ERR_FAIL_TOKEN');
        }

        // 记录log

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     *用户获取试卷进行测试，获取10题 进行测试
     * @userId $userId          -- 用户ID
     * @token $token            -- 用户验证token
     * @subject $subject        -- 对应测试类别
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetTestList()
    {
        // 检查参数
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['token']) || !isset($_REQUEST['subject']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $token = Yii::app()->request->getParam('token', NULL);
        $subject = Yii::app()->request->getParam('subject', NULL);

        $version            = Yii::app()->request->getParam('version', NULL);
        $deviceId           = Yii::app()->request->getParam('deviceId', NULL);
        $platform           = Yii::app()->request->getParam('platform', NULL);
        $channel            = Yii::app()->request->getParam('channel', NULL);
        $appVersion         = Yii::app()->request->getParam('appVersion', NULL);
        $osVersion          = Yii::app()->request->getParam('osVersion', NULL);
        $appId              = Yii::app()->request->getParam('appId', NULL);

        $data = ComQuestion::model()->getTestList($userId, $token, $subject);
        //        var_dump($data);exit;
        if($data === 10010) {
            $this->_return('MSG_ERR_FAIL_USER');
        } elseif ($data === 10009) {
            $this->_return('MSG_ERR_FAIL_TOKEN');
        } elseif ($data === 10012) {
            $this->_return('MSG_ERR_FAIL_SUBJECT');
        }

        // 记录log

        $this->_return('MSG_SUCCESS', $data);
    }
}