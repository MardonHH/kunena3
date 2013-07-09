<?php

class PlgSystemTapatalkInstallerScript
{
    
	public function preflight($cmd, $parent)
	{
	    if ($cmd == 'install' || $cmd == 'update') {
    	    //error_log($cmd);
    		return true;
    	} else {
    	    return true;
    	}
	}
	
}

?>