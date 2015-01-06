<?php
/**
*
* @package Individual posts per page
* @copyright (c) 2015 Oliver Schramm
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace elsensee\postsperpage\migrations;

class release_1_0_0_rc2 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\elsensee\postsperpage\migrations\release_1_0_0_rc1');
	}

	public function update_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'topics'	=> array(
					'topic_posts_per_page'	=> array('USINT', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'		=> array(
				$this->table_prefix . 'topics'	=> array(
					'topic_posts_per_page',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('ppp_maximum_topic_ppp', 0)), // postsperpage_maximum_topic_postsperpage

			// We add that new feature and we need permissions for that
			array('permission.add', array('u_topic_ppp', true)),
			array('permission.add', array('u_topic_ppp', false)),

			array('permission.permission_set', array('ROLE_USER_FULL', 'u_topic_ppp')),
			array('permission.permission_set', array('ROLE_FORUM_FULL', 'u_topic_ppp')),
		);
	}
}
