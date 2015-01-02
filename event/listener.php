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

	/** @var bool */
	protected $config_changed;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var array */
	protected $error;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request */
	protected $request;

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
	* @return \elsensee\postsperpage\event\listener
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->config = $config;
		$this->config_changed = false;
		$this->db = $db;
		$this->error = array();
		$this->helper = $helper;
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
		$this->validate_config();

		if (!$this->config['ppp_maximum_ppp'] && !$this->config['ppp_maximum_tpp'])
		{
			return;
		}

		$this->user->add_lang_ext('elsensee/postsperpage', 'common');

		$data = $event['data'];
		// If I already did this I don't have to do it again
		if (!isset($data['posts_pp']) && !isset($data['topics_pp']))
		{
			$this->validate_request_vars($data, $event['user_row'], false);
		}

		$event['user_prefs_data'] = array_merge($event['users_prefs_data'], array(
			'POSTS_PP'			=> (isset($data['posts_pp'])) ? $data['posts_pp'] : 0,
			'POSTS_PP_CONFIG'	=> $this->config['posts_per_page'],
			'POSTS_PP_MAX'		=> $this->config['ppp_maximum_ppp'],
			'TOPICS_PP'			=> (isset($data['topics_pp'])) ? $data['topics_pp'] : 0,
			'TOPICS_PP_CONFIG'	=> $this->config['topics_per_page'],
			'TOPICS_PP_MAX'		=> $this->config['ppp_maximum_tpp'],
		));
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
		$this->validate_config();

		if (!$this->config['ppp_maximum_ppp'] && !$this->config['ppp_maximum_tpp'])
		{
			return;
		}

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

		$this->template->assign_vars(array(
			'POSTS_PP'			=> (isset($data['posts_pp'])) ? $data['posts_pp'] : 0,
			'POSTS_PP_CONFIG'	=> $this->config['posts_per_page'],
			'POSTS_PP_MAX'		=> $this->config['ppp_maximum_ppp'],
			'TOPICS_PP'			=> (isset($data['topics_pp'])) ? $data['topics_pp'] : 0,
			'TOPICS_PP_CONFIG'	=> $this->config['topics_per_page'],
			'TOPICS_PP_MAX'		=> $this->config['ppp_maximum_tpp'],
		));
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
		// Set explain to true to.. explain something with our own words.. literally!
		$vars['vars']['topics_per_page']['explain'] = true;
		$vars['vars']['posts_per_page']['explain'] = true;

		$own_vars = array(
			'ppp_maximum_tpp'	=> array('lang' => 'PPP_TOPICS_PER_PAGE_MAXIMUM',	'validate' => 'int:0:9999',	'type' => 'number:0:9999',	'explain' => true),
			'ppp_maximum_ppp'	=> array('lang' => 'PPP_POSTS_PER_PAGE_MAXIMUM',	'validate' => 'int:0:9999',	'type' => 'number:0:9999',	'explain' => true),
		);
		// Insert our own_vars array right after posts_per_page to let them appear right there.
		$vars['vars'] = array_insert($vars['vars'], array_search('posts_per_page', array_keys($vars['vars'])), $own_vars);

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
		// We may not modify it here - we would get unexpected results. (At least that's what I expect)
		if (defined('ADMIN_START') || stripos($this->helper->get_current_url(), 'ucp.php') !== false || $this->config_changed)
		{
			return;
		}

		// Remember: We overwrite these config values temporarily
		if ($this->config['ppp_maximum_tpp'] && $this->user->data['user_topics_per_page'])
		{
			$this->config['topics_per_page'] = $this->user->data['user_topics_per_page'];
			$this->config_changed = true;
		}
		if ($this->config['ppp_maximum_ppp'] && $this->user->data['user_posts_per_page'])
		{
			$this->config['posts_per_page'] = $this->user->data['user_posts_per_page'];
			$this->config_changed = true;
		}
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
		if (isset($data['posts_pp']))
		{
			$sql_ary['user_posts_per_page'] = $data['posts_pp'];
		}
		if (isset($data['topics_pp']))
		{
			$sql_ary['user_topics_per_page'] = $data['topics_pp'];
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
		$this->validate_config();

		if (!$this->config['ppp_maximum_ppp'] && !$this->config['ppp_maximum_tpp'])
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
		if (isset($data['posts_pp']))
		{
			$sql_ary['user_posts_per_page'] = $data['posts_pp'];
		}
		if (isset($data['topics_pp']))
		{
			$sql_ary['user_topics_per_page'] = $data['topics_pp'];
		}

		$event['sql_ary'] = array_merge($event['sql_ary'], $sql_ary);
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
	* Validates the config object so no wrong values are in there
	*
	* @return null
	* @access protected
	*/
	protected function validate_config()
	{
		// A somehow unexpected case!
		if ($this->config_changed)
		{
			$sql = 'SELECT config_name, config_value
				FROM ' . CONFIG_TABLE . '
				WHERE ' $this->db->sql_in_set('config_name', array('posts_per_page', 'topics_per_page'));
			$result = $this->db->sql_query($sql, 60);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->config[$row['config_name']] = (int) $row['config_value'];
			}
			$this->db->sql_freeresult($result);

			$this->config_changed = false;
		}
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

		if ($this->config['ppp_maximum_ppp'])
		{
			$data['posts_pp'] = $this->request->variable('posts_pp', (int) $user_row['user_posts_per_page']);
			$validate_array['posts_pp'] = array('num', false, 0, max($this->config['ppp_maximum_ppp'], $this->config['posts_per_page']));
		}
		if ($this->config['ppp_maximum_tpp'])
		{
			$data['topics_pp'] = $this->request->variable('topics_pp', (int) $user_row['user_topics_per_page']);
			$validate_array['topics_pp'] = array('num', false, 0, max($this->config['ppp_maximum_tpp'], $this->config['topics_per_page']));
		}

		if ($validate)
		{
			return validate_data($data, $validate_array);
		}
		return array();
	}
}
