<?php

/**
 * 用户操作动作编码对照表
 * Class Action
 */
class Action extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{action}}';
    }
}