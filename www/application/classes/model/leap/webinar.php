<?php
/**
 * Open Labyrinth [ http://www.openlabyrinth.ca ]
 *
 * Open Labyrinth is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Open Labyrinth is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Open Labyrinth.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2012 Open Labyrinth. All Rights Reserved.
 *
 */
defined('SYSPATH') or die('No direct script access.');

/**
 * Model for users table in database
 */
class Model_Leap_Webinar extends DB_ORM_Model {

    public function __construct() {
        parent::__construct();

        $this->fields = array(
            'id' => new DB_ORM_Field_Integer($this, array(
                'max_length' => 11,
                'nullable' => FALSE,
                'unsigned' => TRUE
            )),

            'author_id' => new DB_ORM_Field_Integer($this, array(
                'max_length' => 11,
                'nullable' => FALSE,
                'unsigned' => TRUE
            )),

            'title' => new DB_ORM_Field_String($this, array(
                'max_length' => 250,
                'nullable' => FALSE,
                'savable' => TRUE
            )),

            'current_step' => new DB_ORM_Field_Integer($this, array(
                'max_length' => 11,
                'nullable' => TRUE,
                'unsigned' => TRUE
            )),

            'forum_id' => new DB_ORM_Field_Integer($this, array(
                'max_length' => 11,
                'nullable' => FALSE,
                'savable' => TRUE
            )),

            'isForum' => new DB_ORM_Field_Integer($this, array(
                'max_length' => 1,
                'nullable' => FALSE,
                'savable' => TRUE
            )),

            'publish' => new DB_ORM_Field_String($this, array(
                'max_length' => 100,
                'nullable' => TRUE,
                'savable' => TRUE
            ))
        );

        $this->relations = array(
            'maps' => new DB_ORM_Relation_HasMany($this, array(
                'child_key' => array('webinar_id'),
                'child_model' => 'webinar_map',
                'parent_key' => array('id')
            )),

            'users' => new DB_ORM_Relation_HasMany($this, array(
                'child_key' => array('webinar_id'),
                'child_model' => 'webinar_user',
                'parent_key' => array('id')
            )),

            'groups' => new DB_ORM_Relation_HasMany($this, array(
                'child_key' => array('webinar_id'),
                'child_model' => 'webinar_group',
                'parent_key' => array('id')
            )),

            'forum' => new DB_ORM_Relation_BelongsTo($this, array(
                'child_key' => array('forum_id'),
                'parent_key' => array('id'),
                'parent_model' => 'dforum'
            )),

            'steps' => new DB_ORM_Relation_HasMany($this, array(
                'child_key' => array('webinar_id'),
                'child_model' => 'webinar_step',
                'parent_key' => array('id')
            ))
        );
    }

    public static function data_source() {
        return 'default';
    }

    public static function table() {
        return 'webinars';
    }

    public static function primary_key() {
        return array('id');
    }

    /**
     * Return all webinars
     *
     * @return array|null
     */
    public function getAllWebinars($userId = null)
    {
        $query = DB_ORM::select('Webinar');

        if ($userId) $query->where('author_id', '=', $userId);

        return $query->query()->as_array();
    }

