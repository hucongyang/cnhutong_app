<?php

class User extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{user}}';
    }

    /**
     * 用户使用手机号码获取验证码后验证验证码
     * @param $mobile
     * @param $checkNum
     * @return array
     */
    public function checkNum($mobile, $checkNum)
    {
        try {
            if(self::getUserByMobile($mobile)) {
                return 20001;       // MSG_ERR_INVALID_MOBILE
            }
            // 验证码是否过期
            if(!LogMobileCheckcode::model()->checkCode($mobile, $checkNum)) {
                return 20003;       //  MSG_ERR_CODE_OVER_TIME   验证码过期
            }
        } catch (Exception $e) {
            error_log($e);
        }
        return true;
    }

    /**
     * 用户使用手机号进行注册新用户
     * @param $mobile           --手机号
     * @param $password         --密码
     * @param $checkNum         --验证码
     * @param $referee          --推荐人手机号 非必填
     * @param $version
     * @param $deviceId
     * @param $platform
     * @return int
     */
    public function register($mobile, $password, $checkNum, $referee, $version, $deviceId, $platform)
    {
//        $passwordMd5 = md5($password);
        $data = array();
        $nowTime = date("Y-m-d H-i-s");              //当前时间
        try {
            if(self::getUserByMobile($mobile)) {
                return 20001;       // MSG_ERR_INVALID_MOBILE
            }
            // 验证码是否过期
            if(!LogMobileCheckcode::model()->checkCode($mobile, $checkNum)) {
                return 20003;       //  MSG_ERR_CODE_OVER_TIME   验证码过期
            }
            // 注册成功插入数据
            $registerTime = date("Y-m-d H-i-s", strtotime("now"));        //用户注册时间
            $lastLoginTime = date("Y-m-d H-i-s", strtotime("now"));       //用户最后登录时间，当前为注册时间
            $recommand = self::getUserByMobile($referee);                   // 推荐人用户ID
            if(!$recommand) {
                $recommand = null;
            } else {
                // 推荐人存在
                // 用户注册填写的推荐人积分增加 150
                $change         = 150;
                $reason         = 4;           // 积分变化类型 scoreChangeByReason($reason) 获得类型
                $scoreRest      = UserScoreHistory::model()->getPoint($recommand) + $change;
                $createTs       = $nowTime;
                $memo           = null;

                // 积分变化记录历史
                $scoreHistory = UserScoreHistory::model()->insertScoreHistory($recommand, $change, $reason, $scoreRest, $createTs, $memo);
                $scoreUpdate = UserScoreHistory::model()->updateUserScore($recommand, $scoreRest);
            }

            Yii::app()->cnhutong_user->createCommand()
                ->insert('user',
                    array(
                        'mobile' => $mobile,
                        'password' => $password,
                        'register_time' => $registerTime,
                        'last_login_time' => $lastLoginTime,
                        'recommand' => $recommand
                    ));

            //获得userId
            $userId = Yii::app()->cnhutong_user->getLastInsertID();

            //注册成功,验证码使用后改变验证码status状态
            Yii::app()->cnhutong_user->createCommand()
                ->update('log_mobile_checkcode',
                    array(
                        'status' => 1
                    ),
                    'mobile = :mobile',
                    array(':mobile' => $mobile)
                );


//            $userId = self::getUserByMobile($mobile);
//            if(!$userId) {
//                return 10006;       //  MSG_ERR_UN_REGISTER_MOBILE
//            }
            //注册成功生成token
            $token = UserToken::model()->getRandomToken(32);
            $create_ts = date("Y-m-d H-i-s", strtotime("now"));
            $expire_ts = date("Y-m-d H-i-s", strtotime("+30 day"));
            Yii::app()->cnhutong_user->createCommand()
                ->insert('user_token',
                    array(
                        'user_id' => $userId,
                        'token' => $token,
                        'type' => $platform,
                        'create_ts' => $create_ts,
                        'expire_ts' => $expire_ts
                    )
                );
            // 注册成功insert user_machine_info
            $register_ip = self::getClientIP();
            Yii::app()->cnhutong_user->createCommand()
                ->insert('user_machine_info',
                    array(
                        'user_id' => $userId,
                        'device_id' => $deviceId,
                        'platform' => $platform,
                        'version' => $version,
                        'regist_ip' => $register_ip,
                        'register_time' => $registerTime
                    )
                );

            //userId
            $data['userId'] = $userId;
            //token
            $data['token'] = UserToken::model()->getToken($userId);
            //用户昵称，积分，等级
            $userMessage = self::getUserMessageByUserId($userId);
            $data['nickname'] = $userMessage['username'];
            $data['points'] = $userMessage['score'];
            $data['level'] = $userMessage['level'];

        } catch(Exception $e) {
            error_log($e);
        }

        return $data;
    }


    /**
     * * 用户自动注册
     * 用户在打开APP进行第一次使用后进行自动注册，
     * 如果这个手机第一次使用APP则可以自动注册成功，
     * 如果不是第一次使用，且没有绑定过账号，则进行自动登录
     * 如果已使用绑定账号，则需要用户进行登录后才能进入APP使用
     *
     * @param $version              -- API版本号，版本控制使用
     * @param $deviceId            -- IMEI设备ID（open-udid）
     * @param $platform             -- 平台 IOS/Android
     * @param $channel              -- 登录渠道 baidu|91|appstore
     * @param $appVersion          -- App版本号
     * @param $osVersion           -- 操作系统版本号
     * @param $appId               -- 应用编号
     * @return array
     */
    public function autoRegister($version, $deviceId, $platform, $channel, $appVersion, $osVersion, $appId)
    {
        $data = array();
        $nowTime = date("Y-m-d H-i-s", strtotime("now"));              //当前时间
        try {
            // 第一次使用打开App，判断该机器($device_id)有没有旧账号($userId)
            $user = UserMachineInfo::model()->IsExistUserIdByDeviceId($deviceId);
            if(!$user) {
                // 没有账号，自动注册, 插入数据
                $registerTime = $nowTime;                                         //用户注册时间
                $lastLoginTime = $nowTime;                                        //用户最后登录时间，当前为注册时间
                // 伪造username
//                $username = 'User' . LogMobileCheckcode::model()->getNum();
                $username = 'User' . LogMobileCheckcode::model()->getCheckNum(6, 0, 9);
                Yii::app()->cnhutong_user->createCommand()
                    ->insert('user',
                        array(
                            'username' => $username,
                            'register_time' => $registerTime,
                            'last_login_time' => $lastLoginTime,
                        )
                    );
                // 获得刚刚自动注册的userId
//                $userId = self::getUserIdByAutoRegister();
                $userId = Yii::app()->cnhutong_user->getLastInsertID();      // 表执行update/insert之后执行获得当前表的自增id
                //注册成功生成token
                $token = UserToken::model()->getRandomToken(32);
                $create_ts = $nowTime;                                          // token 生成时间
                $expire_ts = date("Y-m-d H-i-s", strtotime("+30 day"));     // token 过期时间
                Yii::app()->cnhutong_user->createCommand()
                    ->insert('user_token',
                        array(
                            'user_id' => $userId,
                            'token' => $token,
                            'type' => $platform,
                            'create_ts' => $create_ts,
                            'expire_ts' => $expire_ts
                        )
                    );
                // 注册成功insert user_machine_info
                $register_ip = self::getClientIP();
                Yii::app()->cnhutong_user->createCommand()
                    ->insert('user_machine_info',
                        array(
                            'user_id' => $userId,
                            'device_id' => $deviceId,
                            'platform' => $platform,
                            'version' => $version,
                            'regist_ip' => $register_ip,
                            'register_time' => $registerTime
                        )
                    );
                //userId
                $data['userId'] = $userId;
                //token
                $data['token'] = UserToken::model()->getToken($userId);
                //用户昵称，积分，等级
                $userMessage = self::getUserMessageByUserId($userId);
                $data['mobile']             = $userMessage['mobile'];
                $data['nickname']           = $userMessage['username'];
                $data['points']             = $userMessage['score'];
                $data['level']              = $userMessage['level'];
                //members   新自动注册的用户是不会有member数据的，直接为空
                $data['members'] = [];

                return $data;
            } else {
                //机器中有账号,继续判断userId唯一且密码为空。则自动登录,否则跳转到账号密码登录页面
                $aUser = self::IsAutoLogin($user);
                if($aUser) {
                    // 唯一且手机号/密码为空, 未绑定手机，自动登录
                    //userId
                    $data['userId'] = $aUser;
                    //token
                    $data['token'] = UserToken::model()->getToken($aUser);
                    //用户昵称，积分，等级
                    $userMessage = self::getUserMessageByUserId($aUser);
                    $data['mobile']             = $userMessage['mobile'];
                    $data['nickname']           = $userMessage['username'];
                    $data['points']             = $userMessage['score'];
                    $data['level']              = $userMessage['level'];
                    //members
                    $data['members'] = UserMember::model()->getMembers($aUser);

                    $loginIp = self::getClientIP();
                    //自动登录 记录登录历史 log_user_login_history
                    LogUserLoginHistory::model()->log_user_login_history($aUser, $loginIp, $version, $deviceId, $platform, $channel, $appVersion, $osVersion, $appId, $nowTime);

                    return $data;
                } else {
                    // 有账号密码,绑定了手机号,账号密码登录
                    return 20019;           // MSG_MOBILE_PASSWORD_LOGIN
                }
            }

        } catch (Exception $e) {
            error_log($e);
        }
//        return $data;
    }

    /**
     * 用户绑定手机
     * @param $mobile
     * @param $password
     * @param $checkNum
     * @param $token
     * @param $userId
     * @param $referee
     * @return array
     */
    public function bindMobile($mobile, $password, $checkNum, $token, $userId, $referee)
    {
        $data = array();
        $nowTime = date("Y-m-d H:m:i");
        try {
            $user = self::IsUserId($userId);
            if(!$user) {
                return 20008;       //  MSG_ERR_FAIL_USER
            }
            $userToken = UserToken::model()->IsToken($userId, $token);
//            var_dump($userToken);exit;
            if(!$userToken) {
                return 20007;       //  MSG_ERR_FAIL_TOKEN
            }
            $mobile_checkcode = LogMobileCheckcode::model()->checkCode($mobile, $checkNum);
            if(!$mobile_checkcode) {
                return 20003;           //  MSG_ERR_CODE_OVER_TIME
            }
            // 用户手机绑定成功后 update
            $recommand = self::getUserByMobile($referee);                   // 推荐人用户ID
            if(!$recommand) {
                $recommand = null;
            }
            Yii::app()->cnhutong_user->createCommand()
                ->update('user',
                    array(
                        'mobile' => $mobile,
                        'password' => $password,
                        'recommand' => $recommand
                    ),
                    'id = :userId',
                    array(':userId' => $userId)
                );
            // 绑定手机号成功，验证码使用后改变验证码status状态
            Yii::app()->cnhutong_user->createCommand()
                ->update('log_mobile_checkcode',
                    array(
                        'status' => 1
                    ),
                    'mobile = :mobile',
                    array(':mobile' => $mobile)
                );

            //用户昵称，积分，等级
            $userMessage = self::getUserMessageByUserId($userId);
            $data['nickname']           = $userMessage['username'];
            // 绑定手机（注册）成功，增加积分
            $change         = 200;          // 绑定手机/注册 增加200积分
            $reason         = 3;           // 积分变化类型 scoreChangeByReason($reason) 获得类型
            $scoreRest      = UserScoreHistory::model()->getPoint($userId) + $change;
            $createTs       = $nowTime;
            $memo           = null;

            // 积分变化记录历史
            $scoreHistory = UserScoreHistory::model()->insertScoreHistory($userId, $change, $reason, $scoreRest, $createTs, $memo);
            $scoreUpdate = UserScoreHistory::model()->updateUserScore($userId, $scoreRest);

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 用户使用手机号/密码登录
     * @param $mobile
     * @param $password
     * @return array|int
     */
    public function login($mobile, $password)
    {
//        $passwordMd5 = md5($password);
        $data = array();
        try {
            $user = self::getUserByMobile($mobile);
            if(!$user) {
                return 20004;           // MSG_ERR_UN_REGISTER_MOBILE
            }
            $userId = self::getUserByMobilePassword($mobile, $password);
            if(!$userId) {
                return 20005;           //  MSG_ERR_FAIL_PASSWORD
            }
            //userId
            $data['userId'] = $userId;
            //token
            $data['token'] = UserToken::model()->getToken($userId);
            //用户昵称，积分，等级
            $userMessage = self::getUserMessageByUserId($userId);
            $data['mobile']             = $userMessage['mobile'];
            $data['nickname']           = $userMessage['username'];
            $data['points']             = $userMessage['score'];
            $data['level']              = $userMessage['level'];
            //members
            $data['members'] = UserMember::model()->getMembers($userId);

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 用户自动登录，token自动登录
     * @param $userId
     * @param $token
     * @return array|int
     */
    public function userVerify($userId, $token)
    {
        $data = array();
        try {
            $user = self::IsUserId($userId);
            if(!$user) {
                return 20008;       //  MSG_ERR_FAIL_USER
            }
            $userAuto = self::IsAutoLogin($userId);
            if(!$userAuto) {
                $userToken = UserToken::model()->IsToken($userId, $token);
                //            var_dump($userToken);exit;
                if(!$userToken) {
                    return 20007;       //  MSG_ERR_FAIL_TOKEN
                }
            }

            //userId
            $data['userId'] = $userId;
            //token
            $data['token'] = UserToken::model()->getToken($userId);
            //用户昵称，积分，等级
            $userMessage = self::getUserMessageByUserId($userId);
            $data['mobile']             = $userMessage['mobile'];
            $data['nickname']           = $userMessage['username'];
            $data['points']             = $userMessage['score'];
            $data['level']              = $userMessage['level'];
            //members
            $data['members'] = UserMember::model()->getMembers($userId);

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 用户忘记密码后使用手机号获得验证码重置密码
     * @param $mobile
     * @param $password
     * @param $checkNum
     * @return array|int
     */
    public function resetPassword($mobile, $password, $checkNum)
    {
//        $passwordMd5 = md5($password);
        $data = array();
        try {
            $userId = self::getUserByMobile($mobile);
            if(!$userId) {
                return 20004;           //  MSG_ERR_UN_REGISTER_MOBILE
            }
            $mobile_checkcode = LogMobileCheckcode::model()->checkCode($mobile, $checkNum);
            if(!$mobile_checkcode) {
                return 20003;           //  MSG_ERR_CODE_OVER_TIME
            }
            //手机号码已注册且验证码正确  update
            Yii::app()->cnhutong_user->createCommand()
                ->update('user',
                    array(
                        'password' => $password,
                    ),
                    'mobile = :mobile',
                    array(':mobile' => $mobile)
                );
            //修改成功,验证码使用后改变验证码status状态
            Yii::app()->cnhutong_user->createCommand()
                ->update('log_mobile_checkcode',
                    array(
                        'status' => 1
                    ),
                    'mobile = :mobile',
                    array(':mobile' => $mobile)
                );

            //userId
            $data['userId'] = $userId;
            //token
            $data['token'] = UserToken::model()->getToken($userId);
            //用户昵称，积分，等级
            $userMessage = self::getUserMessageByUserId($userId);
            $data['mobile']             = $userMessage['mobile'];
            $data['nickname']           = $userMessage['username'];
            $data['points']             = $userMessage['score'];
            $data['level']              = $userMessage['level'];
            //members
            $data['members'] = UserMember::model()->getMembers($userId);

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 根据手机号获得user id
     * @param $mobile
     * @return mixed
     */
    public function getUserByMobile($mobile)
    {
        $user = Yii::app()->cnhutong_user->createCommand()
            ->select('id')
            ->from('user')
            ->where('mobile = :mobile', array(':mobile' => $mobile))
            ->queryScalar();
        return $user;
    }

    /**
     * 根据手机号，注册密码获得 user id
     * @param $mobile
     * @param $password
     * @return mixed
     */
    public function getUserByMobilePassword($mobile, $password)
    {
        $user = Yii::app()->cnhutong_user->createCommand()
            ->select('id')
            ->from('user')
            ->where('mobile = :mobile And password = :password And status = 1', array(
                ':mobile' => $mobile,
                ':password' => $password
            ))
            ->queryScalar();
        return $user;
    }

    /**
     * 根据post参数userId判断是否为有效的userId
     * @param $userId
     * @return mixed
     */
    public function IsUserId($userId)
    {
        $user = Yii::app()->cnhutong_user->createCommand()
            ->select('id')
            ->from('user')
            ->where('id = :id And status = 1', array(':id' => $userId))
            ->queryScalar();
        return $user;
    }

    /**
     * @return mixed
     */
    public function getUserIdByAutoRegister()
    {
        $user = Yii::app()->cnhutong_user->craeteCommand()
            ->select('id')
            ->from('user')
            ->order('id desc')
            ->queryRow();
        return $user;
    }

    /*******************************************************
     * 获取连接IP
     * @author Lujia
     * @create 2013/12/26
     *******************************************************/
    public function getClientIP()
    {
        if (getenv("HTTP_CLIENT_IP"))
        {
            $ip = getenv("HTTP_CLIENT_IP");
        }
        else if(getenv("HTTP_X_FORWARDED_FOR"))
        {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        }
        else if(getenv("REMOTE_ADDR"))
        {
            $ip = getenv("REMOTE_ADDR");
        }
        else
        {
            $ip = "Unknow";
        }
        return $ip;
    }

    /**
     * 输入：App唯一标识userId
     * 输出：用户昵称，积分，等级等
     * @param $userId
     * @return array
     */
    public function getUserMessageByUserId($userId)
    {
        $userMessage = array();
        try {
            $userMessage = Yii::app()->cnhutong_user->createCommand()
                ->select('id, mobile, username, score, level')
                ->from('user')
                ->where('id = :userId', array(':userId' => $userId))
                ->queryRow();
        } catch (Exception $e) {
            error_log($e);
        }
        return $userMessage;
    }

    /**
     * 输入：App唯一标识ID
     * 输出：标识ID唯一且没有密码（没有绑定手机号）,自动登录
     * @param $userId
     * @return string
     */
    public function IsAutoLogin($userId)
    {
        $aUser = '';
        try {
            $aUser = Yii::app()->cnhutong_user->createCommand()
                ->select('id')
                ->from('user')
                ->where("id = :userId And mobile = '' And password = '' And status = 1", array(':userId' => $userId))
                ->queryScalar();
        } catch (Exception $e) {
            error_log($e);
        }
        return $aUser;
    }
}