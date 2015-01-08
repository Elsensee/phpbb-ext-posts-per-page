<?php
/**
*
* @package Individual posts per page
* @copyright (c) 2015 Martin Beckmann
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
	'PPP_POSTS_PER_PAGE_MAXIMUM'			=> 'Maximale Anzahl an Beiträgen pro Seite',
	'PPP_POSTS_PER_PAGE_MAXIMUM_EXPLAIN'	=> 'Setzt die maximal erlaubte Anzahl an Beiträgen pro Seite, die Nutzer einstellen können. Die Nutzer dürfen immer mindestens so viele Beiträge pro Seite einstellen, wie in der Board-Konfiguration als Standard definiert ist. Falls Sie hier weniger Beiträge pro Seite erlauben, wird diese Einstellung daher ignoriert. Die einzige Ausnahme hiervon ist die Einstellung 0, was den Benutzern verbietet, die Anzahl an Beiträgen pro Seite selbst anzupassen.',
	'PPP_TOPIC_POSTS_PER_PAGE_MAXIMUM'		=> 'Maximale Anzahl an themenspezifischen Beiträgen pro Seite',
	'PPP_TOPIC_POSTS_PER_PAGE_MAXIMUM_EXPLAIN' => 'Setzt die maximal erlaubte Anzahl an Beiträgen pro Seite, die Nutzer für einzelne Themen individuell einstellen können. Die Nutzer dürfen immer mindestens so viele Beiträge pro Seite einstellen, wie in der Board-Konfiguration als Standard definiert ist. Falls Sie hier weniger Beiträge pro Seite erlauben, wird diese Einstellung daher ignoriert. Die einzige Ausnahme hiervon ist die Einstellung 0, was den Benutzern verbietet, die Anzahl an Beiträgen pro Seite selbst anzupassen.',
	'PPP_TOPICS_PER_PAGE_MAXIMUM'			=> 'Maximale Anzahl an Themen pro Seite',
	'PPP_TOPICS_PER_PAGE_MAXIMUM_EXPLAIN'	=> 'Setzt die maximal erlaubte Anzahl an Themen pro Seite, die Nutzer einstellen können. Die Nutzer dürfen immer mindestens so viele Themen pro Seite einstellen, wie in der Board-Konfiguration als Standard definiert ist. Falls Sie hier weniger Themen pro Seite erlauben, wird diese Einstellung daher ignoriert. Die einzige Ausnahme hiervon ist die Einstellung 0, was den Benutzern verbietet, die Anzahl an Themen pro Seite selbst anzupassen.',

	// Displayed in posting.php, UCP and ACP -> Manage users
	'PPP_EXPLAIN'	=> 'Stelle diesen Wert auf 0 ein, um die Standardeinstellung zu verwenden (derzeit %d).',
	'PPP_POSTS_PER_PAGE_EXPLAIN'		=> 'Diese Einstellung kann von Benutzern mit der Berechtigung für themenspezifische Einstellungen in einigen Themen überschrieben werden. Stelle diesen Wert auf 0 ein, um die Standardeinstellung zu verwenden (derzeit %d).',
	'PPP_TOPIC_POSTS_PER_PAGE_EXPLAIN'	=> 'Diese Einstellung überschreibt benutzerspezifische Einstellungen. Stelle diesen Wert auf 0 ein, um die Standardeinstellung zu verwenden (derzeit %d).',
	'PPP_TOPICS_PER_PAGE_EXPLAIN'		=> 'Stelle diesen Wert auf 0 ein, um die Standardeinstellung zu verwenden (derzeit %d).',

	// Validation errors
	'TOO_LARGE_POSTS_PP'	=> 'Die eingegebene Anzahl an <strong>Beiträgen pro Seite</strong> ist zu groß.',
	'TOO_LARGE_TOPICS_PP'	=> 'Die eingegebene Anzahl an <strong>Themen pro Seite</strong> ist zu groß.',
	'TOO_SMALL_POSTS_PP'	=> 'Die eingegebene Anzahl an <strong>Beiträgen pro Seite</strong> ist zu klein.',
	'TOO_SMALL_TOPICS_PP'	=> 'Die eingegebene Anzahl an <strong>Themen pro Seite</strong> ist zu klein.',
));
