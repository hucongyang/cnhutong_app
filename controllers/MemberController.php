<?php

/**
 * Class MemberController
 */
class MemberController extends ApiPublicController
{

    public function actionText()
    {
        var_dump(LogMobileCheckcode::model()->getCheckNum(6,0,9));
    }
    /**
     * action_id : 2103
     * 获取验证码                actionGetVerificationCode()
     * @mobile $mobile string    --手机号码
     * @type $type int        --类型：1表示注册新用户，2表示找回密码,3表示绑定手机
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     * 根据手机号获取注册验证码（用于注册新用户,找回密码,绑定手机)
     */
    public function actionGetVerificationCode()
    {
        //检查参数
        if(!isset($_REQUEST['mobile']) || !isset($_REQUEST['verifyType'])) {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $mobile = Yii::app()->request->getParam('mobile', NUll);
        $type = Yii::app()->request->getParam('verifyType', NULL);

        if(!$this->isMobile($mobile)) {
            $this->_return('MSG_ERR_FAIL_PARAM');
        }

        $aType = array('1', '2', '3');
        if(!in_array($type, $aType)) {
            $this->_return('MSG_ERR_FAIL_PARAM');
        }
        // 根据手机号码发送验证码
        $data = LogMobileCheckcode::model()->verificationCode($mobile, $type);
//        var_dump($data);exit;
        if($data === 10002) {
            $this->_return("MSG_ERR_FAIL_PARAM");
        } elseif ($data === 20001) {
            $this->_return("MSG_ERR_INVALID_MOBILE");
        } elseif ($data === 20004) {
            $this->_return("MSG_ERR_UN_REGISTER_MOBILE");
        } elseif ($data === 20014) {
            $this->_return("MSG_ERR_INVALID_BIND_MOBILE");
        }

        // TODO : add log
        $actionId = 2103;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId = 0, $actionId, $params);

        $this->_return("MSG_SUCCESS", $data);   // $data为返回的验证码目前测试用，正式上线去除$data
    }

