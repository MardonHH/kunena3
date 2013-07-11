<?php

define('MBQ_PUSH_BLOCK_TIME', 60);    /* push block time(minutes) */

/**
 * push class
 * 
 * @since  2013-7-10
 * @author Wu ZeTao <578014287@qq.com>
 */
Class TapatalkPush extends TapatalkBasePush {
    
    protected $push_v_data;
    //native properties
    protected $oApp;
    protected $oDb;
    protected $errMsg;  //error message
    
    //init
    public function __construct() {
        parent::__construct();
        
        $this->oApp = JFactory::getApplication();
        $this->oDb = JFactory::getDBO ();
        
        $this->initPushStatus();
    }
    
    /**
     * init $this->pushStatus and $this->pushKey
     */
    protected function initPushStatus() {
        if (($plugin = JPluginHelper::getPlugin('system', 'tapatalk')) && JPluginHelper::isEnabled('system', 'tapatalk') && (@ini_get('allow_url_fopen') || function_exists('curl_init'))) {
            $settings = json_decode($plugin->params);
            if ($settings->tapatalk_push_key) {
                $this->pushKey = $settings->tapatalk_push_key;
                $this->pushStatus = true;
                return;
            }
        }
        $this->pushStatus = false;
    }
    
    /**
     * judge db error
     *
     * @return  Boolean
     */
    protected function findDbError() {
        if ($this->oDb->getErrorNum ()) {
            $this->errMsg = 'Db error occured.';
            return true;
        }
        return false;
    }
    
    /**
     * record user info after login from app
     */
    protected function doAfterAppLogin() {
        $oJUser = JFactory::getUser();
        if ($oJUser->id) {
            $query ="SELECT count(user_id) as num FROM #__tapatalk_push_user WHERE user_id = '".addslashes($oJUser->id)."'";
    		$this->oDb->setQuery($query);
			$results = $this->oDb->loadAssocList ();
			if ($this->findDbError()) return false;
			if ($results[0]['num'] = 1) {
                $query = "UPDATE #__tapatalk_push_user SET update_time = '".time()."' WHERE user_id = '".addslashes($oJUser->id)."'";
			} elseif ($results[0]['num'] = 0) {
			    $query = "INSERT INTO #__tapatalk_push_user (user_id, create_time, update_time) VALUES ('".addslashes($oJUser->id)."', '".time()."', '".time()."')";
			} else {
			    return false;
			}
            $this->oDb->setQuery($query);
            $this->oDb->query ();
            if ($this->findDbError()) return false;
        }
    }
    
}

?>