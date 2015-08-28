<?php

class LessonController extends ApiPublicController
{
    /**
     * action_id : 2301
     * 获取学员所有正在进行课程的下一次上课时间 actionGetNextLessonList(),获取课程提醒
     * @userId $userId       -- 用户ID
     * @token $token        -- 用户验证token
     * @memberId $memberId     -- 用户当前绑定的学员所对应的ID
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetNextLessonList()
    {
        // 检查参数
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['token']) || !isset($_REQUEST['memberId']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $token = Yii::app()->request->getParam('token', NULL);
        $memberId = Yii::app()->request->getParam('memberId', NULL);

        $data = HtLessonStudent::model()->nextLessonList($userId, $token, $memberId);
//        var_dump($data);exit;
        if($data === 20008) {
            $this->_return('MSG_ERR_FAIL_USER');
        } elseif ($data === 20007) {
            $this->_return('MSG_ERR_FAIL_TOKEN');
        } elseif ($data === 20017) {
            $this->_return('MSG_ERR_FAIL_MEMBER');
        }

        // TODO : add log
        $actionId = 2301;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2302
     * 获取学员所有课程，包括正在学习的课程和历史课程  actionGetAllSubjects()
     * @userId $userId       -- 用户ID
     * @token $token        -- 用户验证token
     * @memberId $memberId     -- 用户当前绑定的学员所对应的ID
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetAllSubjects()
    {
        // 检查参数
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['token']) || !isset($_REQUEST['memberId']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $token = Yii::app()->request->getParam('token', NULL);
        $memberId = Yii::app()->request->getParam('memberId', NULL);

        $data = HtLessonStudent::model()->allSubjects($userId, $token, $memberId);
        if($data === 20008) {
            $this->_return('MSG_ERR_FAIL_USER');
        } elseif ($data === 20007) {
            $this->_return('MSG_ERR_FAIL_TOKEN');
        } elseif ($data === 20017) {
            $this->_return('MSG_ERR_FAIL_MEMBER');
        }

        // TODO : add log
        $actionId = 2302;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2303
     * 获取学员指定课程的具体课时详情(课程表)
     * @param userId        -- 用户ID
     * @param token         -- 用户验证token
     * @param memberId      -- 用户当前绑定的学员所对应的ID
     * @param lessonArrangeId  -- 课程的唯一排课编号
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetSubjectSchedule()
    {
        // 检查参数
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['token'])
            || !isset($_REQUEST['memberId']) || !isset($_REQUEST['lessonArrangeId']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $token = Yii::app()->request->getParam('token', NULL);
        $memberId = Yii::app()->request->getParam('memberId', NULL);
        $lessonArrangeId = Yii::app()->request->getParam('lessonArrangeId', NULL);

        $data = HtLessonStudent::model()->SubjectSchedule($userId, $token, $memberId, $lessonArrangeId);
        if($data === 20008) {
            $this->_return('MSG_ERR_FAIL_USER');
        } elseif ($data === 20007) {
            $this->_return('MSG_ERR_FAIL_TOKEN');
        } elseif ($data === 20017) {
            $this->_return('MSG_ERR_FAIL_MEMBER');
        } elseif ($data === 20020) {
            $this->_return('MSG_ERR_LESSON_ARRANGE_ID');
        }

        // TODO : add log
        $actionId = 2303;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2304
     * 获取学员指定课时的具体详情
     * @param userId        -- 用户ID
     * @param token         -- 用户验证token
     * @param memberId      -- 用户当前绑定的学员所对应的ID
     * @param lessonStudentId  -- 课程的唯一排课编号
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetLessonDetails()
    {
        // 检查参数
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['token'])
            || !isset($_REQUEST['memberId']) || !isset($_REQUEST['lessonArrangeId']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $token = Yii::app()->request->getParam('token', NULL);
        $memberId = Yii::app()->request->getParam('memberId', NULL);
        $lessonStudentId = Yii::app()->request->getParam('lessonStudentId', NULL);

        $data = HtLessonStudent::model()->lessonDetails($userId, $token, $memberId, $lessonStudentId);
        if($data === 20008) {
            $this->_return('MSG_ERR_FAIL_USER');
        } elseif ($data === 20007) {
            $this->_return('MSG_ERR_FAIL_TOKEN');
        } elseif ($data === 20017) {
            $this->_return('MSG_ERR_FAIL_MEMBER');
        } elseif ($data === 20021) {
            $this->_return('MSG_ERR_LESSON_STUDENT_ID');
        }

        // TODO : add log
        $actionId = 2304;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2305
     * 学员对上过的课时进行评价和打分
     * @param userId        -- 用户ID
     * @param token         -- 用户验证token
     * @param memberId      -- 用户当前绑定的学员所对应的ID
     * @param lessonStudentId  -- 课程的唯一排课编号
     * @param teachAttitude                 -- 学员给教学态度的评分，1-5分
     * @param teachContent                  -- 学员给教学内容的评分，1-5分
     * @param teachEnvironment              -- 学员给教学环境的评分，1-5分
     * @param statement         -- 学员给课时的评价
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionEvalLessonStudent()
    {
        // 检查参数
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['token'])
            || !isset($_REQUEST['memberId']) || !isset($_REQUEST['lessonStudentId'])
            || !isset($_REQUEST['teachAttitude']) || !isset($_REQUEST['teachContent'])
            || !isset($_REQUEST['teachEnvironment']) || !isset($_REQUEST['statement']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $token = Yii::app()->request->getParam('token', NULL);
        $memberId = Yii::app()->request->getParam('memberId', NULL);
        $lessonStudentId = Yii::app()->request->getParam('lessonStudentId', NULL);
        $teachAttitude = Yii::app()->request->getParam('teachAttitude', NUll);
        $teachContent = Yii::app()->request->getParam('teachContent', NUll);
        $teachEnvironment = Yii::app()->request->getParam('teachEnvironment', NUll);
        $stateComment = Yii::app()->request->getParam('statement', NUll);

        $data = HtLessonStudent::model()->lessonStudent($userId, $token, $memberId, $lessonStudentId, $teachAttitude, $teachContent, $teachEnvironment, $stateComment);
        if($data === 20008) {
            $this->_return('MSG_ERR_FAIL_USER');
        } elseif ($data === 20007) {
            $this->_return('MSG_ERR_FAIL_TOKEN');
        } elseif ($data === 20017) {
            $this->_return('MSG_ERR_FAIL_MEMBER');
        } elseif ($data === 20021) {
            $this->_return('MSG_ERR_LESSON_STUDENT_ID');
        } elseif ($data === 20022) {
            $this->_return('MSG_ERR_SCORE');
        } elseif ($data === 20024) {
            $this->_return('MSG_ERR_INVALID_EVAL');
        }

        // TODO : add log
        $actionId = 2305;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2306
     * 学员在APP中对自己的课时进行请假或者取消请假的操作
     * @param userId        -- 用户ID
     * @param token         -- 用户验证token
     * @param memberId      -- 用户当前绑定的学员所对应的ID
     * @param lessonStudentId  -- 课程的唯一排课编号
     * @param leaveType            -- 请假类型 1表示请假，2表示取消请假
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionLessonStudentLeave()
    {
        // 检查参数
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['token'])
            || !isset($_REQUEST['memberId']) || !isset($_REQUEST['lessonStudentId'])
            || !isset($_REQUEST['leaveType']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $token = Yii::app()->request->getParam('token', NULL);
        $memberId = Yii::app()->request->getParam('memberId', NULL);
        $lessonStudentId = Yii::app()->request->getParam('lessonStudentId', NULL);
        $leaveType = Yii::app()->request->getParam('leaveType', NUll);

        $aType = array(1, 2);
        if(!in_array($leaveType, $aType)) {
            $this->_return('MSG_ERR_LEAVE_TYPE');
        }

        $data = HtLessonStudent::model()->lessonStudentLeave($userId, $token, $memberId, $lessonStudentId, $leaveType);
        if($data === 20008) {
            $this->_return('MSG_ERR_FAIL_USER');
        } elseif ($data === 20007) {
            $this->_return('MSG_ERR_FAIL_TOKEN');
        }

        // TODO : add log
        $actionId = 2306;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }
}