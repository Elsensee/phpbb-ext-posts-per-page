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

class posting_listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \elsensee\postsperpage\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var array */
	protected $settings;

	/** @var \phpbb\template\template */
	protected $template;

	/**
	 * posting_listener constructor.
	 *
	 * @param \phpbb\auth\auth					$auth		Authentication object
	 * @param \phpbb\config\config				$config		Configuration object
	 * @param \phpbb\db\driver\driver_interface	$db			phpBB DBAL object
	 * @param \elsensee\postsperpage\helper		$helper		Helper object
	 * @param \phpbb\language\language			$language	Language object
	 * @param \phpbb\template\template			$template	Template object
	 * @param array								$settings	Settings with key, title, explain language key, minimum and maximum config variable key
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \elsensee\postsperpage\helper $helper, \phpbb\language\language $language, \phpbb\template\template $template, array $settings)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->language = $language;
		$this->template = $template;
		$this->settings = $settings;
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
			// posting
			'core.posting_modify_template_vars'		=> 'add_config_to_posting',
			'core.posting_modify_submission_errors'	=> 'check_errors_before_posting',
			'core.posting_modify_submit_post_before' => 'modify_config_before_posting',
			'core.submit_post_end'					=> 'handle_config_after_posting', // (functions_posting)
		);
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
		if (!$this->helper->validate_event_call($page, true, $event['forum_id']) || !in_array($mode, array('post', 'edit')))
		{
			return;
		}
		if ($mode == 'edit' && $event['post_id'] != $event['post_data']['topic_first_post_id'])
		{
			// We only allow setting this if we are editing first post or posting a new topic
			return;
		}

		$this->language->add_lang('acp/board');
		$this->language->add_lang('common', 'elsensee/postsperpage');

		$post_data = $event['post_data'];

		// The following boolean expression was first made with paper and a pen..
		// Then bantu told me something about boolean algebra and that made it look like this. Thank you! :)
		// And then I saw that I missed an variable and the condition was wrong and totally nonsense so it now looks like this:
		if (!$event['preview'] && !$event['refresh'] && !$event['submit'])
		{
			// First is a reference, second is not (third and fourth also not by the way)
			$this->helper->validate_request_vars($post_data, $event['post_data'], $page, false);
		}

		foreach ($post_data as $key => $value)
		{
			if (!isset($this->settings[$key]) || !in_array($page, $this->settings[$key]['pages']))
			{
				continue;
			}
			$setting = $this->settings[$key];
			if (isset($setting['auth']) && !$this->auth->acl_gets($setting['auth'], $event['forum_id']))
			{
				// If auth is set but we are not allowed just.. don't.. okay?
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
		if (!$this->helper->validate_event_call($page, true, $event['forum_id']) || !in_array($mode, array('post', 'edit')))
		{
			return;
		}
		if ($mode == 'edit' && $event['post_id'] != $event['post_data']['topic_first_post_id'])
		{
			// We only allow setting this if we are editing first post or posting a new topic
			return;
		}

		$this->language->add_lang('common', 'elsensee/postsperpage');

		$data = array();
		$error = $this->helper->validate_request_vars($data, $event['post_data'], $page, true);
		if (count($error))
		{
			$event['error'] = array_merge($event['error'], array_map(array($this->language, 'lang'), $error));
		}

		// Merge data with post data so we don't have to do the validation thing again...
		$event['post_data'] = array_merge($event['post_data'], $data);
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
		if (!$this->helper->validate_event_call($page, true, $event['forum_id']) || !in_array($mode, array('post', 'edit')))
		{
			return;
		}
		if ($mode == 'edit' && $event['post_id'] != $event['post_data']['topic_first_post_id'])
		{
			// We only allow setting this if we are editing first post or posting a new topic
			return;
		}

		$post_data = $event['post_data'];
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
		if (!$this->helper->validate_event_call($page, true, $data['forum_id']) || !in_array($mode, array('post', 'edit')))
		{
			return;
		}
		if ($mode == 'edit' && $data['topic_first_post_id'] != $data['post_id'])
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
}
