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
 * Class OAuth_Authorize_Github - OAuth authorize
 */
class OAuth_Authorize_Github extends OAuth_Authorize {
    protected $name = 'github';

    public function login($providerId, $info) {
        if($providerId <= 0 || $info == null) return;

        if($object = json_decode($info, true)) {
            $oauthUserId = null;
            if(isset($object['id'])) {
                $oauthUserId = $object['id'];
            }

            if($oauthUserId == null) return;

            $oauthUsername = null;
            if(isset($object['name']) && !empty($object['name'])) {
                $oauthUsername = $object['name'];
            } else if(isset($object['login']) && !empty($object['login'])) {
                $oauthUsername = $object['login'];
            } else {
                $oauthUsername = 'OAuth user ' . $oauthUserId;
            }

            $user = DB_ORM::model('user')->getUserByOAuth($providerId, $oauthUserId);
            if($user == null) {
                $newUserId = DB_ORM::model('user')->createOAuthUser($providerId, $oauthUserId, $oauthUsername);
                $user = DB_ORM::model('user', array((int)$newUserId));
            }

            Auth::instance()->login($user->username, $user->password);
        }
    }
}