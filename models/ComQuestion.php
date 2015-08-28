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
     * 用户获取试卷进行测试，获取5题 进行测试
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
            $subjects = self::IsSubject();
            $userSubject = in_array($subject, $subjects);
            if(!$userSubject) {
                return 20010;       // MSG_ERR_FAIL_SUBJECT
            }

            if($subject == 1) {
                $result = Yii::app()->cnhutong_user->createCommand()
                    ->select('id, type, content, answer_a, answer_b, answer_c, answer_d, answer')
                    ->from('com_question')
                    ->order('rand()')
                    ->limit('5')
                    ->queryAll();
            } else {
                $result = Yii::app()->cnhutong_user->createCommand()
                    ->select('id, type, content, answer_a, answer_b, answer_c, answer_d, answer')
                    ->from('com_question')
                    ->where('subject = :subject', array(':subject' => $subject))
                    ->order('rand()')
                    ->limit('5')
                    ->queryAll();
            }

            $question_id = '';
            $answers = '';
            foreach($result as $value) {
                $question_id                           .= $value['id'] . '|';
                $answers                               .= $value['answer'] . '|';
            }
            $question_id = rtrim($question_id, "|");
            $answers = rtrim($answers, "|");
//            // 测试用
//            $data['question_id']                  = $question_id;
//            $data['answers']                  = $answers;

            // 生成题目记录到user_question_history
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
            // 取得插入题目的测试编号
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

            // 增加题库类型相应测试人数
            $testUsers = self::getTestUsers($subject);
            $testUsers++;       // 测试人数相应加1
            $addTestUsers = Yii::app()->cnhutong_user->createCommand()
                ->update('com_subject',
                    array('testUsers' => $testUsers),
                    'id = :subject',
                    array(':subject' => $subject)
                );

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 输入：$subject 对应测试类别
     * 输出：对应类别数组(step为1表示已有试卷，0表示没有试卷，不能做题)
     * @subject $subject
     * @return array
     */
    public function IsSubject()
    {
        $subject = array();
        try {
            $result = Yii::app()->cnhutong_user->createCommand()
                ->select('id')
                ->from('com_subject')
                ->where('step = 1')
                ->queryAll();

            foreach($result as $row) {
                $subject[] = $row['id'];
            }
        } catch (Exception $e) {
            error_log($e);
        }
        return $subject;
    }

    /**
     * 获得相应测试类型的测试人数
     * @param $subject
     * @return int
     */
    public function getTestUsers($subject)
    {
        $result = 0;
        try {
            $result = Yii::app()->cnhutong_user->createCommand()
                ->select('testUsers')
                ->from('com_subject')
                ->where('id = :subject And step = 1', array(':subject' => $subject))
                ->queryScalar();
        } catch (Exception $e) {
            error_log($e);
        }
        return $result;
    }
}