<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtThank');

/**
 * thank read class
 * 
 * @since  2012-9-24
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqRdEtThank extends MbqBaseRdEtThank {
    
    public function __construct() {
    }
    
    public function makeProperty(&$oMbqEtThank, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
    
    /**
     * get thank objs
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byForumPostIds' means get data by forum post ids.$var is the ids.
     * $mbqOpt['case'] = 'oKunenaForumMessageThankyou' means init thank by KunenaForumMessageThankyou obj
     * $mbqOpt['oMbqEtUser'] = true means load oMbqEtUser property,default is true
     * @return  Mixed
     */
    public function getObjsMbqEtThank($var, $mbqOpt) {
        $mbqOpt['oMbqEtUser'] = isset($mbqOpt['oMbqEtUser']) ? $mbqOpt['oMbqEtUser'] : true;
        if ($mbqOpt['case'] == 'byForumPostIds') {
            $objsKunenaForumMessageThankyou = KunenaForumMessageThankyouHelper::getByMessage($var);
            $objsMbqEtThank = array();
            foreach ($objsKunenaForumMessageThankyou as $postId => $oKunenaForumMessageThankyou) {
                $newObjsMbqEtThank = $this->getObjsMbqEtThank($oKunenaForumMessageThankyou, array('case' => 'oKunenaForumMessageThankyou', 'oMbqEtUser' => false));
                foreach ($newObjsMbqEtThank as &$newOMbqEtThank) {
                    $newOMbqEtThank->key->setOriValue($postId);
                }
                $objsMbqEtThank = array_merge($objsMbqEtThank, $newObjsMbqEtThank);
            }
            $userIds = array();
            foreach ($objsMbqEtThank as $oMbqEtThank) {
                $userIds[$oMbqEtThank->userId->oriValue] = $oMbqEtThank->userId->oriValue;
            }
            /* load oMbqEtUser property */
            $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
            $objsMbqEtUser = $oMbqRdEtUser->getObjsMbqEtUser($userIds, array('case' => 'byUserIds'));
            foreach ($objsMbqEtThank as &$oMbqEtThank) {
                foreach ($objsMbqEtUser as $oMbqEtUser) {
                    if ($oMbqEtThank->userId->oriValue == $oMbqEtUser->userId->oriValue) {
                        $oMbqEtThank->oMbqEtUser = $oMbqEtUser;
                        break;
                    }
                }
            }
            return $objsMbqEtThank;
        } elseif ($mbqOpt['case'] == 'oKunenaForumMessageThankyou') {
            $arr = $var->getList();
            $userIds = array();
            $objsMbqEtThank = array();
            if ($arr) {
                foreach ($arr as $userId => $v) {
                    $userIds[$userId] = $userId;
                }
                $i = 0;
                foreach ($userIds as $userId) {
                    $objsMbqEtThank[$i] = MbqMain::$oClk->newObj('MbqEtThank');
                    $objsMbqEtThank[$i]->userId->setOriValue($userId);
                    $i ++;
                }
                /* load oMbqEtUser property */
                if ($mbqOpt['oMbqEtUser']) {
                    $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
                    $objsMbqEtUser = $oMbqRdEtUser->getObjsMbqEtUser($userIds, array('case' => 'byUserIds'));
                    foreach ($objsMbqEtThank as &$oMbqEtThank) {
                        foreach ($objsMbqEtUser as $oMbqEtUser) {
                            if ($oMbqEtThank->userId->oriValue == $oMbqEtUser->userId->oriValue) {
                                $oMbqEtThank->oMbqEtUser = $oMbqEtUser;
                                break;
                            }
                        }
                    }
                }
                return $objsMbqEtThank;
            } else {
                return array();
            }
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
  
}

?>