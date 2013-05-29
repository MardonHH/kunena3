<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtForum');

/**
 * forum read class
 * 
 * @since  2012-8-4
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqRdEtForum extends MbqBaseRdEtForum {
    
    public function __construct() {
    }
    
    public function makeProperty(&$oMbqEtForum, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
    
    /**
     * get forum tree structure
     *
     * @return  Array
     */
    public function getForumTree() {
        $arr = KunenaForumCategoryHelper::getCategories();
        $catIds = array();
        foreach ($arr as $oKunenaForumCategory) {
            $catIds[] = $oKunenaForumCategory->id;
        }
        KunenaForumCategoryHelper::getNewTopics($catIds);   //set unread num
        $level = 0;
        $tree = array();
        $newTree = array();
        $i = 0;
        foreach ($arr as $oKunenaForumCategory) {
            if (($oKunenaForumCategory->level == $level) && $oKunenaForumCategory->id) {
                $tree[$i]['obj'] = $oKunenaForumCategory;
                $tree[$i]['children'] = array();
                $this->exttRecurGetKunenaForumCategoryTree($arr, $tree[$i]);
                $i ++;
            }
        }
        foreach ($tree as $item) {
            $id = $item['obj']->id;
            $newTree[$id] = $this->initOMbqEtForum($item['obj'], array('case' => 'oKunenaForumCategory'));
            $this->exttRecurInitObjsSubMbqEtForum($newTree[$id], $item['children'], array('case' => 'objsKunenaForumCategory'));
        }
        return $newTree;
    }
    /**
     * recursive get KunenaForumCategory tree
     *
     * @param  Array  $arr
     * @param  Array  $treeI
     */
    private function exttRecurGetKunenaForumCategoryTree(&$arr, &$treeI) {
        $j = 0;
        foreach ($arr as $oKunenaForumCategory) {
            if ($oKunenaForumCategory->parent_id == $treeI['obj']->id) {
                $treeI['children'][$j]['obj'] = $oKunenaForumCategory;
                $treeI['children'][$j]['children'] = array();
                $this->exttRecurGetKunenaForumCategoryTree($arr, $treeI['children'][$j]);
                $j ++;
            }
        }
    }
    /**
     * recursive init objsSubMbqEtForum
     *
     * @param  Object  $oMbqEtForum  the object need init objsSubMbqEtForum
     * @param  Array  
     * @param  Array  $mbqOpt
     * $mbqOpt['objsKunenaForumCategory'] means init forum by KunenaForumCategory objs
     */
    private function exttRecurInitObjsSubMbqEtForum(&$oMbqEtForum, $arr, $mbqOpt) {
        $i = 0;
        foreach ($arr as $item) {
            $oMbqEtForum->objsSubMbqEtForum[$i] = $this->initOMbqEtForum($item['obj'], array('case' => 'oKunenaForumCategory'));
            $this->exttRecurInitObjsSubMbqEtForum($oMbqEtForum->objsSubMbqEtForum[$i], $item['children'], array('case' => 'objsKunenaForumCategory'));
            $i ++;
        }
    }
    
    /**
     * get forum objs
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byForumIds' means get data by forum ids.$var is the ids.
     * $mbqOpt['case'] = 'subscribed' means get subscribed data.$var is the user id.
     * @return  Array
     */
    public function getObjsMbqEtForum($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'byForumIds') {
            $forumIds = $var;
            $objsKunenaForumCategory = KunenaForumCategoryHelper::getCategories($forumIds);
            $objsMbqEtForum = array();
            foreach ($objsKunenaForumCategory as $oKunenaForumCategory) {
                $objsMbqEtForum[] = $this->initOMbqEtForum($oKunenaForumCategory, array('case' => 'oKunenaForumCategory'));
            }
            return $objsMbqEtForum;
        } elseif ($mbqOpt['case'] == 'subscribed') {
            $arr = KunenaForumCategoryHelper::getLatestSubscriptions($var);
            $objsKunenaForumCategory = $arr[1];
            $objsMbqEtForum = array();
            foreach ($objsKunenaForumCategory as $oKunenaForumCategory) {
                $objsMbqEtForum[] = $this->initOMbqEtForum($oKunenaForumCategory, array('case' => 'oKunenaForumCategory'));
            }
            return $objsMbqEtForum;
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
    
    /**
     * init one forum by condition
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'oKunenaForumCategory' means init forum by KunenaForumCategory obj
     * @return  Mixed
     */
    public function initOMbqEtForum($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'oKunenaForumCategory') {
            $oMbqEtForum = MbqMain::$oClk->newObj('MbqEtForum');
            $oMbqEtForum->forumId->setOriValue($var->id);
            $oMbqEtForum->forumName->setOriValue($var->name);
            $oMbqEtForum->description->setOriValue($var->description);
            $oMbqEtForum->totalTopicNum->setOriValue($var->numTopics);
            $oMbqEtForum->parentId->setOriValue($var->parent_id);
            $oMbqEtForum->subOnly->setOriValue($var->parent_id == 0 ? MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.subOnly.range.yes') : MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.subOnly.range.no'));
            $oMbqEtForum->newPost->setOriValue($var->getNewCount() ? MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.newPost.range.yes') : MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.newPost.range.no'));
            if ($var->authorise('topic.create')) {
                $oMbqEtForum->canPost->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canPost.range.yes'));
            } else {
                $oMbqEtForum->canPost->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canPost.range.no'));
            }
            if ($var->authorise('topic.post.attachment.create')) {
                $oMbqEtForum->canUpload->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canUpload.range.yes'));
            } else {
                $oMbqEtForum->canUpload->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canUpload.range.no'));
            }
            if (MbqMain::hasLogin() && $var->getSubscribed(MbqMain::$oCurMbqEtUser->userId->oriValue)) {
                $oMbqEtForum->isSubscribed->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.isSubscribed.range.yes'));
            } else {
                $oMbqEtForum->isSubscribed->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.isSubscribed.range.no'));
            }
            if (MbqMain::hasLogin() && $var->authorise('subscribe')) {
                $oMbqEtForum->canSubscribe->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canSubscribe.range.yes'));
            } else {
                $oMbqEtForum->canSubscribe->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForum.canSubscribe.range.no'));
            }
            $oMbqEtForum->mbqBind['oKunenaForumCategory'] = $var;
            return $oMbqEtForum;
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
  
}

?>