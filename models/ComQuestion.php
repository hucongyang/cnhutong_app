<?php

class ComQuestion extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{com_question}}';
    }

    /**
     * 用户获取试卷进行测试，获取10题 进行测试
     * @param $userId
     * @param $token
     * @param $subject
     * @return array|int
     */
    public function getTestList($userId, $token, $subject)
    {
        $data = array();
        try {
            // 用户ID验证
            $user = User::model()->IsUserId($userId);
            if(!$user) {
                return 10010;       // MSG_ERR_FAIL_USER
            }
            // 用户token验证
            $userToken = UserToken::model()->IsToken($userId, $token);
//            var_dump($userToken);exit;
            if(!$userToken) {
                return 10009;       // MSG_ERR_FAIL_TOKEN
            }
            // 验证测试类别
            $userSubject = self::IsSubject($subject);
            if(!$userSubject) {
                return 10012;       // MSG_ERR_FAIL_SUBJECT
            }

            $result = Yii::app()->cnhutong_user->createCommand()
                ->select('id, type, content, answer_a, answer_b, answer_c, answer_d')
                ->from('com_question')
                ->where('subject = :subject', array(':subject' => $subject))
                ->limit('10')
                ->queryAll();

            // 判断数据是否为空数组
            if(ApiPublicController::array_is_null($result)) {
                $data[] = [];
            }

            foreach($result as $row) {
                // 获取数据
                $questions = array();
                $questions['questionId']             = $row['id'];
                $questions['questionType']           = $row['type'];
                $questions['content']                 = $row['content'];
                $questions['answerA']                 = $row['answer_a'];
                $questions['answerB']                 = $row['answer_b'];
                $questions['answerC']                 = $row['answer_c'];
                $questions['answerD']                 = $row['answer_d'];
                $data[] = $questions;
            }

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 输入：$subject 对应测试类别
     * 输出：$id 对应类别是否存在
     * @param $subject
     * @return string
     */
    public function IsSubject($subject)
    {
        $id = '';
        try {
            $id = Yii::app()->cnhutong_user->createCommand()
                ->select('id')
                ->from('com_question')
                ->where('subject = :subject', array(':subject' => $subject))
                ->queryScalar();
        } catch (Exception $e) {
            error_log($e);
        }
        return $id;
    }
}