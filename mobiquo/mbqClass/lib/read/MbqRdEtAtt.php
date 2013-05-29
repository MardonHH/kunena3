<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtAtt');

/**
 * attachment read class
 * 
 * @since  2012-8-14
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqRdEtAtt extends MbqBaseRdEtAtt {
    
    public function __construct() {
    }
    
    public function makeProperty(&$oMbqEtAtt, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
    
    /**
     * get attachment objs
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byForumPostIds' means get data by forum post ids.$var is the ids.
     * @return  Mixed
     */
    public function getObjsMbqEtAtt($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'byForumPostIds') {
            $postIds = $var;
            require_once(MBQ_APPEXTENTION_PATH.'ExttMbqKunenaForumMessageAttachmentHelper.php');
            $objsKunenaForumMessageAttachment = ExttMbqKunenaForumMessageAttachmentHelper::getByMessage($postIds);
            $objsMbqEtAtt = array();
            foreach ($objsKunenaForumMessageAttachment as $oKunenaForumMessageAttachment) {
                $objsMbqEtAtt[] = $this->initOMbqEtAtt($oKunenaForumMessageAttachment, array('case' => 'oKunenaForumMessageAttachment'));
            }
            return $objsMbqEtAtt;
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
    
    /**
     * init one attachment by condition
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'oKunenaForumMessageAttachment' means init attachment by KunenaForumMessageAttachment obj
     * $mbqOpt['case'] = 'byAttId' means init attachment by attachment id
     * @return  Mixed
     */
    public function initOMbqEtAtt($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'byAttId') {
            if (($oKunenaForumMessageAttachment = KunenaForumMessageAttachmentHelper::get($var)) && $oKunenaForumMessageAttachment->id) {
                $mbqOpt['case'] = 'oKunenaForumMessageAttachment';
                return $this->initOMbqEtAtt($oKunenaForumMessageAttachment, $mbqOpt);
            }
            return false;
        } elseif ($mbqOpt['case'] == 'oKunenaForumMessageAttachment') {
            $oMbqEtAtt = MbqMain::$oClk->newObj('MbqEtAtt');
            $oMbqEtAtt->attId->setOriValue($var->id);
            $oMbqEtAtt->postId->setOriValue($var->mesid);
            $oMbqEtAtt->filtersSize->setOriValue($var->size);
            $oMbqEtAtt->uploadFileName->setOriValue($var->filename);
            $oMbqEtAtt->attType->setOriValue(MbqBaseFdt::getFdt('MbqFdtAtt.MbqEtAtt.attType.range.forumPostAtt'));
            $ext = MbqMain::$oMbqCm->getFileExtension($oMbqEtAtt->uploadFileName->oriValue);
            if ($ext == 'jpeg' || $ext == 'gif' || $ext == 'bmp' || $ext == 'png' || $ext == 'jpg') {
                $contentType = MbqBaseFdt::getFdt('MbqFdtAtt.MbqEtAtt.contentType.range.image');
            } elseif ($ext == 'pdf') {
                $contentType = MbqBaseFdt::getFdt('MbqFdtAtt.MbqEtAtt.contentType.range.pdf');
            } else {
                $contentType = MbqBaseFdt::getFdt('MbqFdtAtt.MbqEtAtt.contentType.range.other');
            }     
            $oMbqEtAtt->contentType->setOriValue($contentType);
            /* $oMbqEtAtt->thumbnailUrl->setOriValue(preg_replace('/<a .*?href="(.*?)".*?>.*?<\/a>/i', '$1', $var->getThumbnailLink())); */
            $oMbqEtAtt->thumbnailUrl->setOriValue(preg_replace('/<a .*?href=".*?".*?><img .*?src="(.*?)".*?\/><\/a>/i', '$1', $var->getThumbnailLink()));
            $oMbqEtAtt->url->setOriValue(preg_replace('/<a .*?href="(.*?)".*?>.*?<\/a>/i', '$1', $var->getThumbnailLink()));
            $oMbqEtAtt->userId->setOriValue($var->userid);
            $oMbqEtAtt->mbqBind['oKunenaForumMessageAttachment'] = $var;
            return $oMbqEtAtt;
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
  
}

?>