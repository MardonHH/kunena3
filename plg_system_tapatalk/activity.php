<?php

// no direct access
defined('_JEXEC') or die;

jimport('joomla.utilities.string');

jimport('joomla.plugin.plugin');

/**
 * integrated with Kunena
 */
class KunenaActivityTapatalk extends KunenaActivity
{
    
    /**
     * for thank
     *
     * @param  Integer  $target  user id who start the thank action
     * @param  Integer  $actor  message owner
     * @param  Object  $message  KunenaForumMessage object
     */
    public function onAfterThankyou($target, $actor, $message) {
        $pushPath = 'mobiquo/push/TapatalkPush.php';
        require_once($pushPath);
        $oTapatalkPush = new TapatalkPush();
        $oTapatalkPush->callMethod('doPushThank', array(
            'oKunenaForumMessage' => $message
        ));
    }
    
    /**
     * for new topic
     */
	public function onAfterPost($message) {
        $pushPath = 'mobiquo/push/TapatalkPush.php';
        require_once($pushPath);
        $oTapatalkPush = new TapatalkPush();
        $oTapatalkPush->callMethod('doPushNewtopic', array(
            'oKunenaForumMessage' => $message
        ));
	}
    
    /**
     * for reply post
     */
	public function onAfterReply($message) {
        $pushPath = 'mobiquo/push/TapatalkPush.php';
        require_once($pushPath);
        $oTapatalkPush = new TapatalkPush();
        $oTapatalkPush->callMethod('doPushReply', array(
            'oKunenaForumMessage' => $message
        ));
	}
	
}


if(JPluginHelper::isEnabled('system', 'uddeim_hooks')){
    JPluginHelper::importPlugin('system','uddeim_hooks');
}else{
    class uddeIMhookclass {
            private $callbacks = Array();
            public function registerCallback($event, $callback) {
                    $this->callbacks["$event"][] = $callback;
            }
            public function emit($event, $params) {
                    if (count($this->callbacks["$event"])) {
                            foreach ($this->callbacks["$event"] as $callback) {
                                    if (is_callable($callback))
                                            call_user_func($callback, $params);
                            }
                    }
            }
    }
    global $uddeIMhook;
    $uddeIMhook = new uddeIMhookclass();
}

class PmHelper{
    