    /**
     * Save webinar
     *
     * @param array $values - values
     */
    public function saveWebinar($values)
    {
        $webinarId      = Arr::get($values, 'webinarId', null);
        $webinar        = null;
        $isNew          = false;
        $isUseForumName = Arr::get($values, 'use', null);
        $useForumId     = Arr::get($values, 'forum', null);
        $useTopicId     = Arr::get($values, 'topic', null);
        $experts        = Arr::get($values, 'experts', array());
        $poll_nodes     = Arr::get($values, 'poll_nodes', array());

        if($webinarId == null || $webinarId < 0)
        {
            $webinarId = DB_ORM::insert('webinar')
                ->column('title', Arr::get($_POST, 'title', ''))
                ->column('author_id', Auth::instance()->get_user()->id)
                ->execute();
            $webinar   = DB_ORM::model('webinar', array((int)$webinarId));
            $isNew     = true;
        }
        else
        {
            $webinar = DB_ORM::model('webinar', array((int)$webinarId));
            DB_ORM::update('webinar')
                    ->set('title', Arr::get($values, 'title', ''))
                    ->where('id', '=', $webinarId)
                    ->execute();
        }

        $completedStepIDs = array();
        if($webinar->steps != null && count($webinar->steps) > 0) {
            foreach($webinar->steps as $webinarStep) {
                $stepName = Arr::get($values, 's' . $webinarStep->id . '_name', null);
                DB_ORM::model('webinar_map')->removeMapsForStep($webinar->id, $webinarStep->id);
                if($stepName != null) {
                    $completedStepIDs['s'.$webinarStep->id.'_name'] = $webinarStep->id;
                    DB_ORM::model('webinar_step')->updateStep($webinarStep->id, $stepName);
                    $maps = Arr::get($values, 's'.$webinarStep->id.'_labyrinths', null);
                    if($maps != null && count($maps) > 0)
                    {
                        foreach($maps as $webinarStepMap)
                        {
                            $which = substr_count($webinarStepMap, 'section') ? 'section': 'labyrinth';
                            if ($which == 'section') $webinarStepMap = str_replace('section', '', $webinarStepMap);
                            DB_ORM::model('webinar_map')->addMap($webinar->id, $webinarStepMap, $webinarStep->id, $which);
                        }
                    }
                } else {
                    DB_ORM::model('webinar_step')->removeStep($webinarStep->id);
                }
            }
        }

        foreach($values as $key => $value)
        {
            if(strpos($key, '_name') !== false && !isset($completedStepIDs[$key]))
            {
                $pattern = '/s(\d+)_name/i';
                $id = preg_replace($pattern, '$1', $key);
                if(!is_numeric($id)) continue;

                $stepName = Arr::get($values, 's'.$id.'_name', null);
                if($stepName == null) continue;

                $stepId = DB_ORM::model('webinar_step')->addStep($webinar->id, $stepName);

                $maps = Arr::get($values, 's'.$id.'_labyrinths', array());
                foreach($maps as $webinarMap)
                {
                    $which = substr_count($webinarMap, 'section') ? 'section': 'labyrinth';
                    if ($which == 'section') $webinarMap = str_replace('section', '', $webinarMap);
                    DB_ORM::model('webinar_map')->addMap($webinar->id, $webinarMap, $stepId, $which);
                }
            }
        }

        $forumId   = null;
        if($webinar->forum_id != null && $webinar->forum_id > 0) {
            $forumId = $webinar->forum_id;
            $forumInfo = DB_ORM::model('dforum', array((int)$forumId));
            DB_ORM::model('dforum')->updateForum($webinar->title, 1, $forumInfo->status, $forumId);
        } else {
            if (!$isUseForumName) {
                $forumId = DB_ORM::model('dforum')->createForum($webinar->title, 1,1);
            }
            else {
                $forumId = ($useTopicId) ? $useTopicId : $useForumId;
            }
        }

        $firstMessage = Arr::get($values, 'firstmessage', null);

        if($firstMessage != null && strlen($firstMessage) > 0 && ($webinar->forum_id == null || $webinar->forum_id <= 0)) {
            DB_ORM::model('dforum_messages')->createMessage($forumId, $firstMessage);
        }

        DB_ORM::update('webinar')
            ->set('forum_id', $forumId)
            ->where('id', '=', $webinarId)
            ->execute();

        if ($useTopicId) {
            DB_ORM::update('webinar')
                ->set('isForum', 0)
                ->where('id', '=', $webinarId)
                ->where('forum_id','=',$forumId)
                ->execute();
        }

        $formUsers  = Arr::get($values, 'users', null);

        $users  = array();
        $groups = Arr::get($values, 'groups', null);

        $groupsUserMap = array();
        if(count($webinar->groups) > 0) {
            foreach($webinar->groups as $webinarGroup) {
                if(count($webinarGroup->group->users) > 0) {
                    foreach($webinarGroup->group->users as $groupUser) {
                        $groupsUserMap[$groupUser->user_id] = $groupUser->user_id;
                    }
                }
            }
        }

        if($formUsers != null && count($formUsers) > 0) {
            foreach($formUsers as $formUserId) {
                if(!isset($groupsUserMap[$formUserId])) {
                    $users[] = $formUserId;
                }
            }
        }

        DB_ORM::model('webinar_group')->removeAllGroups($webinarId);
        DB_ORM::model('webinar_user')->removeUsers($webinarId);

        if(count($users) > 0)
        {
            foreach($users as $userId)
            {
                $expert = (in_array($userId, $experts)) ? 1 : 0;
                DB_ORM::model('webinar_user')->addUser($webinarId, $userId, $expert);
                $usersMap[$userId] = $userId;
            }
        }

        if(count($groups) > 0) {
            foreach($groups as $groupId) {
                DB_ORM::model('webinar_group')->addGroup($webinarId, $groupId);
                $usersGroup = DB_ORM::model('group')->getAllUsersInGroup($groupId);
                if(count($usersGroup) > 0) {
                    foreach($usersGroup as $userGroup) {
                        if( ! isset($usersMap[$userGroup->id])) {
                            $expert = (in_array($userGroup->id, $experts)) ? 1 : 0;
                            DB_ORM::model('webinar_user')->addUser($webinarId, $userGroup->id, $expert);
                        }
                    }
                }
            }
        }

        $formUsers[] = Auth::instance()->get_user()->id;
        DB_ORM::model('dforum_users')->updateUsers($forumId, $formUsers);
        DB_ORM::model('dforum_groups')->updateGroups($forumId, $groups);

        // create poll node
        $updatePoLLNode = function ($poll_nodes, $webinarId)
        {
            // create poll node
            for ($i=0; $i<count($poll_nodes); $i+=2)
            {
                if ($poll_nodes[$i]) DB_ORM::model('Webinar_PollNode')->update($poll_nodes[$i], $webinarId, $poll_nodes[$i+1]);
            }
        };

        if($isNew) {
            $updatedWebinar = DB_ORM::model('webinar', array((int)$webinar->id));
            if($updatedWebinar->steps != null && count($updatedWebinar->steps) > 0) {
                $min = $updatedWebinar->steps[0]->id;
                foreach($updatedWebinar->steps as $webinarStep) {
                    if($webinarStep->id < $min) {
                        $min = $webinarStep->id;
                    }
                }

                DB_ORM::model('webinar')->changeWebinarStep($updatedWebinar->id, $min);
            }

            $updatePoLLNode($poll_nodes, $webinarId);
        }

        // ----- update poll node ----- //
        $exist_poll_nodes = DB_ORM::model('Webinar_PollNode')->getWebinarNodes($webinarId);
        foreach ($exist_poll_nodes as $pollNodeObj)
        {
            $nodeId = $pollNodeObj->node_id;
            $key    = array_search($nodeId, $poll_nodes);

            if($key !== false)
            {
                if ((int)$poll_nodes[$key+1] != $pollNodeObj->time) DB_ORM::model('Webinar_PollNode')->update($poll_nodes[$key], $webinarId, $poll_nodes[$key+1], $pollNodeObj->id);
                unset($poll_nodes[$key]);
                unset($poll_nodes[$key+1]);
            }
            else DB_ORM::model('Webinar_PollNode')->deleteNode($nodeId);
        }
        print_r($poll_nodes);
        $updatePoLLNode(array_values($poll_nodes), $webinarId);
        // ----- end update poll node ----- //
    }

