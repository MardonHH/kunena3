<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtForumTopic');

/**
 * forum topic read class
 * 
 * @since  2012-8-8
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqRdEtForumTopic extends MbqBaseRdEtForumTopic {
    
    public function __construct() {
    }
    
    public function makeProperty(&$oMbqEtForumTopic, $pName, $mbqOpt = array()) {
        switch ($pName) {
            case 'oAuthorMbqEtUser':
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            if ($oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($oMbqEtForumTopic->topicAuthorId->oriValue, array('case' => 'byUserId'))) {
                $oMbqEtForumTopic->oAuthorMbqEtUser = $oMbqEtUser;
            }
            break;
            case 'byOAuthorMbqEtUser':   /* make properties by oAuthorMbqEtUser */
            if ($oMbqEtForumTopic->oAuthorMbqEtUser) {
                if ($oMbqEtForumTopic->oAuthorMbqEtUser->iconUrl->hasSetOriValue()) {
                    $oMbqEtForumTopic->authorIconUrl->setOriValue($oMbqEtForumTopic->oAuthorMbqEtUser->iconUrl->oriValue);
                }
            }
            break;
            case 'oFirstMbqEtForumPost':
            if ($oMbqEtForumTopic->firstPostId->hasSetOriValue()) {
                $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
                if ($oMbqEtForumPost = $oMbqRdEtForumPost->initOMbqEtForumPost($oMbqEtForumTopic->firstPostId->oriValue, array('case' => 'byPostId'))) {
                    $oMbqEtForumTopic->oFirstMbqEtForumPost = $oMbqEtForumPost;
                }
            }
            break;
            case 'oMbqEtForum':
            if ($oMbqEtForumTopic->forumId->hasSetOriValue()) {
                $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
                if ($objsMbqEtForum = $oMbqRdEtForum->getObjsMbqEtForum(array($oMbqEtForumTopic->forumId->oriValue), array('case' => 'byForumIds'))) {
                    $oMbqEtForumTopic->oMbqEtForum = $objsMbqEtForum[0];
                }
            }
            break;
            case 'oKunenaForumTopicUser':
            if (MbqMain::hasLogin()) {
                $objsKunenaForumTopicUser = KunenaForumTopicUserHelper::getTopics($oMbqEtForumTopic->topicId->oriValue, MbqMain::$oCurMbqEtUser->userId->oriValue);
                foreach ($objsKunenaForumTopicUser as $oKunenaForumTopicUser) {
                    if ($oKunenaForumTopicUser->topic_id == $oMbqEtForumTopic->topicId->oriValue) {
                        $oMbqEtForumTopic->mbqBind['oKunenaForumTopicUser'] = $oKunenaForumTopicUser;
                    }
                }
            }
            break;
            case 'oLastReplyMbqEtUser':
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            if ($oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($oMbqEtForumTopic->lastReplyAuthorId->oriValue, array('case' => 'byUserId'))) {
                $oMbqEtForumTopic->oLastReplyMbqEtUser = $oMbqEtUser;
            }
            break;
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
    
    /**
     * get forum topic objs
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byForum' means get data by forum obj.$var is the forum obj.
     * $mbqOpt['case'] = 'subscribed' means get subscribed data.$var is the user id.
     * $mbqOpt['case'] = 'byObjsKunenaForumTopic' means get data by objsKunenaForumTopic.$var is the objsKunenaForumTopic.
     * $mbqOpt['case'] = 'byTopicIds' means get data by topic ids.$var is the ids.
     * $mbqOpt['case'] = 'byAuthor' means get data by author.$var is the MbqEtUser obj.
     * $mbqOpt['top'] = true means get sticky data.
     * $mbqOpt['notIncludeTop'] = true means get not sticky data.
     * $mbqOpt['oFirstMbqEtForumPost'] = true means load oFirstMbqEtForumPost property of topic,default is true.This param used to prevent infinite recursion call for get oMbqEtForumTopic and oFirstMbqEtForumPost and make memory depleted
     * @return  Mixed
     */
    public function getObjsMbqEtForumTopic($var, $mbqOpt) {
        $mbqOpt['oFirstMbqEtForumPost'] = isset($mbqOpt['oFirstMbqEtForumPost']) ? $mbqOpt['oFirstMbqEtForumPost'] : true;
        if ($mbqOpt['case'] == 'byForum') {
            $oMbqEtForum = $var;
            if ($mbqOpt['oMbqDataPage']) {
                $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                if ($mbqOpt['top']) {
                    //require_once(MBQ_APPEXTENTION_PATH.'ExttMbqKunenaModelTopics.php');
                    //$oExttMbqKunenaModelTopics = new ExttMbqKunenaModelTopics();
                    //$arr = $oExttMbqKunenaModelTopics->exttMbqGetRecentTopics(array('catId' => $oMbqEtForum->forumId->oriValue, 'start' => $oMbqDataPage->startNum, 'limit' => $oMbqDataPage->numPerPage, 'mode' => 'sticky', 'time' => -1));
                    require_once(MBQ_APPEXTENTION_PATH.'ExttMbqKunenaModelCategory.php');
                    $oExttMbqKunenaModelCategory = new ExttMbqKunenaModelCategory();
                    $arr = $oExttMbqKunenaModelCategory->exttMbqGetTopics(array('catId' => $oMbqEtForum->forumId->oriValue, 'start' => $oMbqDataPage->startNum, 'limit' => $oMbqDataPage->numPerPage, 'where' => 'AND tt.ordering > 0'));
                    $objsKunenaForumTopic = $arr['topics'];
                    $oMbqDataPage->totalNum = $arr['total'];
                } else {
                    require_once(MBQ_APPEXTENTION_PATH.'ExttMbqKunenaModelCategory.php');
                    $oExttMbqKunenaModelCategory = new ExttMbqKunenaModelCategory();
                    if ($mbqOpt['notIncludeTop']) {
                        $arr = $oExttMbqKunenaModelCategory->exttMbqGetTopics(array('catId' => $oMbqEtForum->forumId->oriValue, 'start' => $oMbqDataPage->startNum, 'limit' => $oMbqDataPage->numPerPage, 'where' => 'AND tt.ordering = 0'));
                    } else {
                        $arr = $oExttMbqKunenaModelCategory->exttMbqGetTopics(array('catId' => $oMbqEtForum->forumId->oriValue, 'start' => $oMbqDataPage->startNum, 'limit' => $oMbqDataPage->numPerPage));
                    }
                    $objsKunenaForumTopic = $arr['topics'];
                    $oMbqDataPage->totalNum = $arr['total'];
                }
                /* common begin */
                $mbqOpt['case'] = 'byObjsKunenaForumTopic';
                $mbqOpt['oMbqDataPage'] = $oMbqDataPage;
                return $this->getObjsMbqEtForumTopic($objsKunenaForumTopic, $mbqOpt);
                /* common end */
            }
        } elseif ($mbqOpt['case'] == 'subscribed') {
            if ($mbqOpt['oMbqDataPage']) {
                $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                $arr = KunenaForumTopicHelper::getLatestTopics(false, $oMbqDataPage->startNum, $oMbqDataPage->numPerPage, array('subscribed' => 1, 'user' => $var));
                $oMbqDataPage->totalNum = $arr[0];
                $objsKunenaForumTopic = $arr[1];
                /* common begin */
                $mbqOpt['case'] = 'byObjsKunenaForumTopic';
                $mbqOpt['oMbqDataPage'] = $oMbqDataPage;
                return $this->getObjsMbqEtForumTopic($objsKunenaForumTopic, $mbqOpt);
                /* common end */
            }
        } elseif ($mbqOpt['case'] == 'byAuthor') {
            if ($mbqOpt['oMbqDataPage']) {
                $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                $arr = KunenaForumTopicHelper::getLatestTopics(false, $oMbqDataPage->startNum, $oMbqDataPage->numPerPage, array('started' => 1, 'user' => $var->userId->oriValue));
                $oMbqDataPage->totalNum = $arr[0];
                $objsKunenaForumTopic = $arr[1];
                /* common begin */
                $mbqOpt['case'] = 'byObjsKunenaForumTopic';
                $mbqOpt['oMbqDataPage'] = $oMbqDataPage;
                return $this->getObjsMbqEtForumTopic($objsKunenaForumTopic, $mbqOpt);
                /* common end */
            }
        } elseif ($mbqOpt['case'] == 'byTopicIds') {
            $objsKunenaForumTopic = KunenaForumTopicHelper::getTopics($var);
            /* common begin */
            $mbqOpt['case'] = 'byObjsKunenaForumTopic';
            return $this->getObjsMbqEtForumTopic($objsKunenaForumTopic, $mbqOpt);
            /* common end */
        } elseif ($mbqOpt['case'] == 'byObjsKunenaForumTopic') {
            if (MbqMain::hasLogin()) {
                require_once(MBQ_APPEXTENTION_PATH.'ExttMbqKunenaForumTopicHelper.php');
                $objsKunenaForumTopic = ExttMbqKunenaForumTopicHelper::exttMbqFetchNewStatus($var);
            } else {
                $objsKunenaForumTopic = $var;
            }
            /* common begin */
            $objsMbqEtForumTopic = array();
            $authorUserIds = array();
            $lastReplyUserIds = array();
            $forumIds = array();
            $topicIds = array();
            $firstPostIds = array();
            foreach ($objsKunenaForumTopic as $oKunenaForumTopic) {
                $objsMbqEtForumTopic[] = $this->initOMbqEtForumTopic($oKunenaForumTopic, array('case' => 'oKunenaForumTopic', 'withAuthor' => false, 'oMbqEtForum' => false, 'oFirstMbqEtForumPost' => false, 'oKunenaForumTopicUser' => false, 'oLastReplyMbqEtUser' => false, 'needExttMbqFetchNewStatus' => false));
            }
            foreach ($objsMbqEtForumTopic as $oMbqEtForumTopic) {
                $authorUserIds[$oMbqEtForumTopic->topicAuthorId->oriValue] = $oMbqEtForumTopic->topicAuthorId->oriValue;
                $lastReplyUserIds[$oMbqEtForumTopic->lastReplyAuthorId->oriValue] = $oMbqEtForumTopic->lastReplyAuthorId->oriValue;
                $forumIds[$oMbqEtForumTopic->forumId->oriValue] = $oMbqEtForumTopic->forumId->oriValue;
                $firstPostIds[$oMbqEtForumTopic->firstPostId->oriValue] = $oMbqEtForumTopic->firstPostId->oriValue;
                $topicIds[$oMbqEtForumTopic->topicId->oriValue] = $oMbqEtForumTopic->topicId->oriValue;
            }
            /* load oMbqEtForum property */
            $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
            $objsMbqEtForum = $oMbqRdEtForum->getObjsMbqEtForum($forumIds, array('case' => 'byForumIds'));
            foreach ($objsMbqEtForum as $oNewMbqEtForum) {
                foreach ($objsMbqEtForumTopic as &$oMbqEtForumTopic) {
                    if ($oNewMbqEtForum->forumId->oriValue == $oMbqEtForumTopic->forumId->oriValue) {
                        $oMbqEtForumTopic->oMbqEtForum = $oNewMbqEtForum;
                    }
                }
            }
            /* load oFirstMbqEtForumPost property */
            if ($mbqOpt['oFirstMbqEtForumPost']) {
                $oMbqRdEtForumPost = MbqMain::$oClk->newObj('MbqRdEtForumPost');
                $objsMbqEtForumPost = $oMbqRdEtForumPost->getObjsMbqEtForumPost($firstPostIds, array('case' => 'byPostIds'));
                foreach ($objsMbqEtForumPost as $oMbqEtForumPost) {
                    foreach ($objsMbqEtForumTopic as &$oMbqEtForumTopic) {
                        if ($oMbqEtForumPost->postId->oriValue == $oMbqEtForumTopic->firstPostId->oriValue) {
                            $oMbqEtForumTopic->oFirstMbqEtForumPost = $oMbqEtForumPost;
                            if ($oMbqEtForumTopic->oFirstMbqEtForumPost->canEdit->oriValue) {
                                $oMbqEtForumTopic->canRename->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canRename.range.yes'));
                            } else {
                                $oMbqEtForumTopic->canRename->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canRename.range.no'));
                            }
                        }
                    }
                }
            }
            /* load topic author */
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $objsAuthorMbqEtUser = $oMbqRdEtUser->getObjsMbqEtUser($authorUserIds, array('case' => 'byUserIds'));
            foreach ($objsMbqEtForumTopic as &$oMbqEtForumTopic) {
                foreach ($objsAuthorMbqEtUser as $oAuthorMbqEtUser) {
                    if ($oMbqEtForumTopic->topicAuthorId->oriValue == $oAuthorMbqEtUser->userId->oriValue) {
                        $oMbqEtForumTopic->oAuthorMbqEtUser = $oAuthorMbqEtUser;
                        break;
                    }
                }
            }
            foreach ($objsMbqEtForumTopic as &$oMbqEtForumTopic) {
                $this->makeProperty($oMbqEtForumTopic, 'byOAuthorMbqEtUser');
            }
            /* load oLastReplyMbqEtUser */
            $objsLastReplyMbqEtUser = $oMbqRdEtUser->getObjsMbqEtUser($lastReplyUserIds, array('case' => 'byUserIds'));
            foreach ($objsMbqEtForumTopic as &$oMbqEtForumTopic) {
                foreach ($objsLastReplyMbqEtUser as $oLastReplyMbqEtUser) {
                    if ($oMbqEtForumTopic->lastReplyAuthorId->oriValue == $oLastReplyMbqEtUser->userId->oriValue) {
                        $oMbqEtForumTopic->oLastReplyMbqEtUser = $oLastReplyMbqEtUser;
                        break;
                    }
                }
            }
            /* load oKunenaForumTopicUser */
            if (MbqMain::hasLogin()) {
                $objsKunenaForumTopicUser = KunenaForumTopicUserHelper::getTopics($topicIds, MbqMain::$oCurMbqEtUser->userId->oriValue);
                foreach ($objsKunenaForumTopicUser as $oKunenaForumTopicUser) {
                    foreach ($objsMbqEtForumTopic as &$oMbqEtForumTopic) {
                        if ($oKunenaForumTopicUser->topic_id == $oMbqEtForumTopic->topicId->oriValue) {
                            $oMbqEtForumTopic->mbqBind['oKunenaForumTopicUser'] = $oKunenaForumTopicUser;
                        }
                    }
                }
                /* make other properties for example:isSubscribed,canSubscribe */
                foreach ($objsMbqEtForumTopic as &$oMbqEtForumTopic) {
                    if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopicUser']) {
                        if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopicUser']->subscribed) {
                            $oMbqEtForumTopic->isSubscribed->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.isSubscribed.range.yes'));
                        } else {
                            $oMbqEtForumTopic->isSubscribed->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.isSubscribed.range.no'));
                        }
                    } else {
                        $oMbqEtForumTopic->isSubscribed->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.isSubscribed.range.no'));
                    }
                    if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']) {
                        if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->authorise('subscribe')) {
                            $oMbqEtForumTopic->canSubscribe->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canSubscribe.range.yes'));
                        } else {
                            $oMbqEtForumTopic->canSubscribe->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canSubscribe.range.no'));
                        }
                    } else {
                        $oMbqEtForumTopic->canSubscribe->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canSubscribe.range.no'));
                    }
                }
            }
            if ($mbqOpt['oMbqDataPage']) {
                $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                $oMbqDataPage->datas = $objsMbqEtForumTopic;
                return $oMbqDataPage;
            } else {
                return $objsMbqEtForumTopic;
            }
            /* common end */
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
    
    /**
     * init one forum topic by condition
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'oKunenaForumTopic' means init forum topic by KunenaForumCategory obj
     * $mbqOpt['case'] = 'byTopicId' means init forum topic by topic id
     * $mbqOpt['withAuthor'] = true means load topic author,default is true
     * $mbqOpt['oLastReplyMbqEtUser'] = true means load oLastReplyMbqEtUser property,default is true
     * $mbqOpt['oMbqEtForum'] = true means load oMbqEtForum property of this topic,default is true
     * $mbqOpt['oFirstMbqEtForumPost'] = true means load oFirstMbqEtForumPost property of this topic,default is true
     * $mbqOpt['oKunenaForumTopicUser'] = true means load oKunenaForumTopicUser bind property of this topic,default is true
     * $mbqOpt['needExttMbqFetchNewStatus'] = true means need execute the ExttMbqKunenaForumTopicHelper::exttMbqFetchNewStatus() method when init forum topic by KunenaForumCategory obj,default is true
     * 
     * @return  Mixed
     */
    public function initOMbqEtForumTopic($var, $mbqOpt) {
        $mbqOpt['withAuthor'] = isset($mbqOpt['withAuthor']) ? $mbqOpt['withAuthor'] : true;
        $mbqOpt['oLastReplyMbqEtUser'] = isset($mbqOpt['oLastReplyMbqEtUser']) ? $mbqOpt['oLastReplyMbqEtUser'] : true;
        $mbqOpt['oMbqEtForum'] = isset($mbqOpt['oMbqEtForum']) ? $mbqOpt['oMbqEtForum'] : true;
        $mbqOpt['oFirstMbqEtForumPost'] = isset($mbqOpt['oFirstMbqEtForumPost']) ? $mbqOpt['oFirstMbqEtForumPost'] : true;
        $mbqOpt['oKunenaForumTopicUser'] = isset($mbqOpt['oKunenaForumTopicUser']) ? $mbqOpt['oKunenaForumTopicUser'] : true;
        $mbqOpt['needExttMbqFetchNewStatus'] = isset($mbqOpt['needExttMbqFetchNewStatus']) ? $mbqOpt['needExttMbqFetchNewStatus'] : true;
        if ($mbqOpt['case'] == 'oKunenaForumTopic') {
            if ($mbqOpt['needExttMbqFetchNewStatus'] && MbqMain::hasLogin()) {
                require_once(MBQ_APPEXTENTION_PATH.'ExttMbqKunenaForumTopicHelper.php');
                $objsKunenaForumTopic = ExttMbqKunenaForumTopicHelper::exttMbqFetchNewStatus(array($var));
                $var = $objsKunenaForumTopic[0];
            }
            $oMbqEtForumTopic = MbqMain::$oClk->newObj('MbqEtForumTopic');
            $oMbqEtForumTopic->totalPostNum->setOriValue($var->posts);
            $oMbqEtForumTopic->topicId->setOriValue($var->id);
            $oMbqEtForumTopic->forumId->setOriValue($var->category_id);
            $oMbqEtForumTopic->firstPostId->setOriValue($var->first_post_id);
            $oMbqEtForumTopic->topicTitle->setOriValue($var->subject);
            $oMbqEtForumTopic->topicContent->setOriValue($var->first_post_message);
            $oMbqEtForumTopic->shortContent->setOriValue(MbqMain::$oMbqCm->getShortContent($var->first_post_message));
            $oMbqEtForumTopic->topicAuthorId->setOriValue($var->first_post_userid);
            $oMbqEtForumTopic->lastReplyAuthorId->setOriValue($var->last_post_userid);
            //$oMbqEtForumTopic->postTime->setOriValue($var->first_post_time);
            $oMbqEtForumTopic->postTime->setOriValue($var->last_post_time ? $var->last_post_time : $var->first_post_time);
            $oMbqEtForumTopic->lastReplyTime->setOriValue($var->last_post_time);
            $oMbqEtForumTopic->replyNumber->setOriValue(($var->posts > 0) ? ($var->posts - 1) : $var->posts);
            $oMbqEtForumTopic->newPost->setOriValue($var->unread ? MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.newPost.range.yes') : MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.newPost.range.no'));
            $oMbqEtForumTopic->viewNumber->setOriValue($var->hits);
            $oMbqEtForumTopic->mbqBind['oKunenaForumTopic'] = $var;
            if ($mbqOpt['oMbqEtForum']) {
                /* load oMbqEtForum property */
                $this->makeProperty($oMbqEtForumTopic, 'oMbqEtForum');
            }
            if ($mbqOpt['withAuthor']) {
                /* load topic author */
                $this->makeProperty($oMbqEtForumTopic, 'oAuthorMbqEtUser');
            }
            if ($mbqOpt['oLastReplyMbqEtUser']) {
                /* load oLastReplyMbqEtUser property */
                $this->makeProperty($oMbqEtForumTopic, 'oLastReplyMbqEtUser');
            }
            $this->makeProperty($oMbqEtForumTopic, 'byOAuthorMbqEtUser');
            if ($mbqOpt['oFirstMbqEtForumPost']) {
                /* load oFirstMbqEtForumPost property */
                $this->makeProperty($oMbqEtForumTopic, 'oFirstMbqEtForumPost');
                if ($oMbqEtForumTopic->oFirstMbqEtForumPost && $oMbqEtForumTopic->oFirstMbqEtForumPost->canEdit->oriValue) {
                    $oMbqEtForumTopic->canRename->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canRename.range.yes'));
                } else {
                    $oMbqEtForumTopic->canRename->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canRename.range.no'));
                }
            }
            if ($oMbqEtForumTopic->oFirstMbqEtForumPost && $oMbqEtForumTopic->oFirstMbqEtForumPost->mbqBind['oKunenaForumMessage'] && $oMbqEtForumTopic->oFirstMbqEtForumPost->mbqBind['oKunenaForumMessage']->authorise('reply')) {
                $oMbqEtForumTopic->canReply->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canReply.range.yes'));
            } else {
                $oMbqEtForumTopic->canReply->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canReply.range.no'));
            }
            if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->ordering > 0) {
                $oMbqEtForumTopic->isSticky->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.isSticky.range.yes'));
            } else {
                $oMbqEtForumTopic->isSticky->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.isSticky.range.no'));
            }
            if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->authorise('sticky')) {
                $oMbqEtForumTopic->canStick->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canStick.range.yes'));
            } else {
                $oMbqEtForumTopic->canStick->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canStick.range.no'));
            }
            if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->hold == 3 || $oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->hold == 2) {
                $oMbqEtForumTopic->isDeleted->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.isDeleted.range.yes'));
            } else {
                $oMbqEtForumTopic->isDeleted->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.isDeleted.range.no'));
            }
            if ($oMbqEtForumTopic->isDeleted->oriValue) {
                if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->authorise('undelete')) {
                    $oMbqEtForumTopic->canDelete->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canDelete.range.yes'));
                } else {
                    $oMbqEtForumTopic->canDelete->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canDelete.range.no'));
                }
            } else {
                if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->authorise('delete')) {
                    $oMbqEtForumTopic->canDelete->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canDelete.range.yes'));
                } else {
                    $oMbqEtForumTopic->canDelete->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canDelete.range.no'));
                }
            }
            if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->locked == 1) {
                $oMbqEtForumTopic->isClosed->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.isClosed.range.yes'));
            } else {
                $oMbqEtForumTopic->isClosed->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.isClosed.range.no'));
            }
            if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->authorise('lock')) {
                $oMbqEtForumTopic->canClose->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canClose.range.yes'));
            } else {
                $oMbqEtForumTopic->canClose->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canClose.range.no'));
            }
            if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->hold == KunenaForum::PUBLISHED) {
                $oMbqEtForumTopic->isApproved->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.isApproved.range.yes'));
            } elseif ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->hold == KunenaForum::UNAPPROVED) {
                $oMbqEtForumTopic->isApproved->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.isApproved.range.no'));
            }
            if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->authorise('approve') && (($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->hold == KunenaForum::PUBLISHED) || ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->hold == KunenaForum::UNAPPROVED))) {
                $oMbqEtForumTopic->canApprove->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canApprove.range.yes'));
            } else {
                $oMbqEtForumTopic->canApprove->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canApprove.range.no'));
            }
            if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->authorise('move')) {
                $oMbqEtForumTopic->canMove->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canClose.range.yes'));
            } else {
                $oMbqEtForumTopic->canMove->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canClose.range.no'));
            }
            if ($mbqOpt['oKunenaForumTopicUser']) {
                /* load oKunenaForumTopicUser */
                $this->makeProperty($oMbqEtForumTopic, 'oKunenaForumTopicUser');
                /* make other properties for example:isSubscribed,canSubscribe */
                if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopicUser']) {
                    if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopicUser']->subscribed) {
                        $oMbqEtForumTopic->isSubscribed->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.isSubscribed.range.yes'));
                    } else {
                        $oMbqEtForumTopic->isSubscribed->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.isSubscribed.range.no'));
                    }
                } else {
                    $oMbqEtForumTopic->isSubscribed->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.isSubscribed.range.no'));
                }
                if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']) {
                    if ($oMbqEtForumTopic->mbqBind['oKunenaForumTopic']->authorise('subscribe')) {
                        $oMbqEtForumTopic->canSubscribe->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canSubscribe.range.yes'));
                    } else {
                        $oMbqEtForumTopic->canSubscribe->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canSubscribe.range.no'));
                    }
                } else {
                    $oMbqEtForumTopic->canSubscribe->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.canSubscribe.range.no'));
                }
            }
            return $oMbqEtForumTopic;
        } elseif ($mbqOpt['case'] == 'byTopicId') {
            $topicId = $var;
            require_once(MBQ_APPEXTENTION_PATH.'ExttMbqKunenaModelTopic.php');
            $oExttMbqKunenaModelTopic = new ExttMbqKunenaModelTopic();
            //$oExttMbqKunenaModelTopic->setState('item.id', $topicId);
            if (($oKunenaForumTopic = $oExttMbqKunenaModelTopic->exttMbqGetTopic(array('topicId' => $topicId))) && $oKunenaForumTopic->id) {
                $mbqOpt['case'] = 'oKunenaForumTopic';
                return $this->initOMbqEtForumTopic($oKunenaForumTopic, $mbqOpt);
            }
            return false;
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
  
}

?>