    public static function getId() {
        $db = JFactory::getDbo();
        $maxId = $db->setQuery('SELECT MAX(id) FROM #__uddeim')->loadResult();
        return $maxId+1;
    }
    /**
    * get short content
    *
    * @param  String  $str
    * @param  Integer  $length
    * @return  String
    */
    public static function getShortMessage($str, $length = 200) {
       /* get short content standard code begin */
       $str = preg_replace('/\<font [^\>]*?\>(.*?)\<\/font\>/is', '$1', $str);
       $str = preg_replace('/\<font\>(.*?)\<\/font\>/is', '$1', $str);
       $str = preg_replace('/\[quote[^\]]*?\].*?\[\/quote\]/is', '[quote]', $str);
       $str = preg_replace_callback('/\[url\=(.*?)\](.*?)\[\/url\]/is', create_function('$matches','return ($matches[1] == $matches[2]) ? "[url]" : $matches[2];'), $str);
       $str = preg_replace('/\[url\](.*?)\[\/url\]/is', '[url]', $str);
       $str = preg_replace_callback('/\[email\=(.*?)\](.*?)\[\/email\]/is', create_function('$matches','return ($matches[1] == $matches[2]) ? "[url]" : $matches[2];'), $str);
       $str = preg_replace('/\[email\](.*?)\[\/email\]/is', '[url]', $str);
       $str = preg_replace_callback('/\[iurl\=(.*?)\](.*?)\[\/iurl\]/is', create_function('$matches','return ($matches[1] == $matches[2]) ? "[url]" : $matches[2];'), $str);
       $str = preg_replace('/\[iurl\](.*?)\[\/iurl\]/is', '[url]', $str);
       $str = preg_replace('/\[img[^\]]*?\].*?\[\/img\]/is', '[img]', $str);
       $str = preg_replace('/\[video[^\]]*?\].*?\[\/video\]/is', '[V]', $str);
       $str = preg_replace('/\[flash[^\]]*?\].*?\[\/flash\]/is', '[V]', $str);
       $str = preg_replace('/\[media[^\]]*?\].*?\[\/media\]/is', '[V]', $str);
       $str = preg_replace('/\[attachment[^\]]*?\].*?\[\/attachment\]/is', '[attach]', $str);
       $str = preg_replace('/\[attach[^\]]*?\].*?\[\/media\]/is', '[attach]', $str);
       $str = preg_replace('/\[php[^\]]*?\].*?\[\/php\]/is', '[php]', $str);
       $str = preg_replace('/\[html[^\]]*?\].*?\[\/html\]/is', '[html]', $str);
       $str = preg_replace('/\[spoiler[^\]]*?\].*?\[\/spoiler\]/is', '[spoiler]', $str);
       $str = preg_replace('/\[thread[^\]]*?\].*?\[\/thread\]/is', '[thread]', $str);
       $str = preg_replace('/\[topic[^\]]*?\].*?\[\/topic\]/is', '[topic]', $str);
       $str = preg_replace('/\[post[^\]]*?\].*?\[\/post\]/is', '[post]', $str);
       $str = preg_replace('/\[ftp[^\]]*?\].*?\[\/ftp\]/is', '[ftp]', $str);
       $str = preg_replace('/\[sql[^\]]*?\].*?\[\/sql\]/is', '[sql]', $str);
       $str = preg_replace('/\[xml[^\]]*?\].*?\[\/xml\]/is', '[xml]', $str);
       $str = preg_replace('/\[hide[^\]]*?\].*?\[\/hide\]/is', '[hide]', $str);
       $str = preg_replace('/\[confidential[^\]]*?\].*?\[\/confidential\]/is', '[hide]', $str);
       $str = preg_replace('/\[ebay[^\]]*?\].*?\[\/ebay\]/is', '[ebay]', $str);
       $str = preg_replace('/\[map[^\]]*?\].*?\[\/map\]/is', '[map]', $str);
       $str = preg_replace('/[\n|\r|\t]/', '', $str);
       //remove useless bbcode begin
       $str = preg_replace_callback('/\[([^\/]*?)\]/i', create_function('$matches','
       $v = strtolower($matches[1]);
       if (strpos($v, "quote") === 0 || strpos($v, "url") === 0 || strpos($v, "img") === 0 || strpos($v, "v") === 0 || strpos($v, "attach") === 0 || strpos($v, "php") === 0 || strpos($v, "html") === 0 || strpos($v, "spoiler") === 0 || strpos($v, "thread") === 0 || strpos($v, "topic") === 0 || strpos($v, "post") === 0 || strpos($v, "ftp") === 0 || strpos($v, "sql") === 0 || strpos($v, "xml") === 0 || strpos($v, "hide") === 0 || strpos($v, "ebay") === 0 || strpos($v, "map") === 0) {
           return "[$matches[1]]";
       } else {
           return "";
       }
       '), $str);
       $str = preg_replace('/\[\/[^\]]*?\]/i', '', $str);
       //remove useless bbcode end
       $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
       $str = function_exists('mb_substr') ? mb_substr($str, 0, $length) : substr($str, 0, $length);
       $str = strip_tags($str);
       /* get short content standard code end */
       return $str;
    }
}

function doPmNewMessage($params){

    $pushPath = JPATH_SITE . '/mobiquo/push/TapatalkPush.php';
    require_once($pushPath);
    $message = array(
            'userid'    => $params['toid'],
            'type'      => 'pm',
            'id'        => PmHelper::getId(),
            'subid'     => 1,
            'title'     => PmHelper::getShortMessage(JFactory::getApplication()->input->getString('pmessage'), 20),
            'author'    => JFactory::getUser()->name,
            'dateline'  => time()
        );
    $oTapatalkPush = new TapatalkPush();
    $oTapatalkPush->callMethod('doPushCustomMessage', array($message));
}

?>