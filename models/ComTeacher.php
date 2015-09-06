<?php

class ComTeacher extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{com_teacher}}';
    }

    /**
     * 获取秦汉胡同所有老师的列表
     * @param $search
     * @param $subject
     * @param $department
     * @param $teacherId
     * @return array
     */
    public function getAllTeachers($search, $subject, $department, $teacherId)
    {
        $data = array();
        try {
            $teachers = array();
            $where = 't.step = 1';
            if($subject) {
                $where .= ' AND ts.subjectId = "' . addslashes($subject) . '" ';
            }
            if($department) {
                $where .= ' AND td.departmentId = "' . addslashes($department) . '" ';
            }
            if($search) {
                $where .= " AND t.name LIKE '%" . addslashes($search) . "%'";
            }

            if($teacherId == 0) {
                $teachers = Yii::app()->cnhutong_user->createCommand()
                    ->select('DISTINCT(t.id), t.name, t.icon, t.intro')
                    ->from('com_teacher_subject ts')
                    ->join('com_teacher_department td', 'ts.teacherId = td.teacherId')
                    ->join('com_teacher t', 'ts.teacherId = t.id')
                    ->where($where)
                    ->order('id asc')
                    ->limit('10')
                    ->queryAll();
            } else {
                $where .= 'AND t.id > "' . addslashes($teacherId) . '" ';
                $teachers = Yii::app()->cnhutong_user->createCommand()
                    ->select('DISTINCT(t.id), t.name, t.icon, intro')
                    ->from('com_teacher_subject ts')
                    ->join('com_teacher_department td', 'ts.teacherId = td.teacherId')
                    ->join('com_teacher t', 'ts.teacherId = t.id')
                    ->where($where)
                    ->order('id asc')
                    ->limit('10')
                    ->queryAll();
            }

            if(!$teachers) {
                return 20035;       // MSG_ERR_NULL_TEACHER
            }

            foreach($teachers as $row) {
                $result = array();
                $result['teacherId']            = $row['id'];
                $result['name']                  = $row['name'];
                $result['subjects']             = self::teacherSubject($row['id']);
                $result['departments']          = self::teacherDepartment($row['id']);
                $result['icon']                  = $row['icon'];
                $result['intro']                 = $row['intro'];
                $data['teachers'][] = $result;
            }

        } catch (Exception $e) {
            var_dump($e);
            error_log($e);
        }
        return $data;
    }

    /**
     * 获取秦汉胡同教师的详细信息
     * @param $teacherId
     * @return array
     */
    public function getTeacherInfo($teacherId)
    {
        $data = array();
        try {
            $teacherInfo = Yii::app()->cnhutong_user->createCommand()
                ->select('id, name, icon, picture, intro, object, resume')
                ->from('com_teacher')
                ->where('id = :teacherId', array(':teacherId' => $teacherId))
                ->queryRow();

            if(!$teacherInfo) {
                return 20035;       // MSG_ERR_NULL_TEACHER
            }

            $data['teacherId']              = $teacherInfo['id'];
            $data['name']                    = $teacherInfo['name'];
            $data['subjects']             = self::teacherSubject($teacherInfo['id']);
            $data['departments']          = self::teacherDepartment($teacherInfo['id']);
            $data['icon']                    = $teacherInfo['icon'];
            $data['picture']                 = $teacherInfo['picture'];
            $data['intro']                   = $teacherInfo['intro'];
            $data['object']                  = $teacherInfo['object'];
            $data['resume']                  = $teacherInfo['resume'];
            $data['grade']                   = 0;

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 获取秦汉胡同老师所教课程的学生评价
     * @param $teacherId
     * @param $evalId
     * @return array
     */
    public function evalTeacherStudent($teacherId, $evalId)
    {
        $data = array();
        try {
            $evaluates = array();
            if($evalId == 0) {
                $evaluates = Yii::app()->cnhutong_user->createCommand()
                    ->select('id, member_id, lesson_student_id, teach_attitude, teach_content, teach_environment, statement, create_ts, teacher_id, teacher_statement, teacher_create_ts')
                    ->from('lesson_student_eval')
                    ->where('teacher_id = :teacherId', array(':teacherId' => $teacherId))
                    ->order('id asc')
                    ->limit('10')
                    ->queryAll();
            } else {
                $evaluates = Yii::app()->cnhutong_user->createCommand()
                    ->select('id, member_id, lesson_student_id, teach_attitude, teach_content, teach_environment, statement, create_ts, teacher_id, teacher_statement, teacher_create_ts')
                    ->from('lesson_student_eval')
                    ->where('teacher_id = :teacherId And id > :evalId', array(':teacherId' => $teacherId, ':evalId' => $evalId))
                    ->order('id asc')
                    ->limit('10')
                    ->queryAll();
            }

            if(!$evaluates) {
                return 20039;           // MSG_ERR_FAIL_EVAL
            }

            foreach($evaluates as $row) {
                $result = array();
                $result['evalId']                               = $row['id'];
                $result['studentId']                            = $row['member_id'];
                $result['studentName']                          = ApiPublicLesson::model()->getNameByMemberId($row['member_id']);
                $courseId = self::getCourseByLessonStudentId($row['lesson_student_id']);
                $subjectId = ApiPublicLesson::model()->getSubjectIdByCourseId($courseId);
                $subjectInfo = ApiPublicLesson::model()->getSubjectInfoById($subjectId);
                $result['studentSubject']                       = $subjectInfo['title'];
                $create = date("Y-m-d", strtotime($row['create_ts']));
                $result['studentTime']                            = $create;
                $result['studentContent']                            = $row['statement'];
                $result['studentCharge']  = number_format(( $row['teach_attitude'] + $row['teach_content'] + $row['teach_environment'] ) / 3, 1) ;
                $result['teacherReplyContent']                   = $row['teacher_statement'];
                if(!$row['teacher_statement']) {
                    $replyTime = '';
                } else {
                    $replyTime = date("Y-m-d", strtotime($row['teacher_create_ts']));
                }
                $result['teacherReplyTime']                   = $replyTime;
                $data['evaluates'][] = $result;
            }

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 获取秦汉胡同老师所有作品的列表
     * @param $teacherId
     * @param $productId
     * @return array
     */
    public function getTeacherProducts($teacherId, $productId)
    {
        $data = array();
        try {
            $products = array();
            if($productId == 0) {
                $products = Yii::app()->cnhutong_user->createCommand()
                    ->select('id, title, production')
                    ->from('com_teacher_production')
                    ->where('teacherId = :teacherId', array(':teacherId' => $teacherId))
                    ->order('id asc')
                    ->limit('10')
                    ->queryAll();
            } else {
                $products = Yii::app()->cnhutong_user->createCommand()
                    ->select('id, title, production')
                    ->from('com_teacher_production')
                    ->where('teacherId = :teacherId And id > :productId', array(':teacherId' => $teacherId, ':productId' => $productId))
                    ->order('id asc')
                    ->limit('10')
                    ->queryAll();
            }

            if(!$products) {
                $data['products'][] = array();
            }

            foreach($products as $row) {
                $result = array();
                $result['productId']            = $row['id'];
                $result['productName']          = $row['title'];
                $result['production']           = $row['production'];
                $data['products'][] = $result;
            }

        } catch (Exception $e) {
            var_dump($e);
            error_log($e);
        }
        return $data;
    }

    /**
     * 获取秦汉胡同老师作品详情
     * @param $productId
     * @return array
     */
    public function getTeacherProductInfo($productId)
    {
        $data = array();
        try {
            $result = Yii::app()->cnhutong_user->createCommand()
                ->select('id, title, create_ts, production, desc')
                ->from('com_teacher_production')
                ->where('id = :productId', array(':productId' => $productId))
                ->queryRow();

            $data['productName']        = isset($result['title']) ? $result['title'] : '';
            $data['productTime']        = isset($result['create_ts']) ? $result['create_ts'] : '';
            $data['production']         = isset($result['production']) ? $result['production'] : '';
            $data['desc']                = isset($result['desc']) ? $result['desc'] : '';

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 根据教师ID获得教师课程
     * @param $teacherId
     * @return array|string
     */
    public function teacherSubject($teacherId)
    {
        $data =array();
        try {
            $data = Yii::app()->cnhutong_user->createCommand()
                ->select('subjectId')
                ->from('com_teacher_subject')
                ->where('teacherId = :teacherId', array(':teacherId' => $teacherId))
                ->queryAll();

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 根据教师ID获得教师校区
     * @param $teacherId
     * @return array|string
     */
    public function teacherDepartment($teacherId)
    {
        $data = array();
        try {
            $data = Yii::app()->cnhutong_user->createCommand()
                ->select('departmentId')
                ->from('com_teacher_department')
                ->where('teacherId = :teacherId', array(':teacherId' => $teacherId))
                ->queryAll();

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }

    /**
     * 根据课时唯一ID获得该课时学员的课程 courseId
     * @param $lessonStudentId
     * @return string
     */
    public function getCourseByLessonStudentId($lessonStudentId)
    {
        $courseId = 0;
        try {
            $courseId = Yii::app()->cnhutong->createCommand()
                ->select('course_id')
                ->from('ht_lesson_student')
                ->where('id = :id', array(':id' => $lessonStudentId))
                ->queryScalar();
        } catch (Exception $e) {
            error_log($e);
        }
        return $courseId;
    }
}