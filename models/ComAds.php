<?php

class ComAds extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'com_ads';
    }

    public function pushAds($id)
    {
        try {
            // 获取广告
            $table_name = $this->tableName();

            $sql = 'update '. $table_name. ' set send_times=send_times+1 where id='. $id;
            Yii::app()->cnhutong_user->createCommand($sql)->execute();
        } catch (Exception $e) {
            error_log($e);
        }
    }

    public function getAds()
    {
    	$time = date("Y-m-d H:i:s");
        $data = array();
        try {
        	// 获取广告
        	$table_name = $this->tableName();
            $row = Yii::app()->cnhutong_user->createCommand()
                ->select('id, picture, text, link')
                ->from($table_name)
                ->where('starts_ts<=:Time and end_ts>=:Time and status=0',
                	array(':Time' => $time))
                ->order('id desc')
                ->limit(1)
                ->queryRow();
            
            if($row != NULL) {
	            $ads = array();
	            $ads['adPicture'] = $row['picture'];
	            $ads['adContent'] = $row['text'];
	            $ads['adUrl'] = $row['link'];
	            $data[]  = $ads;
	            
	            // 广告统计
                $this->pushAds($row['id']);
            }
            
            
        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }
}