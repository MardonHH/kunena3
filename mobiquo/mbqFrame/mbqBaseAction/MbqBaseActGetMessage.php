<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_message action
 * 
 * @since  2012-12-29
 * @author Wu ZeTao <578014287@qq.com>
 */
Abstract Class MbqBaseActGetMessage extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * action implement
     */
    protected function actionImplement() {
        if (MbqMain::$oMbqConfig->moduleIsEnable('pm')) {
            if(MbqMain::$oMbqAppEnv->pm){
                $msgId = MbqMain::$input[0];
                $boxId = MbqMain::$input[1];
                $html = MbqMain::$input[2];
                $oCurJUser = MbqMain::$oMbqAppEnv->oCurJUser;
                if($oCurJUser->id){
                    $oMbqRdEtPm = MbqMain::$oClk->newObj('MbqRdEtPm');
                    if($msg = $oMbqRdEtPm->initOMbqEtPmBox(array('msgId'=> $msgId, 'boxId'=> $boxId), array('case' => 'byMsgId'))){
                        $this->data = $oMbqRdEtPm->returnApiDataPm($msg, true);
                        $this->data['result'] = true;
                        $this->data['result_text'] = (string)'';
                    }else{
                        MbqError::alert('', "Get message failed!", '', MBQ_ERR_APP);
                    }
                }else{
                    MbqError::alert('', "Please login to get message!", '', MBQ_ERR_APP);
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