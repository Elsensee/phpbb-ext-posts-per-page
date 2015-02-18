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
	'PPP_POSTS_PER_PAGE_MAXIMUM'			=> 'Sayfabaşına maksimum gönderi sayısı',
	'PPP_POSTS_PER_PAGE_MAXIMUM_EXPLAIN'	=> 'Kullanıcıların ayarlayabileceği sayfa başına maksimum gönderi sayısını ayarlar. Minimum izin verilen değer site ayarlarındaki varsayılan sayfa başına gönderi sayısıdır. Eğer daha küçük değer verirseniz, reddedilir. Tek hariç tutulan değer sıfırdır, bu değer sayfa başına gönderi sayısı belirleme özelliğini kapatır.',
	'PPP_TOPIC_POSTS_PER_PAGE_MAXIMUM'		=> 'Sayfabaşına maksimum konu spesifik gönderi sayısı',
	'PPP_TOPIC_POSTS_PER_PAGE_MAXIMUM_EXPLAIN' => 'Gerekli izinleri olan kullanıcılarınBireysel konular için sayfabaşına maksimum gönderi sayısını ayarlar. Minimum izin verilen değer site ayarlarındaki varsayılan sayfa başına gönderi sayısıdır. Eğer daha küçük değer verirseniz, reddedilir. Tek hariç tutulan değer sıfırdır, bu değer sayfa başına bireysel gönderi sayısı belirleme özelliğini kapatır.',
	'PPP_TOPICS_PER_PAGE_MAXIMUM'			=> 'Sayfabaşına maksimum konu sayısı',
	'PPP_TOPICS_PER_PAGE_MAXIMUM_EXPLAIN'	=> 'Kullanıcıların ayarlayabileceği maksimum konu sayısını ayarlar. Minimum izin verilen değer site ayarlarındaki varsayılan sayfa başına konu sayısıdır. Eğer daha küçük değer verirseniz, reddedilir. Tek hariç tutulan değer sıfırdır, bu değer sayfa başına konu sayısı belirleme özelliğini kapatır.',

	// Displayed in posting.php, UCP and ACP -> Manage users
	'PPP_POSTS_PER_PAGE_EXPLAIN'		=> 'Bu ayar bazı konularda konu-spesifik sayfa başına gönderi ayarlamaya yetkisi olan kullanıcıların ayarlarının üstüneyazılabilir. Varsayılan ayarı kullanmak için 0 ayarlayın. (mevcut: %d)',
	'PPP_TOPIC_POSTS_PER_PAGE_EXPLAIN'	=> 'Bu ayar kullanıcı spesifik ayarların üstüne yazılır. Varsayılan ayarı kullanmak için 0 ayarlayın (mevcut: %d)',
	'PPP_TOPICS_PER_PAGE_EXPLAIN'		=> 'Varsayılan ayarı kullanmak için 0 girin. (Mevcut: %d)',

	// Validation errors
	'TOO_LARGE_POSTS_PP'	=> 'Girdiğiniz <strong>Sayfa başına gönderi</strong> sayısı çok geniş.',
	'TOO_LARGE_TOPICS_PP'	=> 'Girdiğiniz <strong>Sayfa başına konu</strong> sayısı çok geniş.',
	'TOO_SMALL_POSTS_PP'	=> 'Girdiğiniz <strong>Sayfa başına gönderi</strong> sayısı çok küçük.',
	'TOO_SMALL_TOPICS_PP'	=> 'Girdiğiniz <strong>Sayfa başına konu</strong> sayısı çok küçük.',
));
