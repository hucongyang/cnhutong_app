<?php

class LogMobileCheckcode extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{log_mobile_checkcode}}';
    }

    /**
     * 手机验证码:根据参数type类型对应操作       verificationCode
     * @param $mobile               --手机号码
     * @param $type                 1-注册    2-找回密码      3-绑定手机
     * @return int                  返回对应的信息
     */
    public function verificationCode($mobile, $type)
    {
        $nowTime = date("Y-m-d H:i:s");
        $overTime = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        //获得验证码，随机产生6位数字
//        $checkNum = self::getCheckNum(4, 0, 9);
        $checkNum = '1111';
        if($type == 1) {        //注册验证码
            try {
                if(User::model()->getUserByMobile($mobile)) {
                    return 20001;       //MSG_ERR_INVALID_MOBILE
                }
                //该手机号码第一次发送验证码，insert
                if(!self::getIsExistMobile($mobile)) {
                    $mobile_checkcode_insert = Yii::app()->cnhutong_user->createCommand()
                        ->insert('log_mobile_checkcode',
                            array(
                                'mobile' => $mobile,
                                'checknum' => $checkNum,
                                'create_ts' => $nowTime,
                                'expire_ts' => $overTime,
                                'status' => 0
                            )
                        );
                } else {
                    //手机号码已发送验证码，再次发送, update
                    $mobile_checkcode_update = Yii::app()->cnhutong_user->createCommand()
                        ->update('log_mobile_checkcode',
                            array(
                                'checknum' => $checkNum,
                                'create_ts' => $nowTime,
                                'expire_ts' => $overTime,
                                'status' => 0
                            ),
                            'mobile = :mobile',
                            array(':mobile' => $mobile)
                        );
                }
            } catch (Exception $e) {
                error_log($e);
            }
        } elseif ($type == 2) {     //找回密码验证码
            try {
                if(!User::model()->getUserByMobile($mobile)) {
                    return 20004;        //MSG_ERR_UN_REGISTER_MOBILE
                }
                // 该手机号码不在mobile_checkcode表里面(ps:删除了) insert
                if(!self::getIsExistMobile($mobile)) {
                    $mobile_checkcode_insert = Yii::app()->cnhutong_user->createCommand()
                        ->insert('log_mobile_checkcode',
                            array(
                                'mobile' => $mobile,
                                'checknum' => $checkNum,
                                'create_ts' => $nowTime,
                                'expire_ts' => $overTime,
                                'status' => 0
                            )
                        );
                } else {
                    // 手机号码已存在mobile_checknum表里面 update
                    $mobile_checkcode = Yii::app()->cnhutong_user->createCommand()
                        ->update('log_mobile_checkcode',
                            array(
                                'checknum' => $checkNum,
                                'create_ts' => $nowTime,
                                'expire_ts' => $overTime,
                                'status' => 0
                            ),
                            'mobile = :mobile',
                            array(':mobile' => $mobile)
                        );
                }
            } catch (Exception $e) {
                error_log($e);
            }
        } elseif ($type == 3) {        //  绑定手机发送验证码
            try {
                if(User::model()->getUserByMobile($mobile)) {
                    return 20014;       //  MSG_ERR_INVALID_BIND_MOBILE
                }
                //该手机号码第一次发送验证码，insert
                if(!self::getIsExistMobile($mobile)) {
                    $mobile_checkcode_insert = Yii::app()->cnhutong_user->createCommand()
                        ->insert('log_mobile_checkcode',
                            array(
                                'mobile' => $mobile,
                                'checknum' => $checkNum,
                                'create_ts' => $nowTime,
                                'expire_ts' => $overTime,
                                'status' => 0
                            )
                        );
                } else {
                    //手机号码已发送验证码，再次发送, update
                    $mobile_checkcode_update = Yii::app()->cnhutong_user->createCommand()
                        ->update('log_mobile_checkcode',
                            array(
                                'checknum' => $checkNum,
                                'create_ts' => $nowTime,
                                'expire_ts' => $overTime,
                                'status' => 0
                            ),
                            'mobile = :mobile',
                            array(':mobile' => $mobile)
                        );
                }
            } catch (Exception $e) {
                error_log($e);
            }
        } else {
            return 10002;       //MSG_ERR_FAIL_PARAM
        }

        // 大汉三通发送验证码短信接口实现
        $account = "dh21317";       // 账号
        $password = "64524df26b97a865accddfa4e8b3b684";         // 密码
        $phone = "15201920323";     // 号码
        $content = "您好，您的验证码为1111";           // 内容
        $sign = "";                     // 短信签名
        $sendTime = "";                 // 发送时间
        $contentXml = self::postCheckNum($account, $password, $phone, $content, $sign, $sendTime);
        // 获取发送短信响应状态码
        $status = self::getStatusByXml($contentXml);
//        $status = explode("</result>", explode("<result>", $contentXml)[1])[0];
//        var_dump($status);
//        var_dump($contentXml);exit;
        // 记录发送短信日志
        self::insertPostCheckNum($mobile, $nowTime, $status, $type);

        return $checkNum;       // 返回的验证码目前测试用，正式上线去除
    }

    /**
     * 记录发送短信日志
     * @param $mobile
     * @param $send_ts
     * @param $status
     * @param $send_type
     * @return int
     */
    public function insertPostCheckNum($mobile, $send_ts, $status, $send_type)
    {
        $result = 0;
        try {
            $result = Yii::app()->cnhutong_user->createCommand()
                ->insert('log_sms_send_history',
                    array(
                        'mobile' => $mobile,
                        'send_ts' => $send_ts,
                        'status' => $status,
                        'send_type' => $send_type
                    )
                );
        } catch (Exception $e) {
            error_log($e);
        }
        return $result;
    }

    /**
     * 大汉三通短信云接口手册V1.3.4
     * 4.2.1 短信下行
     * 访问地址：http://http://3tong.net/http/sms/Submit
     * 提交方式：POST
     * @param $account                      -- 用户帐号
     * @param $password                     -- 账号密码
     * @param $phone                        -- 接手手机号码，多个手机号码用英文逗号分隔，最多500个
     * @param $content                      -- 短信内容，最多350个汉字（包含签名长度）
     * @param string $sign                  -- 短信签名，默认为 【秦汉胡同】
     * @param string $sendTime              -- 定时发送时间 格式yyyyMMddHHmm 为空或早于当前时间则立即发送
     * @return string                       -- xml
     */
    public function postCheckNum($account, $password, $phone, $content, $sign = "", $sendTime = "")
    {
        $sendSmsAddress = "http://3tong.net/http/sms/Submit";
        $message ="<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
            ."<message>"
            . "<account>"
            . $account
            . "</account><password>"
            . $password
            . "</password>"
            . "<msgid></msgid><phones>"
            . $phone
            . "</phones><content>"
            . $content
            . "</content><subcode>"
            ."</subcode><sign>"
            . $sign
            . "</sign><sendtime>"
            . $sendTime
            . "</sendtime>"
            ."</message>";

        $params = array(
            'message' => $message);

        $data = http_build_query($params);
        $context = array('http' => array(
            'method' => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $data,
        ));
        $contents = file_get_contents($sendSmsAddress, false, stream_context_create($context));
        return $contents;
    }

    /**
     * 解析短信响应状态码
     * @param $stringXml
     * @return string
     */
    public function getStatusByXml($stringXml)
    {
        $xml = simplexml_load_string($stringXml);
        $status = (string)$xml->result;
        return $status;
    }

    /**
     * 短信提交响应错误码
     * @param $status
     * @return string
     */
    public function statusDesc($status)
    {
        switch ($status) {
            case 0:
                return "提交成功";
            case 1:
                return "账号无效";
            case 2:
                return "密码错误";
            case 3:
                return "msgid太长，不得超过32位";
            case 5:
                return "手机号码个数超过最大限制（500个）";
            case 6:
                return "短信内容超过最大限制（350字）";
            case 7:
                return "扩展子号码无效";
            case 8:
                return "定时时间格式错误";
            case 14:
                return "手机号码为空";
            case 19:
                return "用户被禁发或禁用";
            case 20:
                return "ip鉴权失败";
            case 21:
                return "短信内容为空";
            case 22:
                return "数据包大小不匹配";
            case 98:
                return "系统正忙";
            case 99:
                return "消息格式错误";
        }
    }

    /**
     * 随机生成6位数字验证码
     * @return array
     */
    public function getNum()
    {
        $range = range(100000, 999999);
        return array_rand($range, 1);
    }

    /**
     * 生成验证码
     * @param $num int 位数
     * @param $start int 开始值
     * @param $end int 结束值
     * @return string 生成的数字验证码
     */
    public function getCheckNum($num, $start, $end)
    {
        $range= range($start, $end);
        $arr =array();
        for($i = 0; $i < $num; $i++) {
            $arr[] = array_rand($range);
        }
        return implode("", $arr);
    }

    /**
     * 查找mobile_checkcode表里面是否存在$mobile
     * @param $mobile string 手机号码
     * @return bool
     */
    public function getIsExistMobile($mobile)
    {
        $mobile_id = Yii::app()->cnhutong_user->createCommand()
            ->select('id')
            ->from('log_mobile_checkcode')
            ->where('mobile = :mobile', array(':mobile' => $mobile))
            ->queryScalar();
        if(!$mobile_id) {
            return false;
        }
        return true;
    }

    /**
     * 根据$mobile, $checknum 判断验证码是否过期
     * @param $mobile
     * @param $checknum
     * @return bool
     */
    public function checkCode($mobile, $checknum)
    {
        $nowTime = strtotime("now");    //当前时间戳
        $check = Yii::app()->cnhutong_user->createCommand()
            ->select('id, create_ts, expire_ts')
            ->from('log_mobile_checkcode')
            ->where('mobile = :mobile And checknum = :checknum',
                array(
                    ':mobile' => $mobile,
                    ':checknum' => $checknum
                )
            )
            ->queryRow();
        if(!$check) {
            return false;
        }
        $check_create = strtotime($check['create_ts']);
        $check_expire = strtotime($check['expire_ts']);
        if($nowTime < $check_create || $nowTime > $check_expire) {
            return false;
        }
        return true;
    }
}