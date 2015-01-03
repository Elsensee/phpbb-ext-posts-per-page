<?php
/**
*
* @package Individual posts per page
* @copyright (c) 2015 Oliver Schramm
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	// Displayed in ACP -> Post settings
	'PPP_POSTS_PER_PAGE_MAXIMUM'			=> 'Maximum number of posts per page',
	'PPP_POSTS_PER_PAGE_MAXIMUM_EXPLAIN'	=> 'Sets the maximum number of posts per page which users are allowed to set. The minimum allowed value will be the default number of posts per page set in the board settings. If this value is smaller, it will be ignored. The only exception is 0, in which case the user is disallowed to specify the number of posts per page.',
	'PPP_TOPICS_PER_PAGE_MAXIMUM'			=> 'Maximum number of topics per page',
	'PPP_TOPICS_PER_PAGE_MAXIMUM_EXPLAIN'	=> 'Sets the maximum number of topics which users are allowed to set. The minimum allowed value will be the default number of topics per page set in the board settings. If this value is smaller, it will be ignored. The only exception is 0, in which case the user is disallowed to specify the number of topics per page.',

	// Displayed in UCP and ACP -> Manage users
	'PPP_EXPLAIN'	=> 'Set to 0 to use the default setting. (currently: %d)',

	// Validation errors
	'TOO_LARGE_POSTS_PP'	=> 'The number of <strong>Posts per page</strong> you entered is too large.',
	'TOO_LARGE_TOPICS_PP'	=> 'The number of <strong>Topics per page</strong> you entered is too large.',
	'TOO_SMALL_POSTS_PP'	=> 'The number of <strong>Posts per page</strong> you entered is too small.',
	'TOO_SMALL_TOPICS_PP'	=> 'The number of <strong>Topics per page</strong> you entered is too small.',
));
