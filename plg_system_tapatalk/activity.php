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
    
	public function onAfterPost($message)
	{
	    
	    //error_log(print_r($message, true).'eerrttyy');
	    
	}
	
}

?>