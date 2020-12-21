<?php
/**
 *
 * @package Individual posts per page
 * @copyright (c) 2015-2020 Oliver Schramm
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace elsensee\postsperpage\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class admin_listener implements EventSubscriberInterface
{
	/** @var string */
	protected $acp_position;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \elsensee\postsperpage\helper */
	protected $helper;

	/** @var array */
	protected $settings;

	/** @var \phpbb\template\template */
	protected $template;

	/**
	 * admin_listener constructor
	 *
	 * @param \phpbb\config\config			$config			Configuration object
	 * @param \phpbb\language\language		$language		Language object
	 * @param \elsensee\postsperpage\helper	$helper			Helper object
	 * @param \phpbb\template\template		$template		Template object
	 * @param array							$settings		Settings with key, title, explain language key, minimum and maximum config variable key
	 * @param string						$acp_position	Position of settings in acp_board
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\language\language $language, \elsensee\postsperpage\helper $helper, \phpbb\template\template $template, array $settings, $acp_position)
	{
		$this->config = $config;
		$this->language = $language;
		$this->helper = $helper;
		$this->template = $template;
		$this->settings = $settings;
		$this->acp_position = $acp_position;
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
			'core.acp_board_config_edit_add'			=> 'add_configuration',
			// acp_users
			'core.acp_users_prefs_modify_sql'			=> 'update_config_in_acp_users',
			'core.acp_users_prefs_modify_template_data'	=> 'add_config_to_acp_users',
		);
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
		// Don't need a validate_event_call() because it would check if max is set but we want to set max here
		if ($event['mode'] != 'post')
		{
			return;
		}

		$this->language->add_lang('common', 'elsensee/postsperpage');

		$vars = $event['display_vars'];

		$own_vars = array();
		foreach ($this->settings as $setting)
		{
			// For every own setting there is an array, which tells us, on which page it is active
			if (!in_array($page, $setting['pages']))
			{
				continue;
			}

			$min_max = '0:9999';
			$own_vars[$setting['max']] = array('lang' => $setting['max_lang'],	'validate' => 'int:' . $min_max,	'type' => 'number:' . $min_max,	'explain' => true);
		}

		// Insert our own_vars array right after posts_per_page to let them appear right there.
		$vars['vars'] = phpbb_insert_config_array($vars['vars'], $own_vars, array('after' => $this->acp_position));

		$event['display_vars'] = $vars;
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
		if (!$this->helper->validate_event_call($page))
		{
			return;
		}

		$this->language->add_lang('acp/board');
		$this->language->add_lang('common', 'elsensee/postsperpage');

		$data = $event['data'];
		// Don't do it twice
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
			$this->helper->validate_request_vars($data, $event['user_row'], $page, false);
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
				'TITLE'		=> $this->language->lang($setting['title']),
				'EXPLAIN'	=> $this->language->lang($setting['explain'], $this->config[$setting['normal_config']]),
				'MIN'		=> $setting['min'],
				'MAX'		=> max($this->config[$setting['max']], $this->config[$setting['normal_config']]),
				'MAX_LENGTH' => strlen(max($this->config[$setting['max']], $this->config[$setting['normal_config']])),
			));
		}
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
		if (!$this->helper->validate_event_call($page))
		{
			return;
		}

		$this->language->add_lang('common', 'elsensee/postsperpage');

		$data = array();
		$error = $this->helper->validate_request_vars($data, $event['user_row'], $page, true);
		$event['data'] = array_merge($event['data'], $data); // Telling myself that I already did this...
		if (count($error))
		{
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
}
