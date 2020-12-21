<?php
/**
 *
 * @package Individual posts per page
 * @copyright (c) 2015-2018 Oliver Schramm
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace elsensee\postsperpage;

class helper
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $phpbb_root_path;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var array */
	protected $settings;

	/**
	 * Helper constructor.
	 *
	 * @param \phpbb\auth\auth			$auth				Authentification object
	 * @param \phpbb\config\config		$config				Config object
	 * @param \phpbb\request\request	$request			Request object
	 * @param string					$php_ext			PHP file extension
	 * @param string					$phpbb_root_path	phpBB root path
	 * @param array						$settings			Settings with key, title, explain language key, minimum and maximum config variable key
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\request\request $request, $php_ext, $phpbb_root_path, array $settings)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->request = $request;
		$this->php_ext = $php_ext;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->settings = $settings;
	}

	/**
	 * Validates if the event call is okay because there is indeed work to do
	 * Just checks if at least one config var is not set to 0
	 *
	 * @param string	$page	The page we are on (for example: acp_board, acp_users, ucp_prefs, viewtopic)
	 * @param bool	$check_auth	Optional: True if auth should be checked if it exists...
	 * @param int	$f		Optional: Specify a forum id to check an optional auth parameter
	 * @return bool
	 * @access public
	 */
	public function validate_event_call($page, $check_auth = false, $f = 0)
	{
		foreach ($this->settings as $setting)
		{
			if (in_array($page, $setting['pages']) && $this->config[$setting['max']])
			{
				if (!$check_auth || !isset($setting['auth']) || $this->auth->acl_gets($setting['auth'], $f))
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
	 * @access public
	 */
	public function validate_request_vars(&$data, $data_row, $page, $validate)
	{
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

		if ($validate && count($validate_array))
		{
			if (!function_exists('validate_data'))
			{
				include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
			}

			return validate_data($data, $validate_array);
		}
		return array();
	}
}
