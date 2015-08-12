<?php

class ComChannel extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'com_channel';
    }

    public function getAppVersion()
    {
        $data = array();
        $table_name = $this->tableName();
        try {
            $channel = Yii::app()->request->getParam('channel', NULL);
            $result = Yii::app()->cnhutong_user->createCommand()
                ->select('last_version, new_version, download')
                ->from($table_name)
                ->where('id=:ID', array(':ID' => $channel))
                ->queryRow();
            if ($result == NULL) {
            	$platform = Yii::app()->request->getParam('platform', NULL);
                // 获取官方渠道版本和更新地址
                if ($platform == 1) {
                    // IOS
                    $channel = Yii::app()->params['channel_cnhutong_ios'];
                } else if ($platform == 2) {
                    // Android
                    $channel = Yii::app()->params['channel_cnhutong_android'];
                }
                $result = Yii::app()->cnhutong_user->createCommand()
                    ->select('last_version, new_version, download')
                    ->from($table_name)
                    ->where('id=:ID', array(':ID' => $channel))
                    ->queryRow();
            }

            $data['lastVersion'] = $result['last_version'];
            $data['updateVersion'] = $result['new_version'];
            $data['downloadUrl'] = $result['download'];
        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }
}