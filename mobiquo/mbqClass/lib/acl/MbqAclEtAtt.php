<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseAclEtAtt');

/**
 * attachment acl class
 * 
 * @since  2012-9-11
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqAclEtAtt extends MbqBaseAclEtAtt {
    
    public function __construct() {
    }
    
    /**
     * judge can upload attachment
     *
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclUploadAttach($oMbqEtForum) {
        if ($oMbqEtForum->mbqBind['oKunenaForumCategory'] && $oMbqEtForum->mbqBind['oKunenaForumCategory']->authorise('topic.post.attachment.create')) {
            return true;
        }
        return false;
    }
    
    /**
     * judge can remove attachment
     *
     * @param  Object  $oMbqEtAtt
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclRemoveAttachment($oMbqEtAtt, $oMbqEtForum) {
        if ($oMbqEtAtt->mbqBind['oKunenaForumMessageAttachment'] && $oMbqEtAtt->mbqBind['oKunenaForumMessageAttachment']->authorise('delete')) {
            return true;
        }
        return false;
    }
  
}

?>