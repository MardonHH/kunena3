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
        $toName = (array) MbqMain::$input[0]; //array($input['user_name']);
        $subject = MbqMain::$input[1];//$input['subject'];
        $text_body = MbqMain::$input[2]; //$input['text_body'];
        $action = MbqMain::$input[3]; //$input['action'];
        $replyid    =         (int) MbqMain::$input[4]; //$input['pm_id']; 
        if (MbqMain::$oMbqConfig->moduleIsEnable('pm')) {
            if(MbqMain::$oMbqAppEnv->pm){
                $oCurJUser = MbqMain::$oMbqAppEnv->oCurJUser;
                if($oCurJUser->id){
                    $toUser = $msg_id = array();
                    $fMessage= '';
                    switch ($action){
                        case 1: //REPLY to a message
                        case 2: // FORWARD to a message
                            $fMessage = uddeIMselectInboxMessage($oCurJUser->id, $replyid, MbqMain::$oMbqAppEnv->pm->config, 0);
                            break;
                        default : // SEND new message
                            $replyid = 0;
                            break;
                    }
                    $cryptmode = MbqMain::$oMbqAppEnv->pm->config->cryptmode;
                    $date  = uddetime(MbqMain::$oMbqAppEnv->pm->config->timezone);
                    if(is_array($toName)){
                        for ($j= 0; $j<count($toName); $j++){
                            $toUser = JFactory::getUser($toName[$j]);
                            $savemessage = (is_array($subject))? $subject[$j] : $subject;
                            if($savemessage) $savemessage .= PHP_EOL;
                            $savemessage .= (is_array($text_body))? $text_body[$j] : $text_body;
                            if (MbqMain::$oMbqAppEnv->pm->config->cryptmode>=1) 
                                $savemessage = strip_tags($savemessage);
                            else 
                                $savemessage = addslashes(strip_tags($savemessage));
                            if($fMessage) 
                                $savemessage .= PHP_EOL . PHP_EOL . '____________' . PHP_EOL . $fMessage[0]->message;
                            $msg_id[] = uddeIMsaveRAWmessage($oCurJUser->id, $toUser->id, $replyid, $savemessage,$date, MbqMain::$oMbqAppEnv->pm->config ,$cryptmode);
                        }
                    }else{
                        MbqError::alert('', "Input data not array!", '', MBQ_ERR_APP);
                    }
                    if($msg_id){
                        $this->data['result'] = true;
                        $this->data['result_text'] = (string)'Sent!';
                        $this->data['msg_id'] = (string) implode(',', $msg_id);
                    }else{
                        MbqError::alert('', "Send message failed!", '', MBQ_ERR_APP);
                    }
                } else {
                    MbqError::alert('', "User not found!", '', MBQ_ERR_APP);
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