    /**
     * Delete webinar with maps
     *
     * @param integer $webinarId - webinar ID
     */
    public function deleteWebinar($webinarId) {
        $webinar = DB_ORM::model('webinar', array((int)$webinarId));

        DB_ORM::model('webinar_map')->removeMaps($webinarId);
        DB_ORM::model('webinar_user')->removeUsers($webinarId);
        DB_ORM::model('webinar_group')->removeAllGroups($webinarId);

        if($webinar != null && $webinar->forum_id > 0) {
            DB_ORM::model('dforum')->deleteForum($webinar->forum_id);
        }

        DB_SQL::delete('default')
                ->from($this->table())
                ->where('id', '=', $webinarId)
                ->execute();
    }

    /**
     * Change webinar step
     *
     * @param integer $webinarId - webinar ID
     * @param integer $step - number of step
     */
    public function changeWebinarStep($webinarId, $step) {
        DB_ORM::update('webinar')
                ->set('current_step', $step)
                ->where('id', '=', $webinarId)
                ->execute();
    }

    /**
     * Return all webinars for user
     *
     * @param integer $userId - user ID
     * @return array - array of users
     */
    public function getWebinarsForUser($userId) {
        $records = DB_SQL::select('default')
                           ->from($this->table())
                           ->join('left', 'webinar_users')
                           ->on('webinar_users.webinar_id', '=', 'webinars.id')
                           ->where('webinar_users.user_id', '=', $userId)
                           ->column('webinars.id')
                           ->query();

        $result = null;
        if($records->is_loaded()) {
            foreach($records as $record) {
                $result[] = DB_ORM::model('webinar', array((int)$record['id']));
            }
        }

        return $result;
    }

