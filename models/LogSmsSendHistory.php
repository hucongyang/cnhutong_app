<?php

class LogSmsSendHistory extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{log_sms_send_history}}';
    }
}