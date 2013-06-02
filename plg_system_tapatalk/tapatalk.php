<?php

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Joomla! Tapatalk Plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	System.Tapatalk
 */
class plgSystemTapatalk extends JPlugin
{
	public function onAfterRender()
	{
		$app = JFactory::getApplication();

		//if($app->getName() != 'kunena')
		//	return false;
		
		if (!class_exists('KunenaForum'))
			return false;
		
		//old detect js code	
        //if (JRequest::getCmd('option') == 'com_kunena' && (JRequest::getCmd('view') == 'topics' || JRequest::getCmd('view') == 'category')) {
        /*
        $pathCustomDetectJs = 'mobiquo/custom/customDetectJs.php';
        if (JRequest::getCmd('option') == 'com_kunena' && is_file($pathCustomDetectJs)) {
            require_once($pathCustomDetectJs);
    		$base	= JURI::base(false).'';
    		$buffer = JResponse::getBody();
    
            $str = "<script type='text/javascript'>";
            $str .= 'var tapatalk_iphone_msg = "'.MbqCustomDetectJs::$MBQ_DETECTJS_IPHONEIPOD_CONFIRM_TITLE.'";';
            $str .= 'var tapatalk_iphone_url = "'.MbqCustomDetectJs::$MBQ_DETECTJS_IPHONEIPOD_DOWNLOAD_URL.'";';
            $str .= 'var tapatalk_ipad_msg = "'.MbqCustomDetectJs::$MBQ_DETECTJS_IPAD_CONFIRM_TITLE.'";';
            $str .= 'var tapatalk_ipad_url = "'.MbqCustomDetectJs::$MBQ_DETECTJS_IPAD_DOWNLOAD_URL.'";';
            $str .= 'var tapatalk_kindle_msg = "'.MbqCustomDetectJs::$MBQ_DETECTJS_KINDLEFIRE_CONFIRM_TITLE.'";';
            $str .= 'var tapatalk_kindle_url = "'.MbqCustomDetectJs::$MBQ_DETECTJS_KINDLEFIRE_DOWNLOAD_URL.'";';
            $str .= 'var tapatalk_kindle_hd_msg = "'.MbqCustomDetectJs::$MBQ_DETECTJS_KINDLEFIRE_HD_CONFIRM_TITLE.'";';
            $str .= 'var tapatalk_kindle_hd_url = "'.MbqCustomDetectJs::$MBQ_DETECTJS_KINDLEFIRE_HD_DOWNLOAD_URL.'";';
            $str .= 'var tapatalk_android_msg = "'.MbqCustomDetectJs::$MBQ_DETECTJS_ANDROID_CONFIRM_TITLE.'";';
            $str .= 'var tapatalk_android_url = "'.MbqCustomDetectJs::$MBQ_DETECTJS_ANDROID_DOWNLOAD_URL.'";';
            $str .= 'var tapatalk_android_hd_msg = "'.MbqCustomDetectJs::$MBQ_DETECTJS_ANDROID_HD_CONFIRM_TITLE.'";';
            $str .= 'var tapatalk_android_hd_url = "'.MbqCustomDetectJs::$MBQ_DETECTJS_ANDROID_HD_DOWNLOAD_URL.'";';
            $str .= 'var tapatalkdir = "'.MbqCustomDetectJs::$MBQ_DETECTJS_TAPATALKDIR.'";';
            $str .= "</script><script type='text/javascript' src='{$base}".MbqCustomDetectJs::$MBQ_DETECTJS_TAPATALKDIR."/tapadetect.js'></script>";
            $str .= '</head>';
    		$buffer = str_ireplace("</head>", $str, $buffer);
    
    		JResponse::setBody($buffer);
    		return true;
    	} else {
    	    return false;
    	}
    	*/
    	
    	//smartbanner
        $pathSmartbanner = 'mobiquo/custom/customSmartbanner.php';
        if (JRequest::getCmd('option') == 'com_kunena' && is_file($pathSmartbanner)) {
            require_once($pathSmartbanner);
    		$base	= JURI::base(false).'';
    		$buffer = JResponse::getBody();
    		
    		$oKunenaConfig = KunenaFactory::getConfig();
    		MbqSmartbanner::$MBQ_SMARTBANNER_APP_FORUM_NAME = $oKunenaConfig->board_title;
            $tapatalk_dir_url = $base.MbqSmartbanner::$MBQ_SMARTBANNER_TAPATALKDIR;
            MbqSmartbanner::$MBQ_SMARTBANNER_APP_LOCATION_URL = 'tapatalk://'.preg_replace('/http[s]?\:\/\/(.*?)/i', '$1', $base).'?location=index';
            //header code
            $str = '<!-- Tapatalk Banner head start -->';
            $str .= '<link href="'.$tapatalk_dir_url.'/smartbanner/appbanner.css" rel="stylesheet" type="text/css" media="screen" />';
            $str .= '<script type="text/javascript">';
            if (MbqSmartbanner::$MBQ_SMARTBANNER_IS_MOBILE_SKIN)
            $str .= 'var is_mobile_skin = '.MbqSmartbanner::$MBQ_SMARTBANNER_IS_MOBILE_SKIN.';';
            if (MbqSmartbanner::$MBQ_SMARTBANNER_APP_IOS_ID)
            $str .= 'var app_ios_id = "'.MbqSmartbanner::$MBQ_SMARTBANNER_APP_IOS_ID.'";';
            if (MbqSmartbanner::$MBQ_SMARTBANNER_APP_ANDROID_URL)
            $str .= 'var app_android_url = "'.MbqSmartbanner::$MBQ_SMARTBANNER_APP_ANDROID_URL.'";';
            if (MbqSmartbanner::$MBQ_SMARTBANNER_APP_KINDLE_URL)
            $str .= 'var app_kindle_url = "'.MbqSmartbanner::$MBQ_SMARTBANNER_APP_KINDLE_URL.'";';
            if (MbqSmartbanner::$MBQ_SMARTBANNER_APP_BANNER_MESSAGE)
            $str .= 'var app_banner_message = "'.MbqSmartbanner::$MBQ_SMARTBANNER_APP_BANNER_MESSAGE.'";';
            if (MbqSmartbanner::$MBQ_SMARTBANNER_APP_FORUM_NAME)
            $str .= 'var app_forum_name = "'.MbqSmartbanner::$MBQ_SMARTBANNER_APP_FORUM_NAME.'";';
            if (MbqSmartbanner::$MBQ_SMARTBANNER_APP_LOCATION_URL)
            $str .= 'var app_location_url = "'.MbqSmartbanner::$MBQ_SMARTBANNER_APP_LOCATION_URL.'";';
            $str .= '</script><script src="'.$tapatalk_dir_url.'/smartbanner/appbanner.js" type="text/javascript"></script>';
            $str .= '<!-- Tapatalk Banner head end-->';
            $str .= '</head>';
            $buffer = preg_replace('/<\/head>/i', $str, $buffer, 1);
    		//body code
            $str = '<!-- Tapatalk Banner body start -->';
            $str .= '<script type="text/javascript">tapatalkDetect();</script>';
            $str .= '<!-- Tapatalk Banner body end -->';
            $buffer = preg_replace('/<body([^>]*?)>/i', '<body$1>'.$str, $buffer, 1);
    
    		JResponse::setBody($buffer);
    		return true;
    	} else {
    	    return false;
    	}
	}
}

?>