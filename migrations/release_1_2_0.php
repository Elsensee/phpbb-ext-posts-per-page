<?php
/**
*
* @package Individual posts per page
* @copyright (c) 2015 Oliver Schramm
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace elsensee\postsperpage\migrations;

class release_1_2_0 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\elsensee\postsperpage\migrations\release_1_0_0_rc2');
	}

	public function update_data()
	{
		return array(
			// We fix a bug by removing the local permission u_topic_ppp
			array('permission.permission_unset', array('ROLE_FORUM_FULL', 'u_topic_ppp')),
			array('permission.remove', array('u_topic_ppp', false)),

			array('permission.add', array('f_topic_ppp', false)),
			array('permission.permission_set', array('ROLE_FORUM_FULL', 'f_topic_ppp')),
		);
	}
}
