<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseWrEtUser');

/**
 * user write class
 * 
 * @since  2012-9-28
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqWrEtUser extends MbqBaseWrEtUser {
    
    public function __construct() {
    }
    
    /**
     * m_ban_user
     *
     * @param  Object  $oMbqEtUser
     * @param  Integer  $mode
     * @param  String  $reasonText
     */
    public function mBanUser($oMbqEtUser, $mode, $reasonText) {
        /* modified from KunenaControllerUser::ban() */
		//$user = KunenaFactory::getUser(JRequest::getInt ( 'userid', 0 ));
		$user = $oMbqEtUser->mbqBind['oKunenaUser'];
		//if(!$user->exists() || !JRequest::checkToken()) {
		if(!$user->exists()) {
			//$this->app->redirect ( $user->getUrl(false), COM_KUNENA_ERROR_TOKEN, 'error' );
			//return;
			MbqError::alert('', 'Need valid user!', '', MBQ_ERR_APP);
		}

		$ip = JRequest::getVar ( 'ip', '' );
		$block = JRequest::getInt ( 'block', 0 );
		$expiration = JRequest::getString ( 'expiration', '' );
		$reason_private = JRequest::getString ( 'reason_private', '' );
		//$reason_public = JRequest::getString ( 'reason_public', '' );
		$reason_public = $reasonText;
		$comment = JRequest::getString ( 'comment', '' );

		$ban = KunenaUserBan::getInstanceByUserid ( $user->userid, true );
		if (! $ban->id) {
			$ban->ban ( $user->userid, $ip, $block, $expiration, $reason_private, $reason_public, $comment );
			$success = $ban->save ();
			//$this->report($user->userid);
			/* modified from KunenaControllerUser::report() */
			$userid = $user->userid;
    		//if(!$this->config->stopforumspam_key || ! $userid)
    		if(!MbqMain::$oMbqAppEnv->oKunenaConfig->stopforumspam_key || ! $userid)
    		{
    			//return false;
    		} else {
        		$spammer = JFactory::getUser($userid);
        
        		$db = JFactory::getDBO();
        		$db->setQuery ( "SELECT ip FROM #__kunena_messages WHERE userid=".$userid." GROUP BY ip ORDER BY `time` DESC", 0, 1 );
        		$ip = $db->loadResult();
        
        		//$data = "username=".$spammer->username."&ip_addr=".$ip."&email=".$spammer->email."&api_key=".$this->config->stopforumspam_key;
        		$data = "username=".$spammer->username."&ip_addr=".$ip."&email=".$spammer->email."&api_key=".MbqMain::$oMbqAppEnv->oKunenaConfig->stopforumspam_key;
        		$fp = fsockopen("www.stopforumspam.com",80);
        		fputs($fp, "POST /add.php HTTP/1.1\n" );
        		fputs($fp, "Host: www.stopforumspam.com\n" );
        		fputs($fp, "Content-type: application/x-www-form-urlencoded\n" );
        		fputs($fp, "Content-length: ".strlen($data)."\n" );
        		fputs($fp, "Connection: close\n\n" );
        		fputs($fp, $data);
        		fclose($fp);
        		//return true;
        	}
		} else {
			$delban = JRequest::getString ( 'delban', '' );

			if ( $delban ) {
				$ban->unBan($comment);
				$success = $ban->save ();
			} else {
				$ban->blocked = $block;
				$ban->setExpiration ( $expiration, $comment );
				$ban->setReason ( $reason_public, $reason_private );
				$success = $ban->save ();
			}
		}

		if ($block) {
			if ($ban->isEnabled ())
				$message = JText::_ ( 'COM_KUNENA_USER_BLOCKED_DONE' );
			else
				$message = JText::_ ( 'COM_KUNENA_USER_UNBLOCKED_DONE' );
		} else {
			if ($ban->isEnabled ())
				$message = JText::_ ( 'COM_KUNENA_USER_BANNED_DONE' );
			else
				$message = JText::_ ( 'COM_KUNENA_USER_UNBANNED_DONE' );
		}

		if (! $success) {
			//$this->app->enqueueMessage ( $ban->getError (), 'error' );
			MbqError::alert('', $ban->getError (), '', MBQ_ERR_APP);
		} else {
			//$this->app->enqueueMessage ( $message );
		}

		//$banDelPosts = JRequest::getVar ( 'bandelposts', '' );
		if ($mode == 2) {
		    $banDelPosts = 'bandelposts';
		} else {
		    $banDelPosts = '';
		}
		$DelAvatar = JRequest::getVar ( 'delavatar', '' );
		$DelSignature = JRequest::getVar ( 'delsignature', '' );
		$DelProfileInfo = JRequest::getVar ( 'delprofileinfo', '' );

		$db = JFactory::getDBO();
		if (! empty ( $DelAvatar ) || ! empty ( $DelProfileInfo )) {
			jimport ( 'joomla.filesystem.file' );
			$avatar_deleted = '';
			// Delete avatar from file system
			if (JFile::exists ( JPATH_ROOT . '/media/kunena/avatars/' . $userprofile->avatar ) && !stristr($userprofile->avatar,'gallery/')) {
				JFile::delete ( JPATH_ROOT . '/media/kunena/avatars/' . $userprofile->avatar );
				$avatar_deleted = $this->app->enqueueMessage ( JText::_('COM_KUNENA_MODERATE_DELETED_BAD_AVATAR_FILESYSTEM') );
			}
			$user->avatar = '';
			$user->save();
			$this->app->enqueueMessage ( JText::_('COM_KUNENA_MODERATE_DELETED_BAD_AVATAR') . $avatar_deleted );
		}
		if (! empty ( $DelProfileInfo )) {
			$user->personalText = '';
			$user->birthdate = '0000-00-00';
			$user->location = '';
			$user->gender = 0;
			$user->icq = '';
			$user->aim = '';
			$user->yim = '';
			$user->msn = '';
			$user->skype = '';
			$user->gtalk = '';
			$user->twitter = '';
			$user->facebook = '';
			$user->myspace = '';
			$user->linkedin = '';
			$user->delicious = '';
			$user->friendfeed = '';
			$user->digg = '';
			$user->blogspot = '';
			$user->flickr = '';
			$user->bebo = '';
			$user->websitename = '';
			$user->websiteurl = '';
			$user->signature = '';
			$user->save();
			$this->app->enqueueMessage ( JText::_('COM_KUNENA_MODERATE_DELETED_BAD_PROFILEINFO') );
		} elseif (! empty ( $DelSignature )) {
			$user->signature = '';
			$user->save();
			$this->app->enqueueMessage ( JText::_('COM_KUNENA_MODERATE_DELETED_BAD_SIGNATURE') );
		}

		if (! empty ( $banDelPosts )) {
			list($total, $messages) = KunenaForumMessageHelper::getLatestMessages(false, 0, 0, array('starttime'=> '-1','user' => $user->userid));
			foreach($messages as $mes) {
				$mes->publish(KunenaForum::DELETED);
			}
			//$this->app->enqueueMessage ( JText::_('COM_KUNENA_MODERATE_DELETED_BAD_MESSAGES') );
		}

		//$this->app->redirect ( $user->getUrl(false) );
    }
  
}

?>