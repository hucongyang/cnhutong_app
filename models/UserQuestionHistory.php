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

//            $result = Yii::app()->cnhutong_user->createCommand()
//                ->select('COUNT(*) as testUsers, subject')
//                ->from('user_question_history')
//                ->group('subject')
//                ->order('testUsers desc')
//                ->queryAll();
//
//            foreach($result as $row) {
//                //获取数据
//                $subjects = array();
//
//                $subjects['subjectId']              = $row['subject'];
//                if($row['subject'] == 0) {
//                    $subjects['subjectName']            = '综合';
//                } else {
//                    $subjectInfo                          = ApiPublicLesson::model()->getSubjectInfoById($row['subject']);
//                    $subjects['subjectName']            = $subjectInfo['title'];
//                }
//
//                $subjects['testUsers']              = $row['testUsers'];
//                $data['subjects'][] = $subjects;
//            }

            $result = Yii::app()->cnhutong_user->createCommand()
                ->select('id, title, picture, testUsers')
                ->from('com_subject')
                ->where('step = 1')
                ->order('id')
                ->queryAll();

            foreach($result as $row) {
                // 获取数据
                $subjects = array();
                $subjects['subjectId']               = $row['id'];
                $subjects['subjectName']             = $row['title'];
                $subjects['testUsers']               = $row['testUsers'];
                $subjects['picture']                  = $row['picture'];
                $data['subjects'][] = $subjects;
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

            $q = rtrim($q, "|");
            $a = rtrim($a, "|");

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

            // 比对题目数组交集
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
            $score = count(array_intersect_assoc($aRightAnswer, $aAnswer)) * 20;
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

            // 答题得积分 （用户每天首轮答题，1题2积分）
            $point = count(array_intersect_assoc($aRightAnswer, $aAnswer)) * 2;     // 答对题数获得的积分，1题2分
            $count = count(self::IssetQuestion($userId));
            if($count > 1) {
                $pointChange = 0;
            } else {
                $pointChange = $point;
            }

            $change         = $pointChange;
            $reason         = 6;           // 积分变化类型 scoreChangeByReason($reason) 获得类型
            $scoreRest      = UserScoreHistory::model()->getPoint($userId) + $pointChange;
            $createTs       = $nowTime;
            $memo           = null;

            // 积分变化记录历史
            $scoreHistory = UserScoreHistory::model()->insertScoreHistory($userId, $change, $reason, $scoreRest, $createTs, $memo);
            $scoreUpdate = UserScoreHistory::model()->updateUserScore($userId, $scoreRest);

            // 获得此课程类型的所有答题分数
            $scores = self::getAllScores($testId);
            // 得分在分数组中位置
            $num = array_search($score, $scores) + 1;
            // 课程答题人数
            $testUsers = self::testUsers($testId);
            $percent = ($num / $testUsers) * 10000;
            $percent = explode(".", $percent);
//            $data['scores'] = $scores;
            $data['percent'] = $percent[0];
            $data['testScore'] = $score;
            $data['point'] = $pointChange;

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 判断是否为合理的测试编号
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

    /**
     * 获得测试编号对应课程所有测试分数数组
     * @param $testId
     * @return array
     */
    public function getAllScores($testId)
    {
        $scores = array();
        try {
            $subject = Yii::app()->cnhutong_user->createCommand()
                ->select('subject')
                ->from('user_question_history')
                ->where('id = :testId', array(':testId' => $testId))
                ->queryScalar();

            $result = Yii::app()->cnhutong_user->createCommand()
                ->select('score')
                ->from('user_question_history')
                ->where('subject = :subject', array(':subject' => $subject))
                ->order('score asc')
                ->queryAll();

            foreach($result as $row) {
                $scores[] = $row['score'];
            }
        } catch (Exception $e) {
            error_log($e);
        }
        return $scores;
    }

    /**
     * 根据测试编号获得科目测试人数
     * @param $testId
     * @return int
     */
    public function testUsers($testId)
    {
        $testUsers = 0;
        try {
            $subject = Yii::app()->cnhutong_user->createCommand()
                ->select('subject')
                ->from('user_question_history')
                ->where('id = :testId', array(':testId' => $testId))
                ->queryScalar();

            $testUsers = Yii::app()->cnhutong_user->createCommand()
                ->select('testUsers')
                ->from('com_subject')
                ->where('id = :subject', array(':subject' => $subject))
                ->queryScalar();
        } catch (Exception $e) {
            error_log($e);
        }
        return $testUsers;
    }

    /**
     * @param $userId
     * @return array
     */
    public function IssetQuestion($userId)
    {
        $id = array();
        $nowTime = date("Y-m-d");
        try {
            $id = Yii::app()->cnhutong_user->createCommand()
                ->select('id')
                ->from('user_question_history')
                ->where('user_id = :userId And DATE(update_ts) = :nowTime', array(':userId' => $userId, ':nowTime' => $nowTime))
                ->queryAll();
        } catch (Exception $e) {
            error_log($e);
        }
        return $id;
    }
}