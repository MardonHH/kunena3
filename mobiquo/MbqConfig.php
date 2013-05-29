<?php

defined('MBQ_IN_IT') or exit;

define('MBQ_DS', DIRECTORY_SEPARATOR);
define('MBQ_PATH', dirname(__FILE__).MBQ_DS);    /* mobiquo path */
define('MBQ_DIRNAME', basename(MBQ_PATH));    /* mobiquo dir name */
define('MBQ_PARENT_PATH', realpath(dirname(__FILE__).MBQ_DS.'..').MBQ_DS);    /* mobiquo parent dir path */
define('MBQ_FRAME_PATH', MBQ_PATH.'mbqFrame'.MBQ_DS);    /* frame path */
require_once(MBQ_FRAME_PATH.'MbqBaseConfig.php');

$_SERVER['SCRIPT_FILENAME'] = str_replace(MBQ_DIRNAME.'/', '', $_SERVER['SCRIPT_FILENAME']);  /* Important!!! */
$_SERVER['PHP_SELF'] = str_replace(MBQ_DIRNAME.'/', '', $_SERVER['PHP_SELF']);  /* Important!!! */
$_SERVER['SCRIPT_NAME'] = str_replace(MBQ_DIRNAME.'/', '', $_SERVER['SCRIPT_NAME']);    /* Important!!! */
$_SERVER['REQUEST_URI'] = str_replace(MBQ_DIRNAME.'/', '', $_SERVER['REQUEST_URI']);    /* Important!!! */

/**
 * plugin config
 * 
 * @since  2012-7-2
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqConfig extends MbqBaseConfig {

    public function __construct() {
        parent::__construct();
        require_once(MBQ_CUSTOM_PATH.'customDetectJs.php');
        $this->initCfg();
    }
    
    /**
     * init cfg default value
     */
    protected function initCfg() {
        parent::initCfg();
      /* user */
        $this->cfg['user']['module_name'] = MbqMain::$oClk->newObj('MbqValue', 'Joomla! Kunena');    /* module name.it indicate this module is supported by whitch applications or 3rd plugins/modules.it is used to distinguish the diffrent 3rd plugins or modules. */
        $this->cfg['user']['module_version'] = MbqMain::$oClk->newObj('MbqValue', 'Joomla!1.5.26+/Joomla!2.5.4+  Kunena2.0.x');    /* module version.it indicate the applications version that support this module. */
      /* forum */
        $this->cfg['forum']['module_name'] = MbqMain::$oClk->newObj('MbqValue', 'Kunena');
        $this->cfg['forum']['module_version'] = MbqMain::$oClk->newObj('MbqValue', 'Kunena2.0.x');
    }
    
    /**
     * calculate the final config of $this->cfg through $this->cfg default value and MbqMain::$customConfig and MbqMain::$oMbqAppEnv and the plugin support degree
     */
    public function calCfg() {
        parent::calCfg();
      /* calculate the final config */
        if ( class_exists('KunenaForum') && KunenaForum::isCompatible('2.0') && KunenaForum::enabled() ) {
            $this->cfg['forum']['module_enable']->setOriValue(MbqBaseFdt::getFdt('MbqFdtConfig.forum.module_enable.range.enable'));
        }
        /* because the forum module is the main function,so the is_open setting relys on the forum module status. */
        if (!$this->moduleIsEnable('forum')) {
            $this->cfg['base']['is_open']->setOriValue(MbqBaseFdt::getFdt('MbqFdtConfig.base.is_open.range.no'));
        }
        $this->cfg['base']['sys_version']->setOriValue(KunenaForum::version());
        if ($this->moduleIsEnable('user') && !MbqMain::$oMbqAppEnv->oKunenaConfig->regonly && ($this->getCfg('user.guest_okay')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.user.guest_okay.range.support'))) {
            $this->cfg['user']['guest_okay']->setOriValue(MbqBaseFdt::getFdt('MbqFdtConfig.user.guest_okay.range.support'));
        } else {
            $this->cfg['user']['guest_okay']->setOriValue(MbqBaseFdt::getFdt('MbqFdtConfig.user.guest_okay.range.notSupport'));
        }
        if ($this->moduleIsEnable('user') && MbqMain::$oMbqAppEnv->oKunenaConfig->showwhoisonline && ($this->getCfg('user.guest_whosonline')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.user.guest_whosonline.range.support'))) {
            $this->cfg['user']['guest_whosonline']->setOriValue(MbqBaseFdt::getFdt('MbqFdtConfig.user.guest_whosonline.range.support'));
        } else {
            $this->cfg['user']['guest_whosonline']->setOriValue(MbqBaseFdt::getFdt('MbqFdtConfig.user.guest_whosonline.range.notSupport'));
        }
        if ($this->moduleIsEnable('forum')) {
            $this->cfg['forum']['max_attachment']->setOriValue(MbqMain::$oMbqAppEnv->oKunenaConfig->attachment_limit);
        }
        if (MbqMain::$oMbqAppEnv->oKunenaConfig->reportmsg && ($this->getCfg('forum.report_post')->oriValue == MbqBaseFdt::getFdt('MbqFdtConfig.forum.report_post.range.support'))) {
            $this->cfg['forum']['report_post']->setOriValue(MbqBaseFdt::getFdt('MbqFdtConfig.forum.report_post.range.support'));
        } else {
            $this->cfg['forum']['report_post']->setOriValue(MbqBaseFdt::getFdt('MbqFdtConfig.forum.report_post.range.notSupport'));
        }
        if (!KunenaFactory::getConfig()->shownew) {
            $this->cfg['forum']['mark_read']->setOriValue(MbqBaseFdt::getFdt('MbqFdtConfig.forum.mark_read.range.notSupport'));
            $this->cfg['forum']['can_unread']->setOriValue(MbqBaseFdt::getFdt('MbqFdtConfig.forum.can_unread.range.notSupport'));
        }
    }
    
}

?>