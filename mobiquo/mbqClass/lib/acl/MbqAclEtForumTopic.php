<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseAclEtForumTopic');

/**
 * forum topic acl class
 * 
 * @since  2012-8-10
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqAclEtForumTopic extends MbqBaseAclEtForumTopic {
    
    public function __construct() {
    }
    
    /**
     * judge can get topic from the forum
     *
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclGetTopic($oMbqEtForum) {
        if ($oMbqEtForum->mbqBind['oKunenaForumCategory'] && $oMbqEtForum->mbqBind['oKunenaForumCategory']->authorise('read')) {
            return true;
        }
        return false;
    }
    
    /**
     * judge can get thread
     *
     * @param  Object  $oMbqEtForumTopic
     * @return  Boolean
     */
    public function canAclGetThread($oMbqEtForumTopic) {
        if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic'] && $oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->authorise('read') && KunenaForumMessageHelper::get($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->first_post_id)->exists()) {
            return true;
        }
        return false;
    }
    
    /**
     * judge can new topic
     *
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclNewTopic($oMbqEtForum) {
        if ($oMbqEtForum->mbqBind['oKunenaForumCategory'] && $oMbqEtForum->mbqBind['oKunenaForumCategory']->authorise('topic.create')) {
            return true;
        }
        return false;
    }
    
    /**
     * judge can get subscribed topic
     *
     * @return  Boolean
     */
    public function canAclGetSubscribedTopic() {
        return MbqMain::hasLogin();
    }
    
    /**
     * judge can mark all my unread topics as read
     *
     * @return  Boolean
     */
    public function canAclMarkAllAsRead() {
        //return MbqMain::hasLogin();
        return (MbqMain::hasLogin() && MbqMain::$oMbqConfig->getCfg('forum.mark_read')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.forum.mark_read.range.support'));
    }
    
    /**
     * judge can get_unread_topic
     *
     * @return  Boolean
     */
    public function canAclGetUnreadTopic() {
        //return MbqMain::hasLogin();
        return (MbqMain::hasLogin() && MbqMain::$oMbqConfig->getCfg('forum.can_unread')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.forum.can_unread.range.support'));
    }
    
    /**
     * judge can get_participated_topic
     *
     * @return  Boolean
     */
    public function canAclGetParticipatedTopic() {
        return MbqMain::hasLogin();
    }
    
    /**
     * judge can get_latest_topic
     *
     * @return  Boolean
     */
    public function canAclGetLatestTopic() {
        if (MbqMain::$oMbqConfig->getCfg('forum.guest_search')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.forum.guest_search.range.support')) {
            return true;
        } else {
            return MbqMain::hasLogin();
        }
    }
    
    /**
     * judge can search_topic
     *
     * @return  Boolean
     */
    public function canAclSearchTopic() {
        if (MbqMain::$oMbqConfig->getCfg('forum.guest_search')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.forum.guest_search.range.support')) {
            return true;
        } else {
            return MbqMain::hasLogin();
        }
    }
    
    /**
     * judge can subscribe_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @return  Boolean
     */
    public function canAclSubscribeTopic($oMbqEtForumTopic) {
        if (MbqMain::hasLogin() && $oMbqEtForumTopic->mbqBind['oKunenaForumTopic'] && $oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->authorise('subscribe') && (!$oMbqEtForumTopic->mbqBind['oKunenaForumTopicUser'] || ($oMbqEtForumTopic->mbqBind['oKunenaForumTopicUser'] && !$oMbqEtForumTopic->mbqBind['oKunenaForumTopicUser']->subscribed))) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * judge can unsubscribe_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @return  Boolean
     */
    public function canAclUnsubscribeTopic($oMbqEtForumTopic) {
        if (MbqMain::hasLogin() && $oMbqEtForumTopic->mbqBind['oKunenaForumTopic'] && $oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->authorise('subscribe') && $oMbqEtForumTopic->mbqBind['oKunenaForumTopicUser'] && $oMbqEtForumTopic->mbqBind['oKunenaForumTopicUser']->subscribed) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * judge can get_user_topic
     *
     * @return  Boolean
     */
    public function canAclGetUserTopic() {
        if (MbqMain::$oMbqConfig->getCfg('user.guest_okay')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.user.guest_okay.range.support')) {
            return true;
        } else {
            return MbqMain::hasLogin();
        }
    }
    
    /**
     * judge can m_stick_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Integer  $mode
     * @return  Boolean
     */
    public function canAclMStickTopic($oMbqEtForumTopic, $mode) {
        if ($mode == 1) {   //stick
            if ($oMbqEtForumTopic->canStick->oriValue && !$oMbqEtForumTopic->isSticky->oriValue) {
                return true;
            }
        } elseif ($mode == 2) { //unstick
            if ($oMbqEtForumTopic->canStick->oriValue && $oMbqEtForumTopic->isSticky->oriValue) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * judge can m_close_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Integer  $mode
     * @return  Boolean
     */
    public function canAclMCloseTopic($oMbqEtForumTopic, $mode) {
        if ($mode == 1) {   //reopen
            if ($oMbqEtForumTopic->canClose->oriValue && $oMbqEtForumTopic->isClosed->oriValue) {
                return true;
            }
        } elseif ($mode == 2) { //close
            if ($oMbqEtForumTopic->canClose->oriValue && !$oMbqEtForumTopic->isClosed->oriValue) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * judge can m_delete_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Integer  $mode
     * @return  Boolean
     */
    public function canAclMDeleteTopic($oMbqEtForumTopic, $mode) {
        if ($mode == 1) {   //soft-delete
            if (!$oMbqEtForumTopic->isDeleted->oriValue && $oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->authorise('delete')) {
                return true;
            }
        } elseif ($mode == 2) { //hard-delete
            //not support
        }
        return false;
    }
    
    /**
     * judge can m_undelete_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @return  Boolean
     */
    public function canAclMUndeleteTopic($oMbqEtForumTopic) {
        if ($oMbqEtForumTopic->isDeleted->oriValue && $oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->authorise('undelete')) {
            return true;
        }
        return false;
    }
    
    /**
     * judge can m_move_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Object  $oMbqEtForum
     * @return  Boolean
     */
    public function canAclMMoveTopic($oMbqEtForumTopic, $oMbqEtForum) {
        if ($oMbqEtForumTopic->canMove->oriValue && $oMbqEtForum->mbqBind['oKunenaForumCategory']->authorise ( 'read' )) {
            return true;
        }
        return false;
    }
    
    /**
     * judge can m_rename_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @return  Boolean
     */
    public function canAclMRenameTopic($oMbqEtForumTopic) {
        return $oMbqEtForumTopic->canRename->oriValue;
    }
    
    /**
     * judge can m_approve_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Integer  $mode
     * @return  Boolean
     */
    public function canAclMApproveTopic($oMbqEtForumTopic, $mode) {
        if ($mode == 1) {   //approve
            if ($oMbqEtForumTopic->canApprove->oriValue && ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->hold == KunenaForum::UNAPPROVED)) {
                return true;
            }
        } elseif ($mode == 2) { //unapprove
            if ($oMbqEtForumTopic->canApprove->oriValue && ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->hold == KunenaForum::PUBLISHED)) {
                return true;
            }
        }
        return false;
    }
  
}

?>