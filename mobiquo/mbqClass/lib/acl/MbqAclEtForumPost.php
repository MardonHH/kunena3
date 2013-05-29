<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseAclEtForumPost');

/**
 * forum post acl class
 * 
 * @since  2012-8-20
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqAclEtForumPost extends MbqBaseAclEtForumPost {
    
    public function __construct() {
    }
    
    /**
     * judge can reply post
     *
     * @param  Object  $oMbqEtForumPost
     * @return  Boolean
     */
    public function canAclReplyPost($oMbqEtForumPost) {
        if ($oMbqEtForumPost->mbqBind['oKunenaForumMessage'] && $oMbqEtForumPost->mbqBind['oKunenaForumMessage']->authorise('reply')) {
            return true;
        }
        return false;
    }
    
    /**
     * judge can get quote post
     *
     * @param  Object  $oMbqEtForumPost
     * @return  Boolean
     */
    public function canAclGetQuotePost($oMbqEtForumPost) {
        return $this->canAclReplyPost($oMbqEtForumPost);
    }
    
    /**
     * judge can search_post
     *
     * @return  Boolean
     */
    public function canAclSearchPost() {
        if (MbqMain::$oMbqConfig->getCfg('forum.guest_search')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.forum.guest_search.range.support')) {
            return true;
        } else {
            return MbqMain::hasLogin();
        }
    }
    
    /**
     * judge can get_raw_post
     *
     * @param  Object  $oMbqEtForumPost
     * @return  Boolean
     */
    public function canAclGetRawPost($oMbqEtForumPost) {
        if ($oMbqEtForumPost->mbqBind['oKunenaForumMessage'] && $oMbqEtForumPost->mbqBind['oKunenaForumMessage']->authorise('edit')) {
            return true;
        }
        return false;
    }
    
    /**
     * judge can save_raw_post
     *
     * @param  Object  $oMbqEtForumPost
     * @return  Boolean
     */
    public function canAclSaveRawPost($oMbqEtForumPost) {
        if ($oMbqEtForumPost->mbqBind['oKunenaForumMessage'] && $oMbqEtForumPost->mbqBind['oKunenaForumMessage']->authorise('edit')) {
            return true;
        }
        return false;
    }
    
    /**
     * judge can get_user_reply_post
     *
     * @return  Boolean
     */
    public function canAclGetUserReplyPost() {
        if (MbqMain::$oMbqConfig->getCfg('user.guest_okay')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.user.guest_okay.range.support')) {
            return true;
        } else {
            return MbqMain::hasLogin();
        }
    }
    
    /**
     * judge can report_post
     *
     * @param  Object  $oMbqEtForumPost
     * @return  Boolean
     */
    public function canAclReportPost($oMbqEtForumPost) {
        if ((MbqMain::$oMbqConfig->getCfg('forum.report_post')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.forum.report_post.range.support')) && MbqMain::hasLogin() && $oMbqEtForumPost->mbqBind['oKunenaForumMessage']->authorise('read')) {
            return true;
        }
        return false;
    }
    
    /**
     * judge can thank_post
     *
     * @param  Object  $oMbqEtForumPost
     * @return  Boolean
     */
    public function canAclThankPost($oMbqEtForumPost) {
        return $oMbqEtForumPost->canThank->oriValue;
    }
    
    /**
     * judge can m_delete_post
     *
     * @param  Object  $oMbqEtForumPost
     * @param  Integer  $mode
     * @return  Boolean
     */
    public function canAclMDeletePost($oMbqEtForumPost, $mode) {
        if ($mode == 1) {   //soft-delete
            if (!$oMbqEtForumPost->isDeleted->oriValue && $oMbqEtForumPost->mbqBind['oKunenaForumMessage']->authorise('delete')) {
                return true;
            }
        } elseif ($mode == 2) { //hard-delete
            //not support
        }
        return false;
    }
    
    /**
     * judge can m_undelete_post
     *
     * @param  Object  $oMbqEtForumPost
     * @return  Boolean
     */
    public function canAclMUndeletePost($oMbqEtForumPost) {
        if ($oMbqEtForumPost->isDeleted->oriValue && $oMbqEtForumPost->mbqBind['oKunenaForumMessage']->authorise('undelete')) {
            return true;
        }
        return false;
    }
    
    /**
     * judge can m_move_post
     *
     * @param  Object  $oMbqEtForumPost
     * @param  Mixed  $oMbqEtForum
     * @param  Mixed  $oMbqEtForumTopic
     * @return  Boolean
     */
    public function canAclMMovePost($oMbqEtForumPost, $oMbqEtForum, $oMbqEtForumTopic) {
        if ($oMbqEtForumPost->canMove->oriValue && $oMbqEtForum && $oMbqEtForum->mbqBind['oKunenaForumCategory']->authorise ( 'read' )) {
            return true;
        } elseif ($oMbqEtForumPost->canMove->oriValue && $oMbqEtForumTopic && $oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->authorise ( 'read' )) {
            return true;
        }
        return false;
    }
    
    
    
    /**
     * judge can m_approve_post
     *
     * @param  Object  $oMbqEtForumPost
     * @param  Integer  $mode
     * @return  Boolean
     */
    public function canAclMApprovePost($oMbqEtForumPost, $mode) {
        if ($mode == 1) {   //approve
            if ($oMbqEtForumPost->canApprove->oriValue && ($oMbqEtForumPost->mbqBind['oKunenaForumMessage']->hold == KunenaForum::UNAPPROVED)) {
                return true;
            }
        } elseif ($mode == 2) { //unapprove
            if ($oMbqEtForumPost->canApprove->oriValue && ($oMbqEtForumPost->mbqBind['oKunenaForumMessage']->hold == KunenaForum::PUBLISHED)) {
                return true;
            }
        }
        return false;
    }
  
}

?>