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

?>