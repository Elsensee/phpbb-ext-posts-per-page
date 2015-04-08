<?php
/**
*
* @package Individual posts per page
* @copyright (c) 2015 Oliver Schramm
* Croatian translation by Ančica Sečan (http://ancica.sunceko.net)
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
	'PPP_POSTS_PER_PAGE_MAXIMUM'			=> 'Maksimalan broj postova po stranici',
	'PPP_POSTS_PER_PAGE_MAXIMUM_EXPLAIN'	=> 'Maksimalan dopušten broj postova po stranici koji korisnici/e mogu postaviti [0=onemogućeno (za korisnika/cu)].<br /><em>Minimalna dopuštena vrijednost broja postova po stranici forumski je zadana, ukoliko je manja, bit će ignorirana.</em>',
	'PPP_TOPIC_POSTS_PER_PAGE_MAXIMUM'		=> 'Maksimalan broj postova po stranici teme',
	'PPP_TOPIC_POSTS_PER_PAGE_MAXIMUM_EXPLAIN' => 'Maksimalan dopušten broj postova po stranici teme koji korisnici/e mogu postaviti [0=onemogućeno (za korisnika/cu)].<br /><em>Minimalna dopuštena vrijednost broja postova po stranici teme forumski je zadana, ukoliko je manja, bit će ignorirana.</em>',
	'PPP_TOPICS_PER_PAGE_MAXIMUM'			=> 'Maksimalan broj tema po stranici',
	'PPP_TOPICS_PER_PAGE_MAXIMUM_EXPLAIN'	=> 'Maksimalan dopušten broj tema po stranici koji korisnici/e mogu postaviti [0=onemogućeno (za korisnika/cu)].<br /><em>Minimalna dopuštena vrijednost broja tema po stranici forumski je zadana, ukoliko je manja, bit će ignorirana.</em>',

	// Displayed in posting.php, UCP and ACP -> Manage users
	'PPP_POSTS_PER_PAGE_EXPLAIN'		=> 'Ova postavka može biti <em>prepisana</em>, od strane autora/ice teme, ukoliko je postavio/la broj prikaza postova po stranici teme.<br />0=zadano [trenutno: %d].',
	'PPP_TOPIC_POSTS_PER_PAGE_EXPLAIN'	=> 'Ova postavka <em>prepisuje</em> postavke korisnika/ce.<br />0=zadano [trenutno: %d].',
	'PPP_TOPICS_PER_PAGE_EXPLAIN'		=> '0=zadano [trenutno: %d].',

	// Validation errors
	'TOO_LARGE_POSTS_PP'	=> 'Broj <strong>Postova po stranici</strong> koji si upisao/la je prevelik.',
	'TOO_LARGE_TOPICS_PP'	=> 'Broj <strong>Tema po stranici</strong> koji si upisao/la je prevelik.',
	'TOO_SMALL_POSTS_PP'	=> 'Broj <strong>Postova po stranici</strong> koji si upisao/la je premalen.',
	'TOO_SMALL_TOPICS_PP'	=> 'Broj <strong>Tema po stranici</strong> koji si upisao/la je premalen.',
));
