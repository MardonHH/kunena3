<?php

defined('MBQ_IN_IT') or exit;

/**
 * create_message action
 * 
 * @since  2012-12-29
 * @author Wu ZeTao <578014287@qq.com>
 */
Abstract Class MbqBaseActCreateMessage extends MbqBaseAct {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * action implement
     */
    protected function actionImplement() {
        $toName = (array) MbqMain::$input[0];
        $subject = MbqMain::$input[1];
        $text_body = MbqMain::$input[2];
        $action = MbqMain::$input[3];
        $replyid    =         (int) MbqMain::$input[4]; 
        if (MbqMain::$oMbqConfig->moduleIsEnable('pm')) {
            if(MbqMain::$oMbqAppEnv->pm){
                $oCurJUser = MbqMain::$oMbqAppEnv->oCurJUser;
                if($oCurJUser->id){
                    $toUser = $msg_id = array();
                    switch ($action){
                        case 1: //REPLY to a message
                        case 2: // FORWARD to a message
                            //$oMbqRdEtPm = MbqMain::$oClk->newObj('MbqRdEtPm');
                            //$fMessage = $oMbqRdEtPm->getObjsMbqEtQuotePm($replyid);
                            break;
                        default : // SEND new message
                            $replyid = 0;
                            break;
                    }
                    $cryptmode = MbqMain::$oMbqAppEnv->pm->config->cryptmode;
                    $date  = uddetime(MbqMain::$oMbqAppEnv->pm->config->timezone);
                    for ($j= 0; $j<count($toName); $j++){
                        $toUser = JFactory::getUser($toName[$j]);
                        $savemessage = (is_array($subject))? $subject[$j] : $subject;
                        if($savemessage) $savemessage .= PHP_EOL . ' ';
                        $savemessage .= (is_array($text_body))? $text_body[$j] : $text_body;
                        if (MbqMain::$oMbqAppEnv->pm->config->cryptmode>=1) 
                            $savemessage = strip_tags($savemessage);
                        else 
                            $savemessage = addslashes(strip_tags($savemessage));
                        //if($fMessage) $savemessage .= PHP_EOL . PHP_EOL . '____________' . PHP_EOL . $fMessage->message;
                        $oMbqWrEtPm = MbqMain::$oClk->newObj('MbqWrEtPm'); //write class
                        $msg_id[] = $oMbqWrEtPm->addMbqEtPm($oCurJUser->id, $toUser->id, $replyid, $savemessage,$date, MbqMain::$oMbqAppEnv->pm->config ,$cryptmode);
                        
                    }
                    if($msg_id){
                        $this->data['result'] = true;
                        $this->data['msg_id'] = (string) implode(',', $msg_id);
                        $oTapatalkPush = new TapatalkPush();
                        $oTapatalkPush->callMethod('doPushNewMessage', array(
                            'oMbqEtPmMessage' => $msg_id
                        ));
                        
                    }else{
                        MbqError::alert('', "Send message failed!", '', MBQ_ERR_APP);
                    }
                } else {
                    MbqError::alert('', "Please login to send message!", '', MBQ_ERR_APP);
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