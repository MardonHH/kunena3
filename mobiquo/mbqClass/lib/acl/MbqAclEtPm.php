<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseAclEtPm');

/**
 * private message acl class
 * 
 * @since  2012-12-29
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqAclEtPm extends MbqBaseAclEtPm {
    
    public function __construct() {
    }
    /**
     * judge can report_pm
     *
     * @return  Boolean
     */
    public function canAclReportPm() {
        return MbqMain::hasLogin();
    }
    
    /**
     * judge can create_message
     *
     * @return  Boolean
     */
    public function canAclCreateMessage() {
        return MbqMain::hasLogin();
    }
    
    /**
     * judge can get_box_info
     *
     * @return  Boolean
     */
    public function canAclGetBoxInfo() {
        return MbqMain::hasLogin();
    }
    
    /**
     * judge can get_box
     *
     * @return  Boolean
     */
    public function canAclGetBox() {
        return MbqMain::hasLogin();
    }
    
    /**
     * judge can get_message
     *
     * @return  Boolean
     */
    public function canAclGetMessage() {
        return MbqMain::hasLogin();
    }
    
    /**
     * judge can get_quote_pm
     *
     * @return  Boolean
     */
    public function canAclGetQuotePm() {
        return MbqMain::hasLogin();
    }
    
    /**
     * judge can delete_message
     *
     * @return  Boolean
     */
    public function canAclDeleteMessage() {
        return MbqMain::hasLogin();
    }
    
    /**
     * judge can mark_pm_unread
     *
     * @return  Boolean
     */
    public function canAclMarkPmUnread() {
        return MbqMain::hasLogin();
    }
  
}

?>