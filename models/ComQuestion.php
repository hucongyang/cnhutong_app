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
        $nowTime = date("Y-m-d H-i-s", strtotime("now"));
        $data = array();
        try {
            // 用户ID验证
            $user = User::model()->IsUserId($userId);
            if(!$user) {
                return 20008;       // MSG_ERR_FAIL_USER
            }
            // 用户token验证
            $userToken = UserToken::model()->IsToken($userId, $token);
//            var_dump($userToken);exit;
            if(!$userToken) {
                return 20007;       // MSG_ERR_FAIL_TOKEN
            }
            // 验证测试类别
            $userSubject = self::IsSubject($subject);
            if(!$userSubject) {
                return 20010;       // MSG_ERR_FAIL_SUBJECT
            }

            $result = Yii::app()->cnhutong_user->createCommand()
                ->select('id, type, content, answer_a, answer_b, answer_c, answer_d, answer')
                ->from('com_question')
                ->where('subject = :subject', array(':subject' => $subject))
                ->order('rand()')
                ->limit('5')
                ->queryAll();

            $question_id = '';
            $answers = '';
            foreach($result as $value) {
                $question_id                           .= $value['id'] . '|';
                $answers                               .= $value['answer'] . '|';
            }
//            // 测试用
//            $data['question_id']                  = $question_id;
//            $data['answers']                  = $answers;

            $question_history = Yii::app()->cnhutong_user->createCommand()
                ->insert('user_question_history',
                    array(
                        'user_id' => $userId,
                        'question_id' => $question_id,
                        'answers' => $answers,
                        'create_ts' => $nowTime,
                        'score' =>  0,
                        'subject' => $subject
                    ));
            $testId = Yii::app()->cnhutong_user->getLastInsertID();
            $data['testId'] = $testId;
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
                $question_filter                        = array_filter($questions);
                $data['questions'][] = $question_filter;
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