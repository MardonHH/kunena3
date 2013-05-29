<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseAclEtForum');

/**
 * forum acl class
 * 
 * @since  2012-8-8
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqAclEtForum extends MbqBaseAclEtForum {
    
    public function __construct() {
    }
    
    /**
     * judge can get subscribed forum
     *
     * @return  Boolean
     */
    public function canAclGetSubscribedForum() {
        return MbqMain::hasLogin();
    }
    
    /**
     * judge can subscribe forum
     *
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclSubscribeForum($oMbqEtForum) {
        if (MbqMain::hasLogin() && $oMbqEtForum->mbqBind['oKunenaForumCategory'] && $oMbqEtForum->mbqBind['oKunenaForumCategory']->authorise('subscribe') && !$oMbqEtForum->mbqBind['oKunenaForumCategory']->getSubscribed(MbqMain::$oCurMbqEtUser->userId->oriValue)) {
            return true;
        }
        return false;
    }
    
    /**
     * judge can unsubscribe forum
     *
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclUnsubscribeForum($oMbqEtForum) {
        if (MbqMain::hasLogin() && $oMbqEtForum->mbqBind['oKunenaForumCategory'] && $oMbqEtForum->mbqBind['oKunenaForumCategory']->authorise('subscribe') && $oMbqEtForum->mbqBind['oKunenaForumCategory']->getSubscribed(MbqMain::$oCurMbqEtUser->userId->oriValue)) {
            return true;
        }
        return false;
    }
  
}

?>