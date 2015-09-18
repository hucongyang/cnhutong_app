<?php

class HttpController extends ApiPublicController
{
    public function actionHttp()
    {
        $account = "dh21317";       // 账号
        $password = "64524df26b97a865accddfa4e8b3b684";         // 密码
        $phone = "15201920325";     // 号码
        $content = "您好，您的验证码为1111";           // 内容
        $sign = "";                     // 短信签名
        $sendTime = "";                 // 发送时间

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
//        echo $contents;

        $this->_return("MSG_SUCCESS", $contents);
    }

    public function actionBaiduXunke()
    {
        $nowTime = date("Y-m-d H:i:s");
//        $fileContent = file_get_contents("php://input");
        $xml = simplexml_load_string("<?xml version='1.0' encoding='UTF-8'?><TXTbaidu><TXTbaiduRequest><userId>cnhutong</userId><password>111111</password></TXTbaiduRequest><body><tempBase><Mediasource>baidu-xunke</Mediasource><Device>Wise</Device><Query>英语培训哪里好</Query><cityName>上海</cityName><cityCode>300310</cityCode><teleNum> 13500998811</teleNum><Name>XXX先生</Name><Class>英语冲刺班</Class></tempBase></body></TXTbaidu>");
//        var_dump($fileContent);
//        var_dump($xml);
        $userId = (string)$xml->TXTbaiduRequest->userId;
        $password = (string)$xml->TXTbaiduRequest->password;
        $mediaSource = (string)$xml->body->tempBase->Mediasource;
//        var_dump($mediaSource);exit;
        if($userId == 'cnhutong' && $password == '111111' && $mediaSource == 'baidu-xunke')
        {
            $device = (string)$xml->body->tempBase->Device;
            $Query = (string)$xml->body->tempBase->Query;
            $cityName = (string)$xml->body->tempBase->cityName;
            $cityCode = (string)$xml->body->tempBase->cityCode;
            $teleNum = (string)$xml->body->tempBase->teleNum;
            $name = (string)$xml->body->tempBase->Name;
            $class = (string)$xml->body->tempBase->Class;

            try {
                $result = Yii::app()->cnhutong_user->createCommand()
                    ->insert('baidu_xunke',
                        array(
                            'device' => $device,
                            'query' => $Query,
                            'cityName' => $cityName,
                            'cityCode' => $cityCode,
                            'teleNum' => $teleNum,
                            'name' => $name,
                            'class' => $class,
                            'create_ts' => $nowTime
                        )
                    );

                if(!$result) {
                    $this->_return("MSG_SUCCESS", '数据库插入数据错误');
                }
                $this->_return("MSG_SUCCESS", $result);
            } catch (Exception $e) {
                error_log($e);
            }
        } else {
            $this->_return("MSG_SUCCESS", '用户名密码错误');
        }
    }
}