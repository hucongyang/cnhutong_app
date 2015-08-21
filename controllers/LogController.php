<?php

class LogController extends ApiPublicController
{
    public function actionUserAction()
    {
        if( !isset($_REQUEST['userId']) ) {
            $this->_return('MSG_ERR_LESS_PARAM');
        }

        $userId = Yii::app()->request->getParam('userId', NULL);

        $data = LogUserAction::model()->getUserAction($userId);

        $this->_return('MSG_SUCCESS', $data);
    }
}