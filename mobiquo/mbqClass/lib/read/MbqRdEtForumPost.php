<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtForumPost');

/**
 * forum post read class
 * 
 * @since  2012-8-13
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqRdEtForumPost extends MbqBaseRdEtForumPost {
    
    public function __construct() {
    }
    
    public function makeProperty(&$oMbqEtForumPost, $pName, $mbqOpt = array()) {
        switch ($pName) {
            case 'oAuthorMbqEtUser':
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            if ($oMbqEtUser = $oMbqRdEtUser->initOMbqEtUser($oMbqEtForumPost->postAuthorId->oriValue, array('case' => 'byUserId'))) {
                $oMbqEtForumPost->oAuthorMbqEtUser = $oMbqEtUser;
            }
            break;
            case 'objsMbqEtAtt':
            $postIds = array($oMbqEtForumPost->postId->oriValue);
            $oMbqRdEtAtt = MbqMain::$oClk->newObj('MbqRdEtAtt');
            $objsMbqEtAtt = $oMbqRdEtAtt->getObjsMbqEtAtt($postIds, array('case' => 'byForumPostIds'));
            $oMbqEtForumPost->objsMbqEtAtt = $objsMbqEtAtt;
            break;
            case 'objsNotInContentMbqEtAtt':
            $attIds = MbqMain::$oMbqCm->getAttIdsFromContent($oMbqEtForumPost->postContent->oriValue);
            foreach ($oMbqEtForumPost->objsMbqEtAtt as $oMbqEtAtt) {
                if (!in_array($oMbqEtAtt->attId->oriValue, $attIds)) {
                    $oMbqEtForumPost->objsNotInContentMbqEtAtt[] = $oMbqEtAtt;
                }
            }
            break;
            case 'byOAuthorMbqEtUser':   /* make properties by oAuthorMbqEtUser */
            if ($oMbqEtForumPost->oAuthorMbqEtUser) {
                if ($oMbqEtForumPost->oAuthorMbqEtUser->isOnline->hasSetOriValue()) {
                    $oMbqEtForumPost->isOnline->setOriValue($oMbqEtForumPost->oAuthorMbqEtUser->isOnline->oriValue ? MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.isOnline.range.yes') : MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.isOnline.range.no'));
                }
                if ($oMbqEtForumPost->oAuthorMbqEtUser->iconUrl->hasSetOriValue()) {
                    $oMbqEtForumPost->authorIconUrl->setOriValue($oMbqEtForumPost->oAuthorMbqEtUser->iconUrl->oriValue);
                }
            }
            break;
            case 'oMbqEtForum':
            if ($oMbqEtForumPost->forumId->hasSetOriValue()) {
                $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
                if ($objsMbqEtForum = $oMbqRdEtForum->getObjsMbqEtForum(array($oMbqEtForumPost->forumId->oriValue), array('case' => 'byForumIds'))) {
                    $oMbqEtForumPost->oMbqEtForum = $objsMbqEtForum[0];
                }
            }
            break;
            case 'oMbqEtForumTopic':
            if ($oMbqEtForumPost->topicId->hasSetOriValue()) {
                $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
                if ($oMbqEtForumTopic = $oMbqRdEtForumTopic->initOMbqEtForumTopic($oMbqEtForumPost->topicId->oriValue, array('case' => 'byTopicId', 'oFirstMbqEtForumPost' => false))) {  /* must set 'oFirstMbqEtForumPost' to false,otherwise will cause infinite recursion call for get oMbqEtForumTopic and oFirstMbqEtForumPost and make memory depleted!!! */
                    $oMbqEtForumPost->oMbqEtForumTopic = $oMbqEtForumTopic;
                }
            }
            break;
            case 'objsMbqEtThank':
            $oMbqRdEtThank = MbqMain::$oClk->newObj('MbqRdEtThank');
            $objsMbqEtThank = $oMbqRdEtThank->getObjsMbqEtThank(array($oMbqEtForumPost->postId->oriValue), array('case' => 'byForumPostIds'));
            $oMbqEtForumPost->objsMbqEtThank = $objsMbqEtThank;
            break;
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
    
    /**
     * get forum post objs
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byTopic' means get data by forum topic obj.$var is the forum topic obj.
     * $mbqOpt['case'] = 'byPostIds' means get data by post ids.$var is the ids.
     * $mbqOpt['case'] = 'byObjsKunenaForumMessage' means get data by objsKunenaForumMessage.$var is the objsKunenaForumMessage.
     * $mbqOpt['case'] = 'byReplyUser' means get data by reply user.$var is the MbqEtUser obj.
     * @return  Mixed
     */
    public function getObjsMbqEtForumPost($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'byTopic') {
            $oMbqEtForumTopic = $var;
            if ($mbqOpt['oMbqDataPage']) {
                $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                require_once(MBQ_APPEXTENTION_PATH.'ExttMbqKunenaModelTopic.php');
                $oExttMbqKunenaModelTopic = new ExttMbqKunenaModelTopic();
                //$oExttMbqKunenaModelTopic->setState('item.id', $oMbqEtForumTopic->topicId->oriValue);
                //$oExttMbqKunenaModelTopic->setState('list.start', $oMbqDataPage->startNum);
                //$oExttMbqKunenaModelTopic->setState('list.limit', $oMbqDataPage->numPerPage);
                $objsKunenaForumMessage = $oExttMbqKunenaModelTopic->exttMbqGetMessages(array('topicId' => $oMbqEtForumTopic->topicId->oriValue, 'start' => $oMbqDataPage->startNum, 'limit' => $oMbqDataPage->numPerPage));
                /* common begin */
                $mbqOpt['case'] = 'byObjsKunenaForumMessage';
                $mbqOpt['oMbqDataPage'] = $oMbqDataPage;
                return $this->getObjsMbqEtForumPost($objsKunenaForumMessage, $mbqOpt);
                /* common end */
            }
        } elseif ($mbqOpt['case'] == 'byPostIds') {
            $objsKunenaForumMessage = KunenaForumMessageHelper::getMessages($var);
            /* common begin */
            $mbqOpt['case'] = 'byObjsKunenaForumMessage';
            return $this->getObjsMbqEtForumPost($objsKunenaForumMessage, $mbqOpt);
            /* common end */
        } elseif ($mbqOpt['case'] == 'byReplyUser') {
            if ($mbqOpt['oMbqDataPage']) {
                $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                require_once(MBQ_APPEXTENTION_PATH.'ExttMbqKunenaForumMessageHelper.php');
                $arr = ExttMbqKunenaForumMessageHelper::exttMbqGetLatestMessages(false, $oMbqDataPage->startNum, $oMbqDataPage->numPerPage, array('user' => $var->userId->oriValue, 'exttMbqIsReply' => true, 'starttime' => -1));
                $oMbqDataPage->totalNum = $arr[0];
                $objsKunenaForumMessage = $arr[1];
                /* common begin */
                $mbqOpt['case'] = 'byObjsKunenaForumMessage';
                $mbqOpt['oMbqDataPage'] = $oMbqDataPage;
                return $this->getObjsMbqEtForumPost($objsKunenaForumMessage, $mbqOpt);
                /* common end */
            }
        } elseif ($mbqOpt['case'] == 'byObjsKunenaForumMessage') {
            $objsKunenaForumMessage = $var;
            /* common begin */
            $objsMbqEtForumPost = array();
            $authorUserIds = array();
            $forumIds = array();
            $topicIds = array();
            foreach ($objsKunenaForumMessage as $oKunenaForumMessage) {
                $objsMbqEtForumPost[] = $this->initOMbqEtForumPost($oKunenaForumMessage, array('case' => 'oKunenaForumMessage', 'withAuthor' => false, 'withAtt' => false, 'withObjsNotInContentMbqEtAtt' => false, 'oMbqEtForum' => false, 'oMbqEtForumTopic' => false, 'objsMbqEtThank' => false));
            }
            foreach ($objsMbqEtForumPost as $oMbqEtForumPost) {
                $authorUserIds[$oMbqEtForumPost->postAuthorId->oriValue] = $oMbqEtForumPost->postAuthorId->oriValue;
                $forumIds[$oMbqEtForumPost->forumId->oriValue] = $oMbqEtForumPost->forumId->oriValue;
                $topicIds[$oMbqEtForumPost->topicId->oriValue] = $oMbqEtForumPost->topicId->oriValue;
            }
            /* load oMbqEtForum property */
            $oMbqRdEtForum = MbqMain::$oClk->newObj('MbqRdEtForum');
            $objsMbqEtForum = $oMbqRdEtForum->getObjsMbqEtForum($forumIds, array('case' => 'byForumIds'));
            foreach ($objsMbqEtForum as $oNewMbqEtForum) {
                foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
                    if ($oNewMbqEtForum->forumId->oriValue == $oMbqEtForumPost->forumId->oriValue) {
                        $oMbqEtForumPost->oMbqEtForum = $oNewMbqEtForum;
                    }
                }
            }
            /* load oMbqEtForumTopic property */
            $oMbqRdEtForumTopic = MbqMain::$oClk->newObj('MbqRdEtForumTopic');
            $objsMbqEtFroumTopic = $oMbqRdEtForumTopic->getObjsMbqEtForumTopic($topicIds, array('case' => 'byTopicIds', 'oFirstMbqEtForumPost' => false));  /* must set 'oFirstMbqEtForumPost' to false,otherwise will cause infinite recursion call for get oMbqEtForumTopic and oFirstMbqEtForumPost and make memory depleted!!! */
            foreach ($objsMbqEtFroumTopic as $oNewMbqEtFroumTopic) {
                foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
                    if ($oNewMbqEtFroumTopic->topicId->oriValue == $oMbqEtForumPost->topicId->oriValue) {
                        $oMbqEtForumPost->oMbqEtForumTopic = $oNewMbqEtFroumTopic;
                    }
                }
            }
            /* load post author */
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $objsAuthorMbqEtUser = $oMbqRdEtUser->getObjsMbqEtUser($authorUserIds, array('case' => 'byUserIds'));
            $postIds = array();
            foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
                $postIds[] = $oMbqEtForumPost->postId->oriValue;
                foreach ($objsAuthorMbqEtUser as $oAuthorMbqEtUser) {
                    if ($oMbqEtForumPost->postAuthorId->oriValue == $oAuthorMbqEtUser->userId->oriValue) {
                        $oMbqEtForumPost->oAuthorMbqEtUser = $oAuthorMbqEtUser;
                        break;
                    }
                }
            }
            /* load attachment */
            $oMbqRdEtAtt = MbqMain::$oClk->newObj('MbqRdEtAtt');
            $objsMbqEtAtt = $oMbqRdEtAtt->getObjsMbqEtAtt($postIds, array('case' => 'byForumPostIds'));
            foreach ($objsMbqEtAtt as $oMbqEtAtt) {
                foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
                    if ($oMbqEtAtt->isForumPostAtt() && ($oMbqEtAtt->postId->oriValue == $oMbqEtForumPost->postId->oriValue)) {
                        $oMbqEtForumPost->objsMbqEtAtt[] = $oMbqEtAtt;
                        break;
                    }
                }
            }
            /* load objsNotInContentMbqEtAtt */
            foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
                $this->makeProperty($oMbqEtForumPost, 'objsNotInContentMbqEtAtt');
            }
            foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
                $this->makeProperty($oMbqEtForumPost, 'byOAuthorMbqEtUser');
            }
            /* load objsMbqEtThank property and make related properties/flags */
            $oMbqRdEtThank = MbqMain::$oClk->newObj('MbqRdEtThank');
            $objsMbqEtThank = $oMbqRdEtThank->getObjsMbqEtThank($postIds, array('case' => 'byForumPostIds'));
            foreach ($objsMbqEtThank as $oMbqEtThank) {
                foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
                    if ($oMbqEtThank->key->oriValue == $oMbqEtForumPost->postId->oriValue) {
                        $oMbqEtForumPost->objsMbqEtThank[] = $oMbqEtThank;
                        break;
                    }
                }
            }
            foreach ($objsMbqEtForumPost as &$oMbqEtForumPost) {
                $oMbqEtForumPost->thankCount->setOriValue(count($oMbqEtForumPost->objsMbqEtThank));
                $isThankedByMe = false;
                if (MbqMain::hasLogin()) {
                    foreach ($oMbqEtForumPost->objsMbqEtThank as $oMbqEtThank) {
                        if ($oMbqEtThank->userId->oriValue == MbqMain::$oCurMbqEtUser->userId->oriValue) {
                            $isThankedByMe = true;
                        }
                    }
                }
                if ($oMbqEtForumPost->mbqBind['oKunenaForumMessage']->authorise('thankyou') && !$isThankedByMe) {
                    $oMbqEtForumPost->canThank->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canThank.range.yes'));
                } else {
                    $oMbqEtForumPost->canThank->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canThank.range.no'));
                }
            }
            /* common end */
            if ($mbqOpt['oMbqDataPage']) {
                $oMbqDataPage = $mbqOpt['oMbqDataPage'];
                $oMbqDataPage->datas = $objsMbqEtForumPost;
                return $oMbqDataPage;
            } else {
                return $objsMbqEtForumPost;
            }
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
    
    /**
     * init one forum post by condition
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'oKunenaForumMessage' means init forum post by KunenaForumMessage obj
     * $mbqOpt['case'] = 'byPostId' means init forum post by post id
     * $mbqOpt['withAuthor'] = true means load post author,default is true
     * $mbqOpt['withAtt'] = true means load post attachments,default is true
     * $mbqOpt['withObjsNotInContentMbqEtAtt'] = true means load the attachement objs not in the content,default is true
     * $mbqOpt['oMbqEtForum'] = true means load oMbqEtForum property of this post,default is true
     * $mbqOpt['oMbqEtForumTopic'] = true means load oMbqEtForumTopic property of this post,default is true
     * $mbqOpt['objsMbqEtThank'] = true means load objsMbqEtThank property of this post,default is true
     * @return  Mixed
     */
    public function initOMbqEtForumPost($var, $mbqOpt) {
        $mbqOpt['withAuthor'] = isset($mbqOpt['withAuthor']) ? $mbqOpt['withAuthor'] : true;
        $mbqOpt['withAtt'] = isset($mbqOpt['withAtt']) ? $mbqOpt['withAtt'] : true;
        $mbqOpt['withObjsNotInContentMbqEtAtt'] = isset($mbqOpt['withObjsNotInContentMbqEtAtt']) ? $mbqOpt['withObjsNotInContentMbqEtAtt'] : true;
        $mbqOpt['oMbqEtForum'] = isset($mbqOpt['oMbqEtForum']) ? $mbqOpt['oMbqEtForum'] : true;
        $mbqOpt['oMbqEtForumTopic'] = isset($mbqOpt['oMbqEtForumTopic']) ? $mbqOpt['oMbqEtForumTopic'] : true;
        $mbqOpt['objsMbqEtThank'] = isset($mbqOpt['objsMbqEtThank']) ? $mbqOpt['objsMbqEtThank'] : true;
        if ($mbqOpt['case'] == 'oKunenaForumMessage') {
            require_once(MBQ_APPEXTENTION_PATH.'ExttMbqKunenaViewTopic.php');
            $oExttMbqKunenaViewTopic = new ExttMbqKunenaViewTopic();
            $oMbqEtForumPost = MbqMain::$oClk->newObj('MbqEtForumPost');
            $oMbqEtForumPost->postId->setOriValue($var->id);
            $oMbqEtForumPost->parentPostId->setOriValue($var->parent);
            $oMbqEtForumPost->forumId->setOriValue($var->catid);
            $oMbqEtForumPost->topicId->setOriValue($var->thread);
            $oMbqEtForumPost->postTitle->setOriValue($var->subject);
            $oMbqEtForumPost->postContent->setOriValue($var->message);
            $newVar = clone $var;
            $newVar->message = MbqMain::$oMbqCm->replaceCodes($newVar->message, 'quote|email|ebay|map');  /* do some change for process */
            $oMbqEtForumPost->postContent->setAppDisplayValue($oExttMbqKunenaViewTopic->exttMbqReturnDisplayMessageContents($var));
            $oMbqEtForumPost->postContent->setTmlDisplayValue($this->processContentForDisplay($oExttMbqKunenaViewTopic->exttMbqReturnDisplayMessageContents($newVar), true, $oMbqEtForumPost));
            $oMbqEtForumPost->postContent->setTmlDisplayValueNoHtml($this->processContentForDisplay($oExttMbqKunenaViewTopic->exttMbqReturnDisplayMessageContents($newVar), false, $oMbqEtForumPost));
            $oMbqEtForumPost->shortContent->setOriValue(MbqMain::$oMbqCm->getShortContent($var->message));
            $oMbqEtForumPost->postAuthorId->setOriValue($var->userid);
            if ($var->authorise('edit')) {
                $oMbqEtForumPost->canEdit->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canEdit.range.yes'));
            } else {
                $oMbqEtForumPost->canEdit->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canEdit.range.no'));
            }
            if ($var->authorise('move')) {
                $oMbqEtForumPost->canMove->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canMove.range.yes'));
            } else {
                $oMbqEtForumPost->canMove->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canMove.range.no'));
            }
            $oMbqEtForumPost->postTime->setOriValue($var->time);
            $oMbqEtForumPost->mbqBind['oKunenaForumMessage'] = $var;
            if ($var->hold == KunenaForum::PUBLISHED) {
                $oMbqEtForumPost->isApproved->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.isApproved.range.yes'));
            } elseif ($var->hold == KunenaForum::UNAPPROVED) {
                $oMbqEtForumPost->isApproved->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.isApproved.range.no'));
            }
            if ($var->authorise('approve') && (($var->hold == KunenaForum::PUBLISHED) || ($var->hold == KunenaForum::UNAPPROVED))) {
                $oMbqEtForumPost->canApprove->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canApprove.range.yes'));
            } else {
                $oMbqEtForumPost->canApprove->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canApprove.range.no'));
            }
            if ($mbqOpt['oMbqEtForum']) {
                /* load oMbqEtForum property */
                $this->makeProperty($oMbqEtForumPost, 'oMbqEtForum');
            }
            if ($mbqOpt['oMbqEtForumTopic']) {
                /* load oMbqEtForumTopic property */
                $this->makeProperty($oMbqEtForumPost, 'oMbqEtForumTopic');
            }
            if ((MbqMain::$oMbqConfig->getCfg('forum.report_post')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.forum.report_post.range.support')) && MbqMain::hasLogin() && $oMbqEtForumPost->mbqBind['oKunenaForumMessage']->authorise('read')) {
                $oMbqEtForumPost->canReport->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canReport.range.yes'));
            } else {
                $oMbqEtForumPost->canReport->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canReport.range.no'));
            }
            if ($mbqOpt['withAuthor']) {
                /* load post author */
                $this->makeProperty($oMbqEtForumPost, 'oAuthorMbqEtUser');
            }
            if ($mbqOpt['withAtt']) {
                /* load attachment */
                $this->makeProperty($oMbqEtForumPost, 'objsMbqEtAtt');
            }
            if ($mbqOpt['withObjsNotInContentMbqEtAtt']) {
                /* load objsNotInContentMbqEtAtt */
                $this->makeProperty($oMbqEtForumPost, 'objsNotInContentMbqEtAtt');
            }
            $this->makeProperty($oMbqEtForumPost, 'byOAuthorMbqEtUser');
            if ($oMbqEtForumPost->mbqBind['oKunenaForumMessage']->hold == 3 || $oMbqEtForumPost->mbqBind['oKunenaForumMessage']->hold == 2) {
                $oMbqEtForumPost->isDeleted->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.isDeleted.range.yes'));
            } else {
                $oMbqEtForumPost->isDeleted->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.isDeleted.range.no'));
            }
            if ($oMbqEtForumPost->isDeleted->oriValue) {
                if ($oMbqEtForumPost->mbqBind['oKunenaForumMessage']->authorise('undelete')) {
                    $oMbqEtForumPost->canDelete->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canDelete.range.yes'));
                } else {
                    $oMbqEtForumPost->canDelete->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canDelete.range.no'));
                }
            } else {
                if ($oMbqEtForumPost->mbqBind['oKunenaForumMessage']->authorise('delete')) {
                    $oMbqEtForumPost->canDelete->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canDelete.range.yes'));
                } else {
                    $oMbqEtForumPost->canDelete->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canDelete.range.no'));
                }
            }
            if ($mbqOpt['objsMbqEtThank']) {
                /* load objsMbqEtThank property and make related properties/flags */
                $this->makeProperty($oMbqEtForumPost, 'objsMbqEtThank');
                $oMbqEtForumPost->thankCount->setOriValue(count($oMbqEtForumPost->objsMbqEtThank));
                $isThankedByMe = false;
                if (MbqMain::hasLogin()) {
                    foreach ($oMbqEtForumPost->objsMbqEtThank as $oMbqEtThank) {
                        if ($oMbqEtThank->userId->oriValue == MbqMain::$oCurMbqEtUser->userId->oriValue) {
                            $isThankedByMe = true;
                        }
                    }
                }
                if ($oMbqEtForumPost->mbqBind['oKunenaForumMessage']->authorise('thankyou') && !$isThankedByMe) {
                    $oMbqEtForumPost->canThank->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canThank.range.yes'));
                } else {
                    $oMbqEtForumPost->canThank->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.canThank.range.no'));
                }
            }
            return $oMbqEtForumPost;
        } elseif ($mbqOpt['case'] == 'byPostId') {
            require_once(KPATH_ADMIN.'/libraries/forum/message/helper.php');
            if (($oKunenaForumMessage = KunenaForumMessageHelper::get($var)) && $oKunenaForumMessage->id) {
                $mbqOpt['case'] = 'oKunenaForumMessage';
                return $this->initOMbqEtForumPost($oKunenaForumMessage, $mbqOpt);
            }
            return false;
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
    
    /**
     * process content for display in mobile app
     *
     * @params  String  $content
     * @params  Boolean  $returnHtml
     * @param  Object  $oMbqEtForumPost
     * @return  String
     */
    public function processContentForDisplay($content, $returnHtml, $oMbqEtForumPost) {
        /*
        support bbcode:url/img/quote
        support html:br/i/b/u/font+color(red/blue)
        <strong> -> <b>
        attention input param:return_html
        attention output param:post_content
        */
        $post = $content;
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
    	} else {
    	    $post = preg_replace('/<br \/>/i', "\n", $post);
    		$post = str_ireplace('[hr]', "\n____________________________________\n", $post);
            $post = str_ireplace('<hr />', "\n____________________________________\n", $post);
    		//$post = strip_tags($post);
    		$post = html_entity_decode($post, ENT_QUOTES, 'UTF-8');
            // strip remaining bbcode
            //$post = preg_replace('/\[\/?.*?\]/i', '', $post);
    	}
    	$post = preg_replace('/\[quote="(.*?)".*?\]/i', '$1 wrote:[quote]', $post);
    	$post = preg_replace('/<div class="kmsgtext-quote">(.*?)<\/div>/i', '[quote]$1[/quote]', $post);
    	$post = preg_replace('/<div class="highlight"><pre xml\:php>(.*?)<\/pre><\/div>/i', '[quote]$1[/quote]', $post);
    	$post = preg_replace('/<div class="kmsgtext\-hide">(.*?)<\/div>/i', '[quote]$1[/quote]', $post);
    	$post = preg_replace('/<div class="kmsgtext-confidential">(.*?)<\/div>/i', '[quote]$1[/quote]', $post);
    	$post = preg_replace('/\[email\](.*?)\[\/email\]/i', '[url=$1]$1[/url]', $post);
    	//$post = preg_replace('/\[ebay\](.*?)\[\/ebay\]/i', '[url=http://www.ebay.com/sch/i.html?_nkw=$1]$1 on eBay[/url]', $post);
    	$post = preg_replace_callback('/\[ebay\](.*?)\[\/ebay\]/i', create_function('$matches','return \'[url=http://www.ebay.com/sch/i.html?_nkw=\'.urlencode($matches[1]).\']\'.$matches[1].\' on eBay[/url]\';'), $post);
    	//$post = preg_replace('/\[map\](.*?)\[\/map\]/i', '[url=https://maps.google.com/maps?q=$1]$1 on Google Maps[/url]', $post);
    	$post = preg_replace_callback('/\[map\](.*?)\[\/map\]/i', create_function('$matches','return \'[url=https://maps.google.com/maps?q=\'.urlencode($matches[1]).\']\'.$matches[1].\' on Google Maps[/url]\';'), $post);
    	/* replace the expression begin */
    	$post = preg_replace('/<img [^>]*?src="[^>]*?cool.png" [^>]*?class="bbcode_smiley" \/>/i', 'B)', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?sad.png" [^>]*?class="bbcode_smiley" \/>/i', ':(', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?smile.png" [^>]*?class="bbcode_smiley" \/>/i', ':)', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?cheerful.png" [^>]*?class="bbcode_smiley" \/>/i', ':cheer:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?wink.png" [^>]*?class="bbcode_smiley" \/>/i', ';)', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?tongue.png" [^>]*?class="bbcode_smiley" \/>/i', ':P', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?angry.png" [^>]*?class="bbcode_smiley" \/>/i', ':angry:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?unsure.png" [^>]*?class="bbcode_smiley" \/>/i', ':unsure:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?shocked.png" [^>]*?class="bbcode_smiley" \/>/i', ':ohmy:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?wassat.png" [^>]*?class="bbcode_smiley" \/>/i', ':huh:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?ermm.png" [^>]*?class="bbcode_smiley" \/>/i', ':dry:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?grin.png" [^>]*?class="bbcode_smiley" \/>/i', ':lol:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?sick.png" [^>]*?class="bbcode_smiley" \/>/i', ':sick:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?silly.png" [^>]*?class="bbcode_smiley" \/>/i', ':silly:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?blink.png" [^>]*?class="bbcode_smiley" \/>/i', ':blink:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?blush.png" [^>]*?class="bbcode_smiley" \/>/i', ':blush:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?blush.png" [^>]*?class="bbcode_smiley" \/>/i', ':oops:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?kissing.png" [^>]*?class="bbcode_smiley" \/>/i', ':kiss:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?w00t.png" [^>]*?class="bbcode_smiley" \/>/i', ':woohoo:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?sideways.png" [^>]*?class="bbcode_smiley" \/>/i', ':side:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?dizzy.png" [^>]*?class="bbcode_smiley" \/>/i', ':S', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?devil.png" [^>]*?class="bbcode_smiley" \/>/i', ':evil:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?whistling.png" [^>]*?class="bbcode_smiley" \/>/i', ':whistle:', $post);
    	$post = preg_replace('/<img [^>]*?src="[^>]*?pinch.png" [^>]*?class="bbcode_smiley" \/>/i', ':pinch:', $post);
    	/* replace the expression end */
    	//$post = preg_replace('/<img .*?src="(.*?)"{1,2} .*?\/>/i', '[img]$1[/img]', $post);
    	$post = preg_replace('/<img .*?src="(.*?)" .*?\/>/i', '[img]$1[/img]', $post);
    	$post = preg_replace('/<a .*?href="(.*?)".*?>(.*?)<\/a>/i', '[url=$1]$2[/url]', $post);
    	$post = preg_replace_callback('/\[img\]([\\|\/].*?)\[\/img\]/i', create_function('$matches','return "[img]".MbqMain::$oMbqAppEnv->joomlaRootUrl.substr($matches[1],1)."[/img]";'), $post);
    	$post = preg_replace_callback('/\[url=([\\|\/].*?)\](.*?)\[\/url\]/i', create_function('$matches','return "[url=".MbqMain::$oMbqAppEnv->joomlaRootUrl.substr($matches[1],1)."]".$matches[2]."[/url]";'), $post);
    	$post = str_ireplace('<strong>', '<b>', $post);
    	$post = str_ireplace('</strong>', '</b>', $post);
    	$post = preg_replace_callback('/<span style=\"color:(\#.*?)\">(.*?)<\/span>/is', create_function('$matches','return MbqMain::$oMbqCm->mbColorConvert($matches[1], $matches[2]);'), $post);
    	$post = preg_replace('/<span style=\"color:(.*?)\">(.*?)<\/span>/is', '<font color="$1">$2</font>', $post);
    	$post = preg_replace('/<object .*?>.*?<embed src="(.*?)".*?><\/embed><\/object>/is', '[url=$1]$1[/url]', $post); /* for youtube content etc. */
    	$post = preg_replace('/<div class="bbcode_indent" .*?>(.*?)<\/div>/is', "\t\t".'$1', $post);
    	$post = preg_replace('/<div class="kspoiler".*?><div class="kspoiler\-header".*?>.*?<\/div><div class="kspoiler\-wrapper".*?><div class="kspoiler\-content".*?>(.*?)<\/div><\/div><\/div>/i', '[spoiler]$1[/spoiler]', $post);
    	if ($returnHtml) {
    	    $post = str_ireplace('</div>', '</div><br />', $post);
    	    $post = strip_tags($post, '<br><i><b><u><font>');
    	    if (KunenaForum::version() == '2.0.1') {
        	    //$post = preg_replace_callback('/\[url=(.*?)\](.*?)\[\/url\]/i', create_function('$matches','return "[url=".str_ireplace("&", "&amp;", $matches[1])."]".str_ireplace("&", "&amp;", $matches[2])."[/url]";'), $post);
        	    $post = preg_replace_callback('/\[url=(.*?)\](.*?)\[\/url\]/i', create_function('$matches','return "[url=".str_ireplace("&amp;", "&", $matches[1])."]".$matches[2]."[/url]";'), $post);   //!!!sometimes contain &amp;
        	    $post = preg_replace_callback('/\[url=(.*?)\](.*?)\[\/url\]/i', create_function('$matches','return "[url=".str_ireplace("&", "&amp;", $matches[1])."]".str_ireplace("&", "&amp;", $matches[2])."[/url]";'), $post);
        	    //$post = preg_replace_callback('/\[img\](.*?)\[\/img\]/i', create_function('$matches','return "[img]".str_ireplace("&", "&amp;", $matches[1])."[/img]";'), $post);
    	        preg_match_all('/\[email\](.*?)\[\/email\]/is', $oMbqEtForumPost->postContent->oriValue, $oriEmails);
    	        $oriEmails = $oriEmails[1];
    	        $post = preg_replace('/\[email\].*?\[\/email\]/is', '[email]temp[/email]', $post);
    	        foreach ($oriEmails as $oriEmail) {
    	            $post = preg_replace('/\[email\]temp\[\/email\]/is', '[email]'.$oriEmail.'[/email]', $post, 1);
    	        }
        	} else {    //kunena version > 2.0.1
        	    //$post = preg_replace_callback('/\[url=(.*?)\](.*?)\[\/url\]/i', create_function('$matches','return "[url=".$matches[1]."]".str_ireplace("&", "&amp;", $matches[2])."[/url]";'), $post);
        	    //$post = preg_replace_callback('/\[img\](.*?)\[\/img\]/i', create_function('$matches','return "[img]".str_ireplace("&", "&amp;", $matches[1])."[/img]";'), $post);
        	    $post = str_replace("&", '&amp;', $post);
        	    $post = str_ireplace('&amp;amp;', '&amp;', $post);
        	    $post = str_ireplace('&amp;lt;', '&lt;', $post);
        	    $post = str_ireplace('&amp;gt;', '&gt;', $post);
        	    $post = str_ireplace('&amp;quot;', '"', $post);
        	}
    	    /*
    		$post = str_replace("&", '&amp;', $post);
    		$post = str_replace("<", '&lt;', $post);
    		$post = str_replace(">", '&gt;', $post);
    		$post = str_ireplace("&lt;b&gt;", '<b>', $post);
    		$post = str_ireplace("&lt;/b&gt;", '</b>', $post);
    		$post = str_ireplace("&lt;i&gt;", '<i>', $post);
    		$post = str_ireplace("&lt;/i&gt;", '</i>', $post);
    		$post = str_ireplace("&lt;u&gt;", '<u>', $post);
    		$post = str_ireplace("&lt;/u&gt;", '</u>', $post);
    		$post = str_ireplace("&lt;br /&gt;", '<br />', $post);
    	    $post = preg_replace('/&lt;font (.*?)&gt;(.*?)&lt;\/font&gt;/i', '<font $1>$2</font>', $post);
    	    */
        } else {
    	    $post = strip_tags($post);
        }
    	$post = trim($post);
    	return $post;
    }
    
    /**
     * return quote post content
     *
     * @param  Object  $oMbqEtForumPost
     * @return  String
     */
    public function getQuotePostContent($oMbqEtForumPost) {
        if ($oMbqEtForumPost->oAuthorMbqEtUser) {
            $name = $oMbqEtForumPost->oAuthorMbqEtUser->getDisplayName();
        } else {
            $name = '';
        }
        $content = "[quote=\"$name\" post=".$oMbqEtForumPost->postId->oriValue."]".$oMbqEtForumPost->postContent->oriValue."[/quote]";
        return $content;
    }
  
}

?> 