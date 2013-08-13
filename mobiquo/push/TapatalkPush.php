<?php

define('MBQ_PUSH_BLOCK_TIME', 60);    /* push block time(minutes) */

require_once(dirname(__FILE__).'/../mbqFrame/basePush/TapatalkBasePush.php');   //this sentence only used for push feature of native plugin

/**
 * push class
 * 
 * @since  2013-7-10
 * @author Wu ZeTao <578014287@qq.com>
 */
Class TapatalkPush extends TapatalkBasePush {
    
    //native properties
    protected $oApp;
    protected $oDb;
    protected $errMsg;  //error message
    protected $oJUser;  //current user object if loged in
    
    //init
    public function __construct() {
        parent::__construct();
        
        $this->oApp = JFactory::getApplication();
        $this->oDb = JFactory::getDBO ();
        
        $oJUser = JFactory::getUser();
        if ($oJUser->id) {
            $this->oJUser = $oJUser;
        }
        $this->loadImActive();
        
        $this->loadPushStatus();
        $this->loadSupportedPushType();
        $this->loadSlug();
        
        $this->siteUrl = JURI::root();
    }
    
    /**
     * load $this->supportedPushType
     */
    protected function loadSupportedPushType() {
        if (MbqCommonConfig::$cfg['push_type']) {
            $this->supportedPushType = explode(',', MbqCommonConfig::$cfg['push_type']);
        }
    }
    
    /**
     * load $this->imActive
     *
     * @return Boolean
     */
    protected function loadImActive() {
        if ($this->oJUser && $this->getActiveAppUserIds($this->oJUser->id)) {
            $this->imActive = true;
        } else {
            $this->imActive = false;
        }
    }
    
    /**
     * filter active user id from tapatalk_push_user table
     *
     * @param  Mixed  user id(integer) or user ids(array)
     * @return  Array  return empty array when get error,or return active user ids array
     */
    protected function getActiveAppUserIds($var) {
        if (!is_array($var)) $var = array($var);
        foreach ($var as &$v) {
            $v = $this->oDb->quote($v);
        }
        $inSql = implode (',', $var);
        $query ="SELECT user_id FROM #__tapatalk_push_user WHERE user_id IN ($inSql)";
		$this->oDb->setQuery($query);
		$results = $this->oDb->loadAssocList ();
		if ($this->findDbError()) return array();
		$ret = array();
		foreach ($results as $r) {
		    $ret[] = $r['user_id'];
		}
		return $ret;
    }
    
    /**
     * load $this->pushStatus and $this->pushKey
     */
    protected function loadPushStatus() {
        if (MbqCommonConfig::$cfg['push'] && ($plugin = JPluginHelper::getPlugin('system', 'tapatalk')) && JPluginHelper::isEnabled('system', 'tapatalk') && (@ini_get('allow_url_fopen') || function_exists('curl_init'))) {
            $settings = json_decode($plugin->params);
            if ($settings->tapatalk_push_key) {
                $this->pushKey = $settings->tapatalk_push_key;
            }
            if ($settings->activity) {
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
     * save slug
     *
     * @param  Mixed $slug
     * @return Boolean
     */
    protected function saveSlug($slug = NULL) {
        if (is_null($slug)) {
            $data = json_encode($this->slugData);
        } else {
            $this->slugData = $slug;
            $data = json_encode($slug);
        }
        $query ="SELECT count(update_time) as num FROM #__tapatalk_status";
		$this->oDb->setQuery($query);
		$results = $this->oDb->loadAssocList ();
		if ($this->findDbError()) return false;
		if ($results[0]['num'] == 1) {
            $query = "UPDATE #__tapatalk_status SET update_time = ".$this->oDb->quote(time()).", status_info = ".$this->oDb->quote($data);
		} elseif ($results[0]['num'] == 0) {
		    $query = "INSERT INTO #__tapatalk_status (status_info, create_time, update_time) VALUES (".$this->oDb->quote($data).", ".$this->oDb->quote(time()).", ".$this->oDb->quote(time()).")";
		} else {
		    return false;
		}
        $this->oDb->setQuery($query);
        $this->oDb->query ();
        if ($this->findDbError()) 
            return false;
        else
            return true;
    }
    
    /**
     * load $this->slugData
     *
     * @return Boolean
     */
    protected function loadSlug() {
        $query ="SELECT * FROM #__tapatalk_status LIMIT 1";
		$this->oDb->setQuery($query);
		$results = $this->oDb->loadAssocList ();
		if ($this->findDbError()) return false;
		if ($results) {
            $this->slugData = json_decode($results[0]['status_info']);
		} else {
		    $this->slugData = array();  //default is empty array
		}
		return true;
    } 
    
    /**
     * wrap push data before process push
     *
     * @param  Array  $push_data
     */
    protected function push($push_data) {
        if (!empty($push_data)) {
            foreach ($push_data as $pack) {
                if (!in_array($pack['type'], $this->supportedPushType)) {
                    return false;
                }
            }
            $data = array(
                'url'  => $this->siteUrl,
                'key'  => $this->pushKey,
                'data' => base64_encode(serialize($push_data)),
            );
            if($this->pushStatus)
                $this->do_post_request($data);
        }
    }
    
    protected function do_post_request($data) {
        $push_url = 'http://push.tapatalk.com/push.php';

        //Get push_slug from db
        if ($this->loadSlug()) 
            $slug = $this->slugData;
        else 
            return false;
        $slug = $this->push_slug($slug, 'CHECK');

        //If it is valide(result = true) and it is not sticked, we try to send push
        if($slug[2] && !$slug[5])
        {
            //Slug is initialed or just be cleared
            if($slug[8])
            {
                $this->saveSlug($slug);
            }

            //Send push
            $push_resp = $this->getContentFromRemoteServer($push_url, 0, $this->errMsg, 'POST', $data);

            if(trim($push_resp) === 'Invalid push notification key') $push_resp = 1;
            if(!is_numeric($push_resp) && !preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $push_resp))
            {
                //Sending push failed, try to update push_slug to db
                $slug = $this->push_slug($slug, 'UPDATE');

                if($slug[2] && $slug[8])
                {
                    $this->saveSlug($slug);
                }
            }
        }

        return $push_resp;
    }
    
    protected function push_slug($push_v_data, $method = 'NEW') {
        if(empty($push_v_data))
            $push_v_data = array();

        $current_time = time();
        if(!is_array($push_v_data))
            return array(2 => 0, 3 => 'Invalid v data', 5 => 0);
        if($method != 'CHECK' && $method != 'UPDATE' && $method != 'NEW')
            return array(2 => 0, 3 => 'Invalid method', 5 => 0);

        if($method != 'NEW' && !empty($push_v_data))
        {
            $push_v_data[8] = $method == 'UPDATE';
            if($push_v_data[5] == 1)
            {
                if($push_v_data[6] + $push_v_data[7] > $current_time)
                    return $push_v_data;
                else
                    $method = 'NEW';
            }
        }

        if($method == 'NEW' || empty($push_v_data))
        {
            $push_v_data = array();     //Slug
            $push_v_data[0] = 3;        //        $push_v_data['max_times'] = 3;                //max push failed attempt times in period
            $push_v_data[1] = 300;      //        $push_v_data['max_times_in_period'] = 300;     //the limitation period
            $push_v_data[2] = 1;        //        $push_v_data['result'] = 1;                   //indicate if the output is valid of not
            $push_v_data[3] = '';       //        $push_v_data['result_text'] = '';             //invalid reason
            $push_v_data[4] = array();  //        $push_v_data['stick_time_queue'] = array();   //failed attempt timestamps
            $push_v_data[5] = 0;        //        $push_v_data['stick'] = 0;                    //indicate if push attempt is allowed
            $push_v_data[6] = 0;        //        $push_v_data['stick_timestamp'] = 0;          //when did push be sticked
            $push_v_data[7] = 600;      //        $push_v_data['stick_time'] = 600;             //how long will it be sticked
            $push_v_data[8] = 1;        //        $push_v_data['save'] = 1;                     //indicate if you need to save the slug into db
            return $push_v_data;
        }

        if($method == 'UPDATE')
        {
            $push_v_data[4][] = $current_time;
        }
        $sizeof_queue = count($push_v_data[4]);

        $period_queue = $sizeof_queue > 1 ? ($push_v_data[4][$sizeof_queue - 1] - $push_v_data[4][0]) : 0;

        $times_overflow = $sizeof_queue > $push_v_data[0];
        $period_overflow = $period_queue > $push_v_data[1];

        if($period_overflow)
        {
            if(!array_shift($push_v_data[4]))
                $push_v_data[4] = array();
        }

        if($times_overflow && !$period_overflow)
        {
            $push_v_data[5] = 1;
            $push_v_data[6] = $current_time;
        }

        return $push_v_data;
    }
    
    /**
     * Get content from remote server
     *
     * @param string $url      NOT NULL          the url of remote server, if the method is GET, the full url should include parameters; if the method is POST, the file direcotry should be given.
     * @param string $holdTime [default 0]       the hold time for the request, if holdtime is 0, the request would be sent and despite response.
     * @param string $error_msg                  return error message
     * @param string $method   [default GET]     the method of request.
     * @param string $data     [default array()] post data when method is POST.
     *
     * @exmaple: getContentFromRemoteServer('http://push.tapatalk.com/push.php', 0, $error_msg, 'POST', $ttp_post_data)
     * @return string when get content successfully|false when the parameter is invalid or connection failed.
    */
    protected function getContentFromRemoteServer($url, $holdTime = 0, &$error_msg, $method = 'GET', $data = array()) {
        //Validate input.
        $vurl = parse_url($url);
        if ($vurl['scheme'] != 'http')
        {
            $error_msg = 'Error: invalid url given: '.$url;
            return false;
        }
        if($method != 'GET' && $method != 'POST')
        {
            $error_msg = 'Error: invalid method: '.$method;
            return false;//Only POST/GET supported.
        }
        if($method == 'POST' && empty($data))
        {
            $error_msg = 'Error: data could not be empty when method is POST';
            return false;//POST info not enough.
        }

        if(!empty($holdTime) && function_exists('file_get_contents') && $method == 'GET')
        {
            $response = @file_get_contents($url);
        }
        else if (@ini_get('allow_url_fopen'))
        {
            if(empty($holdTime))
            {
                // extract host and path:
                $host = $vurl['host'];
                $path = $vurl['path'];

                if($method == 'POST')
                {
                    $fp = @fsockopen($host, 80, $errno, $errstr, 5);

                    if(!$fp)
                    {
                        $error_msg = 'Error: socket open time out or cannot connect.';
                        return false;
                    }

                    $data =  http_build_query($data);

                    fputs($fp, "POST $path HTTP/1.1\r\n");
                    fputs($fp, "Host: $host\r\n");
                    fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
                    fputs($fp, "Content-length: ". strlen($data) ."\r\n");
                    fputs($fp, "Connection: close\r\n\r\n");
                    fputs($fp, $data);
                    fclose($fp);
                    return 1;
                }
                else
                {
                    $error_msg = 'Error: 0 hold time for get method not supported.';
                    return false;
                }
            }
            else
            {
                if($method == 'POST')
                {
                    $params = array('http' => array(
                        'method' => 'POST',
                        'content' => http_build_query($data, '', '&'),
                    ));
                    $ctx = stream_context_create($params);
                    $old = ini_set('default_socket_timeout', $holdTime);
                    $fp = @fopen($url, 'rb', false, $ctx);
                }
                else
                {
                    $fp = @fopen($url, 'rb', false);
                }
                if (!$fp)
                {
                    $error_msg = 'Error: fopen failed.';
                    return false;
                }
                ini_set('default_socket_timeout', $old);
                stream_set_timeout($fp, $holdTime);
                stream_set_blocking($fp, 0);

                $response = @stream_get_contents($fp);
            }
        }
        elseif (function_exists('curl_init'))
        {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            if($method == 'POST')
            {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            if(empty($holdTime))
            {
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT,1);
            }
            $response = curl_exec($ch);
            curl_close($ch);
        }
        else
        {
            $error_msg = 'CURL is disabled and PHP option "allow_url_fopen" is OFF. You can enable CURL or turn on "allow_url_fopen" in php.ini to fix this problem.';
            return false;
        }
        return $response;
    }
    
    /**
     * record user info after login from app
     *
     * @return Boolean
     */
    protected function doAfterAppLogin() {
        $oJUser = JFactory::getUser();
        if ($oJUser->id && $this->pushStatus) {
            $query ="SELECT count(user_id) as num FROM #__tapatalk_push_user WHERE user_id = ".$this->oDb->quote($oJUser->id);
    		$this->oDb->setQuery($query);
			$results = $this->oDb->loadAssocList ();
			if ($this->findDbError()) return false;
			if ($results[0]['num'] == 1) {
                $query = "UPDATE #__tapatalk_push_user SET update_time = ".$this->oDb->quote(time())." WHERE user_id = ".$this->oDb->quote($oJUser->id);
			} elseif ($results[0]['num'] == 0) {
			    $query = "INSERT INTO #__tapatalk_push_user (user_id, create_time, update_time) VALUES (".$this->oDb->quote($oJUser->id).", ".$this->oDb->quote(time()).", ".$this->oDb->quote(time()).")";
			} else {
			    return false;
			}
            $this->oDb->setQuery($query);
            $this->oDb->query ();
            if (!$this->findDbError()) return true;
        }
        return false;
    }
    
    /**
     * thank push
     *
     * @param  Array  $p
     * @return Boolean
     */
    protected function doPushThank($p) {
        $push_data = array();
        if (defined('MBQ_IN_IT') && MBQ_IN_IT) {    //mobiquo
            if ($p['oMbqEtForumPost'] && $p['oMbqEtThank']) {
                $pushPack = array(
                    'userid'    => $p['oMbqEtForumPost']->postAuthorId->oriValue,
                    'type'      => 'thank',
                    'id'        => $p['oMbqEtForumPost']->topicId->oriValue,
                    'subid'     => $p['oMbqEtForumPost']->postId->oriValue,
                    'title'     => $p['oMbqEtForumPost']->postTitle->oriValue,
                    'author'    => $this->oJUser->name,
                    'dateline'  => time()
                );
                $push_data[] = $pushPack;
                $this->push($push_data);
            }
        } else {    //native plugin
            if ($p['oKunenaForumMessage']) {
                $pushPack = array(
                    'userid'    => $p['oKunenaForumMessage']->userid,
                    'type'      => 'thank',
                    'id'        => $p['oKunenaForumMessage']->thread,
                    'subid'     => $p['oKunenaForumMessage']->id,
                    'title'     => $p['oKunenaForumMessage']->subject,
                    'author'    => $this->oJUser->name,
                    'dateline'  => time()
                );
                $push_data[] = $pushPack;
                $this->push($push_data);
            }
        }
        return false;
    }
    
}

?>