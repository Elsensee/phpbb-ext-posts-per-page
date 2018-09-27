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
	'PPP_POSTS_PER_PAGE_MAXIMUM'			=> 'Número máximo de mensajes por página',
	'PPP_POSTS_PER_PAGE_MAXIMUM_EXPLAIN'	=> 'Establece el número máximo de mensajes por página que se permite a los usuarios configurar. El valor mínimo permitido será el número predeterminado de mensajes por página establecido en los ajustes del foro. Si este valor es menor, será ignorado. La única excepción es 0, en cuyo caso el usuario no podrá especificar el número de mensajes por página.',
	'PPP_TOPIC_POSTS_PER_PAGE_MAXIMUM'		=> 'Número máximo de mensajes por página en temas-específicos',
	'PPP_TOPIC_POSTS_PER_PAGE_MAXIMUM_EXPLAIN' => 'Establece el número máximo de mensajes por página que se permite a los usuarios con el permiso necesario para establecer en temas individuales. El valor mínimo permitido será el número predeterminado de mensajes por página establecido en los ajustes del foro. Si este valor es menor, será ignorado. La única excepción es 0, en cuyo caso todos los usuarios no podrán especificar el número de mensajes por página de temas individuales.',
	'PPP_TOPICS_PER_PAGE_MAXIMUM'			=> 'Número máximo de mensajes por página',
	'PPP_TOPICS_PER_PAGE_MAXIMUM_EXPLAIN'	=> 'Establece el número máximo de mensajes que se les permite a los usuarios configurar. El valor mínimo permitido será el número predeterminado de los temas por página establecido en los ajustes del foro. Si este valor es menor, será ignorado. La única excepción es 0, en cuyo caso el usuario no podrá especificar el número de temas por página.',

	// Displayed in posting.php, UCP and ACP -> Manage users
	'PPP_POSTS_PER_PAGE_EXPLAIN'		=> 'Esta configuración sobrescribe en algunos temas por los usuarios que están autorizados para establecer mensajes específicos de temas en la configuración de página. Se establece en 0 para utilizar la configuración por defecto. (actualmente: %d)',
	'PPP_TOPIC_POSTS_PER_PAGE_EXPLAIN'	=> 'Esta configuración sobrescribe los ajustes específicos del usuario. Se establece en 0 para utilizar la configuración por defecto. (actualmente: %d)',
	'PPP_TOPICS_PER_PAGE_EXPLAIN'		=> 'Se establece en 0 para utilizar la configuración por defecto. (actualmente: %d)',

	// Validation errors
	'TOO_LARGE_POSTS_PP'	=> 'El número de <strong>Mensajes por página</strong> que ha introducido es demasiado grande.',
	'TOO_LARGE_TOPICS_PP'	=> 'El número de <strong>Temas por página</strong> que ha introducido es demasiado grande.',
	'TOO_SMALL_POSTS_PP'	=> 'El número de <strong>Mensajes por página</strong> que ha introducido es demasiado pequeño.',
	'TOO_SMALL_TOPICS_PP'	=> 'El número de <strong>Temas por página</strong> que ha introducido es demasiado pequeño.',
));
