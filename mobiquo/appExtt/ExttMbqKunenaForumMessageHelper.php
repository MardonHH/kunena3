<?php

require_once(KPATH_ADMIN.'/libraries/forum/message/helper.php');

/**
 * for kunena 2.0.1/2.0.2/2.0.3/2.0.4
 * ExttMbqKunenaForumMessageHelper extended from KunenaForumMessageHelper
 * add method exttMbqGetLatestMessages() modified from method getLatestMessages().
 * 
 * @since  2012-8-30
 * @modified by Wu ZeTao <578014287@qq.com>
 */
abstract class ExttMbqKunenaForumMessageHelper extends KunenaForumMessageHelper {

    /**
     * $params['exttMbqOnlySql'] = true means only get sql string
     * $params['exttMbqIsReply'] = true means get reply post
     * @return  Mixed
     */
	static public function exttMbqGetLatestMessages($categories=false, $limitstart=0, $limit=0, $params=array()) {
		$reverse = isset($params['reverse']) ? (int) $params['reverse'] : 0;
		$orderby = isset($params['orderby']) ? (string) $params['orderby'] : 'm.time DESC';
		$starttime = isset($params['starttime']) ? (int) $params['starttime'] : 0;
		$mode = isset($params['mode']) ? $params['mode'] : 'recent';
		$user = isset($params['user']) ? $params['user'] : false;
		$where = isset($params['where']) ? (string) $params['where'] : '';
		$childforums = isset($params['childforums']) ? (bool) $params['childforums'] : false;

		$db = JFactory::getDBO();
		// FIXME: use right config setting
		//if ($limit < 1) $limit = KunenaFactory::getConfig ()->threads_per_page;   //from kunena 2.0.1
		if ($limit < 1 && empty($params['nolimit'])) $limit = KunenaFactory::getConfig ()->threads_per_page;    //from kunena 2.0.2
		$cquery = new KunenaDatabaseQuery();
		$cquery->select('COUNT(*)')
			->from('#__kunena_messages AS m')
			->innerJoin('#__kunena_messages_text AS t ON m.id = t.mesid')
			->where('m.moved=0'); // TODO: remove column

		$rquery = new KunenaDatabaseQuery();
		$rquery->select('m.*, t.message')
			->from('#__kunena_messages AS m')
			->innerJoin('#__kunena_messages_text AS t ON m.id = t.mesid')
			->where('m.moved=0') // TODO: remove column
			->order($orderby);

		$authorise = 'read';
		$hold = 'm.hold=0';
		$userfield = 'm.userid';
		if ($params['exttMbqIsReply']) $exttMbqSubSqlIsReply = "m.parent > 0";
		switch ($mode) {
			case 'unapproved':
				$authorise = 'approve';
				$hold = "m.hold=1";
				break;
			case 'deleted':
				$authorise = 'undelete';
				$hold = "m.hold>=2";
				break;
			case 'mythanks':
				$userfield = 'th.userid';
				$cquery->innerJoin('#__kunena_thankyou AS th ON m.id = th.postid');
				$rquery->innerJoin('#__kunena_thankyou AS th ON m.id = th.postid');
				break;
			case 'thankyou':
				$userfield = 'th.targetuserid';
				$cquery->innerJoin('#__kunena_thankyou AS th ON m.id = th.postid');
				$rquery->innerJoin('#__kunena_thankyou AS th ON m.id = th.postid');
				break;
			case 'recent':
			default:
		}
		if (is_array($categories) && in_array(0, $categories)) {
			$categories = false;
		}
		$categories = KunenaForumCategoryHelper::getCategories($categories, $reverse, 'topic.'.$authorise);
		if ($childforums) {
			$categories += KunenaForumCategoryHelper::getChildren($categories, -1, false, array('action'=>'topic.'.$authorise));
		}
		$catlist = array();
		foreach ($categories as $category) {
			$catlist += $category->getChannels();
		}
		if (empty($catlist)) return array(0, array());
		$allowed = implode(',', array_keys($catlist));
		$cquery->where("m.catid IN ({$allowed})");
		$rquery->where("m.catid IN ({$allowed})");

		$cquery->where($hold);
		$rquery->where($hold);
		if ($user) {
			$cquery->where("{$userfield}={$db->Quote($user)}");
			$rquery->where("{$userfield}={$db->Quote($user)}");
		}

		// Negative time means no time
		if ($starttime == 0) {
			$starttime = KunenaFactory::getSession ()->lasttime;
		} elseif ($starttime > 0) {
			$starttime = JFactory::getDate ()->toUnix () - ($starttime * 3600);
		}
		if ($starttime > 0) {
			$cquery->where("m.time>{$db->Quote($starttime)}");
			$rquery->where("m.time>{$db->Quote($starttime)}");
		}
		if ($exttMbqSubSqlIsReply) {
    		$cquery->where($exttMbqSubSqlIsReply);
    		$rquery->where($exttMbqSubSqlIsReply);
		}
		if ($where) {
			$cquery->where($where);
			$rquery->where($where);
		}

		$db->setQuery ( $cquery );
		$total = ( int ) $db->loadResult ();
		if (KunenaError::checkDatabaseError() || !$total) return array(0, array());

		// If out of range, use last page
		//if ($total < $limitstart)     //from kunena 2.0.1
		if ($limit && $total < $limitstart)     //from kunena 2.0.2
			$limitstart = intval($total / $limit) * $limit;

		$db->setQuery ( $rquery, $limitstart, $limit );
		if ($params['exttMbqOnlySql'] == true) {
		    return (string) $db->getQuery();    //return sql
		}
		
		$results = $db->loadAssocList ();
		if (KunenaError::checkDatabaseError()) return array(0, array());

		$messages = array();
		foreach ( $results as $result ) {
			$instance = new KunenaForumMessage ($result);
			$instance->exists(true);
			self::$_instances [$instance->id] = $instance;
			$messages[$instance->id] = $instance;
		}
		unset ($results);
		return array($total, $messages);
	}
}

?>