<?php

require_once(KPATH_SITE.'/models/search.php');

/**
 * for kunena 2.0.1/2.0.2/2.0.3/2.0.4
 * ExttMbqKunenaModelSearch extended from KunenaModelSearch
 * add method exttMbqSearchTopic() modified from method getResults()
 * add method exttMbqSearchPost() modified from method getResults()
 * modify method populateState()
 * add property exttMbqParams
 * 
 * @since  2012-8-29
 * @modified by Wu ZeTao <578014287@qq.com>
 */
class ExttMbqKunenaModelSearch extends KunenaModelSearch {
    
    protected  $exttMbqParams = array();
    
    protected function populateState() {
        // Get search word list
        /*
        $value = JString::trim ( JRequest::getString ( 'q', '' ) );
        if ($value == JText::_('COM_KUNENA_GEN_SEARCH_BOX')) {
            $value = '';
        }
        $this->setState ( 'searchwords', $value );
        */
        $this->setState ( 'searchwords', $this->exttMbqParams['keywords']);

        $value = JRequest::getInt ( 'titleonly', 0 );
        $this->setState ( 'query.titleonly', $value );

        $value = JRequest::getString ( 'searchuser', '' );
        $this->setState ( 'query.searchuser', $value );

        $value = JRequest::getInt ( 'starteronly', 0 );
        $this->setState ( 'query.starteronly', $value );

        $value = JRequest::getInt ( 'exactname', 0 );
        $this->setState ( 'query.exactname', $value );

        $value = JRequest::getInt ( 'replyless', 0 );
        $this->setState ( 'query.replyless', $value );

        $value = JRequest::getInt ( 'replylimit', 0 );
        $this->setState ( 'query.replylimit', $value );

        //$value = JRequest::getString ( 'searchdate', 365 );
        $value = JRequest::getString ( 'searchdate', 'all' );
        $this->setState ( 'query.searchdate', $value );

        $value = JRequest::getWord ( 'beforeafter', 'after' );
        $this->setState ( 'query.beforeafter', $value );

        $value = JRequest::getWord ( 'sortby', 'lastpost' );
        $this->setState ( 'query.sortby', $value );

        $value = JRequest::getWord ( 'order', 'dec' );
        $this->setState ( 'query.order', $value );

        $value = JRequest::getInt ( 'childforums', 1 );
        $this->setState ( 'query.childforums', $value );

        $value = JRequest::getInt ( 'topic_id', 0 );
        $this->setState ( 'query.topic_id', $value );

        if (isset ( $_POST ['q'] ) || isset ( $_POST ['searchword'] )) {
            $value = JRequest::getVar ( 'catids', array (0), 'post', 'array' );
            JArrayHelper::toInteger($value);
        } else {
            $value = JRequest::getString ( 'catids', '0', 'get' );
            $value = explode ( ' ', $value );
            JArrayHelper::toInteger($value);
        }
        $this->setState ( 'query.catids', $value );

        $value = JRequest::getInt ( 'show', 0 );
        $this->setState ( 'query.show', $value );

        /*
        $value = $this->getInt ( 'limitstart', 0 );
        if ($value < 0) $value = 0;
        $this->setState ( 'list.start', $value );
        */
        $this->setState ( 'list.start', $this->exttMbqParams['oMbqDataPage']->startNum );

        /*
        $value = $this->getInt ( 'limit', 0 );
        if ($value < 1 || $value > 100) $value = $this->config->messages_per_page_search;
        $this->setState ( 'list.limit', $value );
        */
        $this->setState ( 'list.limit', $this->exttMbqParams['oMbqDataPage']->numPerPage );
    }
    
    /**
     * search post
     */
    public function exttMbqSearchPost($exttMbqParams) {
        $this->exttMbqParams = $exttMbqParams;
        if ($this->messages !== false) return $this->messages;

        $q = $this->getState('searchwords');
        if (!$q && !$this->getState('query.searchuser')) {
            $this->setError( JText::_('COM_KUNENA_SEARCH_ERR_SHORTKEYWORD'));
            return array();
        }

        /* get results */
        $hold = $this->getState('query.show');
        if ($hold == 1) {
            $mode = 'unapproved';
        } elseif ($hold >= 2) {
            $mode = 'deleted';
        } else {
            $mode = 'recent';
        }
        $params=array(
            'mode'=>$mode,
            'childforums'=>$this->getState('query.childforums'),
            'where'=>$this->buildWhere(),
            'orderby'=>$this->buildOrderBy(),
            'starttime'=>-1
        );
        $limitstart = $this->getState('list.start');
        $limit = $this->getState('list.limit');
        list($this->total, $this->messages) = KunenaForumMessageHelper::getLatestMessages($this->getState('query.catids'), $limitstart, $limit, $params);
        /*
        if ($this->total < $limitstart)
            $this->setState('list.start', intval($this->total / $limit) * $limit);

        $topicids = array();
        $userids = array();
        foreach ($this->messages as $message) {
            $topicids[$message->thread] = $message->thread;
            $userids[$message->userid] = $message->userid;
        }
        if ($topicids) {
            $topics = KunenaForumTopicHelper::getTopics($topicids);
            foreach ($topics as $topic) {
                $userids[$topic->first_post_userid] = $topic->first_post_userid;
            }
        }
        KunenaUserHelper::loadUsers($userids);
        KunenaForumMessageHelper::loadLocation($this->messages);

        if ( empty($this->messages) ) $this->app->enqueueMessage( JText::sprintf('COM_KUNENA_SEARCH_NORESULTS_FOUND', $q));
        */
        //return $this->messages;
        return array($this->total, $this->messages);
    }
    
