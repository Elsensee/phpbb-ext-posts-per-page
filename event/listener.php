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
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var array */
	protected $error;

	/** @var \phpbb\controller\helper */
	protected $helper;

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
	* @param \phpbb\config\config					$config		Config object
	* @param \phpbb\controller\helper				$helper		Controller helper object
	* @param \phpbb\db\driver\driver_interface		$db			Database driver object
	* @param \phpbb\request\request					$request	Request object
	* @param \phpbb\template\template				$template	Template object
	* @param \phpbb\user							$user		User object
	* @param array									$settings	Settings with key, title, explain language key, minimum and maximum config variable key
	* @return \elsensee\postsperpage\event\listener
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $settings)
	{
		$this->config = $config;
		$this->db = $db;
		$this->error = array();
		$this->helper = $helper;
		$this->settings = $settings;
		$this->request = $request;
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
			'core.acp_board_config_edit_add'		=> 'add_configuration',
			'core.acp_users_prefs_modify_sql'		=> 'update_config_in_acp_users',
			'core.acp_users_prefs_modify_template_data'	=> 'add_config_to_acp_users',
			'core.ucp_prefs_modify_common'			=> 'modify_ucp_pref_before_load',
			'core.ucp_prefs_view_data'				=> 'add_config_to_ucp',
			'core.ucp_prefs_view_update_data'		=> 'update_config_in_ucp',
			'core.user_setup'						=> 'modify_per_page_config',
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
		if (!$this->validate_event_call())
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
			if (is_array($setting) && isset($data[$setting['key']]))
			{
				$all_unset = false;
				break;
			}
		}
		if ($all_unset)
		{
			$this->validate_request_vars($data, $event['user_row'], false);
		}

		foreach ($data as $key => $value)
		{
			if (!isset($this->settings[$key]) || !is_array($this->settings[$key]))
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
		if (!$this->validate_event_call())
		{
			return;
		}

		$this->user->add_lang('acp/board');
		$this->user->add_lang_ext('elsensee/postsperpage', 'common');

		$data = array();
		$error = $this->validate_request_vars($data, $this->user->data, $event['submit']);

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
			if (!is_array($this->settings[$key]))
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
			if (!is_array($setting))
			{
				continue;
			}

			$own_vars[$setting['max']] = array('lang' => $setting['max_lang'],	'validate' => 'int:' . $setting['min_acp'] . ':' . $setting['max_acp'],	'type' => 'number:' . $setting['min_acp'] . ':' . $setting['max_acp'],	'explain' => true);
		}

		// Insert our own_vars array right after posts_per_page to let them appear right there.
		$vars['vars'] = $this->array_insert($vars['vars'], array_search($this->settings['acp_position'], array_keys($vars['vars'])) + 1, $own_vars);

		// That's it. We're done.
		$event['display_vars'] = $vars;
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
		if (defined('ADMIN_START') || stripos($current_url, 'ucp.php') !== false)
		{
			return;
		}

		// Remember: We overwrite these config values temporarily
		foreach ($this->settings as $setting)
		{
			if (!is_array($setting))
			{
				continue;
			}

			if ($this->config[$setting['max']] && $this->user->data[$setting['user_config']])
			{
				$this->config[$setting['normal_config']] = $this->user->data[$setting['user_config']];
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
		$data = $event['data'];

		$sql_ary = array();
		foreach ($this->settings as $setting)
		{
			if (is_array($setting) && isset($data[$setting['key']]))
			{
				$sql_ary[$setting['user_config']] = $data[$setting['key']];
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
		if (!$this->validate_event_call())
		{
			return;
		}

		$data = array();
		$error = $this->validate_request_vars($data, $event['user_row'], true);
		if (sizeof($error))
		{
			$event['data'] = array_merge($event['data'], $data); // Telling myself that I already did this...
			$event['error'] = array_merge($event['error'], $error);
			return;
		}

		$sql_ary = array();
		foreach ($this->settings as $setting)
		{
			if (is_array($setting) && isset($data[$setting['key']]))
			{
				$sql_ary[$setting['user_config']] = $data[$setting['key']];
			}
		}

		$event['sql_ary'] = array_merge($event['sql_ary'], $sql_ary);
	}

	/**
	* Validates if the event call is okay because there is indeed work to do
	* Just checks if at least one config var is not set to 0
	*
	* @return bool
	* @access protected
	*/
	protected function validate_event_call()
	{
		foreach ($this->settings as $setting)
		{
			if (is_array($setting) && $this->config[$setting['max']])
			{
				return true;
			}
		}
		return false;
	}

	/**
	* Validates the variables given by the page per request
	*
	* @param array	&$data		Array with data which will be given by reference
	* @param array	$user_row	Array with user data
	* @param bool	$validate	true if validate, false if not (and return an empty array)
	* @return array				Array with errors occured at validation
	* @access protected
	*/
	protected function validate_request_vars(&$data, $user_row, $validate)
	{
		$validate_array = array();

		foreach ($this->settings as $setting)
		{
			if (is_array($setting) && $this->config[$setting['max']])
			{
				$data[$setting['key']] = $this->request->variable($setting['key'], (int) $user_row[$setting['user_config']]);
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
