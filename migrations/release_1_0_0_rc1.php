<?php
/**
*
* @package Individual posts per page
* @copyright (c) 2015 Oliver Schramm
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace elsensee\postsperpage\migrations;

class release_1_0_0_rc1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		// This should be implemented because I had to rename the class (from uppercase to lowercase)
		// And checking for config should be enough because schema won't exist if config doesn't exist.
		return isset($this->config['ppp_maximum_ppp']);
	}

	public function update_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'users'	=> array(
					'user_posts_per_page'	=> array('USINT', 0, 'after' => 'user_post_sortby_dir'),
					'user_topics_per_page'	=> array('USINT', 0, 'after' => 'user_topics_sortby_dir'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'		=> array(
				$this->table_prefix . 'users'	=> array(
					'user_posts_per_page',
					'user_topics_per_page',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('ppp_maximum_ppp', 0)),
			array('config.add', array('ppp_maximum_tpp', 0)),
		);
	}
}
