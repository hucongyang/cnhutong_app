<?php

/**
 * 织梦文章主表
 * Class DedeArchives
 */
class DedeArchives extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{dede_archives}}';
    }

    /**
     * 获取秦汉胡同新闻列表
     * @param $newsId
     * @param $typeId
     * @return array
     */
    public function getNews($newsId, $typeId)
    {
        $data = array();
        $nowTime = date("Y-m-d");
        try {
            $newses = array();
            if($newsId == 0) {
                $newses = Yii::app()->dede->createCommand()
                    ->select('id, title, sortrank, litpic, description, redirecturl')
                    ->from('dede_archives ar')
                    ->join('dede_addonarticle ad', 'ar.id = ad.aid')
                    ->where('ar.typeid = :typeId', array(':typeId' => $typeId))
                    ->order('ar.id desc')
                    ->limit('10')
                    ->queryAll();
            } else {
                $newses = Yii::app()->dede->createCommand()
                    ->select('id, title, sortrank, litpic, description, redirecturl')
                    ->from('dede_archives ar')
                    ->join('dede_addonarticle ad', 'ar.id = ad.aid')
                    ->where('ar.typeid = :typeId And ar.id > :newsId', array(':typeId' => $typeId, ':newsId' => $newsId))
                    ->order('ar.id desc')
                    ->limit('10')
                    ->queryAll();
            }

            if(!$newses) {
                return 20042;        //  MSG_ERR_FAIL_NEWS
            }

            foreach($newses as $row) {
                $result = array();
                $result['newsId']               = $row['id'];
                $result['newsTitle']           = $row['title'];
                $result['newsTime']            = date("Y-m-d", $row['sortrank']);
                $result['newsPic']               = $row['litpic'];
                $result['newsDesc']               = mb_substr($row['description'], 0, 30);
                $result['newsUrl']               = $row['redirecturl'];
                $data['newses'][] = $result;
            }

        } catch (Exception $e) {
            error_log($e);
        }
        return $data;
    }
}