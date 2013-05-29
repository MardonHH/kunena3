<?php

require_once(KPATH_SITE.'/views/topic/view.html.php');

/**
 * for kunena 2.0.1/2.0.2/2.0.3/2.0.4
 * ExttMbqKunenaViewTopic extended from KunenaViewTopic
 * add method exttMbqReturnDisplayMessageContents() modified from method displayMessageContents()
 * 
 * @since  2012-8-15
 * @modified by Wu ZeTao <578014287@qq.com>
 */
class ExttMbqKunenaViewTopic extends KunenaViewTopic {
//class ExttMbqKunenaViewTopic extends KunenaView {

    /**
     * return forum post content displayed in web page.
     */
	function exttMbqReturnDisplayMessageContents($message) {
	    $this->message = $message;
	    //return $this->loadTemplateFile('message');
	    /* modified from default_message.php */
	    return KunenaHtmlParser::parseBBCode ($this->message->message, $this);
	}
}

?>