<?php

require_once(KPATH_ADMIN.'/libraries/forum/message/attachment/attachment.php');

/**
 * for kunena 2.0.1/2.0.2/2.0.3/2.0.4
 * ExttMbqKunenaForumMessageAttachment extended from KunenaForumMessageAttachment
 * add method exttMbqSave() modified from method save().
 * 
 * @since  2012-9-11
 * @modified by Wu ZeTao <578014287@qq.com>
 */
class ExttMbqKunenaForumMessageAttachment extends KunenaForumMessageAttachment {

	public function exttMbqSave($updateOnly = false) {
		// Do not save altered message
		//if ($this->_disabled) return;
		if ($this->_disabled || $this->disabled) return; //$this->disabled for compatible with kunena 2.0.3

		// Create the messages table object
		$table = $this->getTable ();
		$table->bind ( $this->getProperties () );
		$table->exists ( $this->_exists );

		if ($this->getError()) {
			return false;
		}
		// Check and store the object.
		if (! $table->check ()) {
			$this->setError ( $table->getError () );
			//return false;
		}

		//are we creating a new message
		$isnew = ! $this->_exists;

		// If we aren't allowed to create new message return
		if ($isnew && $updateOnly) {
			return true;
		}

		//Store the message data in the database
		if (! $result = $table->store ()) {
			$this->setError ( $table->getError () );
		}

		// Set the id for the KunenaForumMessageAttachment object in case we created a new message.
		if ($result && $isnew) {
			$this->load ( $table->get ( 'id' ) );
			$this->_exists = true;
		}

		return $result;
	}
	
}

?>