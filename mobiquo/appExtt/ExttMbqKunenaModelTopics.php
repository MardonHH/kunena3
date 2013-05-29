<?php

require_once(KPATH_SITE.'/models/topics.php');

/**
 * for kunena 2.0.1/2.0.2/2.0.3/2.0.4
 * ExttMbqKunenaModelTopics extended from KunenaModelTopics
 * add method exttMbqGetRecentTopics() modified from method getRecentTopics()
 * 
 * @since  2012-8-23
 * @modified by Wu ZeTao <578014287@qq.com>
 */
class ExttMbqKunenaModelTopics extends KunenaModelTopics {

    /**
     * get topic
     * hack the getState() method in the original getRecentTopics() method
     *
     * @param  Array
	 * $p['catId'] means category id,in the original getTopics() method it always be changed to 0,so add this parameter to hack it.
	 * $p['start'] means the data index num need to be get start,in the original getTopics() method it always be changed to 0,so add this parameter to hack it.
	 * $p['limit'] means the data num per page,in the original getTopics() method it always be changed to config setting,so add this parameter to hack it.
	 * $p['mode'] means the list mode.
	 * $p['time'] means the time condition.
     */
	public function exttMbqGetRecentTopics($p) {
		//$catid = $this->getState ( 'item.id' );
		$catid = $p['catId'];
		//$limitstart = $this->getState ( 'list.start' );
		$limitstart = $p['start'];
		//$limit = $this->getState ( 'list.limit' );
		$limit = $p['limit'];
		//$time = $this->getState ( 'list.time' );
		$time = $p['time'];;
		if ($time < 0) {
			$time = 0;
		} elseif ($time == 0) {
			$time = KunenaFactory::getSession ()->lasttime;
		} else {
			$time = JFactory::getDate ()->toUnix () - ($time * 3600);
		}

		//$latestcategory = $this->getState ( 'list.categories' );
		$latestcategory = array($catid);
		$latestcategory_in = $this->getState ( 'list.categories.in' );

		$hold = 0;
		$where = '';
		$lastpost = true;

		//switch ($this->getState ( 'list.mode' )) {
		switch ($p['mode']) {
			case 'topics' :
				$lastpost = false;
				break;
			case 'sticky' :
				$where = 'AND tt.ordering>0';
				break;
			case 'locked' :
				$where = 'AND tt.locked>0';
				break;
			case 'noreplies' :
				$where = 'AND tt.posts=1';
				break;
			case 'unapproved' :
				$allowed = KunenaForumCategoryHelper::getCategories(false, false, 'topic.approve');
				if (empty($allowed)) {
					return array(0, array());
				}
				$allowed = implode(',', array_keys($allowed));
				$hold = '1';
				$where = "AND tt.category_id IN ({$allowed})";
				break;
			case 'deleted' :
				$allowed = KunenaForumCategoryHelper::getCategories(false, false, 'topic.undelete');
				if (empty($allowed)) {
					return array(0, array());
				}
				$allowed = implode(',', array_keys($allowed));
				$hold = '2';
				$where = "AND tt.category_id IN ({$allowed})";
				break;
			case 'replies' :
			default :
				break;
		}

		$params = array (
			'reverse' => ! $latestcategory_in,
			'orderby' => $lastpost ? 'tt.last_post_time DESC' : 'tt.first_post_time DESC',
			'starttime' => $time,
			'hold' => $hold,
			'where' => $where );

		list ( $this->total, $this->topics ) = KunenaForumTopicHelper::getLatestTopics ( $latestcategory, $limitstart, $limit, $params );
		$this->_common ();
		
		return array('total' => $this->total, 'topics' => $this->topics);
	}
	
}

?>