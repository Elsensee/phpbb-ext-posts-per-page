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
	'PPP_POSTS_PER_PAGE_MAXIMUM'			=> 'Massimo numero di post per pagina',
	'PPP_POSTS_PER_PAGE_MAXIMUM_EXPLAIN'	=> 'Imposta il numero massimo di post per pagina che gli utenti siano abilitati a settare. Il numero minimo sarà il numero predefinito di post per pagina impostato nelle impostazioni della board. Se questo valore è inferiore, sarà ignorato. L’unica eccezione è 0, nel qual caso all’utente non sarà permesso specificare i post per pagina.',
	'PPP_TOPIC_POSTS_PER_PAGE_MAXIMUM'		=> 'Massimo numero di post specifici per topic per pagina',
	'PPP_TOPIC_POSTS_PER_PAGE_MAXIMUM_EXPLAIN' => 'Imposta il numero massimo di post per pagina che gli utenti con i permessi richiesti possono impostare per i topic individuali. Il minimo valore permesso sarà il numero predefinito di post per pagina impostato nelle impostazioni della board. Se questo valore è inferiore, sarà ignorato. L’unica eccezione è 0, nel qual caso tutti gli utenti non potranno specificare i post per pagina per i topic individuali.',
	'PPP_TOPICS_PER_PAGE_MAXIMUM'			=> 'Massimo numero di topic per pagina',
	'PPP_TOPICS_PER_PAGE_MAXIMUM_EXPLAIN'	=> 'Imposta il numero massimo di topic che gli utenti possono impostare. Il minimo valore consentito è il numero predefinito di topic per pagina impostato nelle impostazioni della board. Se questo valore è inferiore, sarà ignoranto. L’unica eccezione è 0, nel qual caso all’utente non è permesso specificare il numero di topic per pagina.',

	// Displayed in posting.php, UCP and ACP -> Manage users
	'PPP_POSTS_PER_PAGE_EXPLAIN'		=> 'Questa impostazione può essere sovrascritta in alcuni topic da utenti che abbiano il permesso di impostare il numero di post per pagina per specifico topic. Impostalo a 0 per usare il valore predefinito. (attualmente: %d)',
	'PPP_TOPIC_POSTS_PER_PAGE_EXPLAIN'	=> 'Questa impostazione sovrascrive le impostazioni specifiche per l’utente. Imposta a 0 per usare il valore predefinito (attualmente: %d)',
	'PPP_TOPICS_PER_PAGE_EXPLAIN'		=> 'Imposta a 0 per usare le impostazioni predefinite. (attualmente: %d)',

	// Validation errors
	'TOO_LARGE_POSTS_PP'	=> 'Il numero di <strong>Post per pagina</strong> che hai inserito è troppo grande.',
	'TOO_LARGE_TOPICS_PP'	=> 'Il numero di <strong>Topic per pagina</strong> che hai inserito è troppo grande.',
	'TOO_SMALL_POSTS_PP'	=> 'Il numero di <strong>Post per pagina</strong> che hai inserito è troppo piccolo.',
	'TOO_SMALL_TOPICS_PP'	=> 'Il numero di <strong>Topic per pagina</strong> che hai inserito è troppo piccolo.',
));
