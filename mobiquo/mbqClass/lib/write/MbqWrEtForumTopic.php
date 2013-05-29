<?php

defined('MBQ_IN_IT') or exit;

MbqMain::$oClk->includeClass('MbqBaseWrEtForumTopic');

/**
 * forum topic write class
 * 
 * @since  2012-8-15
 * @author Wu ZeTao <578014287@qq.com>
 */
Class MbqWrEtForumTopic extends MbqBaseWrEtForumTopic {
    
    public function __construct() {
    }
    
    /**
     * add forum topic view num
     *
     * @param  Mixed  $var($oMbqEtForumTopic or $objsMbqEtForumTopic)
     */
    public function addForumTopicViewNum(&$var) {
        if (is_array($var)) {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NOT_ACHIEVE);
        } else {
            $var->mbqBind['oKunenaForumTopic']->hit();
        }
    }
    
    /**
     * mark forum topic read
     *
     * @param  Mixed  $var($oMbqEtForumTopic or $objsMbqEtForumTopic)
     * @param  Array  $mbqOpt
     * $mbqOpt['case'] = 'markAllAsRead' means mark all my unread topics as read
     */
    public function markForumTopicRead(&$var = NULL, $mbqOpt = array()) {
        if ($mbqOpt['case'] == 'markAllAsRead') {
            $session = KunenaFactory::getSession();
            $session->markAllCategoriesRead();
            if (!$session->save ()) {
                MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_SAVE_FAIL);
            }
        } else {
            if (is_array($var)) {
                MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NOT_ACHIEVE);
            } else {
                $var->mbqBind['oKunenaForumTopic']->markRead();
            }
        }
    }
    
    /**
     * reset forum topic subscription
     *
     * @param  Mixed  $var($oMbqEtForumTopic or $objsMbqEtForumTopic)
     */
    public function resetForumTopicSubscription(&$var) {
        if (is_array($var)) {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NOT_ACHIEVE);
        } else {
            $var->mbqBind['oKunenaForumTopic']->markRead();
            // Check is subscriptions have been sent and reset the value
            if ($var->mbqBind['oKunenaForumTopic']->authorise('subscribe')) {
                $usertopic = $var->mbqBind['oKunenaForumTopic']->getUserTopic();
                if ($usertopic->subscribed == 2) {
                    $usertopic->subscribed = 1;
                    $usertopic->save();
                }
            }
        }
    }
    
    /**
     * add forum topic
     *
     * @param  Mixed  $var($oMbqEtForumTopic or $objsMbqEtForumTopic)
     */
    public function addMbqEtForumTopic(&$var) {
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
                'subject' => $var->topicTitle->oriValue,
                //'message' => JRequest::getVar ( 'message', null, 'POST', 'string', JREQUEST_ALLOWRAW ),
                'message' => $var->topicContent->oriValue,
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
    
            $category = KunenaForumCategoryHelper::get($var->forumId->oriValue);
            if (!$category->authorise('topic.create')) {
                //$this->app->enqueueMessage ( $category->getError(), 'notice' );
                //$this->redirectBack ();
                MbqError::alert('', '', '', MBQ_ERR_APP);
            }
            list ($topic, $message) = $category->newTopic($fields);
    
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
                $activity->onBeforePost($message);
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
            
            $var->topicId->setOriValue($topic->id);
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
            if ($topic->hold == 1) {
                $var->state->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.state.range.postOkNeedModeration'));
            } else {
                $var->state->setOriValue(MbqBaseFdt::getFdt('MbqFdtForum.MbqEtForumTopic.state.range.postOk'));
            }
        }
    }
    
    /**
     * subscribe topic
     *
     * @param  Mixed  $var($oMbqEtForumTopic or $objsMbqEtForumTopic)
     */
    public function subscribeTopic(&$var) {
        if (is_array($var)) {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NOT_ACHIEVE);
        } else {
            if (!$var->mbqBind['oKunenaForumTopic']->subscribe(1)) {
                MbqError::alert('', "Subscribe topic fail!", '', MBQ_ERR_APP);
            } else {
    			// Activity integration
    			$activity = KunenaFactory::getActivityIntegration();
    			//$activity->onAfterSubscribe($topic, 1);
    			$activity->onAfterSubscribe($var->mbqBind['oKunenaForumTopic'], 1);
            }
        }
    }
    
    /**
     * unsubscribe topic
     *
     * @param  Mixed  $var($oMbqEtForumTopic or $objsMbqEtForumTopic)
     */
    public function unsubscribeTopic(&$var) {
        if (is_array($var)) {
            MbqError::alert('', __METHOD__ . ',line:' . __LINE__ . '.' . MBQ_ERR_INFO_NOT_ACHIEVE);
        } else {
            if (!$var->mbqBind['oKunenaForumTopic']->subscribe(0)) {
                MbqError::alert('', "Unsubscribe topic fail!", '', MBQ_ERR_APP);
            } else {
    			// Activity integration
    			$activity = KunenaFactory::getActivityIntegration();
    			//$activity->onAfterSubscribe($topic, 0);
    			$activity->onAfterSubscribe($var->mbqBind['oKunenaForumTopic'], 0);
            }
        }
    }
    
    /**
     * m_stick_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Integer  $mode
     */
    public function mStickTopic($oMbqEtForumTopic, $mode) {
        $topic = $oMbqEtForumTopic->mbqBind['oKunenaForumTopic'];
        if ($mode == 1) {   //stick
            //modified from KunenaControllerTopic::sticky()
            if ($topic->sticky(1)) {
                // Activity integration
    			$activity = KunenaFactory::getActivityIntegration();
    			$activity->onAfterSticky($topic, 1);
            } else {
                MbqError::alert('', "Stick topic failed!", '', MBQ_ERR_APP);
            }
        } elseif ($mode == 2) { //unstick
            //modified from KunenaControllerTopic::unsticky()
            if ($topic->sticky(0)) {
    			// Activity integration
    			$activity = KunenaFactory::getActivityIntegration();
    			$activity->onAfterSticky($topic, 0);
            } else {
                MbqError::alert('', "Unstick topic failed!", '', MBQ_ERR_APP);
            }
        } else {
            MbqError::alert('', "Need valid mode!", '', MBQ_ERR_APP);
        }
    }
    
    /**
     * m_close_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Integer  $mode
     */
    public function mCloseTopic($oMbqEtForumTopic, $mode) {
        $topic = $oMbqEtForumTopic->mbqBind['oKunenaForumTopic'];
        if ($mode == 1) {   //reopen
            //modified from KunenaControllerTopic::unlock()
    		if ($topic->lock(0)) {
    			// Activity integration
    			$activity = KunenaFactory::getActivityIntegration();
    			$activity->onAfterLock($topic, 0);
    		} else {
    			MbqError::alert('', "Reopen topic failed!", '', MBQ_ERR_APP);
    		}
        } elseif ($mode == 2) { //close
            //modified from KunenaControllerTopic::lock()
    		if ($topic->lock(1)) {
    			// Activity integration
    			$activity = KunenaFactory::getActivityIntegration();
    			$activity->onAfterLock($topic, 1);
    		} else {
    			MbqError::alert('', "Close topic failed!", '', MBQ_ERR_APP);
    		}
        } else {
            MbqError::alert('', "Need valid mode!", '', MBQ_ERR_APP);
        }
    }
    
    /**
     * m_delete_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Integer  $mode
     */
    public function mDeleteTopic($oMbqEtForumTopic, $mode) {
        $topic = $oMbqEtForumTopic->mbqBind['oKunenaForumTopic'];
        if ($mode == 1) {   //soft-delete
            //modified from KunenaControllerTopic::delete()
            $hold = KunenaForum::TOPIC_DELETED;
            if (!$topic->publish($hold)) {
    			MbqError::alert('', "Delete topic failed!", '', MBQ_ERR_APP);
            }
        } elseif ($mode == 2) { //hard-delete
            MbqError::alert('', "Sorry!Not support hard-delete a topic!", '', MBQ_ERR_APP);
        } else {
            MbqError::alert('', "Need valid mode!", '', MBQ_ERR_APP);
        }
    }
    
    /**
     * m_undelete_topic
     *
     * @param  Object  $oMbqEtForumTopic
     */
    public function mUndeleteTopic($oMbqEtForumTopic) {
        $topic = $oMbqEtForumTopic->mbqBind['oKunenaForumTopic'];
        //modified from KunenaControllerTopic::undelete()
        if (!$topic->publish(KunenaForum::PUBLISHED)) {
            MbqError::alert('', "Undelete topic failed!", '', MBQ_ERR_APP);
        }
    }
    
    /**
     * m_move_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Object  $oMbqEtForum
     */
    public function mMoveTopic($oMbqEtForumTopic, $oMbqEtForum) {
        $this->exttMove(array('oMbqEtForumTopic' => $oMbqEtForumTopic, 'oTargetMbqEtForum' => $oMbqEtForum));
    }
    
    /**
     * move for m_move_topic/m_move_post
     */
    public function exttMove($mbqOpt) {
        /* modified from KunenaControllerTopic::move() */
        /*
		if (! JRequest::checkToken ()) {
			$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$this->redirectBack ();
		}
		*/

		//$topicId = JRequest::getInt('id', 0);
		$topicId = $mbqOpt['oMbqEtForumTopic'] ? $mbqOpt['oMbqEtForumTopic']->topicId->oriValue : 0;
		//$messageId = JRequest::getInt('mesid', 0);
		$messageId = $mbqOpt['oMbqEtForumPost'] ? $mbqOpt['oMbqEtForumPost']->postId->oriValue : 0;
		//$targetTopic = JRequest::getInt ( 'targetid', JRequest::getInt ( 'targettopic', 0 ));
		$targetTopic = $mbqOpt['oTargetMbqEtForumTopic'] ? $mbqOpt['oTargetMbqEtForumTopic']->topicId->oriValue : 0;
		//$targetCategory = JRequest::getInt ( 'targetcategory', 0 );
		$targetCategory = $mbqOpt['oTargetMbqEtForum'] ? $mbqOpt['oTargetMbqEtForum']->forumId->oriValue : 0;

		if ($messageId) {
			$object = KunenaForumMessageHelper::get ( $messageId ); //need moved post
			$topic = $object->getTopic();
		} else {
			$object = KunenaForumTopicHelper::get ( $topicId ); //need moved topic
			$topic = $object;
		}
		if ($targetTopic) {
			$target = KunenaForumTopicHelper::get( $targetTopic );  //need moved to topic
		} else {
			$target = KunenaForumCategoryHelper::get( $targetCategory );    //need moved to forum
		}

		$error = null;
		if (!$object->authorise ( 'move' )) {
			$error = $object->getError();
			MbqError::alert('', $error, '', MBQ_ERR_APP);
		} elseif (!$target->authorise ( 'read' )) {
			$error = $target->getError();
			MbqError::alert('', $error, '', MBQ_ERR_APP);
		} else {
			$changesubject = JRequest::getBool ( 'changesubject', false );
			//$subject = JRequest::getString ( 'subject', '' );
			$subject = $mbqOpt['topicTitle'] ? $mbqOpt['topicTitle'] : '';  //new topic title
			$shadow = JRequest::getBool ( 'shadow', false );
			$topic_emoticon = JRequest::getInt ( 'topic_emoticon', null );
			if (!is_null($topic_emoticon)) $topic->icon_id = $topic_emoticon;

			if ($object instanceof KunenaForumMessage) {
				$mode = JRequest::getWord ( 'mode', 'selected' );
				switch ($mode) {
					case 'newer':
						$ids = new JDate($object->time);
						break;
					case 'selected':
					default:
						$ids = $object->id;
						break;
				}
			} else {
				$ids = false;
			}
			$targetobject = $topic->move ( $target, $ids, $shadow, $subject, $changesubject );
			if (!$targetobject) {
				$error = $topic->getError();
				MbqError::alert('', $error, '', MBQ_ERR_APP);
			}
		}
		/*
		if ($error) {
			$this->app->enqueueMessage ( $error, 'notice' );
		} else {
			$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_SUCCESS_MOVE' ) );
		}
		if ($targetobject) {
			$this->app->redirect ( $targetobject->getUrl($this->return, false, 'last' ) );
		} else {
			$this->app->redirect ( $topic->getUrl($this->return, false, 'first' ) );
		}
		*/
    }
    
    /**
     * m_rename_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  String  $title
     */
    public function mRenameTopic($oMbqEtForumTopic, $title) {
        if ($oFirstMbqEtForumPost = $oMbqEtForumTopic->oFirstMbqEtForumPost) {
            $oMbqWrEtForumPost = MbqMain::$oClk->newObj('MbqWrEtForumPost');
            $oFirstMbqEtForumPost->postTitle->setOriValue($title);
            $oMbqWrEtForumPost->mdfMbqEtForumPost($oFirstMbqEtForumPost, array('case' => 'edit'));
        } else {
            MbqError::alert('', 'Need valid oFirstMbqEtForumPost property!', '', MBQ_ERR_APP);
        }
    }
    
    /**
     * m_approve_topic
     *
     * @param  Object  $oMbqEtForumTopic
     * @param  Integer  $mode
     */
    public function mApproveTopic($oMbqEtForumTopic, $mode) {
        $target = $oMbqEtForumTopic->mbqBind['oKunenaForumTopic'];
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