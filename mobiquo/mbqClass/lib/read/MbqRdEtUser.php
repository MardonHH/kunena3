<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseRdEtUser');

/**
 * user read class
 * 
 * @since  2012-8-6
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqRdEtUser extends MbqBaseRdEtUser {
    
    public function __construct() {
    }
    
    public function makeProperty(&$oMbqEtUser, $pName, $mbqOpt = array()) {
        switch ($pName) {
            default:
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_PNAME . ':' . $pName . '.');
            break;
        }
    }
    
    /**
     * login
     *
     * @param  String  $loginName
     * @param  String  $password
     * @return  Boolean  return true when login success.
     */
    public function login($loginName, $password) {
        $result = MbqMain::$oMbqAppEnv->oApp->login(array('username' => $loginName, 'password' => $password), array('remember' => true));
        if ($result) {
            MbqMain::$oMbqAppEnv->oCurJUser = JFactory::getUser();
            KunenaUserHelper::initialize();
            MbqMain::$oMbqAppEnv->oCurKunenaUser = KunenaUserHelper::getMyself();
            $this->initOCurMbqEtUser();
        }
        return $result ? true : false;
    }
    
    /**
     * logout
     *
     * @return  Boolean  return true when logout success.
     */
    public function logout() {
        $result = MbqMain::$oMbqAppEnv->oApp->logout();
        return $result ? true : false;
    }
    
    /**
     * get user objs
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'byUserIds' means get data by user ids.$var is the ids.
     * @mbqOpt['case'] = 'online' means get online user.
     * @return  Array
     */
    public function getObjsMbqEtUser($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'byUserIds') {
            require_once(MBQ_APPEXTENTION_PATH.'ExttMbqKunenaUserHelper.php');
            $userIds = $var;
            $objsKunenaUser = ExttMbqKunenaUserHelper::exttMbqLoadUsers($userIds);
            $objsJUser = array();
            $objsMbqEtUser = array();
            foreach ($userIds as $userId) {
                if ($oJUser = JFactory::getUser($userId)) {
                    $objsJUser[$oJUser->id] = $oJUser;
                }
            }
            foreach ($objsKunenaUser as $oKunenaUser) {
                foreach ($objsJUser as $oJUser) {
                    if ($oKunenaUser->userid && ($oKunenaUser->userid == $oJUser->id)) {
                        $objsMbqEtUser[] = $this->initOMbqEtUser(array('oJuser' => $oJUser, 'oKunenaUser' => $oKunenaUser), array('case' => 'JUserAndKunenaUser'));
                    }
                }
            }
            return $objsMbqEtUser;
        } elseif ($mbqOpt['case'] == 'online') {
            $arr = KunenaUserHelper::getOnlineUsers();
            return $this->getObjsMbqEtUser(array_keys($arr), array('case' => 'byUserIds'));
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
    
    /**
     * init one user by condition
     *
     * @param  Mixed  $var
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'JUserAndKunenaUser' means init user by JUser obj and KunenaUser obj.$var['oJuser'] is JUser obj,$var['oKunenaUser'] is KunenaUser obj.
     * $mbqOpt['case'] = 'byUserId' means init user by user id.$var is user id.
     * $mbqOpt['case'] = 'byLoginName' means init user by login name.$var is login name.
     * @return  Mixed
     */
    public function initOMbqEtUser($var, $mbqOpt) {
        if ($mbqOpt['case'] == 'JUserAndKunenaUser') {
            $oJUser = $var['oJuser'];
            $oKunenaUser = $var['oKunenaUser'];
            $oMbqEtUser = MbqMain::$oClk->newObj('MbqEtUser');
            $oMbqEtUser->userId->setOriValue($oJUser->id);
            $oMbqEtUser->loginName->setOriValue($oJUser->username);
            $oMbqEtUser->userName->setOriValue($oJUser->name);
            if (property_exists($oJUser, 'groups')) //used in joomla 2.5.x
                $oMbqEtUser->userGroupIds->setOriValue(MbqMain::$oMbqCm->removeArrayKey($oJUser->groups));
            else    //used in joomla 1.5.x
                $oMbqEtUser->userGroupIds->setOriValue(array($oJUser->gid));
            $oMbqEtUser->iconUrl->setOriValue($var['oKunenaUser']->getAvatarURL());
            $oMbqEtUser->canSearch->setOriValue(MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canSearch.range.yes'));
            $oMbqEtUser->postCount->setOriValue($var['oKunenaUser']->posts);
            //$oMbqEtUser->displayText->setOriValue($var['oKunenaUser']->signature);
            $oMbqEtUser->displayText->setOriValue($var['oKunenaUser']->getRank(0, 'title'));
            $oMbqEtUser->regTime->setOriValue(strtotime($oJUser->registerDate));
            $oMbqEtUser->lastActivityTime->setOriValue(strtotime($oJUser->lastvisitDate));
            $oMbqEtUser->isOnline->setOriValue($var['oKunenaUser']->isOnline() ? MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.isOnline.range.yes') : MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.isOnline.range.no'));
            $oMbqEtUser->isBan->setOriValue($var['oKunenaUser']->isBanned() ? MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.isBan.range.yes') : MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.isBan.range.no'));
            $oMbqEtUser->maxAttachment->setOriValue(MbqMain::$oMbqAppEnv->oKunenaConfig->attachment_limit);
            $oMbqEtUser->maxPngSize->setOriValue(MbqMain::$oMbqAppEnv->oKunenaConfig->imagesize * 1024);
            $oMbqEtUser->maxJpgSize->setOriValue(MbqMain::$oMbqAppEnv->oKunenaConfig->imagesize * 1024);
            $oMbqEtUser->mbqBind['oJuser'] = $var['oJuser'];
            $oMbqEtUser->mbqBind['oKunenaUser'] = $var['oKunenaUser'];
            $oMbqEtUser->canWhosonline->setOriValue(MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canWhosonline.range.yes'));
            $ban = KunenaUserBan::getInstanceByUserid ( $oMbqEtUser->userId->oriValue, true );
            if ($ban->canBan()) {
                $oMbqEtUser->canBan->setOriValue(MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canBan.range.yes'));
            } else {
                $oMbqEtUser->canBan->setOriValue(MbqBaseFdt::getFdt('MbqFdtUser.MbqEtUser.canBan.range.no'));
            }
            return $oMbqEtUser;
        } elseif ($mbqOpt['case'] == 'byUserId') {
            $userIds = array($var);
            $objsMbqEtUser = $this->getObjsMbqEtUser($userIds, array('case' => 'byUserIds'));
            if (is_array($objsMbqEtUser) && (count($objsMbqEtUser) == 1)) {
                return $objsMbqEtUser[0];
            }
            return false;
        } elseif ($mbqOpt['case'] == 'byLoginName') {
            $loginName = $var;
            $oKunenaUser = KunenaFactory::getUser($loginName);
            if ($oKunenaUser->userid) {
                return $this->initOMbqEtUser($oKunenaUser->userid, array('case' => 'byUserId'));
            } else {
                return false;
            }
        }
        MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
    }
    
    /**
     * init current user obj if login
     */
    public function initOCurMbqEtUser() {
        if (MbqMain::$oMbqAppEnv->oCurJUser && MbqMain::$oMbqAppEnv->oCurKunenaUser) {
            MbqMain::$oCurMbqEtUser = $this->initOMbqEtUser(array('oJuser' => MbqMain::$oMbqAppEnv->oCurJUser, 'oKunenaUser' => MbqMain::$oMbqAppEnv->oCurKunenaUser), array('case' => 'JUserAndKunenaUser'));
        }
    }
    
    /**
     * get user display name
     *
     * @param  Object  $oMbqEtUser
     * @return  String
     */
    public function getDisplayName($oMbqEtUser) {
        //return $oMbqEtUser->userName->hasSetOriValue() ? $oMbqEtUser->userName->oriValue : $oMbqEtUser->loginName->oriValue;
        return $oMbqEtUser->loginName->oriValue;
    }
  
}

?>