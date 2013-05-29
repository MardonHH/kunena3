<?php

require_once(KPATH_SITE.'/models/topic.php');

/**
 * for kunena 2.0.1/2.0.2/2.0.3/2.0.4
 * ExttMbqKunenaModelTopic extended from KunenaModelTopic
 * add method exttMbqGetTopic() modified from method getTopic()
 * add method exttMbqGetMessages() modified from method getMessages()
 * 
 * @since  2012-8-12
 * @modified by Wu ZeTao <578014287@qq.com>
 */
class ExttMbqKunenaModelTopic extends KunenaModelTopic {
	
	/**
	 * get topic
	 *
	 * @param  $params
	 * $params['topicId'] means topic id,in the original getTopic() method it always be changed to 0,so add this parameter to hack it.
	 */
    public function exttMbqGetTopic($params = array()) {
		if ($this->topic === false) {
			$mesid = $this->getState('item.mesid');
			if ($mesid) {
				// Find actual topic by fetching current message
				$message = KunenaForumMessageHelper::get($mesid);
				$topic = KunenaForumTopicHelper::get($message->thread);
				$this->setState ( 'list.start', intval($topic->getPostLocation($mesid) / $this->getState ( 'list.limit')) * $this->getState ( 'list.limit') );
			} else {
				//$topic = KunenaForumTopicHelper::get($this->getState ( 'item.id'));
				$topic = KunenaForumTopicHelper::get($params['topicId']);
				$ids = array();
				// If topic has been moved, find the new topic
				while ($topic->moved_id) {
					if (isset($ids[$topic->moved_id])) {
						// Break on loops
						return false;
					}
					$ids[$topic->moved_id] = 1;
					$topic = KunenaForumTopicHelper::get($topic->moved_id);
				}
				// If topic doesn't exist, check if there's a message with the same id
				/*if (! $topic->exists()) {
					$message = KunenaForumMessageHelper::get($this->getState ( 'item.id'));
					if ($message->exists()) {
						$topic = KunenaForumTopicHelper::get($message->thread);
					}
				}*/
			}
			$this->topic = $topic;
		}
		return $this->topic;
	}
	
	/**
	 * get messages
	 *
	 * @param  $params
	 * $params['topicId'] means topic id,in the original getMessages() method it always be changed to 0,so add this parameter to hack it.
	 * $params['start'] means the data index num need to be get start,in the original getMessages() method it always be changed to 0,so add this parameter to hack it.
	 * $params['limit'] means the data num per page,in the original getMessages() method it always be changed to config setting,so add this parameter to hack 
	 */
	public function exttMbqGetMessages($params) {
		if ($this->messages === false) {
			$layout = $this->getState ('layout');
			$threaded = ($layout == 'indented' || $layout == 'threaded');
			/*
			$this->messages = KunenaForumMessageHelper::getMessagesByTopic($this->getState ( 'item.id'),
				$this->getState ( 'list.start'), $this->getState ( 'list.limit'), $this->getState ( 'list.direction'), $this->getState ( 'hold'), $threaded);
            */
			$this->messages = KunenaForumMessageHelper::getMessagesByTopic($params['topicId'],
				$params['start'], $params['limit'], $this->getState ( 'list.direction'), $this->getState ( 'hold'), $threaded);

			// Get thankyous for all messages in the page
			$thankyous = KunenaForumMessageThankyouHelper::getByMessage($this->messages);

			// First collect ids and users
			$userlist = array();
			$this->threaded = array();
			if (KunenaForum::version() >= '2.0.3') $location = $params['start'];    //for compatible with kunena 2.0.3
			foreach($this->messages AS $message){
			    if (KunenaForum::version() >= '2.0.3') $message->replynum = ++$location;    //for compatible with kunena 2.0.3
				if ($threaded) {
					// Threaded ordering
					if (isset($this->messages[$message->parent])) {
						$this->threaded[$message->parent][] = $message->id;
					} else {
						$this->threaded[0][] = $message->id;
					}
				}
				$userlist[intval($message->userid)] = intval($message->userid);
				$userlist[intval($message->modified_by)] = intval($message->modified_by);

				$thankyou_list = $thankyous[$message->id]->getList();
				$message->thankyou = array();
				if(!empty($thankyou_list)) {
					$message->thankyou = $thankyou_list;
				}
			}
			if (!isset($this->messages[$this->getState ( 'item.mesid')]) && !empty($this->messages)) $this->setState ( 'item.mesid', reset($this->messages)->id);
			if ($threaded) {
				if (!isset($this->messages[$this->topic->first_post_id]))
					$this->messages = $this->getThreadedOrdering(0, array('edge'));
				else
					$this->messages = $this->getThreadedOrdering();
			}

			// Prefetch all users/avatars to avoid user by user queries during template iterations
			KunenaUserHelper::loadUsers($userlist);

			// Get attachments
			KunenaForumMessageAttachmentHelper::getByMessage($this->messages);
		}

		return $this->messages;
	}
    
}

?>