<?php

require_once(KPATH_ADMIN.'/libraries/forum/message/attachment/helper.php');

/**
 * for kunena 2.0.1/2.0.2/2.0.3/2.0.4
 * ExttMbqKunenaForumMessageAttachmentHelper extended from KunenaForumMessageAttachmentHelper
 * modified method loadByMessage()
 * copied method getByMessage()
 * 
 * @since  2012-9-10
 * @modified by Wu ZeTao <578014287@qq.com>
 */
abstract class ExttMbqKunenaForumMessageAttachmentHelper extends KunenaForumMessageAttachmentHelper {
    
    /**
     * must copy this method from KunenaForumMessageAttachmentHelper,otherwise will call the KunenaForumMessageAttachmentHelper::loadByMessage() inner this method perhaps!
     */
    static public function getByMessage($ids = false, $authorise='read') {
		if ($ids === false) {
			return self::$_instances;
		} elseif (is_array ($ids) ) {
			$ids2 = array();
			foreach ($ids as $id) {
				if ($id instanceof KunenaForumMessage) $id = $id->id;
				$ids2[(int)$id] = (int)$id;
			}
			$ids = $ids2;
		} else {
			$ids = array($ids);
		}
		if (empty($ids)) return array();
		self::loadByMessage($ids);

		$list = array ();
		foreach ( $ids as $id ) {

			if (!empty(self::$_messages [$id])) {
				foreach (self::$_messages [$id] as $instance) {
					if ($instance->authorise($authorise, null, true)) {
						$list [$instance->id] = $instance;
					}
				}
			}
		}
		return $list;
	}

	static protected function loadByMessage($ids) {
	    /*
		foreach ($ids as $i=>$id) {
			$id = intval($id);
			if (!$id || isset(self::$_messages [$id]))
				unset($ids[$i]);
		}
		if (empty($ids))
			return;
        */

		$idlist = implode(',', $ids);
		$db = JFactory::getDBO ();
		$query = "SELECT * FROM #__kunena_attachments WHERE mesid IN ({$idlist})";
		$db->setQuery ( $query );
		$results = (array) $db->loadAssocList ('id');
		KunenaError::checkDatabaseError ();

		foreach ( $ids as $mesid ) {
			if (!isset(self::$_messages [$mesid])) {
				self::$_messages [$mesid] = array();
			}
		}
		foreach ( $results as $id=>$result ) {
			$instance = new KunenaForumMessageAttachment ();
			$instance->bind ( $result );
			$instance->exists(true);
			self::$_instances [$id] = $instance;
			self::$_messages [$instance->mesid][$id] = $instance;
		}
		unset ($results);
	}
	
}

?>