    /**
     * search topic
     */
    public function exttMbqSearchTopic($exttMbqParams) {
        $this->exttMbqParams = $exttMbqParams;
        if ($this->messages !== false) return $this->messages;

        $q = $this->getState('searchwords');
        if (!$q && !$this->getState('query.searchuser')) {
            $this->setError( JText::_('COM_KUNENA_SEARCH_ERR_SHORTKEYWORD'));
            return array();
        }

        /* get results */
        $hold = $this->getState('query.show');
        if ($hold == 1) {
            $mode = 'unapproved';
        } elseif ($hold >= 2) {
            $mode = 'deleted';
        } else {
            $mode = 'recent';
        }
        $params=array(
            'mode'=>$mode,
            'childforums'=>$this->getState('query.childforums'),
            'where'=>$this->buildWhere(),
            'orderby'=>$this->buildOrderBy(),
            'starttime'=>-1
        );
        $limitstart = $this->getState('list.start');
        $limit = $this->getState('list.limit');
        /*
        list($this->total, $this->messages) = KunenaForumMessageHelper::getLatestMessages($this->getState('query.catids'), $limitstart, $limit, $params);

        if ($this->total < $limitstart)
            $this->setState('list.start', intval($this->total / $limit) * $limit);

        $topicids = array();
        $userids = array();
        foreach ($this->messages as $message) {
            $topicids[$message->thread] = $message->thread;
            $userids[$message->userid] = $message->userid;
        }
        if ($topicids) {
            $topics = KunenaForumTopicHelper::getTopics($topicids);
            foreach ($topics as $topic) {
                $userids[$topic->first_post_userid] = $topic->first_post_userid;
            }
        }
        KunenaUserHelper::loadUsers($userids);
        KunenaForumMessageHelper::loadLocation($this->messages);

        if ( empty($this->messages) ) $this->app->enqueueMessage( JText::sprintf('COM_KUNENA_SEARCH_NORESULTS_FOUND', $q));
        
        return $this->messages;
        */
        require_once(MBQ_APPEXTENTION_PATH.'ExttMbqKunenaForumMessageHelper.php');
        $params['exttMbqOnlySql'] = true;
        $exttMbqLoadMessageSql = ExttMbqKunenaForumMessageHelper::exttMbqGetLatestMessages($this->getState('query.catids'), $limitstart, $limit, $params);
        $user = KunenaUserHelper::getMyself();
        $db = JFactory::getDBO ();
        $query = "SELECT tt.*, ut.posts AS myposts, ut.last_post_id AS my_last_post_id, ut.favorite, tt.last_post_id AS lastread, 0 AS unread
                FROM #__kunena_topics AS tt
                LEFT JOIN #__kunena_user_topics AS ut ON tt.id=ut.topic_id AND ut.user_id={$db->Quote($user->userid)}
                WHERE tt.id in (
                    SELECT thread from ({$exttMbqLoadMessageSql}) as message
                )
                ORDER BY last_post_time DESC";
        $queryCount = "SELECT COUNT(*)
                FROM #__kunena_topics AS tt
                LEFT JOIN #__kunena_user_topics AS ut ON tt.id=ut.topic_id AND ut.user_id={$db->Quote($user->userid)}
                WHERE tt.id in (
                    SELECT thread from ({$exttMbqLoadMessageSql}) as message
                )";
        $db->setQuery($queryCount);
        $total = ( int ) $db->loadResult ();
        // If out of range, use last page
        if ($total < $limitstart)
            $limitstart = intval($total / $limit) * $limit;
        $db->setQuery ( $query, $limitstart, $limit );
        $results = (array) $db->loadAssocList ('id');
        $topics = array();
        foreach ( $results as $id => $result ) {
            $instance = new KunenaForumTopic ($result);
            $instance->exists(true);
            $topics[$id] = $instance;
        }
        unset ($results);
        return array($total, $topics);
    }
    
}

?>