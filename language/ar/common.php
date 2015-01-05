<?php
/**
*
* @package Individual posts per page
* @copyright (c) 2015 Oliver Schramm
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
 * Translated By : Basil Taha Alhitary - www.alhitary.net
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
	'PPP_POSTS_PER_PAGE_MAXIMUM'			=> 'الحد الأقصى لعدد المشاركات ',
	'PPP_POSTS_PER_PAGE_MAXIMUM_EXPLAIN'	=> 'اضافة الحد الأقصى للمشاركات في كل صفحة بحيث يستطيع الأعضاء تحديدها من لوحة التحكم الخاص بهم. والعدد الافتراضي المُحدد للمشاركات ( إعدادات المنتدى -> إعدادات المشاركة -> المشاركات بالصفحة ) سيكون هو الحد الأدنى المسموح به. وسيتم تجاهل أي قيمة أقل من العدد الإفتراضي. القيمة صغر تعني تعطيل هذا الخيار.',
	'PPP_TOPICS_PER_PAGE_MAXIMUM'			=> 'الحد الأقصى لعدد المواضيع ',
	'PPP_TOPICS_PER_PAGE_MAXIMUM_EXPLAIN'	=> 'اضافة الحد الأقصى للمواضيع في كل صفحة بحيث يستطيع الأعضاء تحديدها من لوحة التحكم الخاص بهم. والعدد الافتراضي المُحدد للمواضيع ( إعدادات المنتدى -> إعدادات المشاركة -> المواضيع بالصفحة ) سيكون هو الحد الأدنى المسموح به. وسيتم تجاهل أي قيمة أقل من العدد الإفتراضي. القيمة صغر تعني تعطيل هذا الخيار.',

	// Displayed in UCP and ACP -> Manage users
	'PPP_EXPLAIN'	=> 'القيمة صفر تعني استخدام الإعدادات الإفتراضية. ( حالياً : %d )',

	// Validation errors
	'TOO_LARGE_POSTS_PP'	=> 'عدد <strong>المشاركات لكل صفحة</strong> أكبر من القيمة المسموح بها.',
	'TOO_LARGE_TOPICS_PP'	=> 'عدد <strong>المواضيع لكل صفحة</strong> أكبر من القيمة المسموح بها.',
	'TOO_SMALL_POSTS_PP'	=> 'عدد <strong>المشاركات لكل صفحة</strong> أقل من القيمة المسموح بها.',
	'TOO_SMALL_TOPICS_PP'	=> 'عدد <strong>المواضيع لكل صفحة</strong> أقل من القيمة المسموح بها.',
));
