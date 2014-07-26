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
    public function addMbqEtPm() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    
    public function deleteMbqEtPmMessage($userid, $msgId, $boxId) {
        if(!$userid || !$msgId || !$boxId) return false;
        $uddeim = new uddeIMAPI();        
        $deletetime = uddetime($uddeim->config->timezone);
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