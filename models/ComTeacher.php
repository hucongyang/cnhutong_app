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
     * @return array
     */
    public function evalTeacherStudent($teacherId)
    {
        $data = array();
        try {

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
}