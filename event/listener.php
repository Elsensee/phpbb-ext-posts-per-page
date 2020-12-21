<?php
/**
*
* @package Individual posts per page
* @copyright (c) 2015-2018 Oliver Schramm
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace elsensee\postsperpage\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var array */
	protected $old_config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \elsensee\postsperpage\helper */
	protected $helper;

	/** @var string */
	protected $php_ext;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var array */
	protected $settings;

	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config				$config				Configuration object
	 * @param \phpbb\db\driver\driver_interface	$db					phpBB DBAL object
	 * @param \elsensee\postsperpage\helper		$helper				Helper object
	 * @param \phpbb\request\request			$request			Request object
	 * @param \phpbb\user						$user				User object
	 * @param string							$php_ext			The PHP extension
	 * @param array								$settings			Settings with key, title, explain language key, minimum and maximum config variable key
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \elsensee\postsperpage\helper $helper, \phpbb\request\request $request, \phpbb\user $user, $php_ext, array $settings)
	{
		$this->config = $config;
		$this->old_config = array(); // Learning the difference between an array and an object implementing ArrayAccess...
		$this->db = $db;
		$this->helper = $helper;
		$this->php_ext = $php_ext;
		$this->request = $request;
		$this->settings = $settings;
		$this->user = $user;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			// permissions
			'core.permissions'						=> 'add_permissions',
			// change config vars with following two events:
			'core.user_setup'						=> 'modify_per_page_config',
			'core.viewforum_modify_topicrow'		=> 'modify_per_page_config_viewforum',
		);
	}

	/**
	* Add permissions for setting topic based posts per page settings.
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function add_permissions($event)
	{
		$event['permissions'] = array_merge($event['permissions'], array(
			'u_topic_ppp'	=> array('lang' => 'ACL_U_TOPIC_PPP', 'cat' => 'post'),
			'f_topic_ppp'	=> array('lang' => 'ACL_U_TOPIC_PPP', 'cat' => 'post'),
		));
	}

	/**
	* Modifies the config object to modify the per page behaviour per user
	*
	* @param object	$event The event object
	* @return null
	* @access public
	*/
	public function modify_per_page_config($event)
	{
		$current_page = $this->user->page['page_name'];
		if (defined('ADMIN_START') || stripos($current_page, 'ucp.' . $this->php_ext) !== false || stripos($current_page, 'posting.' . $this->php_ext) !== false)
		{
			// We may not modify it here - we would get unexpected results.
			return;
		}

		$changed = false;
		if (stripos($current_page, 'viewtopic.' . $this->php_ext) !== false)
		{
			// We'll have to handle viewtopic differently - much much differently
			$setting = $this->settings['topic_posts_pp'];
			// Check if it's even allowed...
			if ($this->config[$setting['max']])
			{
				$post_id = (int) $this->request->variable('p', 0);
				$topic_id = (int) $this->request->variable('t', 0);
				if ($post_id)
				{
					$sql_array = array(
						'SELECT'	=> 't.' . $setting['data_row_config'],
						'FROM'		=> array(
							POSTS_TABLE		=> 'p',
							TOPICS_TABLE	=> 't',
						),
						'WHERE'		=> "p.post_id = $post_id AND t.topic_id = p.topic_id",
					);
					$sql = $this->db->sql_build_query('SELECT', $sql_array);
					$result = $this->db->sql_query($sql);
					$ppp_setting = $this->db->sql_fetchfield($setting['data_row_config']);
					$this->db->sql_freeresult($result);
				}
				if (empty($ppp_setting) && $topic_id) // Just a fallback
				{
					$sql = 'SELECT ' . $setting['data_row_config'] . '
						FROM ' . TOPICS_TABLE . "
						WHERE topic_id = $topic_id";
					$result = $this->db->sql_query($sql);
					$ppp_setting = $this->db->sql_fetchfield($setting['data_row_config']);
					$this->db->sql_freeresult($result);
				}

				if (!empty($ppp_setting))
				{
					$this->old_config[$setting['normal_config']] = $this->config[$setting['normal_config']];
					$this->config[$setting['normal_config']] = $ppp_setting;
					$changed = true;
				}
			}
		}

		if (!$changed)
		{
			// Remember: We overwrite these config values temporarily
			foreach ($this->settings as $setting)
			{
				if ($this->config[$setting['max']] && isset($this->user->data[$setting['data_row_config']]) && $this->user->data[$setting['data_row_config']])
				{
					$this->old_config[$setting['normal_config']] = $this->config[$setting['normal_config']];
					$this->config[$setting['normal_config']] = $this->user->data[$setting['data_row_config']];
				}
			}
		}
	}

	/**
	* Modifies the config object to modify the per page behaviour per topic on viewforum
	* The event was not intended for this use I think but it fits perfectly because it's
	* just before the pagination generation.
	*
	* @param object	$event The event object
	* @return null
	* @access public
	*/
	public function modify_per_page_config_viewforum($event)
	{
		$page = 'viewforum';
		if (!$this->helper->validate_event_call($page))
		{
			return;
		}

		$row = $event['row'];
		foreach ($this->settings as $setting)
		{
			if (isset($row[$setting['data_row_config']]) && in_array($page, $setting['pages']))
			{
				if (!isset($this->old_config[$setting['normal_config']]))
				{
					$this->old_config[$setting['normal_config']] = $this->config[$setting['normal_config']];
				}

				if ($this->config[$setting['max']] && $row[$setting['data_row_config']])
				{
					$this->config[$setting['normal_config']] = $row[$setting['data_row_config']];
				}
				// We hardcode posts_pp because YAML is limited and I have to put them in classes to make it properly (that's overkill)
				else if ($this->config[$this->settings['posts_pp']['max']] && $this->user->data[$this->settings['posts_pp']['data_row_config']])
				{
					$this->config[$setting['normal_config']] = $this->user->data[$this->settings['posts_pp']['data_row_config']];
				}
				else
				{
					$this->config[$setting['normal_config']] = $this->old_config[$setting['normal_config']];
				}
			}
		}
	}
}
