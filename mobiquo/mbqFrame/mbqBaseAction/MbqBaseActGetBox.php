<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_box action
 * 
 * @since  2012-12-29
 * @author Wu ZeTao <578014287@qq.com>
 */
Abstract Class MbqBaseActGetBox extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * action implement
     */
    protected function actionImplement() {
        if (MbqMain::$oMbqConfig->moduleIsEnable('pm')) {
            if(MbqMain::$oMbqAppEnv->pm){
                $boxId = MbqMain::$input[0] = 2;
                $startNum = (int) MbqMain::$input[1];
                $lastNum = (int) MbqMain::$input[2];
                $oMbqDataPage = MbqMain::$oClk->newObj('MbqDataPage');
                $oMbqDataPage->initByStartAndLast($startNum, $lastNum);

                $oMbqRdEtPm = MbqMain::$oClk->newObj('MbqRdEtPm');
                if ($oMbqEtPmBox = $oMbqRdEtPm->initOMbqEtPmBox($boxId, array('case' => 'byBoxId', 'oMbqDataPage' => $oMbqDataPage ))) {
                    $this->data['result'] = true;
                    $this->data['total_message_count'] = (int) $oMbqRdEtPm->getTotalMessageInbox($boxId);
                    $this->data['total_unread_count'] = (int) $oMbqRdEtPm->getTotalMessageInbox($boxId, 'unread');
                    $this->data['list'] = $oMbqEtPmBox;
                } else {
                    MbqError::alert('', "Need valid pm box id!", '', MBQ_ERR_APP);
                }
            }else{
                MbqError::alert('', "You not install component uddeim!", '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Not support module private message!", '', MBQ_ERR_NOT_SUPPORT);
        }  

    }
    
}

?>