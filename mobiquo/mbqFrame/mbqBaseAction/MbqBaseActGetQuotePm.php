<?php

defined('MBQ_IN_IT') or exit;

/**
 * get_quote_pm action
 * 
 * @since  2012-12-29
 * @author Wu ZeTao <578014287@qq.com>
 */
Abstract Class MbqBaseActGetQuotePm extends MbqBaseAct {
    
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
                $oCurJUser = MbqMain::$oMbqAppEnv->oCurJUser;
                if($oCurJUser->id){
                    $oMbqRdEtPm = MbqMain::$oClk->newObj('MbqRdEtPm'); //read class
                    if($objsMbqEtQuotePm = $oMbqRdEtPm->initOMbqEtPm($msgId,  array('case' => 'byPostId'))){
                        $this->data = $oMbqRdEtPm->returnApiDataPm($objsMbqEtQuotePm);
                        $this->data['result'] = true;
                    }else{
                        MbqError::alert('', "Get quote Pm fail!", '', MBQ_ERR_APP);
                    }
                }else{
                    MbqError::alert('', "Please login to get quote Pm!", '', MBQ_ERR_APP);
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