    /**
     * action_id : 2014
     * 用户使用手机号码获取验证码后验证验证码
     * @mobile $mobile int             --手机号码
     * @checkNum $checkNum int         --服务器发送的验证码
     */
    public function actionCheckNum()
    {
        // 检查参数
        if(!isset($_REQUEST['mobile']) || !isset($_REQUEST['checkNum'])) {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $mobile = Yii::app()->request->getParam('mobile', NULL);
        $checkNum = Yii::app()->request->getParam('checkNum', NULL);

        if(!$this->isMobile($mobile)) {
            $this->_return('MSG_ERR_FAIL_MOBILE');
        }

        // 验证根据手机号收到的验证码
        $data = User::model()->checkNum($mobile, $checkNum);
        if($data === 20001) {
            $this->_return("MSG_ERR_INVALID_MOBILE");
        } elseif ($data === 20003) {
            $this->_return("MSG_ERR_CODE_OVER_TIME");
        }

        // TODO : add log
        $actionId = 2014;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId = 0, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2105
     * 用户注册       actionRegister()
     * @mobile $mobile int             --手机号码
     * @password $password string      --密码（md5加密）
     * @checkNum $checkNum int         --服务器发送的验证码
     * @referee $referee string        --推荐人手机号
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionRegister()
    {
        // 检查参数
        if(!isset($_REQUEST['mobile']) || !isset($_REQUEST['password']) ||
            !isset($_REQUEST['checkNum']) || !isset($_REQUEST['referee'])) {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $mobile = Yii::app()->request->getParam('mobile', NULL);
        $password = Yii::app()->request->getParam('password', NULL);
        $checkNum = Yii::app()->request->getParam('checkNum', NULL);
        $referee = Yii::app()->request->getParam('referee', NULL);

        $version            = Yii::app()->request->getParam('version', NULL);
        $deviceId           = Yii::app()->request->getParam('deviceId', NULL);
        $platform           = Yii::app()->request->getParam('platform', NULL);
        $channel            = Yii::app()->request->getParam('channel', NULL);
        $appVersion         = Yii::app()->request->getParam('appVersion', NULL);
        $osVersion          = Yii::app()->request->getParam('osVersion', NULL);
        $appId              = Yii::app()->request->getParam('appId', NULL);

        if(!$this->isMobile($mobile)) {
            $this->_return('MSG_ERR_FAIL_MOBILE');
        }

        if(!empty($referee) && !$this->isMobile($referee)) {
            $this->_return('MSG_ERR_FAIL_REFEREE_MOBILE');
        }

//        if(!$this->isPasswordValid($password)) {
//            $this->_return('MSG_ERR_FAIL_PARAM');
//        }
        //根据手机号，密码，验证码注册新用户
        $data = User::model()->register($mobile, $password, $checkNum, $referee, $version, $deviceId, $platform);
        if($data === 20001) {
            $this->_return("MSG_ERR_INVALID_MOBILE");
        } elseif ($data === 20003) {
            $this->_return("MSG_ERR_CODE_OVER_TIME");
        }

        // TODO : add log
        $actionId = 2105;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId = 0, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2106
     * 用户绑定手机      actionBindMobile()
     * @mobile $mobile int           --手机号码
     * @password $password string      --密码（md5加密）
     * @checkNum $checkNum int      --服务器发送的验证码
     * @token  $token  string         --登录token
     * @userId  $userId int         --用户id(APP中用户的唯一标识)
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionBindMobile()
    {
        // 检查参数
        if(!isset($_REQUEST['mobile']) || !isset($_REQUEST['password']) ||
            !isset($_REQUEST['checkNum']) || !isset($_REQUEST['token']) ||
            !isset($_REQUEST['userId'])) {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $mobile = Yii::app()->request->getParam('mobile', NULL);
        $password = Yii::app()->request->getParam('password', NULL);
        $checkNum = Yii::app()->request->getParam('checkNum', NULL);
        $token = Yii::app()->request->getParam('token', NUll);
        $userId = Yii::app()->request->getParam('userId', NULL);
        $referee = Yii::app()->request->getParam('referee', NULL);

        if(!$this->isMobile($mobile)) {
            $this->_return('MSG_ERR_FAIL_PARAM');
        }

        // 根据手机号码，密码，验证码，登录token，用户id 绑定手机号码
        $data = User::model()->bindMobile($mobile, $password, $checkNum, $token, $userId, $referee);
        if($data === 20008) {
            $this->_return("MSG_ERR_FAIL_USER");
        } elseif ($data === 20003) {
            $this->_return("MSG_ERR_CODE_OVER_TIME");
        } elseif ($data === 20007) {
            $this->_return("MSG_ERR_FAIL_TOKEN");
        }

        // TODO : add log
        $actionId = 2106;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2107
     * 用户自动注册      actionAutoRegister()
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionAutoRegister()
    {
        // 检查参数
        $version            = Yii::app()->request->getParam('version', NULL);
        $deviceId           = Yii::app()->request->getParam('deviceId', NULL);
        $platform           = Yii::app()->request->getParam('platform', NULL);
        $channel            = Yii::app()->request->getParam('channel', NULL);
        $appVersion         = Yii::app()->request->getParam('appVersion', NULL);
        $osVersion          = Yii::app()->request->getParam('osVersion', NULL);
        $appId              = Yii::app()->request->getParam('appId', NULL);

        // 自动注册
        $data = User::model()->autoRegister($version, $deviceId, $platform, $channel, $appVersion, $osVersion, $appId);
        if($data === 20019) {
            $this->_return('MSG_MOBILE_PASSWORD_LOGIN');
        }

        // TODO : add log
        $actionId = 2107;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId = 0, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2109
     * 用户绑定学员信息      actionBindMember()
     * @salt $salt string           --绑定学员对应的口令
     * @token  $token  string       --登录token
     * @userId  $userId int         --用户id(APP中用户的唯一标识)
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionBindMember()
    {
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['token']) || !isset($_REQUEST['salt'])) {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $token = Yii::app()->request->getParam('token', NULL);
        $salt = Yii::app()->request->getParam('salt', NULL);

        // 绑定学员信息
        $data = UserMember::model()->bindMember($userId, $token, $salt);
        if($data === 20008) {
            $this->_return('MSG_ERR_FAIL_USER');
        } elseif ($data === 20007) {
            $this->_return('MSG_ERR_FAIL_TOKEN');
        } elseif ($data === 20015) {
            $this->_return('MSG_ERR_SALT');
        } elseif ($data === 20016) {
            $this->_return('MSG_ERR_INVALID_MEMBER');
        } elseif ($data === 20018) {
            $this->_return('MSG_ERR_NULL_SALT');
        } elseif ($data === 20013) {
            $this->_return('MSG_ERR_OVER_THREE_MEMBERID');
        }

        // TODO : add log
        $actionId = 2109;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2110
     * 用户解除绑定学员信息      actionRemoveMember()
     * @memberId $memberId string       --绑定学员对应ID
     * @token  $token  string       --登录token
     * @userId  $userId int         --用户id(APP中用户的唯一标识)
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionRemoveMember()
    {
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['token']) || !isset($_REQUEST['memberId'])) {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $token = Yii::app()->request->getParam('token', NULL);
        $memberId = Yii::app()->request->getParam('memberId', NULL);

        // 解除绑定学员id
        $data = UserMember::model()->removeMember($userId, $token, $memberId);
        if($data === 20008) {
            $this->_return('MSG_ERR_FAIL_USER');
        } elseif ($data === 20007) {
            $this->_return('MSG_ERR_FAIL_TOKEN');
        } elseif ($data === 20017) {
            $this->_return('MSG_ERR_FAIL_MEMBER');
        }

        // TODO : add log
        $actionId = 2110;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2101
     * 登录接口 actionLogin()  手机号码，密码登录
     * @mobile $mobile string           --注册时使用的手机号
     * @password $password string       --注册时密码
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionLogin()
    {
        if(!isset($_REQUEST['mobile']) || !isset($_REQUEST['password'])) {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $mobile = Yii::app()->request->getParam('mobile', NULL);
        $password = Yii::app()->request->getParam('password', NULL);

        if(!$this->isMobile($mobile)) {
            $this->_return('MSG_ERR_FAIL_MOBILE');
        }

//        if(!$this->isPasswordValid($password)) {
//            $this->_return('MSG_ERR_FAIL_PARAM');
//        }

        $data = User::model()->login($mobile, $password);
        if($data === 20004) {
            $this->_return('MSG_ERR_UN_REGISTER_MOBILE');
        } elseif ($data === 20005) {
            $this->_return('MSG_ERR_FAIL_PASSWORD');
        }

        // TODO : add log
        $actionId = 2101;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId = 0, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2102
     * 用户自动登录接口 actionUserVerify()  登录token(在系统中存放30天有效);用户id
     * @token $token string     --登录token
     * @userId $userId int      --用户id
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionUserVerify()
    {
        if(!isset($_REQUEST['token']) || !isset($_REQUEST['userId'])) {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $token = Yii::app()->request->getParam('token', NULL);
        $userId = Yii::app()->request->getParam('userId', NULL);

        $version            = Yii::app()->request->getParam('version', NULL);
        $deviceId           = Yii::app()->request->getParam('deviceId', NULL);
        $platform           = Yii::app()->request->getParam('platform', NULL);
        $channel            = Yii::app()->request->getParam('channel', NULL);
        $appVersion         = Yii::app()->request->getParam('appVersion', NULL);
        $osVersion          = Yii::app()->request->getParam('osVersion', NULL);
        $appId              = Yii::app()->request->getParam('appId', NULL);

        $data = User::model()->userVerify($userId, $token);
        if($data === 20008) {
            $this->_return('MSG_ERR_FAIL_USER');
        } elseif ($data === 20007) {
            $this->_return('MSG_ERR_FAIL_TOKEN');
        }

        // 登录log
        $loginIp = $this->getClientIP();
        $nowTime = date("Y-m-d H-i-s", strtotime("now"));              //当前时间
        LogUserLoginHistory::model()->log_user_login_history($userId, $loginIp, $version, $deviceId, $platform, $channel, $appVersion, $osVersion, $appId, $nowTime);

        // TODO : add log
        $actionId = 2102;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2108
     * 用户重置密码 actionResetPassword()
     * @mobile $mobile string           --注册用手机号
     * @password $password string       --密码(MD5加密)
     * @checkNum $checkNum string       --服务器验证码
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionResetPassword()
    {
        if(!isset($_REQUEST['mobile']) || !isset($_REQUEST['password']) || !isset($_REQUEST['checkNum']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $mobile = Yii::app()->request->getParam('mobile', NULL);
        $password = Yii::app()->request->getParam('password', NULL);
        $checkNum = Yii::app()->request->getParam('checkNum', NULL);

        if(!$this->isMobile($mobile)) {
            $this->_return('MSG_ERR_FAIL_PARAM');
        }

//        if(!$this->isPasswordValid($password)) {
//            $this->_return('MSG_ERR_FAIL_PARAM');
//        }
        //验证是否为合理的验证码格式  isCheckNum()

        $data = User::model()->resetPassword($mobile, $password, $checkNum);
        if($data === 20003) {
            $this->_return('MSG_ERR_CODE_OVER_TIME');
        } elseif ($data === 20004) {
            $this->_return('MSG_ERR_UN_REGISTER_MOBILE');
        }

        // TODO : add log
        $actionId = 2108;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId = 0, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2111
     * 用户在app中获取用户积分等相关信息
     * @token $token string     --登录token
     * @userId $userId int      --用户id
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetPointInfo()
    {
        if(!isset($_REQUEST['token']) || !isset($_REQUEST['userId'])) {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $token = Yii::app()->request->getParam('token', NULL);
        $userId = Yii::app()->request->getParam('userId', NULL);

        if(!ctype_digit($userId)) {
            $this->_return('MSG_ERR_FAIL_USER');
        }

        // 用户user/token验证
        $userToken = UserToken::model()->IsToken($userId, $token);
        if(!$userToken) {
            $this->_return('MSG_ERR_FAIL_TOKEN');       // MSG_ERR_FAIL_TOKEN
        }

        $data = UserScoreHistory::model()->pointInfo($userId);

        // TODO : add log
        $actionId = 2111;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2112
     * 用户在app中进行签到，以获取相关积分
     * @token $token string     --登录token
     * @userId $userId int      --用户id
     * @signType $signType int  -- 签到类型signType
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionUserDailySign()
    {
        if(!isset($_REQUEST['token']) || !isset($_REQUEST['userId']) || !isset($_REQUEST['signType'])) {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $token = Yii::app()->request->getParam('token', NULL);
        $userId = Yii::app()->request->getParam('userId', NULL);
        $signType = Yii::app()->request->getParam('signType', NULL);

        if(!ctype_digit($userId)) {
            $this->_return('MSG_ERR_FAIL_USER');
        }

        // 用户user/token验证
        $userToken = UserToken::model()->IsToken($userId, $token);
        if(!$userToken) {
            $this->_return('MSG_ERR_FAIL_TOKEN');       // MSG_ERR_FAIL_TOKEN
        }

        $aType = array('1', '2');
        if(!in_array($signType, $aType)) {
            $this->_return('MSG_ERR_SIGN_TYPE');
        }

        $data = UserDailySign::model()->dailySign($userId, $signType);
        if ($data === 20026) {
            $this->_return('MSG_ERR_INVALID_SIGN');
        }

        // TODO : add log
        $actionId = 2112;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2113
     * 用户在app中进行签到抽奖，以获取相关积分
     * @token $token string     --登录token
     * @userId $userId int      --用户id
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionUserPrizeSign()
    {
        if(!isset($_REQUEST['token']) || !isset($_REQUEST['userId'])) {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $token = Yii::app()->request->getParam('token', NULL);
        $userId = Yii::app()->request->getParam('userId', NULL);

        if(!ctype_digit($userId)) {
            $this->_return('MSG_ERR_FAIL_USER');
        }

        // 用户user/token验证
        $userToken = UserToken::model()->IsToken($userId, $token);
        if(!$userToken) {
            $this->_return('MSG_ERR_FAIL_TOKEN');       // MSG_ERR_FAIL_TOKEN
        }

        $data = UserDailySign::model()->prizeSign($userId, $token);
//        var_dump($data);exit;
        if ($data === 20027) {
            $this->_return('MSG_ERR_INVALID_PRIZE');
        }

        // TODO : add log
        $actionId = 2113;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }

    /**
     * action_id : 2114
     * 用户在app中获取积分历史
     * @token $token string     --登录token
     * @userId $userId int      --用户id
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetUserPointHistory()
    {
        if(!isset($_REQUEST['token']) || !isset($_REQUEST['userId']) || !isset($_REQUEST['historyId']) ) {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $token = Yii::app()->request->getParam('token', NULL);
        $userId = Yii::app()->request->getParam('userId', NULL);
        $historyId = Yii::app()->request->getParam('historyId', NULL);

        if(!ctype_digit($userId)) {
            $this->_return('MSG_ERR_FAIL_USER');
        }

        if(!ctype_digit($historyId)) {
            $this->_return('MSG_ERR_HISTORY_ID');
        }

        // 用户user/token验证
        $userToken = UserToken::model()->IsToken($userId, $token);
        if(!$userToken) {
            $this->_return('MSG_ERR_FAIL_TOKEN');       // MSG_ERR_FAIL_TOKEN
        }

        $data = UserScoreHistory::model()->userPointHistory($userId, $historyId);
        if($data === 20030) {
            $this->_return('MSG_ERR_NULL_HISTORY');
        }

        // TODO : add log
        $actionId = 2114;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }
}