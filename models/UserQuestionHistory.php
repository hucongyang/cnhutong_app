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
                return 10010;       // MSG_ERR_FAIL_USER
            }
            // 用户token验证
            $userToken = UserToken::model()->IsToken($userId, $token);
//            var_dump($userToken);exit;
            if(!$userToken) {
                return 10009;       // MSG_ERR_FAIL_TOKEN
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
}