<?php

/**
 * 产品中心模块
 * Class ProductController
 */
class ProductController extends ApiPublicController
{
    /**
     * action_id : 2501
     * 获取秦汉胡同所有老师的列表
     * @param userId        -- 用户ID
     * @param search         -- 搜索字符，即老师的姓名
     * @param subject        -- 课程id
     * @param department     -- 校区id
     * @param teacherId      -- 教师id
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetAllTeachers()
    {
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['search']) ||
        !isset($_REQUEST['subject']) || !isset($_REQUEST['department']) ||
        !isset($_REQUEST['teacherId']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $search = trim(Yii::app()->request->getParam('search', ''));
        $subject = Yii::app()->request->getParam('subject', NULL);
        $department = Yii::app()->request->getParam('department', NULL);
        $teacherId = Yii::app()->request->getParam('teacherId', NULL);

        if(!ctype_digit($userId)) {
            $this->_return('MSG_ERR_FAIL_USER');
        }

        if(!ctype_digit($subject)) {
            $this->_return('MSG_ERR_FAIL_SUBJECT');
        }

        if(!ctype_digit($department)) {
            $this->_return('MSG_ERR_FAIL_DEPARTMENT');
        }

        if(!ctype_digit($teacherId)) {
            $this->_return('MSG_ERR_FAIL_TEACHER_ID');
        }

        $data = ComTeacher::model()->getAllTeachers($search, $subject, $department, $teacherId);
        if($data === 20035) {
            $this->_return('MSG_ERR_NULL_TEACHER');
        }

        // TODO : add log
        $actionId = 2501;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2502
     * 获取秦汉胡同老师的详细信息
     * @param userId        -- 用户ID
     * @param teacherId     -- 教师对应ID
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetTeacherInfo()
    {
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['teacherId']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $teacherId = Yii::app()->request->getParam('teacherId', NULL);

        if(!ctype_digit($userId)) {
            $this->_return('MSG_ERR_FAIL_USER');
        }

        if(!ctype_digit($teacherId)) {
            $this->_return('MSG_ERR_FAIL_TEACHER_ID');
        }

        $data = ComTeacher::model()->getTeacherInfo($teacherId);
        if($data === 20035) {
            $this->_return('MSG_ERR_NULL_TEACHER');
        }

        // TODO : add log
        $actionId = 2502;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2503
     * 获取秦汉胡同老师所教课程的学生评价
     * @param userId        -- 用户ID
     * @param teacherId     -- 教师对应ID
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionEvalTeacherStudent()
    {
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['teacherId']) ||
        !isset($_REQUEST['evalId']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $teacherId = Yii::app()->request->getParam('teacherId', NULL);
        $evalId = Yii::app()->request->getParam('evalId', NULL);

        if(!ctype_digit($userId)) {
            $this->_return('MSG_ERR_FAIL_USER');
        }

        if(!ctype_digit($teacherId)) {
            $this->_return('MSG_ERR_FAIL_TEACHER_ID');
        }

        if(!ctype_digit($evalId)) {
            $this->_return('MSG_ERR_FAIL_EVAL_ID');
        }

        $data = ComTeacher::model()->evalTeacherStudent($teacherId);
        if($data === 20035) {
            $this->_return('MSG_ERR_NULL_TEACHER');
        }

        // TODO : add log
        $actionId = 2503;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2504
     * 获取秦汉胡同老师作品的列表
     * @param userId        -- 用户ID
     * @param teacherId     -- 教师对应ID
     * @param produceId     -- 作品对应ID
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetTeacherProducts()
    {
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['teacherId']) ||
            !isset($_REQUEST['productId']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $teacherId = Yii::app()->request->getParam('teacherId', NULL);
        $productId = Yii::app()->request->getParam('productId', NULL);

        if(!ctype_digit($userId)) {
            $this->_return('MSG_ERR_FAIL_USER');
        }

        if(!ctype_digit($teacherId)) {
            $this->_return('MSG_ERR_FAIL_TEACHER_ID');
        }

        if(!ctype_digit($productId)) {
            $this->_return('MSG_ERR_FAIL_PRODUCE_ID');
        }

        $data = ComTeacher::model()->getTeacherProducts($teacherId, $productId);

        // TODO : add log
        $actionId = 2504;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2505
     * 获取秦汉胡同老师作品详情
     * @param userId        -- 用户ID
     * @param produceId     -- 作品对应ID
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetTeacherProductInfo()
    {
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['productId']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $productId = Yii::app()->request->getParam('productId', NULL);

        if(!ctype_digit($userId)) {
            $this->_return('MSG_ERR_FAIL_USER');
        }

        if(!ctype_digit($productId)) {
            $this->_return('MSG_ERR_FAIL_PRODUCE_ID');
        }

        $data = ComTeacher::model()->getTeacherProductInfo($productId);

        // TODO : add log
        $actionId = 2504;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }
}