<?php

class HtLessonStudent extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{ht_lesson_student}}';
    }

    /**
     * 获取学员所有正在进行课程的下一次上课时间
     * @param $userId       -- 用户ID
     * @param $token        -- 用户验证token
     * @param $memberId     -- 用户当前绑定的学员所对应的ID
     * @return array
     */
    public function nextLessonList($userId, $token, $memberId)
    {
        $data = array();
        $nowTime = date('Y-m-d');
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

            $isExistUserMemberId = UserMember::model()->IsExistMemberId($userId, $memberId);
            if(!$isExistUserMemberId) {
                return 20017;        // MSG_ERR_FAIL_MEMBER
            }

            $result = Yii::app()->cnhutong->createCommand()
                ->select('course_id, lesson_arrange_id as lessonArrangeId, id as lessonStudentId,
                date as lessonDate, time as lessonTime, lesson_serial as lessonSerial,
                department_id as departmentId')
                ->from('ht_lesson_student')
                ->where('student_id = :studentId And date >= :beginDate',
                    array(
                        ':studentId' => $memberId,
                        ':beginDate' => $nowTime
                    )
                )
                ->group('course_id')
                ->order('lessonSerial asc')
                ->queryAll();

            // 判断数据是否为空数组
            if(ApiPublicController::array_is_null($result)) {
                $data[] = [];
            }

            foreach($result as $row) {
                // 获取数据
                $lesson = array();

                $lesson['courseId']                          = $row['course_id'];
                $subjectId = ApiPublicLesson::model()->getSubjectIdByCourseId($lesson['courseId'] );
                $lesson['subjectId']                         = $subjectId;
                $lesson['lessonArrangeId']                  = $row['lessonArrangeId'];
                $lesson['lessonStudentId']                  = $row['lessonStudentId'];
                $subjectInfo = ApiPublicLesson::model()->getSubjectInfoById($subjectId);
                $lesson['subjectName']                       = $subjectInfo['title'] . '课';
                $lesson['subjectPic']                        = $subjectInfo['feature_img'];
                $lesson['lessonSerial']                      = '第' . $row['lessonSerial'] . '课时';
                $lessonDate = $row['lessonDate'];
                $aDate = explode('-', $lessonDate);
                $lesson['lessonDate']                        = $aDate[1] . '月' . $aDate[2] . '日' . ' ' . $row['lessonTime'];
                $lesson['departmentId']                      = $row['departmentId'];
                $departmentInfo = ApiPublicLesson::model()->getDepartmentInfoById($lesson['departmentId']);
                $lesson['departmentName']                    = $departmentInfo['name'] . '分院';
                $data['subjects'][] = $lesson;
            }

//            $data = $result;
        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 获取学员所有的课程，包括正在学习的课程和历史课程
     * @param $userId       -- 用户ID
     * @param $token        -- 用户验证token
     * @param $memberId     -- 用户当前绑定的学员所对应的ID
     * @return array
     */
    public function allSubjects($userId, $token, $memberId)
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

            $isExistUserMemberId = UserMember::model()->IsExistMemberId($userId, $memberId);
            if(!$isExistUserMemberId) {
                return 20017;        // MSG_ERR_FAIL_MEMBER
            }

            $result = Yii::app()->cnhutong->createCommand()
                ->select('id, course_id, teacher_id, cnt, department_id')
                ->from('ht_lesson_arrange_rules')
                ->where('student_id = :studentId',
                    array(
                        ':studentId' => $memberId
                    )
                )
                ->queryAll();

            // 判断数据是否为空数组
            if(ApiPublicController::array_is_null($result)) {
                $data[] = [];
            }

            foreach($result as $row) {
                // 获取数据
                $subject = array();
                $subjectId = ApiPublicLesson::model()->getSubjectIdByCourseId($row['course_id']);
                $subject['subjectId']                         = $subjectId;
                $subject['lessonArrangeId']                  = $row['id'];
                $subjectInfo = ApiPublicLesson::model()->getSubjectInfoById($subjectId);
                $subject['subjectName']                       = $subjectInfo['title'] . '课';
                $subject['teacherId']                           = $row['teacher_id'];
                $subject['teacherName']                         = ApiPublicLesson::model()->getNameByMemberId($row['teacher_id']) . '老师';
                $cnt                                             = $row['cnt'];
                $lessonNowSerial = ApiPublicLesson::model()->getLessonNowSerial($memberId, $row['id']);
                $subject['lessonProgress']                      = '已上' . $lessonNowSerial . '课时/共' . $row['cnt'] . '课时';
                if($lessonNowSerial < $cnt) {
                    $subject['lessonStatus']                        = '1';
                } elseif ($lessonNowSerial == $cnt) {
                    $subject['lessonStatus']                        = '0';
                } else {
                    $subject['lessonStatus']                        = '';
                }
                $subject['departmentId']                      = $row['department_id'];
                $departmentInfo = ApiPublicLesson::model()->getDepartmentInfoById($subject['departmentId']);
                $subject['departmentName']                    = $departmentInfo['name'] . '分院';
                $data['subjects'][] = $subject;
            }

//            $data = $result;
        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 获取学员指定课程的具体课时详情（课程表）
     * @param $userId       -- 用户ID
     * @param $token        -- 用户验证token
     * @param $memberId     -- 用户当前绑定的学员所对应的ID
     * @param $lessonArrangeId      -- 课程的唯一排课编号
     * @return array
     */
    public function subjectSchedule($userId, $token, $memberId, $lessonArrangeId)
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

            $isExistUserMemberId = UserMember::model()->IsExistMemberId($userId, $memberId);
            if(!$isExistUserMemberId) {
                return 20017;        // MSG_ERR_FAIL_MEMBER
            }

            $isLessonArrangeId = self::IsLessonArrangeId($memberId, $lessonArrangeId);
            if(!$isLessonArrangeId) {
                return 20020;
            }

            $result = Yii::app()->cnhutong->createCommand()
                ->select('id as lessonStudentId, lesson_serial as lessonSerial,
                date, time, status_id, step, student_rating')
                ->from('ht_lesson_student')
                ->where('student_id = :studentId And lesson_arrange_id = :lessonArrangeId',
                    array(
                        ':studentId' => $memberId,
                        ':lessonArrangeId' => $lessonArrangeId
                    )
                )
                ->order('date asc')
                ->queryAll();

            // 判断数据是否为空数组
            if(ApiPublicController::array_is_null($result)) {
                $data[] = [];
            }

            foreach($result as $row) {
                // 获取数据
                $lessons = array();
                $lessons['lessonStudentId']                     = $row['lessonStudentId'];
                $lessons['lessonSerial']                        = $row['lessonSerial'];
                $lessons['lessonDate']                          = $row['date'] . ' ' . $row['time'];
//                if($row['step'] == 0 || $row['step'] == 1) {
//                    $lessons['lessonStatus']                    = self::getLessonStudentStatus($row['status_id']);
//                } else {
//                    $lessons['lessonStatus']                        = self::getLessonStudentStep($row['step']);
//                }
//
//                $lessons['lessonCharge']                        = $row['student_rating'];
                // 测试用
//                $lessons['statusId']                          = $row['status_id'];
//                $lessons['step']                               = $row['step'];
//                $lessons['student_rating']                    = $row['student_rating'];

                $lessons['lessonStatus']                        = self::lessonStatus($row['step'], $row['status_id'], $row['student_rating']);

                $data['lessons'][] = $lessons;
            }
//            $data = $result;
        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }


    /**
     * 获取某一次课时的具体详情
     * @param $userId                   -- 用户ID
     * @param $token                    -- 用户验证token
     * @param $memberId                 -- 用户当前绑定的学员所对应的ID
     * @param $lessonStudentId          -- 课时唯一编号
     * @return array|int
     */
    public function lessonDetails($userId, $token, $memberId, $lessonStudentId)
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

            $isExistUserMemberId = UserMember::model()->IsExistMemberId($userId, $memberId);
            if(!$isExistUserMemberId) {
                return 20017;        // MSG_ERR_FAIL_MEMBER
            }

            $isLessonStudentId = self::IsLessonStudentId($memberId, $lessonStudentId);
            if(!$isLessonStudentId) {
                return 20021;
            }

            $result = Yii::app()->cnhutong->createCommand()
                ->select('id as lessonStudentId, lesson_serial as lessonSerial, date, time,
                step, teacher_id as teacherId, student_rating, student_comment, lesson_content')
                ->from('ht_lesson_student')
                ->where('student_id = :studentId And id = :id',
                    array(
                        ':studentId' => $memberId,
                        ':id' => $lessonStudentId
                    )
                )
                ->order('date')
                ->queryAll();

            // 判断数据是否为空数组
            if(ApiPublicController::array_is_null($result)) {
                $data[] = [];
            }

            foreach($result as $row) {
                // 获取数据
                $lessonDetail = array();
                $lessonDetail['lessonStudentId']                = $row['lessonStudentId'];
                $lessonDetail['lessonSerial']                   = $row['lessonSerial'];
                $lessonDetail['lessonDate']                     = $row['date'] . ' ' . $row['time'];
                $lessonDetail['lessonStatus']                   = self::getLessonStatusNow($row['step']);
                $lessonDetail['teacherId']                      = $row['teacherId'];
                $lessonDetail['teacherName']                    = ApiPublicLesson::model()->getNameByMemberId($row['teacherId']);
//                $lessonDetail['lessonScore']                    = $row['student_rating'];

                $eval = Yii::app()->cnhutong_user->createCommand()
                    ->select('teach_attitude, teach_content, teach_environment, statement')
                    ->from('lesson_student_eval')
                    ->where('member_id = :memberId And lesson_student_id = :lessonStudentId',
                        array(
                            ':memberId' => $memberId,
                            ':lessonStudentId' => $lessonStudentId
                        )
                    )
                    ->queryRow();

                $lessonDetail['teachAttitude'] = isset( $eval['teach_attitude'] ) ? $eval['teach_attitude'] : '';
                $lessonDetail['teachContent'] = isset( $eval['teach_content'] ) ? $eval['teach_content'] : '';
                $lessonDetail['teachEnvironment'] = isset( $eval['teach_environment'] ) ? $eval['teach_environment'] : '';
                $lessonDetail['lessonCharge'] = isset( $eval['statement'] ) ? $eval['statement'] : '';

//                $lessonDetail['lessonCharge']                   = $row['student_comment'];
                $lessonDetail['lessonContent']                  = $row['lesson_content'];
                if(empty($lessonDetail['lessonContent'])) {
                    $lessonDetail['lessonContent']    = '';
                }
                $data[] = $lessonDetail;
            }

//            $data = $result;
        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }


    /**
     * 学员对上过的课时进行评价和打分
     * @param $userId                   -- 用户ID
     * @param $token                    -- 用户验证token
     * @param $memberId                 -- 用户当前绑定的学员对对应的ID
     * @param $lessonStudentId          -- 课时唯一编号
     * @param teachAttitude                 -- 学员给教学态度的评分，1-5分
     * @param teachContent                  -- 学员给教学内容的评分，1-5分
     * @param teachEnvironment              -- 学员给教学环境的评分，1-5分
     * @param $stateComment             -- 课时评价，可以为空
     * @return array|int
     */
    public function lessonStudent($userId, $token, $memberId, $lessonStudentId, $teachAttitude, $teachContent, $teachEnvironment, $stateComment)
    {
        $nowTime = date("Y-m-d H-i-s");              //当前时间
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

            $isExistUserMemberId = UserMember::model()->IsExistMemberId($userId, $memberId);
            if(!$isExistUserMemberId) {
                return 200017;        // MSG_ERR_FAIL_MEMBER
            }

            $isLessonStudentId = self::IsLessonStudentId($memberId, $lessonStudentId);
            if(!$isLessonStudentId) {
                return 20021;
            }

            if( ($teachAttitude < 0 || $teachAttitude > 5) || ($teachContent < 0 || $teachContent > 5) || ($teachEnvironment < 0 || $teachEnvironment > 5) ) {
                return 20022;
            }

            $issetEval = self::IssetEval($memberId, $lessonStudentId);
            if($issetEval) {
                return 20024;
            }

            // insert 学生对课程评分和评价
            $eval = Yii::app()->cnhutong_user->createCommand()
                ->insert('lesson_student_eval',
                    array(
                        'member_id' => $memberId,
                        'lesson_student_id' => $lessonStudentId,
                        'teach_attitude' => $teachAttitude,
                        'teach_content' => $teachContent,
                        'teach_environment' => $teachEnvironment,
                        'statement' => $stateComment,
                        'create_ts' => $nowTime
                    )
                );
            // 连接 CMS 数据库系统, 更新课时评价数据
            $score = $teachAttitude + $teachContent + $teachEnvironment;        // cms系统评分为教学三项评分总和
            $result = Yii::app()->cnhutong->createCommand()
                ->update('ht_lesson_student',
                    array(
                        'student_rating' => $score,
                        'student_comment' => $stateComment
                    ),
                    'student_id = :studentId And id = :id',
                    array(
                        ':studentId' => $memberId,
                        ':id' => $lessonStudentId
                    )
                );

//            $data = $result;
        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }


    /**
     * 学员在APP中对自己的课时进行请假或者取消请假的操作
     * @param $userId
     * @param $token
     * @param $memberId
     * @param $lessonStudentId
     * @param $leaveType
     * @return array|int
     */
    public function lessonStudentLeave($userId, $token, $memberId, $lessonStudentId, $leaveType)
    {
        $nowTime = date("Y-m-d H-i-s");
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

            $isExistUserMemberId = UserMember::model()->IsExistMemberId($userId, $memberId);
            if(!$isExistUserMemberId) {
                return 200017;        // MSG_ERR_FAIL_MEMBER
            }

            $isLessonStudentId = self::IsLessonStudentId($memberId, $lessonStudentId);
            if(!$isLessonStudentId) {
                return 20021;         // MSG_ERR_LESSON_STUDENT_ID
            }

            // step 状态  0 - 等待确认，1 - 取消请假，3 - 锁定，2 - 请假完成
            $aStep = array(0, 1, 2, 3);
            // 学员请假
            if($leaveType == 1) {
                $reminder = self::getHtReminder(410, 82);
                if($reminder) {                                 // 请假记录存在
                    $step = $reminder['step'];
                    if($step == 1) {                            // 学员请假后，客服未处理。学员又取消了请假,此时可以请假
                        self::setStep($memberId, $lessonStudentId, $step);      // 设置请假状态
                    } else {
                        return 20032;               //MSG_ERR_NO_LEAVE
                    }
                } else {                                        // 请假记录不存在
                    self::insertLeave($memberId, $lessonStudentId);     // 增加请假记录
                }

//                var_dump($reminder['step']);
            } elseif ($leaveType == 2) {
                $reminder = self::getHtReminder(410, 82);
                if(!$reminder) {
                    return 20033;                   // MSG_ERR_NO_CANCEL_LEAVE
                } else {
                    
                }

//                var_dump(7);
            } else {
                return 20031;
            }

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 输入课时标识，输出课时状态
     * @param $step
     * @param $statusId
     * @param $isRating
     * @return int
     */
    public function lessonStatus($step, $statusId, $isRating)
    {
        $queQin = array(2,3,4,5,7,8);        // ht_lessonStudent step 为数组中数组为缺勤
        $normal = array(0,1);               // 正常
        if(in_array($step, $queQin)) {
            return 5;                           // 课时状态为 缺勤
        } elseif ($step == 6) {
            return 4;                           // 课时状态为 请假
        } elseif (in_array($step, $normal)) {
            if($statusId == 0) {
                return 3;                       // 课时状态为 未上
            } else {
                if($isRating) {
                    return 2;                   // 课时状态为 已上已评
                } else {
                    return 1;                   // 课时状态为 已上未评
                }
            }
        } else {
            return 9999;                        // 课时状态为 未知状态
        }
    }

    /**
     * 输入 状态 step 0-8
     * 输出 对应的课时状态 正常，补课等等
     * @param $step
     * @return string
     */
    public function getLessonStudentStep($step)
    {
        switch ($step) {
            case "0":
                return "正常";
            case "1":
                return "正常";        // 补课
            case "2":
                return "缺勤";
            case "3":
                return "缺勤";        // 弃课
            case "4":
                return "缺勤";        // 冻结
            case "5":
                return "缺勤";        // 退费
            case "6":
                return "请假";
            case "7":
                return "顺延补课";
            case "8":
                return "缺勤";        // 补课后弃课
            default:
                return '';
        }
    }

    /**
     * 输入 状态 step 0-1
     * 输出 对应的课时状态 未上课，已上课
     * @param $status
     * @return string
     */
    public function getLessonStudentStatus($status)
    {
        if($status == 0) {
            return '未上课';
        } else {
            return '已上课';
        }
    }

    /**
     * 输入：用户当前绑定的学员所对应的ID
     * 输出：该ID所对应的 课程唯一排课编号lessonArrangeId
     * @param $memberId
     * @param $lessonArrangeId
     * @return array
     */
    public function IsLessonArrangeId($memberId, $lessonArrangeId)
    {
        $arr = array();
        try {
            $arr = Yii::app()->cnhutong->createCommand()
                ->select('id')
                ->from('ht_lesson_student')
                ->where('student_id = :memberId And lesson_arrange_id = :lessonArrangeId',
                    array(
                        ':memberId' => $memberId,
                        ':lessonArrangeId' => $lessonArrangeId
                    )
                )
                ->queryAll();
        } catch (Exception $e) {
            error_log($e);
        }
        return $arr;
    }

    /**
     * 输入：用户当前绑定的学员所对应的ID
     * 输出：该ID所对应的 课时唯一编号 id
     * @param $memberId
     * @param $lessonStudentId
     * @return array
     */
    public function IsLessonStudentId($memberId, $lessonStudentId)
    {
        $id = '';
        try {
            $id = Yii::app()->cnhutong->createCommand()
                ->select('id')
                ->from('ht_lesson_student')
                ->where('student_id = :memberId And id = :lessonStudentId',
                    array(
                        ':memberId' => $memberId,
                        ':lessonStudentId' => $lessonStudentId
                    )
                )
                ->queryScalar();
        } catch (Exception $e) {
            error_log($e);
        }
        return $id;
    }

    /**
     * 判断该学员该课时是否已评价，如已评则不在让评价
     * 输入：memberId 学员特有ID；lessonStudentId 课时ID
     * 输出：课时是否评价
     * @param $memberId
     * @param $lessonStudentId
     * @return string
     */
    public function IssetEval($memberId, $lessonStudentId)
    {
        $id = '';
        try {
            $id = Yii::app()->cnhutong_user->createCommand()
                ->select('id')
                ->from('lesson_student_eval')
                ->where('member_id = :memberId And lesson_student_id = :lessonStudentId',
                    array(
                        ':memberId' => $memberId,
                        ':lessonStudentId' => $lessonStudentId
                    )
                )
                ->queryScalar();
        } catch (Exception $e) {
            error_log($e);
        }
        return $id;
    }

    /**
     * 获得CS系统请假相关表 ht_reminder 相关字段信息
     * 详情请查阅：秦汉胡同App请假流程对接价值中心CS系统文档说明
     * 输入：学员 memberId；课时唯一编号 lessonStudentId
     * 输出：请假相关字段信息
     * @param $memberId
     * @param $lessonStudentId
     * @return array
     */
    public function getHtReminder($memberId, $lessonStudentId)
    {
        $result = array();
        try {
            $result = Yii::app()->cnhutong->createCommand()
                ->select('id, step')
                ->from('ht_reminder')
                ->where('member_id = :memberId And item_id = :lessonStudentId',
                    array(
                        ':memberId' => $memberId,
                        ':lessonStudentId' => $lessonStudentId
                    )
                )
                ->queryRow();

        } catch (Exception $e) {
            error_log($e);
        }
        return $result;
    }

    /**
     * 设置请假状态 step
     * @param $memberId
     * @param $lessonStudentId
     * @param $step
     * @return int
     */
    public function setStep($memberId, $lessonStudentId, $step)
    {
        $data = 0;
        try {
            $data = Yii::app()->cnhutong->createCommand()
                ->update('ht_reminder',
                    array('step' => $step),
                    'member_id = :memberId And item_id = :lessonStudentId',
                    array(
                        ':memberId' => $memberId,
                        ':lessonStudentId' => $lessonStudentId
                    )
                );
        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 学员请假插入数据记录
     * @param $memberId
     * @param $lessonStudentId
     * @return int
     */
    public function insertLeave($memberId, $lessonStudentId)
    {
        $nowTime = date('Y-m-d H:m:s');
        $data = 0;
        try {
            $departmentId = ApiPublicLesson::model()->getDepartmentId($memberId, $lessonStudentId);     // 获区校区ID

            $data = Yii::app()->cnhutong->createCommand()
                ->insert('ht_reminder',
                    array(
                        'member_id' => $memberId,           // 学员memberId
                        'item_type' => 7,                   // 请假标识为7
                        'item_id' => $lessonStudentId,      // 课时唯一标识
                        'create_time' => $nowTime,
                        'step' => 0,                        // step为等待确认
                        'to_member_id' => 0,                // 分配处理学员请假流程的客服memberId
                        'department_id' => $departmentId    // 学员所在的校区
                    )
                );
        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }
}