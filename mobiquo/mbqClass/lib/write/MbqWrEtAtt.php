<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseWrEtAtt');

/**
 * attachment write class
 * 
 * @since  2012-9-11
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqWrEtAtt extends MbqBaseWrEtAtt {
    
    public function __construct() {
    }
    
    /**
     * upload an attachment
     *
     * @param  Integer  $forumId
     * @param  String  $groupId
     * @return  Object  $oMbqEtAtt
     */
    public function uploadAttachment($forumId, $groupId) {
    	foreach($_FILES['attachment'] as $k => $v){
    		if(is_array($_FILES['attachment'][$k]))
    			$_FILES['attachment'][$k] = $_FILES['attachment'][$k][0];
    	}
    	// Upload new attachments
    	//$message = KunenaForumMessage::getInstance();
		foreach ($_FILES as $key=>$file) {
		    /*
			$intkey = 0;
			if (preg_match('/\D*(\d+)/', $key, $matches))
				$intkey = (int)$matches[1];
			if ($file['error'] != UPLOAD_ERR_NO_FILE) $message->uploadAttachment($intkey, $key);
			*/
			if ($file['error'] != UPLOAD_ERR_NO_FILE) {
                require_once(MBQ_APPEXTENTION_PATH.'ExttMbqKunenaForumMessageAttachment.php');
        		$attachment = new ExttMbqKunenaForumMessageAttachment();
        		$attachment->mesid = 0;
        		$attachment->userid = (MbqMain::$oCurMbqEtUser) ? MbqMain::$oCurMbqEtUser->userId->oriValue : 0;
        		if (KunenaForum::version() >= '2.0.3') $success = $attachment->upload('attachment', $forumId);  //for compatible with kunena 2.0.3
        		else $success = $attachment->upload('attachment');
        		if ($success) {
        		    if ($attachment->exttMbqSave()) {
    	                $oMbqRdEtAtt = MbqMain::$oClk->newObj('MbqRdEtAtt');
    	                return $oMbqRdEtAtt->initOMbqEtAtt($attachment, array('case' => 'oKunenaForumMessageAttachment'));
    	            } else {
    	                MbqError::alert('', "Save attachment failed!", '', MBQ_ERR_APP);
    	            }
        		} else {
    	            MbqError::alert('', "Upload attachment failed!", '', MBQ_ERR_APP);
        		}
        	}
		}
    }
    
    /**
     * delete attachment
     *
     * @param  Object  $oMbqEtAtt
     */
    public function deleteAttachment($oMbqEtAtt) {
        if (!$oMbqEtAtt->mbqBind['oKunenaForumMessageAttachment']->delete()) {
            MbqError::alert('', "Delete attachment failed!", '', MBQ_ERR_APP);
        }
    }
  
}

?>