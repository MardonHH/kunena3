<?php

defined('MBQ_IN_IT') or exit;

/**
 * private message write class
 * 
 * @since  2012-12-29
 * @author Wu ZeTao <578014287@qq.com>
 */
Abstract Class MbqBaseWrEtPm extends MbqBaseWr {
    
    public function __construct() {
    }
    
    /**
     * add private message
     */
    public function addMbqEtPm($fromid, $toid, $replyid, $message, $date, $config, $cryptmode) {
        $message = $this->processToSave($message);
        $msgId = uddeIMsaveRAWmessage($fromid, $toid, $replyid, $message, $date, $config, $cryptmode);
        return $msgId;
    }
    
    public function processToSave($message){
        //$message = preg_replace('/\[img\](.*?)\[\/img\]/i', '<img alt="" src="$1"/>', $message);
        return $message;
    }

    public function deleteMbqEtPmMessage($userid, $msgId, $boxId = 0) {
        $oMbqRdEtPm = MbqMain::$oClk->newObj('MbqRdEtPm');
        if(!$boxId) $boxId = $oMbqRdEtPm->getObjsMbqEtBoxId($msgId);
        if(!$userid || !$msgId || !$boxId) return false;        
        $deletetime = uddetime(MbqMain::$oMbqAppEnv->pm->config->timezone);
        $database = uddeIMgetDatabase();
        if($boxId==1){
            if($database->setQuery("UPDATE #__uddeim SET totrash=1, totrashdate=".(int)$deletetime." WHERE toid=".(int)$userid." AND id=".(int)$msgId )->query())
                return (object) array('message'=>'Message deleted');
        }else{
            if($database->setQuery("UPDATE #__uddeim SET totrashoutbox=1, totrashdateoutbox=".(int)$deletetime." WHERE fromid=".(int)$userid." AND id=".(int)$msgId )->query())
                return (object) array('message'=>'Message deleted');
        }
	return false;
        //uddeIMpurgeMessageFromUser($fromid, $messageid);
    }
    

    

    public function markMbqEtPmUnread($userid, $msgId){
        $database = uddeIMgetDatabase();
	if($database->setQuery("UPDATE #__uddeim SET toread=0 WHERE toid=".(int)$userid." AND id=".(int)$msgId)->query())
                return (object) array('message'=>'Message Mark Unread');
        return false;
    }
    
    public function markMbqEtPmRead($userid, $msgId){
        $database = uddeIMgetDatabase();
	if($database->setQuery("UPDATE #__uddeim SET toread=1 WHERE toid=".(int)$userid." AND id=".(int)$msgId)->query())
                return (object) array('message'=>'Message Mark Read');
        return false;
    }
    
  
}

?>