<?php

class UserQuestionHistory extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{user_question_history}}";
    }

    /**
     * 在列表页内获取每个学科（类别）所进行过测试的人数
     * @param $userId
     * @param $token
     * @return array
     */
    public function getTestUsers($userId, $token)
    {
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

            $result = Yii::app()->cnhutong_user->createCommand()
                ->select('COUNT(*) as testUsers, subject')
                ->from('user_question_history')
                ->group('subject')
                ->order('subject asc')
                ->queryAll();

            // 判断数据是否为空数组
            if(ApiPublicController::array_is_null($result)) {
                $data[] = [];
            }

            foreach($result as $row) {
                //获取数据
                $subjects = array();

                $subjects['subjectId']              = $row['subject'];
                $subjectInfo                          = ApiPublicLesson::model()->getSubjectInfoById($row['subject']);
                $subjects['subjectName']            = $subjectInfo['title'];
                $subjects['testUsers']              = $row['testUsers'];
                $data[] = $subjects;
            }

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 用户完成测试试卷后提交试卷
     * @param $userId
     * @param $token
     * @param $testId
     * @param $answer
     * @return array
     */
    public function postTestAnswer($userId, $token, $testId, $answer)
    {
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
            // 测试编号testId验证
            $userTestId = self::IsTestId($testId);
            if(!$userTestId) {
                return 20012;       // MSG_ERR_FAIL_TESTID
            }

            $question_answer = explode("|", $answer);
            var_dump($question_answer);
            $len = count($question_answer);
            var_dump($len);
            $questions = array();
            for($i = 0; $i < $len; $i++) {
                $questions[$i] = explode('-',$question_answer[$i]);
            }
            $q = '';
            $a = '';
            foreach($questions as $row) {
                $q .= $row[0][0] . '|';
                $a .= $row[1][0] . '|';
            }
            var_dump($questions);
            var_dump($q);
            var_dump($a);
            $aAnswer = explode('|', $a);
            var_dump($aAnswer);

            $rightAnswer = '';
            $rightAnswer = Yii::app()->cnhutong_user->createCommand()
                ->select('answers')
                ->from('user_question_history')
                ->where('id = :testId And user_id = :userId',
                    array(
                        ':testId' => $testId,
                        ':userId' => $userId
                    ))
                ->queryScalar();
            var_dump($rightAnswer);
            $aRightAnswer = explode('|', $rightAnswer);
            var_dump($aRightAnswer);
            $score = 0;
            foreach($aAnswer as $k1 => $v1) {
                foreach($aRightAnswer as $k2 => $v2) {
                    if($k1[$v1] == $k2[$v2]) {
                        $score++;
                    }
                }
            }
            var_dump($score);

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * @param $testId
     * @return string
     */
    public function IsTestId($testId)
    {
        $id = '';
        try {
            $id = Yii::app()->cnhutong_user->createCommand()
                ->select('id')
                ->from('user_question_history')
                ->where('id = :testId', array(':testId' => $testId))
                ->queryScalar();
        } catch (Exception $e) {
            error_log($e);
        }
        return $id;
    }
}