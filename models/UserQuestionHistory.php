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
        $nowTime = date("Y-m-d H-i-s", strtotime("now"));              //当前时间;
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
            // 3-2|2-1|4-1234 拆分为 数组 3-2,2-1,4-1234
            $question_answer = explode("|", $answer);
//            var_dump($question_answer);
            $len = count($question_answer);
//            var_dump($len);
            // 一维数组 3-1,2-1,4-1234 拆分为二位数组
            $questions = array();
            for($i = 0; $i < $len; $i++) {
                $questions[$i] = explode('-',$question_answer[$i]);
            }

            $q = '';
            $a = '';
            // $q 题目ID字符串 以‘|’连接
            // $a 题目答案字符串 以 ‘|’ 连接
            for($j = 0; $j < count($questions); $j++) {
                $q .= $questions[$j][0] . '|';
                $a .= $questions[$j][1] . '|';
            }
//            var_dump($questions);
//            var_dump($q);
//            var_dump($a);

            $rightAnswer = '';
            $rightAnswer = Yii::app()->cnhutong_user->createCommand()
                ->select('question_id, answers')
                ->from('user_question_history')
                ->where('id = :testId And user_id = :userId',
                    array(
                        ':testId' => $testId,
                        ':userId' => $userId
                    ))
                ->queryRow();
//            var_dump($rightAnswer);exit;

            // 答案数组
            $aAnswer = explode('|', $a);
//            var_dump($aAnswer);
            // 题目ID数组
            $aQuestion = explode('|', $q);
            // 比对生成题目ID的顺序与提交题目ID的顺序是否相同，如果不同，报错

            // 正确答案数组
            $aRightAnswer = explode('|', $rightAnswer['answers']);
//            var_dump($aRightAnswer);exit;
            // 正确题目ID数组
            $aRightQuestion = explode('|', $rightAnswer['question_id']);
//            var_dump($aRightQuestion);
//            var_dump($aQuestion);

            // 比对数组交集
            $count = count(array_intersect_assoc($aRightQuestion, $aQuestion));
//            var_dump(array_intersect_assoc($aRightQuestion, $aQuestion));
//            var_dump($count);
            // 实际题目数量
            $num = count($aQuestion);
//            var_dump($num);
            if($count !== $num) {
                return 20023;
            }

            // 比对答案得出分数
            $score = (count(array_intersect_assoc($aRightAnswer, $aAnswer)) - 1)* 10;
//            var_dump($score);
//            var_dump(array_intersect_assoc($aRightAnswer, $aAnswer));

            // 提交答案比对生成题库答案，得出分数score，更新得分等数据
            $scoreResult = Yii::app()->cnhutong_user->createCommand()
                ->update('user_question_history',
                    array(
                        'update_ts' => $nowTime,
                        'post_answer' => $a,
                        'score' => $score
                    ),
                    'id = :testId And user_id = :userId',
                    array(
                        ':testId' => $testId,
                        ':userId' => $userId
                    )
                );

            $data['testScore'] = $score;
            $data['point'] = '10分';             // 做题积分功能待定

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