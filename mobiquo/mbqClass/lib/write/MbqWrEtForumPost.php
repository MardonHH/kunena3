<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseWrEtForumPost');

/**
 * forum post write class
 * 
 * @since  2012-8-21
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqWrEtForumPost extends MbqBaseWrEtForumPost {
    
    public function __construct() {
    }
    
    /**
     * add forum post
     *
     * @param  Mixed  $var($oMbqEtForumPost or $objsMbqEtForumPost)
     */
    public function addMbqEtForumPost(&$var) {
        if (is_array($var)) {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NOT_ACHIEVE);
        } else {
            /* modified from KunenaControllerTopic::post(),for kunena 2.0.1/2.0.2/2.0.3/2.0.4 */
            require_once KPATH_SITE . '/lib/kunena.link.class.php';
            //$this->id = JRequest::getInt('parentid', 0);
            $fields = array (
                //'catid' => $this->catid,
                'catid' => $var->forumId->oriValue,
                //'name' => JRequest::getString ( 'authorname', $this->me->getName () ),
                'name' => (MbqMain::$oCurMbqEtUser ? MbqMain::$oCurMbqEtUser->loginName->oriValue : ''),
                'email' => JRequest::getString ( 'email', null ),
                //'subject' => JRequest::getVar ( 'subject', null, 'POST', 'string', JREQUEST_ALLOWRAW ),
                'subject' => $var->postTitle->oriValue,
                //'message' => JRequest::getVar ( 'message', null, 'POST', 'string', JREQUEST_ALLOWRAW ),
                'message' => $var->postContent->oriValue,
                'icon_id' => JRequest::getInt ( 'topic_emoticon', null ),
                'anonymous' => JRequest::getInt ( 'anonymous', 0 ),
                'poll_title' => JRequest::getString ( 'poll_title', '' ),
                'poll_options' => JRequest::getVar('polloptionsID', array (), 'post', 'array'),
                'poll_time_to_live' => JRequest::getString ( 'poll_time_to_live', 0 ),
                'tags' => JRequest::getString ( 'tags', null ),
                'mytags' => JRequest::getString ( 'mytags', null ),
                'subscribe' => JRequest::getInt ( 'subscribeMe', 0 )
            );
            //$this->app->setUserState('com_kunena.postfields', $fields);
            MbqMain::$oMbqAppEnv->oApp->setUserState('com_kunena.postfields', $fields);
    
            /*
            if (! JRequest::checkToken ()) {
                $this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
                $this->redirectBack ();
            }
            */
    
            /*
            $captcha = KunenaSpamRecaptcha::getInstance();
            if ($captcha->enabled()) {
                $success = $captcha->verify();
                if ( !$success ) {
                    $this->app->enqueueMessage ( $captcha->getError(), 'error' );
                    $this->redirectBack ();
                }
            }
            */
            
            $parent = KunenaForumMessageHelper::get($var->parentPostId->oriValue);
            if (!$parent->authorise('reply')) {
                //$this->app->enqueueMessage ( $parent->getError(), 'notice' );
                //$this->redirectBack ();
                MbqError::alert('', '', '', MBQ_ERR_APP);
            }
            list ($topic, $message) = $parent->newReply($fields);
            $category = $topic->getCategory();
    
            // Flood protection
            //if ($this->config->floodprotection && ! $this->me->isModerator($category)) {
            if (MbqMain::$oMbqAppEnv->oKunenaConfig->floodprotection && MbqMain::$oCurMbqEtUser && ! MbqMain::$oCurMbqEtUser->mbqBind['oKunenaUser']->isModerator($category)) {
                //$timelimit = JFactory::getDate()->toUnix() - $this->config->floodprotection;
                $timelimit = JFactory::getDate()->toUnix() - MbqMain::$oMbqAppEnv->oKunenaConfig->floodprotection;
                $ip = $_SERVER ["REMOTE_ADDR"];
    
                $db = JFactory::getDBO();
                $db->setQuery ( "SELECT COUNT(*) FROM #__kunena_messages WHERE ip={$db->Quote($ip)} AND time>{$db->quote($timelimit)}" );
                $count = $db->loadResult ();
                if (KunenaError::checkDatabaseError() || $count) {
                    //$this->app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_POST_TOPIC_FLOOD', $this->config->floodprotection) );
                    //$this->redirectBack ();
                    MbqError::alert('', "Please post later!", '', MBQ_ERR_APP);
                }
            }
    
            // Set topic icon if permitted
            //if ($this->config->topicicons && isset($fields['icon_id']) && $topic->authorise('edit', null, false)) {
            if (MbqMain::$oMbqAppEnv->oKunenaConfig->topicicons && isset($fields['icon_id']) && $topic->authorise('edit', null, false)) {
                $topic->icon_id = $fields['icon_id'];
            }
            
            // Remove IP address
            // TODO: Add administrator tool to remove all tracked IP addresses (from the database)
            //if (!$this->config->iptracking) {
            if (!MbqMain::$oMbqAppEnv->oKunenaConfig->iptracking) {
                $message->ip = '';
            }
    
            // If requested: Make message to be anonymous
            if ($fields['anonymous'] && $message->getCategory()->allow_anonymous) {
                $message->makeAnonymous();
            }
    
            // If configured: Hold posts from guests
            //if ( !$this->me->userid && $this->config->hold_guest_posts) {
            if (MbqMain::$oCurMbqEtUser && !MbqMain::$oCurMbqEtUser->mbqBind['oKunenaUser']->userid && MbqMain::$oMbqAppEnv->oKunenaConfig->hold_guest_posts) {
                $message->hold = 1;
            }
            // If configured: Hold posts from users
            //if ( !$this->me->isModerator($category) && $this->me->posts < $this->config->hold_newusers_posts ) {
            if (MbqMain::$oCurMbqEtUser && !MbqMain::$oCurMbqEtUser->mbqBind['oKunenaUser']->isModerator($category) && MbqMain::$oCurMbqEtUser->mbqBind['oKunenaUser']->posts < MbqMain::$oMbqAppEnv->oKunenaConfig->hold_newusers_posts ) {
                $message->hold = 1;
            }
            
            // Prevent user abort from this point in order to maintain data integrity.
		    @ignore_user_abort(true);
    
            // Upload new attachments
            /*
            foreach ($_FILES as $key=>$file) {
                $intkey = 0;
                if (preg_match('/\D*(\d+)/', $key, $matches))
                    $intkey = (int)$matches[1];
                if ($file['error'] != UPLOAD_ERR_NO_FILE) $message->uploadAttachment($intkey, $key);
            }
            */
    
            // Activity integration
            $activity = KunenaFactory::getActivityIntegration();
            if ( $message->hold == 0 ) {
                /*
                if (!$topic->exists()) {
                    $activity->onBeforePost($message);
                } else {
                    $activity->onBeforeReply($message);
                }
                */
                $activity->onBeforeReply($message);
            }
    
            // Save message
            $success = $message->save ();
            if (! $success) {
                //$this->app->enqueueMessage ( $message->getError (), 'error' );
                //$this->redirectBack ();
                MbqError::alert('', "Can not save!".$message->getError (), '', MBQ_ERR_APP);
            }
    
            // Message has been sent, we can now clear saved form
            //$this->app->setUserState('com_kunena.postfields', null);
            MbqMain::$oMbqAppEnv->oApp->setUserState('com_kunena.postfields', null);
    
            // Display possible warnings (upload failed etc)
            foreach ( $message->getErrors () as $warning ) {
                //$this->app->enqueueMessage ( $warning, 'notice' );
                MbqError::alert('', $warning, '', MBQ_ERR_APP);
            }
    
            // Create Poll
            /*
            $poll_title = $fields['poll_title'];
            $poll_options = $fields['poll_options'];
            if (! empty ( $poll_options ) && ! empty ( $poll_title )) {
                if ($topic->authorise('poll.create', null, false)) {
                    $poll = $topic->getPoll();
                    $poll->title = $poll_title;
                    $poll->polltimetolive = $fields['poll_time_to_live'];
                    $poll->setOptions($poll_options);
                    if (!$poll->save()) {
                        $this->app->enqueueMessage ( $poll->getError(), 'notice' );
                    } else {
                        $topic->poll_id = $poll->id;
                        $topic->save();
                        $this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POLL_CREATED' ) );
                    }
                } else {
                    $this->app->enqueueMessage ( $topic->getError(), 'notice' );
                }
            }
            */
    
            // Update Tags
            //$this->updateTags($message->thread, $fields['tags'], $fields['mytags']);
    
            $message->sendNotification();
    
            //now try adding any new subscriptions if asked for by the poster
            $usertopic = $topic->getUserTopic();
            if ($fields['subscribe'] && !$usertopic->subscribed) {
                if ($topic->subscribe(1)) {
                    //$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_SUBSCRIBED_TOPIC' ) );
    
                    // Activity integration
                    $activity = KunenaFactory::getActivityIntegration();
                    $activity->onAfterSubscribe($topic, 1);
                } else {
                    //$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_NO_SUBSCRIBED_TOPIC' ) .' '. $topic->getError() );
                }
            }
    
            if ($message->hold == 1) {
                //$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_SUCCES_REVIEW' ) );
            } else {
                //$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_SUCCESS_POSTED' ) );
            }
            /*
            $category = KunenaForumCategoryHelper::get($this->return);
            if ($message->authorise('read', null, false)) {
                $this->setRedirect ( $message->getUrl($category, false) );
            } elseif ($topic->authorise('read', null, false)) {
                $this->setRedirect ( $topic->getUrl($category, false) );
            } else {
                $this->setRedirect ( $category->getUrl(null, false) );
            }
            */
            
            $var->postId->setOriValue($message->id);
            if ($var->attachmentIdArray->hasSetOriValue() && is_array($var->attachmentIdArray->oriValue) && $var->attachmentIdArray->oriValue) {
                //associate attachment
                $oDb = MbqMain::$oMbqAppEnv->oDb;
                $attIds = array();
                foreach ($var->attachmentIdArray->oriValue as $attId) {
                    $attIds[] = (int) $attId;
                }
                $sqlIn = MbqMain::$oMbqCm->getSqlIn($attIds, false);
                $userId = (MbqMain::$oCurMbqEtUser) ? MbqMain::$oCurMbqEtUser->userId->oriValue : 0;
                $oDb->setQuery("UPDATE #__kunena_attachments SET mesid={$oDb->Quote($message->id)} WHERE userid={$oDb->Quote($userId)} AND id in (".$sqlIn.") AND mesid = 0");
                $oDb->query();
            }
            if ($message->hold == 1) {
                $var->state->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.state.range.postOkNeedModeration'));
            } else {
                $var->state->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.state.range.postOk'));
            }
        }
    }
    
    /**
     * modify forum post
     *
     * @param  Mixed  $var($oMbqEtForumPost or $objsMbqEtForumPost)
     * $mbqOpt['case'] = 'edit' means edit forum post.
     */
    public function mdfMbqEtForumPost(&$var, $mbqOpt) {
        if (is_array($var)) {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NOT_ACHIEVE);
        } else {
            if ($mbqOpt['case'] == 'edit') {
                /* modified from KunenaControllerTopic::edit(),for kunena 2.0.1/2.0.2/2.0.3/2.0.4 */
                require_once KPATH_SITE . '/lib/kunena.link.class.php';
        		//$this->id = JRequest::getInt('mesid', 0);
        
        		//$message = KunenaForumMessageHelper::get($this->id);
        		$message = KunenaForumMessageHelper::get($var->postId->oriValue);
        		$topic = $message->getTopic();
        		$fields = array (
        			//'name' => JRequest::getString ( 'authorname', $message->name ),
                    'name' => (MbqMain::$oCurMbqEtUser ? MbqMain::$oCurMbqEtUser->loginName->oriValue : $message->name),
        			'email' => JRequest::getString ( 'email', $message->email ),
        			//'subject' => JRequest::getVar ( 'subject', $message->subject, 'POST', 'string', JREQUEST_ALLOWRAW ),
                    'subject' => ($var->postTitle->oriValue ? $var->postTitle->oriValue : $message->subject),
        			//'message' => JRequest::getVar ( 'message', $message->message, 'POST', 'string', JREQUEST_ALLOWRAW ),
                    'message' => ($var->postContent->oriValue ? $var->postContent->oriValue : $message->message),
        			'modified_reason' => JRequest::getString ( 'modified_reason', $message->modified_reason ),
        			'icon_id' => JRequest::getInt ( 'topic_emoticon', $topic->icon_id ),
        			'anonymous' => JRequest::getInt ( 'anonymous', 0 ),
        			'poll_title' => JRequest::getString ( 'poll_title', null ),
        			'poll_options' => JRequest::getVar('polloptionsID', array (), 'post', 'array'),
        			'poll_time_to_live' => JRequest::getString ( 'poll_time_to_live', 0 ),
        			'tags' => JRequest::getString ( 'tags', null ),
        			'mytags' => JRequest::getString ( 'mytags', null )
        		);
        
                /*
        		if (! JRequest::checkToken ()) {
        			$this->app->setUserState('com_kunena.postfields', $fields);
        			$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
        			$this->redirectBack ();
        		}
        		*/
        
        		if (!$message->authorise('edit')) {
        			//$this->app->setUserState('com_kunena.postfields', $fields);
                    MbqMain::$oMbqAppEnv->oApp->setUserState('com_kunena.postfields', $fields);
        			//$this->app->enqueueMessage ( $message->getError(), 'notice' );
        			//$this->redirectBack ();
                    MbqError::alert('', '', '', MBQ_ERR_APP);
        		}
        
        		// Update message contents
        		$message->edit ( $fields );
        		// If requested: Make message to be anonymous
        		if ($fields['anonymous'] && $message->getCategory()->allow_anonymous) {
        			$message->makeAnonymous();
        		}
        
        		// Mark attachments to be deleted
        		/*
        		$attachments = JRequest::getVar ( 'attachments', array(), 'post', 'array' );
        		$attachkeeplist = JRequest::getVar ( 'attachment', array(), 'post', 'array' );
        		$message->removeAttachment(array_keys(array_diff_key($attachments, $attachkeeplist)));
        		*/
        
        		// Upload new attachments
        		/*
        		foreach ($_FILES as $key=>$file) {
        			$intkey = 0;
        			if (preg_match('/\D*(\d+)/', $key, $matches))
        				$intkey = (int)$matches[1];
        			if ($file['error'] != UPLOAD_ERR_NO_FILE) $message->uploadAttachment($intkey, $key);
        		}
        		*/
        
        		// Set topic icon if permitted
        		//if ($this->config->topicicons && isset($fields['icon_id']) && $topic->authorise('edit', null, false)) {
                if (MbqMain::$oMbqAppEnv->oKunenaConfig->topicicons && isset($fields['icon_id']) && $topic->authorise('edit', null, false)) {
        			$topic->icon_id = $fields['icon_id'];
        		}
        
        		// Check if we are editing first post and update topic if we are!
        		if ($topic->first_post_id == $message->id) {
        			$topic->subject = $fields['subject'];
        		}
        
        		// Activity integration
        		$activity = KunenaFactory::getActivityIntegration();
        		$activity->onBeforeEdit($message);
        
        		// Save message
        		$success = $message->save ();
        		if (! $success) {
        			//$this->app->setUserState('com_kunena.postfields', $fields);
        			MbqMain::$oMbqAppEnv->oApp->setUserState('com_kunena.postfields', $fields);
        			//$this->app->enqueueMessage ( $message->getError (), 'error' );
        			//$this->redirectBack ();
                    MbqError::alert('', "Can not save!".$message->getError (), '', MBQ_ERR_APP);
        		}
        		// Display possible warnings (upload failed etc)
        		/*
        		foreach ( $message->getErrors () as $warning ) {
        			$this->app->enqueueMessage ( $warning, 'notice' );
        		}
        		*/
                
                /*
        		$poll_title = $fields['poll_title'];
        		if ($poll_title !== null) {
        			// Save changes into poll
        			$poll_options = $fields['poll_options'];
        			$poll = $topic->getPoll();
        			if (! empty ( $poll_options ) && ! empty ( $poll_title )) {
        				$poll->title = $poll_title;
        				$poll->polltimetolive = $fields['poll_time_to_live'];
        				$poll->setOptions($poll_options);
        				if (!$topic->poll_id) {
        					// Create a new poll
        					if (!$topic->authorise('poll.create')) {
        						$this->app->enqueueMessage ( $topic->getError(), 'notice' );
        					} elseif (!$poll->save()) {
        						$this->app->enqueueMessage ( $poll->getError(), 'notice' );
        					} else {
        						$topic->poll_id = $poll->id;
        						$topic->save();
        						$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POLL_CREATED' ) );
        					}
        				} else {
        					// Edit existing poll
        					if (!$topic->authorise('poll.edit')) {
        						$this->app->enqueueMessage ( $topic->getError(), 'notice' );
        					} elseif (!$poll->save()) {
        						$this->app->enqueueMessage ( $poll->getError(), 'notice' );
        					} else {
        						$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POLL_EDITED' ) );
        					}
        				}
        			} elseif ($poll->exists() && $topic->authorise('poll.edit')) {
        				// Delete poll
        				if (!$topic->authorise('poll.delete')) {
        					// Error: No permissions to delete poll
        					$this->app->enqueueMessage ( $topic->getError(), 'notice' );
        				} elseif (!$poll->delete()) {
        					$this->app->enqueueMessage ( $poll->getError(), 'notice' );
        				} else {
        					$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POLL_DELETED' ) );
        				}
        			}
        		}
        		*/
        
        		// Update Tags
        		//$this->updateTags($message->thread, $fields['tags'], $fields['mytags']);
        
        		//$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_SUCCESS_EDIT' ) );
        		if ($message->hold == 1) {
        			//$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_GEN_MODERATED' ) );
        		}
        		//$this->app->redirect ( $message->getUrl($this->return, false ) );
                
                if ($message->hold == 1) {
                    $var->state->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.state.range.postOkNeedModeration'));
                } else {
                    $var->state->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumPost.state.range.postOk'));
                }
            } else {
                MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_UNKNOWN_CASE);
            }
        }
    }
    
    /**
     * report post
     *
     * @param  Object  $oMbqEtForumPost
     * @param  Object  $oMbqEtForumReportPost
     */
    public function reportPost($oMbqEtForumPost, $oMbqEtForumReportPost) {
        /* modified from KunenaControllerTopic::report() */
        /*
		if (! JRequest::checkToken ()) {
			$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$this->redirectBack ();
		}
		*/

		//if (!$this->me->exists() || $this->config->reportmsg == 0) {
		if (!MbqMain::$oCurMbqEtUser->mbqBind['oKunenaUser']->exists() || MbqMain::$oMbqAppEnv->oKunenaConfig->reportmsg == 0) {
			// Deny access if report feature has been disabled or user is guest
			//$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_NO_ACCESS' ), 'notice' );
			//$this->redirectBack ();
			MbqError::alert('', '', '', MBQ_ERR_APP);
		}

		//if (!$this->config->get('send_emails')) {
		if (!MbqMain::$oMbqAppEnv->oKunenaConfig->get('send_emails')) {
			// Emails have been disabled
			//$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_EMAIL_DISABLED' ), 'notice' );
			//$this->redirectBack ();
			MbqError::alert('', "Email disabled!", '', MBQ_ERR_APP);
		}
		jimport ( 'joomla.mail.helper' );
		//if (! $this->config->getEmail() || ! JMailHelper::isEmailAddress ( $this->config->getEmail() )) {
		if (! MbqMain::$oMbqAppEnv->oKunenaConfig->getEmail() || ! JMailHelper::isEmailAddress ( MbqMain::$oMbqAppEnv->oKunenaConfig->getEmail() )) {
			// Error: email address is invalid
			//$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_EMAIL_INVALID' ), 'error' );
			//$this->redirectBack ();
			MbqError::alert('', "Email is invalid!", '', MBQ_ERR_APP);
		}

		// Get target object for the report
		/*
		if ($this->mesid) {
			$message = $target = KunenaForumMessageHelper::get($this->mesid);
			$topic = $target->getTopic();
		} else {
			$topic = $target = KunenaForumTopicHelper::get($this->id);
			$message = KunenaForumMessageHelper::get($topic->first_post_id);
		}
		*/
		$message = $target = $oMbqEtForumPost->mbqBind['oKunenaForumMessage'];
		$topic = $oMbqEtForumPost->oMbqEtForumTopic->mbqBind['oKunenaForumTopic'];
		$messagetext = $message->message;
		$baduser = KunenaFactory::getUser($message->userid);

		if (!$target->authorise('read')) {
			// Deny access if user cannot read target
			//$this->app->enqueueMessage ( $target->getError(), 'notice' );
			//$this->redirectBack ();
			MbqError::alert('', '', '', MBQ_ERR_APP);
		}
		$category = $topic->getCategory();

		//$reason = JRequest::getString ( 'reason' );
		//$text = JRequest::getString ( 'text' );
		$reason = $oMbqEtForumReportPost->reason->oriValue;
		$text = '';

		if (empty ( $reason ) && empty ( $text )) {
			// Do nothing: empty subject or reason is empty
			//$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_REPORT_FORG0T_SUB_MES' ) );
			//$this->redirectBack ();
			MbqError::alert('', "Need valid report reason!", '', MBQ_ERR_APP);
		} else {
			$acl = KunenaAccess::getInstance();
			//$emailToList = $acl->getSubscribers($topic->category_id, $topic->id, false, true, false, $this->me->userid);
			$emailToList = $acl->getSubscribers($topic->category_id, $topic->id, false, true, false, MbqMain::$oCurMbqEtUser->mbqBind['oKunenaUser']->userid);

			if (!empty ( $emailToList )) {
				//$mailsender = JMailHelper::cleanAddress ( $this->config->board_title . ' ' . JText::_ ( 'COM_KUNENA_FORUM' ) . ': ' . $this->me->getName() );
				$mailsender = JMailHelper::cleanAddress ( MbqMain::$oMbqAppEnv->oKunenaConfig->board_title . ' ' . JText::_ ( 'COM_KUNENA_FORUM' ) . ': ' . MbqMain::$oCurMbqEtUser->mbqBind['oKunenaUser']->getName() );
				//$mailsubject = "[" . $this->config->board_title . " " . JText::_ ( 'COM_KUNENA_FORUM' ) . "] " . JText::_ ( 'COM_KUNENA_REPORT_MSG' ) . ": ";
				$mailsubject = "[" . MbqMain::$oMbqAppEnv->oKunenaConfig->board_title . " " . JText::_ ( 'COM_KUNENA_FORUM' ) . "] " . JText::_ ( 'COM_KUNENA_REPORT_MSG' ) . ": ";
				if ($reason) {
					$mailsubject .= $reason;
				} else {
					$mailsubject .= $topic->subject;
				}

				jimport ( 'joomla.environment.uri' );
				$msglink = JUri::getInstance()->toString(array('scheme', 'host', 'port')) . $target->getPermaUrl(null, false);

				//$mailmessage = "" . JText::_ ( 'COM_KUNENA_REPORT_RSENDER' ) . " {$this->me->username} ({$this->me->name})";
				$mailmessage = "" . JText::_ ( 'COM_KUNENA_REPORT_RSENDER' ) . " ".MbqMain::$oCurMbqEtUser->mbqBind['oKunenaUser']->username." (".MbqMain::$oCurMbqEtUser->mbqBind['oKunenaUser']->name.")";
				$mailmessage .= "\n";
				$mailmessage .= "" . JText::_ ( 'COM_KUNENA_REPORT_RREASON' ) . " " . $reason;
				$mailmessage .= "\n";
				$mailmessage .= "" . JText::_ ( 'COM_KUNENA_REPORT_RMESSAGE' ) . " " . $text;
				$mailmessage .= "\n\n";
				$mailmessage .= "" . JText::_ ( 'COM_KUNENA_REPORT_POST_POSTER' ) . " {$baduser->username} ({$baduser->name})";
				$mailmessage .= "\n";
				$mailmessage .= "" . JText::_ ( 'COM_KUNENA_REPORT_POST_SUBJECT' ) . ": " . $topic->subject;
				$mailmessage .= "\n";
				$mailmessage .= "" . JText::_ ( 'COM_KUNENA_REPORT_POST_MESSAGE' ) . "\n-----\n" . KunenaHtmlParser::stripBBCode($messagetext, 0, false);
				$mailmessage .= "\n-----\n\n";
				$mailmessage .= "" . JText::_ ( 'COM_KUNENA_REPORT_POST_LINK' ) . " " . $msglink;
				$mailmessage = JMailHelper::cleanBody ( strtr ( $mailmessage, array ('&#32;' => '' ) ) );

				foreach ( $emailToList as $emailTo ) {
					if (! $emailTo->email || ! JMailHelper::isEmailAddress ( $emailTo->email ))
						continue;

					//JUtility::sendMail ( $this->config->getEmail(), $mailsender, $emailTo->email, $mailsubject, $mailmessage );
					JUtility::sendMail ( MbqMain::$oMbqAppEnv->oKunenaConfig->getEmail(), $mailsender, $emailTo->email, $mailsubject, $mailmessage );
				}

				//$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_REPORT_SUCCESS' ) );
			} else {
				//$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_REPORT_NOT_SEND' ) );
			    MbqError::alert('', "Report not send!", '', MBQ_ERR_APP);
			}
		}
		//$this->app->redirect ( $target->getUrl($this->return, false) );
    }
    
    /**
     * thank post
     *
     * @param  Object  $oMbqEtForumPost
     * @param  Object  $oMbqEtThank
     */
    public function thankPost($oMbqEtForumPost, $oMbqEtThank) {
        /* modified from KunenaControllerTopic::setThankyou() */
        /*
		if (! JRequest::checkToken ('get')) {
			$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$this->redirectBack ();
		}
		*/

		//$message = KunenaForumMessageHelper::get($this->mesid);
		$message = $oMbqEtForumPost->mbqBind['oKunenaForumMessage'];
		//if (!$message->authorise($type)) {
		if (!$message->authorise('thankyou')) {
			//$this->app->enqueueMessage ( $message->getError() );
			//$this->redirectBack ();
			MbqError::alert('', '', '', MBQ_ERR_APP);
		}

		//$category = KunenaForumCategoryHelper::get($this->catid);
		//$thankyou = KunenaForumMessageThankyouHelper::get($this->mesid);
		$thankyou = KunenaForumMessageThankyouHelper::get($oMbqEtForumPost->postId->oriValue);
		$activityIntegration = KunenaFactory::getActivityIntegration();
		//if ( $type== 'thankyou') {
			//if (!$thankyou->save ( $this->me )) {
			if (!$thankyou->save ( MbqMain::$oCurMbqEtUser->mbqBind['oKunenaUser'] )) {
				//$this->app->enqueueMessage ( $thankyou->getError() );
				//$this->redirectBack ();
				MbqError::alert('', $thankyou->getError(), '', MBQ_ERR_APP);
			}
			//$activityIntegration->onAfterThankyou($this->me->userid, $message->userid, $message);
			$activityIntegration->onAfterThankyou(MbqMain::$oCurMbqEtUser->mbqBind['oKunenaUser']->userid, $message->userid, $message);
	    /*
		} else {
			$userid = JRequest::getInt('userid','0');
			if (!$thankyou->delete ( $userid )) {
				$this->app->enqueueMessage ( $thankyou->getError() );
				$this->redirectBack ();
			}
			$activityIntegration->onAfterUnThankyou($userid, $this->me->userid, $message);
		}
		*/
		//$this->setRedirect($message->getUrl($category->exists() ? $category->id : $message->catid, false));
    }
    
    /**
     * m_delete_post
     *
     * @param  Object  $oMbqEtForumPost
     * @param  Integer  $mode
     */
    public function mDeletePost($oMbqEtForumPost, $mode) {
        $target = $oMbqEtForumPost->mbqBind['oKunenaForumMessage'];
        if ($mode == 1) {   //soft-delete
            //modified from KunenaControllerTopic::delete()
            $hold = KunenaForum::DELETED;
            if (!$target->publish($hold)) {
    			MbqError::alert('', "Delete post failed!", '', MBQ_ERR_APP);
            }
        } elseif ($mode == 2) { //hard-delete
            MbqError::alert('', "Sorry!Not support hard-delete a post!", '', MBQ_ERR_APP);
        } else {
            MbqError::alert('', "Need valid mode!", '', MBQ_ERR_APP);
        }
    }
    
    /**
     * m_undelete_post
     *
     * @param  Object  $oMbqEtForumPost
     */
    public function mUndeletePost($oMbqEtForumPost) {
        $target = $oMbqEtForumPost->mbqBind['oKunenaForumMessage'];
        //modified from KunenaControllerTopic::undelete()
        if (!$target->publish(KunenaForum::PUBLISHED)) {
			MbqError::alert('', "Undelete post failed!", '', MBQ_ERR_APP);
        }
    }
    
    /**
     * m_move_post
     *
     * @param  Object  $oMbqEtForumPost
     * @param  Mixed  $oTargetMbqEtForum
     * @param  Mixed  $oTargetMbqEtForumTopic
     * @param  Mixed  $topicTitle
     */
    public function mMovePost($oMbqEtForumPost, $oTargetMbqEtForum, $oTargetMbqEtForumTopic, $topicTitle) {
        $oMbqWrEtForumTopic = MbqMain::$oClk->newObj('MbqWrEtForumTopic');
        $oMbqWrEtForumTopic->exttMove(array('oMbqEtForumPost' => $oMbqEtForumPost, 'oTargetMbqEtForumTopic' => $oTargetMbqEtForumTopic, 'oTargetMbqEtForum' => $oTargetMbqEtForum, 'topicTitle' => $topicTitle));
    }
    
    /**
     * m_approve_post
     *
     * @param  Object  $oMbqEtForumPost
     * @param  Integer  $mode
     */
    public function mApprovePost($oMbqEtForumPost, $mode) {
        $target = $oMbqEtForumPost->mbqBind['oKunenaForumMessage'];
        if ($mode == 1) {
            $hold = KunenaForum::PUBLISHED;
        } elseif ($mode == 2) {
            $hold = KunenaForum::UNAPPROVED;
        } else {
            MbqError::alert('', "Need valid mode!", '', MBQ_ERR_APP);
        }
        /* modified from KunenaControllerTopic::approve() */
		if ($target->publish($hold)) {
			$target->sendNotification();
		} else {
			MbqError::alert('', $target->getError(), '', MBQ_ERR_APP);
		}
    }
  
}

?>