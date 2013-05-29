<?php

defined('MBQ_IN_IT') or exit;

/**
 * application environment class
 * 
 * @since  2012-7-2
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqAppEnv extends MbqBaseAppEnv {
    
    /* this class fully relys on the application,so you can define the properties what you need come from the application. */
    public $oApp;    /* joomla application obj */
    public $oDb;
    public $oCurKunenaUser;
    public $oCurJUser;
    public $timeOffset;
    public $oKunenaConfig;
    public $joomlaRootUrl;
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * application environment init
     */
    public function init() {
        @ ob_start();
        define('_JEXEC', 1);
        define('DS', DIRECTORY_SEPARATOR);
        define('JPATH_BASE', realpath(MBQ_PARENT_PATH));    /* attention!!! */
        require_once JPATH_BASE.'/includes/defines.php';
        require_once JPATH_BASE.'/includes/framework.php';
        $this->oApp = JFactory::getApplication('site');
        $this->oApp->initialise();
        //$this->oApp->route();
        $GLOBALS['mainframe'] = $this->oApp;    //only for old joomla version,for example joomla 1.5.26
        $this->joomlaRootUrl = JURI::root();
        
        // Initialize Kunena (if Kunena System Plugin isn't enabled)
        $api = JPATH_ADMINISTRATOR . '/components/com_kunena/api.php';
        if (file_exists($api)) require_once $api;
        // Load router
        //require_once KPATH_SITE . '/router.php';
        KunenaFactory::loadLanguage('com_kunena.controllers');
        KunenaFactory::loadLanguage('com_kunena.models');
        KunenaFactory::loadLanguage('com_kunena.views');
        KunenaFactory::loadLanguage('com_kunena.templates');
        KunenaFactory::loadLanguage('com_kunena.sys', 'admin');
        // Load last to get deprecated language files to work
        KunenaFactory::loadLanguage('com_kunena');
        KunenaForum::setup();
        // Initialize error handlers
        KunenaError::initialize ();
        
        $this->timeOffset = $this->oApp->getCfg('offset');
        
        // Initialize session
        $ksession = KunenaFactory::getSession ( true );
        if ($ksession->userid > 0) {
            // Create user if it does not exist
            $kuser = KunenaUserHelper::getMyself ();
            if (! $kuser->exists ()) {
                $kuser->save ();
            }
            // Save session
            if (! $ksession->save ()) {
                MbqError::alert('', JText::_ ( 'COM_KUNENA_ERROR_SESSION_SAVE_FAILED' ));
            }
            if (MbqMain::$oMbqConfig->moduleIsEnable('user')) {
                $this->oCurKunenaUser = $kuser;
                $this->oCurJUser = JFactory::getUser();
                $oMbqRdEtUser = MbqMain::$oClk->newObj('MbqRdEtUser');
                $oMbqRdEtUser->initOCurMbqEtUser();
            }
        }
        
        $this->oKunenaConfig = KunenaFactory::getConfig();
        $this->oDb = JFactory::getDBO ();
        
        require_once(KPATH_SITE.'/lib/kunena.link.class.php');
        @ ob_end_clean();
    }
    
}

?>