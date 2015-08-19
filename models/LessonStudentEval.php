<?php

/**
 * 学员课时评价app记录信息
 * Class LessonStudentEval
 */
class LessonStudentEval extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{lesson_student_eval}}';
    }
}