    /**
     * Reset webinar
     *
     * @param integer $webinarId - webinar ID
     */
    public function resetWebinar($webinarId) {
        $webinar = DB_ORM::model('webinar', array((int)$webinarId));
        if($webinar != null && $webinar->steps != null && count($webinar->steps) > 0) {
            $min = $webinar->steps[0]->id;
            foreach($webinar->steps as $webinarStep) {
                if($webinarStep->id < $min) {
                    $min = $webinarStep->id;
                }
            }

            $this->changeWebinarStep($webinarId, $min);

            DB_ORM::model('user_session')->deleteWebinarSessions($webinarId);
        }
    }

    public function getAllowedWebinars($userId) {

        $builder = DB_SQL::select('default', array(DB_SQL::expr('web.id')))
            ->from('webinars', 'web')
            ->join('LEFT', 'webinar_users', 'webu')
            ->on('webu.webinar_id', '=', 'web.id')
            ->where('author_id', '=', $userId, 'AND')
            ->order_by('web.id', 'DESC');

        $result = $builder->query();

        $res = array();

        if ($result->is_loaded()) {
            foreach ($result as $record => $val) {
                $res[] =  $val['id'];
            }
        }
        return $res;
    }

    public function generateJSON($scenarioId)
    {
        $json   = array();
        $steps  = DB_ORM::select('Webinar_Step')->where('webinar_id', '=', $scenarioId)->query()->as_array();

        foreach($steps as $stepObj)
        {
            $stepId                 = $stepObj->id;
            $json['steps'][$stepId] = $stepObj->name;
            $elements               = DB_ORM::select('Webinar_Map')->where('step', '=', $stepId)->query()->as_array();

            foreach($elements as $elementObj)
            {
                $id     = $elementObj->reference_id;
                $type   = $elementObj->which;

                if ($type == 'labyrinth') $name = DB_ORM::model('Map', array($id))->name;
                else $name = DB_ORM::model('Map_Node_Section', array($id))->name;

                $json['elements'][$stepId][$elementObj->id]['id']   = $id;
                $json['elements'][$stepId][$elementObj->id]['type'] = $type;
                $json['elements'][$stepId][$elementObj->id]['name'] = $name;
            }
        }
        return json_encode($json);
    }
}