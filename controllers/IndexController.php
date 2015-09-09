<?php

/**
 * 首页控制器
 * Class IndexController
 */
class IndexController extends ApiPublicController
{
    /**
     * action_id : 2601
     * 获取秦汉胡同首页新闻列表
     * @userId $userId       -- 用户ID
     * @newsId $newId        -- 新闻ID
     * @typeId $typeId       -- 新闻类型ID
     * @return result          调用返回结果
     * @return msg             调用返回结果说明
     * @return data             调用返回数据
     */
    public function actionGetNews()
    {
        if(!isset($_REQUEST['userId']) || !isset($_REQUEST['newsId']) || !isset($_REQUEST['typeId']))
        {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);
        $newsId = Yii::app()->request->getParam('newsId', NULL);
        $typeId = Yii::app()->request->getParam('typeId', NULL);

        if(!ctype_digit($userId)) {
            $this->_return('MSG_ERR_FAIL_USER');
        }

        if(!ctype_digit($newsId)) {
            $this->_return('MSG_ERR_FAIL_NEWS_ID');
        }

        $aType = [8, 9, 10, 11, 12];        // 织梦文章栏目ID 8-
        if(!in_array($typeId, $aType)) {
            $this->_return('MSG_ERR_FAIL_NEWS_TYPE_ID');
        }

        $data  = DedeArchives::model()->getNews($newsId, $typeId);
        if($data === 20042) {
            $this->_return('MSG_ERR_FAIL_NEWS');
        }

        // TODO : add log
        $actionId = 2601;
        $params = '';
        foreach($_REQUEST as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }
        LogUserAction::model()->userAction($userId, $actionId, $params);

        $this->_return('MSG_SUCCESS', $data);
    }
}