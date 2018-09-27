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

class user_listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var array */
	protected $errors;

	/** @var \elsensee\postsperpage\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var array */
	protected $settings;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/**
	 * user_listener constructor
	 *
	 * @param \phpbb\config\config			$config		Configuration object
	 * @param \elsensee\postsperpage\helper	$helper		Helper object
	 * @param \phpbb\language\language		$language	Language object
	 * @param \phpbb\template\template		$template	Template object
	 * @param \phpbb\user					$user		User object
	 * @param array							$settings	Settings with key, title, explain language key, minimum and maximum config variable key
	 */
	public function __construct(\phpbb\config\config $config, \elsensee\postsperpage\helper $helper, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\user $user, array $settings)
	{
		$this->config = $config;
		$this->errors = array();
		$this->helper = $helper;
		$this->language = $language;
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
			// ucp_prefs
			'core.ucp_prefs_modify_common'			=> 'modify_ucp_pref_before_load',
			'core.ucp_prefs_view_data'				=> 'add_config_to_ucp',
			'core.ucp_prefs_view_update_data'		=> 'update_config_in_ucp',
		);
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
		if (!$this->helper->validate_event_call($page))
		{
			return;
		}

		$this->language->add_lang('acp/board');
		$this->language->add_lang('common', 'elsensee/postsperpage');

		$data = array();
		$error = $this->helper->validate_request_vars($data, $this->user->data, $page, $event['submit']);

		if ($event['submit'] && count($error))
		{
			// Somehow I am not able to pass the errors back to the event.. weird..
			$event['submit'] = false;
			$this->errors = $error;

			// Retrieve the errors that would have occurred if we wouldn't exist :/
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
			$this->errors = array_merge($error, $this->errors);

			if (!check_form_key('ucp_prefs_view'))
			{
				$this->errors[] = 'FORM_INVALID';
			}

			// Now replace them with their localised form
			$this->errors = array_map(array($this->language, 'lang'), $this->errors);
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
				'TITLE'		=> $this->language->lang($setting['title']),
				'EXPLAIN'	=> $this->language->lang($setting['explain'], $this->config[$setting['normal_config']]),
				'MIN'		=> $setting['min'],
				'MAX'		=> max($this->config[$setting['max']], $this->config[$setting['normal_config']]),
				'MAX_LENGTH' => strlen(max($this->config[$setting['max']], $this->config[$setting['normal_config']])),
			));
		}

		$event['data'] = array_merge($event['data'], $data);
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
		// No validate_event_call().. Already validated through $this->error
		if ($event['mode'] != 'view' || !count($this->errors))
		{
			return;
		}

		$this->template->assign_var('ERROR', implode('<br />', $this->errors));
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
		// No validate_event_call() because already validated through $data and I don't do much here
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
}
