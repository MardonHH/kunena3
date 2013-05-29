<?php

require_once(KPATH_ADMIN.'/libraries/user/helper.php');

/**
 * for kunena 2.0.1/2.0.2/2.0.3/2.0.4
 * ExttMbqKunenaUserHelper extended from KunenaUserHelper
 * add method exttMbqLoadUsers() modified from method loadUsers(),fixed a bug in method loadUsers().
 * 
 * @since  2012-8-9
 * @modified by Wu ZeTao <578014287@qq.com>
 */
abstract class ExttMbqKunenaUserHelper extends KunenaUserHelper {

	public static function exttMbqLoadUsers(array $userids = array()) {
	    $exttMbqUserIds = $userids;
		// Make sure that userids are unique and that indexes are correct
		$e_userids = array();
		foreach($userids as &$userid){
			if (!$userid || $userid != intval($userid)) {
				unset($userid);
			} elseif (empty ( self::$_instances [$userid] )) {
				$e_userids[$userid] = $userid;
			}
		}

		if (!empty($e_userids)) {
			$userlist = implode ( ',', $e_userids );

			$db = JFactory::getDBO ();
			$query = "SELECT u.name, u.username, u.email, u.block as blocked, u.registerDate, u.lastvisitDate, ku.*
				FROM #__users AS u
				LEFT JOIN #__kunena_users AS ku ON u.id = ku.userid
				WHERE u.id IN ({$userlist})";
			$db->setQuery ( $query );
			$results = $db->loadAssocList ();
			KunenaError::checkDatabaseError ();

			foreach ( $results as $user ) {
				$instance = new KunenaUser (false);
				$instance->setProperties ( $user );
				$instance->exists(true);
				self::$_instances [$instance->userid] = $instance;
			}

			// Preload avatars if configured
			$avatars = KunenaFactory::getAvatarIntegration();
			$avatars->load($e_userids);
		}

		$list = array ();
		//foreach ($userids as $userid) {
		/* use $exttMbqUserIds instead of $userids,in the original loadUsers() method it can cause data inconsistent error comes from the & operator for $userids in the beginning of this method */
		foreach ($exttMbqUserIds as $userid) {
			if (isset(self::$_instances [$userid])) $list [$userid] = self::$_instances [$userid];
		}
		return $list;
	}
	
}

?>