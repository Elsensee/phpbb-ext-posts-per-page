<?php
/**
*
* @package Individual posts per page
* @copyright (c) 2015 Oliver Schramm
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
	/** @var string */
	protected $acp_position;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var array */
	protected $error;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var array */
	protected $old_config;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $phpbb_root_path;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var array */
	protected $settings;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth					$auth			Authentication object
	* @param \phpbb\config\config				$config			Config object
	* @param \phpbb\controller\helper			$helper			Controller helper object
	* @param \phpbb\request\request				$request		Request object
	* @param \phpbb\template\template			$template		Template object
	* @param \phpbb\user						$user			User object
	* @param string								$phpbb_root_path	phpBB root path
	* @param string								$php_ext		PHP file extension
	* @param string								$acp_position	Position of settings in acp_board
	* @param array								$settings		Settings with key, title, explain language key, minimum and maximum config variable key
	* @return \elsensee\postsperpage\event\listener
	* @access public
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $phpbb_root_path, $php_ext, $acp_position, $settings)
	{
		$this->acp_position = $acp_position;
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->error = array();
		$this->helper = $helper;
		$this->old_config = array(); // Learning the difference between an array and an object implementing ArrayAccess...
		$this->php_ext = $php_ext;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->settings = $settings;
		$this->template = $template;
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
			// acp_board
			'core.acp_board_config_edit_add'		=> 'add_configuration',
			// acp_users
			'core.acp_users_prefs_modify_sql'		=> 'update_config_in_acp_users',
			'core.acp_users_prefs_modify_template_data'	=> 'add_config_to_acp_users',
			// permissions
			'core.permissions'						=> 'add_permissions',
			// posting
			'core.posting_modify_template_vars'		=> 'add_config_to_posting',
			'core.posting_modify_submission_errors'	=> 'check_errors_before_posting',
			'core.posting_modify_submit_post_before' => 'modify_config_before_posting',
			'core.submit_post_end'					=> 'handle_config_after_posting', // (functions_posting)
			// ucp_prefs
			'core.ucp_prefs_modify_common'			=> 'modify_ucp_pref_before_load',
			'core.ucp_prefs_view_data'				=> 'add_config_to_ucp',
			'core.ucp_prefs_view_update_data'		=> 'update_config_in_ucp',
			// change config vars with following two events:
			'core.user_setup'						=> 'modify_per_page_config',
			'core.viewforum_modify_topicrow'		=> 'modify_per_page_config_viewforum',
		);
	}

	/**
	* Add configuration items for ppp-extension to ACP users
	*
	* @param object	$event The event object
	* @return null
	* @access public
	*/
	public function add_config_to_acp_users($event)
	{
		$page = 'acp_users';
		if (!$this->validate_event_call($page))
		{
			return;
		}

		$this->user->add_lang('acp/board');
		$this->user->add_lang_ext('elsensee/postsperpage', 'common');

		$data = $event['data'];
		// If I already did this I don't have to do it again
		$all_unset = true;
		foreach ($this->settings as $setting)
		{
			if (isset($data[$setting['key']]))
			{
				$all_unset = false;
				break;
			}
		}
		if ($all_unset)
		{
			$this->validate_request_vars($data, $event['user_row'], $page, false);
		}

		foreach ($data as $key => $value)
		{
			if (!isset($this->settings[$key]) || !in_array($page, $this->settings[$key]['pages']))
			{
				continue;
			}

			$setting = $this->settings[$key];

			$this->template->assign_block_vars('ppp_setting', array(
				'KEY'		=> $key,
				'VALUE'		=> $value,
				'TITLE'		=> $this->user->lang($setting['title']),
				'EXPLAIN'	=> $this->user->lang($setting['explain'], $this->config[$setting['normal_config']]),
				'MIN'		=> $setting['min'],
				'MAX'		=> max($this->config[$setting['max']], $this->config[$setting['normal_config']]),
				'MAX_LENGTH' => strlen(max($this->config[$setting['max']], $this->config[$setting['normal_config']])),
			));
		}
	}

	/**
	* Add configuration item for ppp-extension to posting
	*
	* @param object	$event The event object
	* @return null
	* @access public
	*/
	public function add_config_to_posting($event)
	{
		$page = 'posting';
		$mode = $event['mode'];
		if (!$this->validate_event_call($page, true, $event['forum_id']) || !in_array($mode, array('post', 'edit')))
		{
			return;
		}
		if ($mode == 'edit' && $event['post_id'] != $event['post_data']['topic_first_post_id'])
		{
			// We only allow setting this if we are editing first post or posting a new topic
			return;
		}

		$this->user->add_lang('acp/board');
		$this->user->add_lang_ext('elsensee/postsperpage', 'common');

		$post_data = $event['post_data'];

		$preview = $event['preview'];
		$submit = $event['submit'];
		// The following boolean expression is made with paper and a pen
		// It looks beautiful!
		if ((!$preview && !$submit) || (sizeof($event['error']) && ($preview || $submit)))
		{
			// First is a reference, second is not (third and fourth also not by the way)
			$this->validate_request_vars($post_data, $event['post_data'], $page, false);
		}

		foreach ($post_data as $key => $value)
		{
			if (!isset($this->settings[$key]) || !in_array($page, $this->settings[$key]['pages']) || !$this->auth->acl_gets($this->settings[$key]['auth'], $event['forum_id']))
			{
				continue;
			}
			$setting = $this->settings[$key];

			$this->template->assign_block_vars('ppp_setting', array(
				'KEY'		=> $key,
				'VALUE'		=> $value,
				'TITLE'		=> $this->user->lang($setting['title']),
				'EXPLAIN'	=> $this->user->lang($setting['explain'], $this->config[$setting['normal_config']]),
				'MIN'		=> $setting['min'],
				'MAX'		=> max($this->config[$setting['max']], $this->config[$setting['normal_config']]),
				'MAX_LENGTH' => strlen(max($this->config[$setting['max']], $this->config[$setting['normal_config']])),
			));
		}
	}

	/**
	* Add configuration items for ppp-extension to UCP
	*
	* @param object	$event The event object
	* @return null
	* @access public
	*/
	public function add_config_to_ucp($event)
	{
		$page = 'ucp_prefs';
		if (!$this->validate_event_call($page))
		{
			return;
		}

		$this->user->add_lang('acp/board');
		$this->user->add_lang_ext('elsensee/postsperpage', 'common');

		$data = array();
		$error = $this->validate_request_vars($data, $this->user->data, $page, $event['submit']);

		if ($event['submit'] && sizeof($error))
		{
			// Somehow I am not able to pass the errors back to the event.. weird..
			$event['submit'] = false;
			$this->error = $error;

			// Retrieve the errors that would have occured if we wouldn't exist :/
			$error = validate_data($event['data'], array(
				'topic_sk'	=> array(
					array('string', false, 1, 1),
					array('match', false, '#(a|r|s|t|v)#'),
				),
				'topic_sd'	=> array(
					array('string', false, 1, 1),
					array('match', false, '#(a|d)#'),
				),
				'post_sk'	=> array(
					array('string', false, 1, 1),
					array('match', false, '#(a|s|t)#'),
				),
				'post_sd'	=> array(
					array('string', false, 1, 1),
					array('match', false, '#(a|d)#'),
				),
			));
			$this->error = array_merge($error, $this->error);

			if (!check_form_key('ucp_prefs_view'))
			{
				$this->error[] = 'FORM_INVALID';
			}

			// Now replace them with their localised form
			$this->error = array_map(array($this->user, 'lang'), $this->error);
		}

		foreach ($data as $key => $value)
		{
			$setting = $this->settings[$key];
			if (!in_array($page, $setting['pages']))
			{
				continue;
			}

			$this->template->assign_block_vars('ppp_setting', array(
				'KEY'		=> $key,
				'VALUE'		=> $value,
				'TITLE'		=> $this->user->lang($setting['title']),
				'EXPLAIN'	=> $this->user->lang($setting['explain'], $this->config[$setting['normal_config']]),
				'MIN'		=> $setting['min'],
				'MAX'		=> max($this->config[$setting['max']], $this->config[$setting['normal_config']]),
				'MAX_LENGTH' => strlen(max($this->config[$setting['max']], $this->config[$setting['normal_config']])),
			));
		}

		$event['data'] = array_merge($event['data'], $data);
	}

	/**
	* Add configuration items for ppp-extension to ACP
	*
	* @param object	$event The event object
	* @return null
	* @access public
	*/
	public function add_configuration($event)
	{
		$page = 'acp_board';
		if ($event['mode'] != 'post')
		{
			// Sorry, didn't want to interrupt you
			return;
		}

		$this->user->add_lang_ext('elsensee/postsperpage', 'common');

		$vars = $event['display_vars'];

		$own_vars = array();
		foreach ($this->settings as $setting)
		{
			if (!in_array($page, $setting['pages']))
			{
				continue;
			}

			$min_max = $setting['min_acp'] . ':' . $setting['max_acp'];
			$own_vars[$setting['max']] = array('lang' => $setting['max_lang'],	'validate' => 'int:' . $min_max,	'type' => 'number:' . $min_max,	'explain' => true);
		}

		// Insert our own_vars array right after posts_per_page to let them appear right there.
		$vars['vars'] = $this->array_insert($vars['vars'], array_search($this->acp_position, array_keys($vars['vars'])) + 1, $own_vars);

		// That's it. We're done.
		$event['display_vars'] = $vars;
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
		));
	}

	/**
	* Inserts an array into an array at a specified offset and keeps the keys.
	* (array_splice wouldn't allow keeping the keys)
	* See: http://php.net/manual/en/function.array-splice.php#56794
	*
	* @param array	$input			The input array.
	* @param int	$offset			Specifies the offset at which the array should be inserted at.
	* @param array	$insert_array	The array which should be inserted.
	* @return array
	* @access protected
	*/
	protected function array_insert($input, $offset, $insertion)
	{
		$first_array = array_splice($input, 0, $offset);
		return array_merge($first_array, $insertion, $input);
	}

	/**
	* Check for errors before posting
	*
	* @param object	$event The event object
	* @return null
	* @access public
	*/
	public function check_errors_before_posting($event)
	{
		$page = 'posting';
		$mode = $event['mode'];
		if (!$this->validate_event_call($page, true, $event['forum_id']) || !in_array($mode, array('post', 'edit')))
		{
			return;
		}
		if ($mode == 'edit' && $event['post_id'] != $event['post_data']['topic_first_post_id'])
		{
			// We only allow setting this if we are editing first post or posting a new topic
			return;
		}

		$this->user->add_lang_ext('elsensee/postsperpage', 'common');

		$data = array();
		$error = $this->validate_request_vars($data, $event['post_data'], $page, ($mode == 'edit' || $event['preview'] || $event['submit']));
		if (sizeof($error))
		{
			$event['error'] = array_merge($event['error'], array_map(array($this->user, 'lang'), $error));
		}

		// Merge data with post data so we don't have to do the validation thing again...
		$event['post_data'] = array_merge($event['post_data'], $data);
	}

	/**
	* Handles the config if given to submit_post.. I hate that function
	*
	* @param object	$event The event object
	* @return null
	* @access public
	*/
	public function handle_config_after_posting($event)
	{
		$page = 'posting';
		$mode = $event['mode'];
		$data = $event['data'];
		if (!$this->validate_event_call($page, true, $data['forum_id']) || !in_array($mode, array('post', 'edit')))
		{
			return;
		}
		if ($mode == 'edit' && $data['topic_first_post_id'] != $data['post_id'])
		{
			return;
		}
		$all_unset = true;
		foreach ($this->settings as $setting)
		{
			if (in_array($page, $setting['pages']) && array_search($setting['key'], $data))
			{
				$all_unset = false;
				break;
			}
		}
		if ($all_unset)
		{
			return;
		}

		$sql_ary = array();
		foreach ($this->settings as $setting)
		{
			if (!isset($data[$setting['key']]) || !in_array($page, $setting['pages']))
			{
				continue;
			}

			$sql_ary[$setting['data_row_config']] = $data[$setting['key']];
		}
		if (empty($sql_ary))
		{
			return;
		}

		$sql = 'UPDATE ' . TOPICS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE topic_id = ' . (int) $data['topic_id'];
		$this->db->sql_query($sql);
	}

	/**
	* Modifies the data object sent to submit_post before.. doing that..
	* (Well it just copies our data from post_data to data)
	*
	* @param object	$event The event object
	* @return null
	* @access public
	*/
	public function modify_config_before_posting($event)
	{
		$page = 'posting';
		$mode = $event['mode'];
		if (!$this->validate_event_call($page, true, $event['forum_id']) || !in_array($mode, array('post', 'edit')))
		{
			return;
		}
		if ($mode == 'edit' && $event['post_id'] != $event['post_data']['topic_first_post_id'])
		{
			// We only allow setting this if we are editing first post or posting a new topic
			return;
		}
		$post_data = $event['post_data'];
		$all_unset = true;
		foreach ($this->settings as $setting)
		{
			if (in_array($page, $setting['pages']) && array_search($setting['key'], $post_data))
			{
				$all_unset = false;
				break;
			}
		}
		if ($all_unset)
		{
			return;
		}

		$data = array();
		foreach ($post_data as $key => $value)
		{
			if (isset($this->settings[$key]) && in_array($page, $this->settings[$key]['pages']))
			{
				$data[$key] = $value;
			}
		}
		$event['data'] = array_merge($event['data'], $data);
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
		$current_url = $this->helper->get_current_url();
		// We may not modify it here - we would get unexpected results. (At least that's what I expect)
		if (defined('ADMIN_START') || stripos($current_url, 'ucp.php') !== false || stripos($current_url, 'posting.php') !== false)
		{
			return;
		}

		$changed = false;
		if (stripos($current_url, 'viewtopic.php') !== false)
		{
			// We'll have to handle viewtopic differently - much much differently
			$setting = $this->settings['topic_posts_pp']; // hardcoded because.. really.. I couldn't care less.
			// Check if it's even allowed...
			if ($this->config[$setting['max']])
			{
				$post_id = $this->request->variable('p', 0);
				$topic_id = $this->request->variable('t', 0);

				if ($post_id)
				{
					// I can't build this SQL statement. I'm too dumb for SQL
					// That's why I hire professionals.. or I just ask phpBB to do the job for me
					// Although I think it's something like INNER JOIN or similar...
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
				if ((!isset($ppp_setting) || $ppp_setting === false) && $topic_id)
				{
					$sql = 'SELECT ' . $setting['data_row_config'] . '
						FROM ' . TOPICS_TABLE . "
						WHERE topic_id = $topic_id";
					$result = $this->db->sql_query($sql);
					$ppp_setting = $this->db->sql_fetchfield($setting['data_row_config']);
					$this->db->sql_freeresult($result);
				}

				if (isset($ppp_setting) && $ppp_setting)
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
		if (!$this->validate_event_call($page))
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
				// We hardcode posts_pp because I'm too tired.. (and I don't give a f***)
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

	/**
	* Modifies template data if errors occured (in UCP)
	*
	* @param object	$event The event object
	* @return null
	* @access public
	*/
	public function modify_ucp_pref_before_load($event)
	{
		if ($event['mode'] != 'view' || !sizeof($this->error))
		{
			return;
		}

		$this->template->assign_var('ERROR', implode('<br />', $this->error));
	}

	/**
	* Updates users config when in UCP
	*
	* @param object	$event The event object
	* @return null
	* @access public
	*/
	public function update_config_in_ucp($event)
	{
		$page = 'ucp_prefs';
		$data = $event['data'];

		$sql_ary = array();
		foreach ($this->settings as $setting)
		{
			if (in_array($page, $setting['pages']) && isset($data[$setting['key']]))
			{
				$sql_ary[$setting['data_row_config']] = $data[$setting['key']];
			}
		}
		$event['sql_ary'] = array_merge($event['sql_ary'], $sql_ary);
	}

	/**
	* Updates users config when in ACP users
	*
	* @param object	$event The event object
	* @return null
	* @access public
	*/
	public function update_config_in_acp_users($event)
	{
		$page = 'acp_users';
		if (!$this->validate_event_call($page))
		{
			return;
		}

		$this->user->add_lang_ext('elsensee/postsperpage', 'common');

		$data = array();
		$error = $this->validate_request_vars($data, $event['user_row'], $page, true);
		if (sizeof($error))
		{
			$event['data'] = array_merge($event['data'], $data); // Telling myself that I already did this...
			$event['error'] = array_merge($event['error'], $error);
			return;
		}

		$sql_ary = array();
		foreach ($this->settings as $setting)
		{
			if (in_array($page, $setting['pages']) && isset($data[$setting['key']]))
			{
				$sql_ary[$setting['data_row_config']] = $data[$setting['key']];
			}
		}

		$event['sql_ary'] = array_merge($event['sql_ary'], $sql_ary);
	}

	/**
	* Validates if the event call is okay because there is indeed work to do
	* Just checks if at least one config var is not set to 0
	*
	* @param string	$page	The page we are on (for example: acp_board, acp_users, ucp_prefs, viewtopic)
	* @param bool	$check_auth	Optional: True if auth should be checked if it exists...
	* @param int	$f		Optional: Specify a forum id to check an optional auth parameter
	* @return bool
	* @access protected
	*/
	protected function validate_event_call($page, $check_auth = false, $f = 0)
	{
		foreach ($this->settings as $setting)
		{
			if (in_array($page, $setting['pages']) && $this->config[$setting['max']])
			{
				if (!$check_auth || !isset($setting['auth']) || (isset($setting['auth']) && $this->auth->acl_gets($setting['auth'], $f)))
				{
					return true;
				}
			}
		}
		return false;
	}

	/**
	* Validates the variables given by the page per request
	*
	* @param array	&$data		Array with data which will be given by reference
	* @param array	$data_row	Array with user/topic data
	* @param string	$page		The page we are on (for example: acp_board, acp_users, ucp_prefs, viewtopic)
	* @param bool	$validate	true if validate, false if not (and return an empty array)
	* @return array				Array with errors occured at validation
	* @access protected
	*/
	protected function validate_request_vars(&$data, $data_row, $page, $validate)
	{
		if (!function_exists('validate_data'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}
		$validate_array = array();

		foreach ($this->settings as $setting)
		{
			if (in_array($page, $setting['pages']) && $this->config[$setting['max']])
			{
				$default = (isset($data_row[$setting['data_row_config']])) ? $data_row[$setting['data_row_config']] : 0;
				$data[$setting['key']] = $this->request->variable($setting['key'], (int) $default);
				$validate_array[$setting['key']] = array('num', false, $setting['min'], max($this->config[$setting['max']], $this->config[$setting['normal_config']]));
			}
		}

		if ($validate && sizeof($validate_array))
		{
			return validate_data($data, $validate_array);
		}
		return array();
	}
}
