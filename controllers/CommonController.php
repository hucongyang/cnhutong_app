<?php

class CommonController extends ApiPublicController
{

    /**
     * 获取校区列表               getAllSchools
     * @userId  $userId int         -- 用户ID
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetAllSchools()
    {
        // 非必须
        $userId = Yii::app()->request->getParam('userId', NULL);


        $deviceId           = Yii::app()->request->getParam('deviceId', NULL);
        $platform           = Yii::app()->request->getParam('platform', NULL);
        $channel            = Yii::app()->request->getParam('channel', NULL);
        $appVersion         = Yii::app()->request->getParam('appVersion', NULL);
        $osVersion          = Yii::app()->request->getParam('osVersion', NULL);
        $appId              = Yii::app()->request->getParam('appId', NULL);

        $data = ComDepartment::model()->getAllSchools();
        
        // TODO : add log
        
        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * 获取校区详细列表      getSchoolInfo()
     * @userId userId int 用户id          --非必填
     * @departmentId departmentId int    --校区对应ID 必填
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetSchoolInfo()
    {
        if(!isset($_REQUEST['departmentId']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }
        // 非必须
        $userId = Yii::app()->request->getParam('userId', NULL);
        // 必须
        $departmentId = Yii::app()->request->getParam('departmentId', NULL);

        $data = array();


        $version            = Yii::app()->request->getParam('version', NULL);
        $deviceId           = Yii::app()->request->getParam('deviceId', NULL);
        $platform           = Yii::app()->request->getParam('platform', NULL);
        $channel            = Yii::app()->request->getParam('channel', NULL);
        $appVersion         = Yii::app()->request->getParam('appVersion', NULL);
        $osVersion          = Yii::app()->request->getParam('osVersion', NULL);
        $appId              = Yii::app()->request->getParam('appId', NULL);  $school = ComDepartment::model()->getSchoolInfo($departmentId);
        if(!$school) {
            $this->_return('MSG_ERR_FAIL_DEPARTMENT');
        }
        $data['id'] = $school['id'];
        $data['name'] = $school['name'];
        $data['busLine'] = $school['bus_line'];
        $data['parking'] = $school['parking'];
        $pictures = ComDepartmentPicture::model()->getSchoolInfoPicture($departmentId);
        if(!$pictures) {
            $data['pictures'] = [''];
//            $this->_return('MSG_ERR_FAIL_DEPARTMENT');
        }
        $data['pictures'] = $pictures;
        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * 用户在APP中提交试听等相关的信息
     * @userId $userId
     * @name $name
     * @mobile $mobile
     * @subject $subject
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionPostAppointment()
    {
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['name']) || !isset($_REQUEST['mobile'])
        || !isset($_REQUEST['subject'])) {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId             = Yii::app()->request->getParam('userId', NULL);
        $name               = Yii::app()->request->getParam('name', NULL);
        $mobile             = Yii::app()->request->getParam('mobile', NULL);
        $subject            = Yii::app()->request->getParam('subject', NUll);

        if(!ctype_digit($userId)) {
            $this->_return('MSG_ERR_FAIL_USER');
        }

        if(!$this->isMobile($mobile)) {
            $this->_return('MSG_ERR_FAIL_MOBILE');
        }

        if(!ctype_digit($subject)) {
            $this->_return('MSG_ERR_FAIL_SUBJECT');
        }

        $data = ComAppointment::model()->postAppointment($userId, $name, $mobile, $subject);
        if(!$data) {
        	$this->_return('MSG_ERR_UNKOWN');
        }

        $this->_return('MSG_SUCCESS');
    }

    /**
     * 用户在APP启动时获取APP所对应最新的版本号及广告信息
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetCommonInfo()
    {
        $data = array();

        $data['ads'] = ComAds::model()->getAds();
        $version = ComChannel::model()->getAppVersion();

        // print_r($version); exit;
        $data['lastVersion'] = $version['lastVersion'];
        $data['updateVersion'] = $version['updateVersion'];
        $data['downloadUrl'] = $version['downloadUrl'];

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * 用户在App中提交反馈意见等相关信息
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionPostFeedback()
    {
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['feedback'])) {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId             = Yii::app()->request->getParam('userId', NULL);
        $feedback           = Yii::app()->request->getParam('feedback', NULL);

        if(!ctype_digit($userId)) {
            $this->_return('MSG_ERR_FAIL_USER');
        }

        $encode = 'UTF-8';
        $str_num = mb_strlen($feedback, $encode);
        if( $str_num <= 0 || $str_num > 200 ) {
            $this->_return('MSG_ERR_FEEDBACK');
        }

        $data = ComFeedback::model()->postFeedback($userId, $feedback);
        if(!$data) {
            $this->_return('MSG_ERR_UNKOWN');
        }

        $this->_return('MSG_SUCCESS');
    }
}