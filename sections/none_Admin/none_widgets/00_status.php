<?php

/**
 * This widget will be loaded inside the Admin area in Flatnuke dashboard.
 * Display some informations about the current status of your site.
 *
 * @author Marco Segato <marco.segato@gmail.com>
 * @version 20130216
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */

if (preg_match("/status.php/i", $_SERVER['PHP_SELF'])) {
	Header("Location: ../../../index.php");
	die();
}

// include Flatnuke API
include_once("flatnews/include/news_functions.php");

// translations
global $lang;
switch($lang) {
	case "it":
		define ("_WDG_STATUS_NEWSSECTIONS", "Sezioni di notizie");
		define ("_WDG_STATUS_NEWS", "Notizie");
		define ("_WDG_STATUS_PENDING", "In attesa");
		define ("_WDG_STATUS_CATEGORIES", "Categorie");
		define ("_WDG_STATUS_TAGS", "Tag");
		define ("_WDG_STATUS_CHANGETHEME", "Cambia tema");
		define ("_WDG_STATUS_WITHTHEME", "con tema");
	break;
	default:
		define ("_WDG_STATUS_NEWSSECTIONS", "News sections");
		define ("_WDG_STATUS_NEWS", "News");
		define ("_WDG_STATUS_PENDING", "Pending");
		define ("_WDG_STATUS_CATEGORIES", "Categories");
		define ("_WDG_STATUS_TAGS", "Tags");
		define ("_WDG_STATUS_CHANGETHEME", "Change theme");
		define ("_WDG_STATUS_WITHTHEME", "with theme");
}

// get the number of users' registrations pending
$handle = opendir(get_waiting_users_dir());
$pending_users = 0;
while($file = readdir($handle)) {
	if(preg_match("/^[0-9a-zA-Z]+\.php$/i", $file)) {
		$pending_users++;
	}
}
closedir($handle);

// get the number of news published
$news_total_number = 0;
foreach (list_news_sections() as $news_section) {
	$news_total_number += count(load_news_list($news_section));
}

// start building the main table
?>

<table><tbody>
<tr class="first">
	<td class="first b"><?php echo count(list_news_sections())?></td>
	<td class="t posts"><?php echo _WDG_STATUS_NEWSSECTIONS?></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td class="first b"><?php echo $news_total_number?></td>
	<td class="t posts"><?php echo _WDG_STATUS_NEWS?></td>
	<td class="b"><span class="total-count"><?php echo count(load_proposed_news_list())?></span></td>
	<td class="last t waiting"><?php echo _WDG_STATUS_PENDING?></td>
</tr>
<tr>
	<td class="first b"><?php echo count(list_news_categories())?></td>
	<td class="t cats"><?php echo _WDG_STATUS_CATEGORIES?></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr><td class="first b"><?php echo count(load_tags_list())?></td>
	<td class="t tags"><?php echo _WDG_STATUS_TAGS?></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr><td class="first b"><?php echo count(list_users())?></td>
	<td class="t tags"><?php echo _USERS?></td>
	<td class="b"><span class="total-count"><?php echo $pending_users?></span></td>
	<td class="last t waiting"><?php echo _WDG_STATUS_PENDING?></td>
</tr>
</tbody></table>

<?php
// print footer with theme change option button
global $theme;
$widget_footer = "<a href=\"javascript:jQueryFNcall('sections/none_Admin/section.php?mod=none_Admin&amp;op=fnccconf','GET','fn_adminpanel');\" class=\"button rbutton\">"._WDG_STATUS_CHANGETHEME."</a>Flatnuke v<span class=\"b\">".get_fn_version()."</span> "._WDG_STATUS_WITHTHEME." <span class=\"b\">$theme</span>";
?>
