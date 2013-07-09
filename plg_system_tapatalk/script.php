<?php

class PlgSystemTapatalkInstallerScript
{
    
	public function preflight($cmd, $parent)
	{
	    $app = JFactory::getApplication();
	    $db = JFactory::getDBO ();
	    if ($cmd == 'install' || $cmd == 'update') {
    	    $query ="DROP TABLE IF EXISTS `#__tapatalk_push_user`";
    		$db->setQuery($query);
    		$db->query ();
    	    $query ="CREATE TABLE IF NOT EXISTS `#__tapatalk_push_user` (
                      `user_id` int(11) NOT NULL DEFAULT '0',
                      `create_time` int(11) NOT NULL DEFAULT '0',
                      `update_time` int(11) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`user_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    		$db->setQuery($query);
    		$db->query ();
    	    $query ="DROP TABLE IF EXISTS `#__tapatalk_status`";
    		$db->setQuery($query);
    		$db->query ();
    	    $query ="CREATE TABLE IF NOT EXISTS `#__tapatalk_status` (
                      `status_info` text NOT NULL,
                      `create_time` int(11) NOT NULL DEFAULT '0',
                      `update_time` int(11) NOT NULL DEFAULT '0'
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    		$db->setQuery($query);
    		$db->query ();
    		return true;
    	} elseif ($cmd == 'uninstall') {
    	    $query ="DROP TABLE IF EXISTS `#__tapatalk_push_user`";
    		$db->setQuery($query);
    		$db->query ();
    	    $query ="DROP TABLE IF EXISTS `#__tapatalk_status`";
    		$db->setQuery($query);
    		$db->query ();
    		return true;
    	} else {
    	    return true;
    	}
	}
	
}

?>