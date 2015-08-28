<?php

/**
 * 题库功能部分
 * Class TestController
 */
class TestController extends ApiPublicController
{
    /**
     * action_id : 2401
     * 在页面中获取每个学科（类别）所进行过测试的人数
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

        $data = UserQuestionHistory::model()->getTestUsers($userId, $token);
//        var_dump($data);exit;
        if($data === 20008) {
            $this->_return('MSG_ERR_FAIL_USER');
        } elseif ($data === 20007) {
            $this->_return('MSG_ERR_FAIL_TOKEN');
        }

        // TODO : add log
        $actionId = 2401;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2402
     * 用户获取试卷进行测试，获取5题 进行测试
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

        $data = ComQuestion::model()->getTestList($userId, $token, $subject);
        //        var_dump($data);exit;
        if($data === 20008) {
            $this->_return('MSG_ERR_FAIL_USER');
        } elseif ($data === 20007) {
            $this->_return('MSG_ERR_FAIL_TOKEN');
        } elseif ($data === 20010) {
            $this->_return('MSG_ERR_FAIL_SUBJECT');
        }

        // TODO : add log
        $actionId = 2402;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2403
     * 用户完成测试试卷后提交试卷
     * @userId $userId          -- 用户ID
     * @token $token            -- 用户验证token
     * @subject $testId       -- 本次测试对应编号
     * @answer $answer         -- 提交答案
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionPostTestAnswer()
    {
        // 检查参数
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['token']) || !isset($_REQUEST['testId']) || !isset($_REQUEST['answer']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $token = Yii::app()->request->getParam('token', NULL);
        $testId = Yii::app()->request->getParam('testId', NULL);
        $answer = Yii::app()->request->getParam('answer', NULL);

        $data = UserQuestionHistory::model()->postTestAnswer($userId, $token, $testId, $answer);
        if($data === 20008) {
            $this->_return('MSG_ERR_FAIL_USER');
        } elseif ($data === 20007) {
            $this->_return('MSG_ERR_FAIL_TOKEN');
        } elseif ($data === 20012) {
            $this->_return('MSG_ERR_FAIL_TESTID');
        } elseif ($data === 20023) {
            $this->_return('MSG_ERR_QUESTION');
        }

        // TODO : add log
        $actionId = 2403;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }
}