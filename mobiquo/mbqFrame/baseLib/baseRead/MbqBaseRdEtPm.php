<?php

defined('MBQ_IN_IT') or exit;

/**
 * private message read class
 * 
 * @since  2012-12-29
 * @author Wu ZeTao <578014287@qq.com>
 */
Abstract Class MbqBaseRdEtPm extends MbqBaseRd {
    
    public function __construct() {
    }
    
    /**
     * return private message api data
     *
     * @param  Object  $oMbqEtPm
     * @param  Boolean  $returnHtml
     * @return  Array
     */
    public function returnApiDataPm($oMbqEtPm, $returnHtml = true) {
        $data = array();
        if ($oMbqEtPm->boxId->hasSetOriValue()) {
            $data['box_id'] = (string) $oMbqEtPm->boxId->oriValue;
        }
        
        if ($oMbqEtPm->msgId->hasSetOriValue()) {
            $data['msg_id'] = (string) $oMbqEtPm->msgId->oriValue;
        }
        if ($oMbqEtPm->msgTitle->hasSetOriValue()) {
            $data['msg_subject'] = (string) $oMbqEtPm->msgTitle->oriValue;
        }
        if ($returnHtml) {
            if ($oMbqEtPm->msgContent->hasSetTmlDisplayValue()) {
                $data['text_body'] = (string) $oMbqEtPm->msgContent->tmlDisplayValue;
            }
        } else {
            if ($oMbqEtPm->msgContent->hasSetTmlDisplayValueNoHtml()) {
                $data['text_body'] = (string) $oMbqEtPm->msgContent->tmlDisplayValueNoHtml;
            }
        }
       
        $data['short_content'] = (string) $oMbqEtPm->shortContent->oriValue;
        if ($oMbqEtPm->isOnline->hasSetOriValue()) {
            $data['is_online'] = (boolean) $oMbqEtPm->isOnline->oriValue;
        }
        if ($oMbqEtPm->isRead->hasSetOriValue() && $oMbqEtPm->isRead->oriValue == MbqBaseFdt::getFdt('MbqFdtPm.MbqEtPm.isRead.range.yes')) {
            $data['msg_state'] = 2;
        } elseif ($oMbqEtPm->isReply->hasSetOriValue() && $oMbqEtPm->isReply->oriValue == MbqBaseFdt::getFdt('MbqFdtPm.MbqEtPm.isReply.range.yes')) {
            $data['msg_state'] = 3;
        } elseif ($oMbqEtPm->isForward->hasSetOriValue() && $oMbqEtPm->isForward->oriValue == MbqBaseFdt::getFdt('MbqFdtPm.MbqEtPm.isForward.range.yes')) {
            $data['msg_state'] = 4;
        } else {
            $data['msg_state'] = 1;
        } 
        if ($oMbqEtPm->sentDate->hasSetOriValue()) {
            $data['sent_date'] = (string) MbqMain::$oMbqCm->datetimeIso8601Encode($oMbqEtPm->sentDate->oriValue);
        }
        if ($oMbqEtPm->msgFromId->hasSetOriValue()) {
            $data['msg_from_id'] = (string) $oMbqEtPm->msgFromId->oriValue;
        }
        if ($oMbqEtPm->msgFrom->hasSetOriValue()) {
            $data['msg_from'] = (string) $oMbqEtPm->msgFrom->oriValue;
        }
        if ($oMbqEtPm->allowSmilies->hasSetOriValue()) {
            $data['allow_smilies'] = (boolean) $oMbqEtPm->allowSmilies->oriValue;
        }
        if ($oMbqEtPm->oMbqEtPmBox && $oMbqEtPm->oMbqEtPmBox->isSentBox() && $oMbqEtPm->oFirstRecipientMbqEtUser && $oMbqEtPm->oFirstRecipientMbqEtUser->iconUrl->hasSetOriValue()) {
            $data['icon_url'] = (string) $oMbqEtPm->oFirstRecipientMbqEtUser->iconUrl->oriValue;
        } elseif ($oMbqEtPm->iconUrl->hasSetOriValue()) {
            $data['icon_url'] = (string) $oMbqEtPm->iconUrl->oriValue;
        }
        if ($oMbqEtPm->objsRecipientMbqEtUser) {
            $data['msg_to'] = (array) array($oMbqEtPm->objsRecipientMbqEtUser);
        } else {
            $data['msg_to'] = array();
        }
        return $data;
    }
    
    /**
     * return private message array api data
     *
     * @param  Array  $objsMbqEtPm
     * @param  Boolean  $returnHtml
     * @return  Array
     */
    public function returnApiArrDataPm($objsMbqEtPm, $returnHtml = true) {
        $data = array();
        foreach ($objsMbqEtPm as $oMbqEtPm) {
            $data[] = $this->returnApiDataPm($oMbqEtPm, $returnHtml);
        }
        return $data;
    }
    
    /**
     * get private message objs
     *
     * @return  Mixed
     */
    public function getObjsMbqEtPm() {
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * init one private message by condition
     *
     * @return  Mixed
     */
    public function initOMbqEtPm($var, $mbqOpt = array('case'=> 'onPmMessage')) {
        if ($mbqOpt['case'] == 'onPmMessage') {
            $oMbqEtQuotePm = MbqMain::$oClk->newObj('MbqEtPm');
            $oMbqEtQuotePm->msgId->setOriValue($var->id);
            $oMbqEtQuotePm->msgContent->setOriValue($var->message);
            $oMbqEtQuotePm->msgContent->setTmlDisplayValue($var->message);
            return $oMbqEtQuotePm;
        }else if($mbqOpt['case'] =='message'){
            $oMbqEtQuotePm = MbqMain::$oClk->newObj('MbqEtPm');
            $oMbqEtQuotePm->msgContent->setOriValue($var->message);
            return $oMbqEtQuotePm;
        }else if ($mbqOpt['case'] == 'byPostId'){
            $message = $this->getObjsMbqEtQuotePm($var);
            return $this->initOMbqEtPm($message);
        }
        
        //MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NEED_ACHIEVE_IN_INHERITED_CLASSE);
    }
    
    /**
     * return private message box api data
     *
     * @param  Object  $oMbqEtPmBox
     * @return  Array
     */
    public function returnApiDataPmBox($oMbqEtPmBox) {
        $data = array();
        if ($oMbqEtPmBox->boxId->hasSetOriValue()) {
            $data['box_id'] = (string) $oMbqEtPmBox->boxId->oriValue;
        }
        if ($oMbqEtPmBox->boxName->hasSetOriValue()) {
            $data['box_name'] = (string) $oMbqEtPmBox->boxName->oriValue;
        }
        if ($oMbqEtPmBox->msgCount->hasSetOriValue()) {
            $data['msg_count'] = (int) $oMbqEtPmBox->msgCount->oriValue;
            $data['total_message_count'] = (int) $oMbqEtPmBox->msgCount->oriValue;
        }
        if ($oMbqEtPmBox->unreadCount->hasSetOriValue()) {
            $data['unread_count'] = (int) $oMbqEtPmBox->unreadCount->oriValue;
            $data['total_unread_count'] = (int) $oMbqEtPmBox->unreadCount->oriValue;
        }
        if ($oMbqEtPmBox->boxType->hasSetOriValue()) {
            $data['box_type'] = (string) $oMbqEtPmBox->boxType->oriValue;
        }
        return $data;
    }
    
    /**
     * return private message box array api data
     *
     * @param  Array  $objsMbqEtPmBox
     * @return  Array
     */
    public function returnApiArrDataPmBox($objsMbqEtPmBox) {
        $data = array();
        foreach ($objsMbqEtPmBox as $oMbqEtPmBox) {
            $data[] = $this->returnApiDataPmBox($oMbqEtPmBox);
        }
        return $data;
    }
    
    /**
     * get private message box objs
     *
     * @return  Mixed
     */
    public function getObjsMbqEtPmBoxs() {
        static $list = array();
        if(!empty($list)) return $list;
        $oCurJUser = MbqMain::$oMbqAppEnv->oCurJUser;
        $type = array('inbox'=>'Inbox','sent'=>'Outbox','archive'=>'Archive');
        $i = 0;
        foreach ($type as $k=>$v){
            $list[$i]['box_id'] = $i+1;
            $list[$i]['box_name'] = $v;
            if($k=='inbox'){
                $list[$i]['msg_count'] = uddeIMgetInboxCount($oCurJUser->id);
                $list[$i]['unread_count'] = uddeIMgetInboxCount($oCurJUser->id, 0, true);
            }else if($k=='sent'){
                $list[$i]['msg_count'] = uddeIMgetOutboxCount($oCurJUser->id);
                $list[$i]['unread_count'] = uddeIMgetOutboxCount($oCurJUser->id, 0 , true);
            }else{
                $list[$i]['msg_count'] = uddeIMgetInboxArchiveCount($oCurJUser->id);
                $list[$i]['unread_count'] = 0;
            }
            $list[$i]['box_type'] = strtoupper($k); 
            $i++;
        }
        return $list;
    }
    
    
    public function getObjsMbqEtPmBox($boxId, $oMbqDataPage){
           
        $limit = (!$oMbqDataPage->lastNum)?  $oMbqDataPage->numPerPage : $oMbqDataPage->lastNum;
        $oCurJUser = MbqMain::$oMbqAppEnv->oCurJUser;
        if($boxId==1) $box = uddeIMselectInbox($oCurJUser->id, $oMbqDataPage->startNum, $limit, MbqMain::$oMbqAppEnv->pm->config);
        else if($boxId==2) $box = uddeIMselectOutbox($oCurJUser->id, $oMbqDataPage->startNum, $limit, MbqMain::$oMbqAppEnv->pm->config);
        else if($boxId==3) $box = uddeIMselectArchive($oCurJUser->id, $oMbqDataPage->startNum, $limit, MbqMain::$oMbqAppEnv->pm->config);
        else return false;
        foreach ($box as &$b){
            $b->box_id = $boxId;
            $b->is_online = uddeIMisOnline($b->fromid);
            $avatars = KunenaFactory::getAvatarIntegration();
            $b->icon_url = $avatars->getURL($user);
        }
        return $box;
    }

    public function getObjsMbqEtPmMessage($var){
        
    }
    
    
    public function getUsername($var){
        return JFactory::getUser($var)->username;
    }

    /**
     * init one private message box by condition
     *
     * @return  Mixed
     */
    public function initOMbqEtPmBox($var,  $mbqOpt = array('case'=> 'onPmBox')) {

        if(!$var) return false;
        if($mbqOpt['case'] =='onPmBox' ){
            $boxs = array();
            foreach ($var as $v){
                if(!$v->toid || !$v->fromid) continue;
                $oMbqEtPm = MbqMain::$oClk->newObj('MbqEtPm');
                $oMbqEtPm->boxId->setOriValue($v->box_id);
                $oMbqEtPm->msgId->setOriValue($v->id);
                $oMbqEtPm->msgTitle->setOriValue($this->getTitleMessage($v->message));
                $oMbqEtPm->sentDate->setOriValue($v->datum);
                $oMbqEtPm->isRead->setOriValue($v->toread);
                $oMbqEtPm->isReply->setOriValue($v->toread);
                $oMbqEtPm->isForward->setOriValue($v->replyid);
                $oMbqEtPm->msgFromId->setOriValue($v->fromid);
                $oMbqEtPm->msgFrom->setOriValue($this->getUsername($v->fromid));
                $oMbqEtPm->isOnline->setOriValue($v->is_online);
                $oMbqEtPm->iconUrl->setOriValue($v->icon_url);
                $oMbqEtPm->objsRecipientMbqEtUser = array(
                        'user_id' => (string)$v->toid,
                        'username' => (string) $this->getUsername($v->toid),
                );
                $oMbqEtPm->shortContent->setOriValue($this->processToDisplay(MbqMain::$oMbqCm->getShortContent($v->message),false));
                $boxs[] = $this->returnApiDataPm($oMbqEtPm, false);
            }
            return $boxs;
        }else if($mbqOpt['case'] =='byBoxId'){
            $box = $this->getObjsMbqEtPmBox($var, $mbqOpt['oMbqDataPage']);
            return $this->initOMbqEtPmBox($box, array('case'=> 'onPmBox', 'oMbqDataPage' => $mbqOpt['oMbqDataPage'] ));
        }else if($mbqOpt['case'] == 'byMsgId'){
            $msg = $this->getObjsMbqEtQuotePm($var['msgId'],$var['boxId'] );
            return $this->initOMbqEtPmBox($msg, array('case'=>'onPmMsg' ));
        }else if($mbqOpt['case'] == 'onPmMsg'){
            $avatars = KunenaFactory::getAvatarIntegration();
            $oMbqEtPm = MbqMain::$oClk->newObj('MbqEtPm');
            $oMbqEtPm->msgFromId->setOriValue($var->fromid);
            $oMbqEtPm->msgId->setOriValue($var->id);
            $oMbqEtPm->msgFrom->setOriValue($this->getUsername($var->fromid));
            $oMbqEtPm->iconUrl->setOriValue($avatars->getURL($user));
            $oMbqEtPm->sentDate->setOriValue($var->datum);
            
            $oMbqEtPm->msgContent->setTmlDisplayValue($this->processToDisplay($var->message));
            $oMbqEtPm->msgContent->setTmlDisplayValueNoHtml($var->message);
            $oMbqEtPm->isOnline->setOriValue(uddeIMisOnline($var->fromid));
            
           
            $oMbqEtPm->isRead->setOriValue($var->toread);
            $oMbqEtPm->isReply->setOriValue($var->toread);
            $oMbqEtPm->isForward->setOriValue($var->replyid);
            $oMbqEtPm->objsRecipientMbqEtUser = array(
                'user_id' => (string)$var->toid,
                'username' => (string) $this->getUsername($var->toid),
            );
            return $oMbqEtPm;
        }
    }
    
    
    public function getTitleMessage($message){
        $content = explode(PHP_EOL, $message);
        return MbqMain::$oMbqCm->getShortContent($content[0], 20);
    }
            
    function processToDisplay($post, $returnHtml = true){
        $post = MbqMain::$oMbqCm->unreplaceCodes($post, 'quote|email|ebay|map');
        /* change the &quot; in quote bbcode to " maked by kunena! */
        $post = preg_replace('/\[quote=&quot;(.*?)&quot;.*?\]/i', '[quote="$1"]', $post);
    	if($returnHtml){
            //$post = str_replace("&", '&amp;', $post);
            //$post = str_replace("<", '&lt;', $post);
            //$post = str_replace(">", '&gt;', $post);
            $post = str_ireplace("[b]", '<b>', $post);
            $post = str_ireplace("[/b]", '</b>', $post);
            $post = str_ireplace("[i]", '<i>', $post);
            $post = str_ireplace("[/i]", '</i>', $post);
            $post = str_ireplace("[u]", '<u>', $post);
            $post = str_ireplace("[/u]", '</u>', $post);
            $post = str_replace("\r", '', $post);
            //$post = str_replace("\n", '<br />', $post);
            $post = str_ireplace('[hr]', '<br />____________________________________<br />', $post);
            $post = str_ireplace('<hr />', '<br />____________________________________<br />', $post);
    	    $post = str_ireplace('<li>', "\t\t<li>", $post);
    	    $post = str_ireplace('</li>', "</li><br />", $post);
    	    $post = str_ireplace('</tr>', '</tr><br />', $post);
    	    $post = str_ireplace('</td>', "</td>\t\t", $post);
             $post = str_ireplace('</div>', '</div><br />', $post);
    	} else {
    	    $post = preg_replace('/<br \/>/i', "\n", $post);
            $post = str_ireplace('[hr]', "\n____________________________________\n", $post);
            $post = str_ireplace('<hr />', "\n____________________________________\n", $post);
            //$post = strip_tags($post);
            $post = html_entity_decode($post, ENT_QUOTES, 'UTF-8');
            $post = strip_tags($post, '<br><i><b><u><font>');
    	}
    	$post = trim($post);
    	return $post;
        /*
        $pathtouser  = uddeIMgetPath('user');
        require_once($pathtouser.'/bbparser.php');
        $config = MbqMain::$oMbqAppEnv->pm->config;
        // if system message or bbcodes allowed, call parser
        if ($config->allowbb) $post = uddeIMbbcode_replace($post, $config);
        if ($config->allowsmile) $post = uddeIMsmile_replace($post, $config);
        return $post;
         * 
         */
    }
    
    
    /**
     * init one private message box by condition
     *
     * @return  Mixed
     */
    public function initOMbqEtPmBoxInfo() {
        $boxInfo = $this->getObjsMbqEtPmBoxs();
        $boxs = array();
        foreach ($boxInfo as $box){
            $box = (object) $box;
            $oMbqEtQuotePmBox = MbqMain::$oClk->newObj('MbqEtPmBox');
            $oMbqEtQuotePmBox->boxId->setOriValue($box->box_id);
            $oMbqEtQuotePmBox->boxName->setOriValue($box->box_name);
            $oMbqEtQuotePmBox->msgCount->setOriValue($box->msg_count);
            $oMbqEtQuotePmBox->unreadCount->setOriValue($box->unread_count);
            $oMbqEtQuotePmBox->boxType->setOriValue($box->box_type);
            $boxs[] = $oMbqEtQuotePmBox;
        }
        return $boxs;
    }
    
    public function getTotalMessage(){
        $boxInfo = $this->getObjsMbqEtPmBoxs();
        return $boxInfo[0]['msg_count'] + $boxInfo[1]['msg_count'];
    }
    
    public function getTotalMessageInbox($boxId, $type=''){
        $boxInfo = $this->getObjsMbqEtPmBoxs();
        if($type=='unread') return $boxInfo[$boxId-1]['unread_count'];
        return $boxInfo[$boxId-1]['msg_count'];
    }
    
   
    
    public function getObjsMbqEtQuotePm($msgId, $boxId = 1){
        $oCurJUser = MbqMain::$oMbqAppEnv->oCurJUser;
        if($boxId==1) $message =  uddeIMselectInboxMessage($oCurJUser->id, $msgId, MbqMain::$oMbqAppEnv->pm->config, 0);
        elseif($boxId==2) $message =  uddeIMselectOutboxMessage($oCurJUser->id, $msgId, MbqMain::$oMbqAppEnv->pm->config, 0);
        elseif($boxId==3) $message =  uddeIMselectArchiveMessage($oCurJUser->id, $msgId, MbqMain::$oMbqAppEnv->pm->config);
        else return false;
        $oMbqWrEtPm = MbqMain::$oClk->newObj('MbqWrEtPm'); //write class
        $oMbqWrEtPm->markMbqEtPmRead($oCurJUser->id, $msgId);
        return $message[0];
    }
    

  
}

?>