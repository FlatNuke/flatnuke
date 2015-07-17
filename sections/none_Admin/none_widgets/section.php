<?php

/**
 * This module shows Admin area in a dashboard on Flatnuke.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @author Lorenzo Caporale <piercolone@gmail.com>
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * 
 * @version 20130303
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */

if (preg_match("/section.php/i", $_SERVER['PHP_SELF'])) {
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
		define ("_WDG_STATUS_WITHTHEME", "Tema");
		define ("_WDG_STATUS_VISITORS", "Visitatori a");
	break;
	default:
		define ("_WDG_STATUS_NEWSSECTIONS", "News sections");
		define ("_WDG_STATUS_NEWS", "News");
		define ("_WDG_STATUS_PENDING", "Pending");
		define ("_WDG_STATUS_CATEGORIES", "Categories");
		define ("_WDG_STATUS_TAGS", "Tags");
		define ("_WDG_STATUS_CHANGETHEME", "Change theme");
		define ("_WDG_STATUS_WITHTHEME", "Theme");
		define ("_WDG_STATUS_VISITORS", "Visitors in");
}
//define the theme
global $theme;
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


$months=get_month_list();
$months=explode(',', $months);
$visitors=explode(',',get_monthly_stats(get_selected_year()));
?>
<div style="clear:both;"></div>
<div class="row glances" style="padding-top:10px">
<?php
//Flatnuke version
fncc_create_badge('info', 'fa-linux', get_fn_version(), "Flatnuke version",null,null);

//Flatnuke Theme
$textheme=build_fnajax_link($mod, "&amp;op=fnccthemestruct", "fncc-adminpanel", "get")._WDG_STATUS_WITHTHEME."</a>";
fncc_create_badge('success', 'fa-html5', $theme, $textheme,null,null);

//Flatnuke Categories
fncc_create_badge('primary', 'fa-compass', count(list_news_categories()), _WDG_STATUS_CATEGORIES,null,null);

//Flatnuke Tags
fncc_create_badge('danger', 'fa-tag', count(load_tags_list()), _WDG_STATUS_TAGS,null,null);
?>
</div>
<div class="row glances" style="padding-top:10px">
	<!--div class="collapse" id="optional-glances" style="min-height:100px"-->

<?php
//Flatnuke news
$subtextnews=_WDG_STATUS_PENDING.": <span class=\"badge\">".count(load_proposed_news_list())."</span>";
fncc_create_badge('info', 'fa-pencil-square', $news_total_number, _WDG_STATUS_NEWS,$subtextnews,null);

//Flatnuke Users
$textusers= build_fnajax_link($mod, "&amp;op=fnccmembers", "fncc-adminpanel", "get")._USERS."</a>";
$subtextusers=_WDG_STATUS_PENDING.":  <span class=\"badge\">".build_fnajax_link($mod, "&amp;op=fnccwaitingusers", "fncc-adminpanel", "get").$pending_users."</a></span>";
fncc_create_badge('warning', 'fa-users', count(list_users()), $textusers,$subtextusers,null);

//Flatnuke News Sections
fncc_create_badge('default', 'fa-paperclip ', count(list_news_sections()), _WDG_STATUS_NEWSSECTIONS,null,null);

//Flatnuke Stats
$textvisitors=_WDG_STATUS_VISITORS."&nbsp;".$months[date('n')-1];
fncc_create_badge('success', 'fa-tasks fa-rotate-90', $visitors[date('n')-1], $textvisitors,null,null);
?>
	<!--/div>
<button class="btn btn-success form-control collapse-btn text-center" data-toggle="collapse" data-target="#optional-glances" href="#">View details &raquo;</button-->
</div>

<!--script>
$('.optional .collapse-btn').on('click', function(e) {
    e.preventDefault();
    var $this = $(this);
    var $collapse = $this.closest('.collapse-group').find('.collapse');
    $collapse.collapse('toggle');
});
</script-->
<div style="clear:both;"></div>
<div id="dashboard-widgets">
	
<?php

// security checks
$mod = _FN_MOD;

// manage translations
global $lang;
$title    = "title-$lang";
$subtitle = "subtitle-$lang";

// scan widget directory and build widgets' list
$widgets = array();
if(file_exists(get_fn_dir("sections")."/$mod/none_widgets")) {
	$widgets_dir = opendir(get_fn_dir("sections")."/$mod/none_widgets");
	while($file = readdir($widgets_dir)) {
		if(preg_match("/[\.]*[[:alpha:]]+\.php$/i",$file) AND !preg_match("/none_/i",$file) AND $file!="section.php") {
			array_push($widgets, $file);
		}
	}
	closedir($widgets_dir);
}
if(count($widgets)>0) sort($widgets);
//var_dump($widgets);
// load each widget
foreach($widgets as $current_widget) {
	// load widget XML definition
	if (file_exists(get_fn_dir("sections")."/$mod/none_widgets/".str_replace(".php",".xml",$current_widget))){
		$string = get_file(get_fn_dir("sections")."/$mod/none_widgets/".str_replace(".php",".xml",$current_widget));
		$xmldata = new SimpleXMLElement($string);
		// general informations about the widget
		foreach ($xmldata->info as $info){
			$widget_id_name  = (string) $info->id_name;
			$widget_descript = htmlentities(addslashes((string) $info->description),ENT_COMPAT,_CHARSET);
			$widget_version  = (string) $info->version;
		}
		// informations about widget's author
		foreach ($xmldata->author as $author){
			$widget_author   = addslashes((string) $author->name);
			$widget_mail     = (string) $author->mail;
			$widget_website  = (string) $author->website;
		}
		// widget title and subtitle translations
		foreach ($xmldata->translations as $translations){
			$widget_title    = ((string) $translations->$title   =="") ? ((string) $translations->title)    : ((string) $translations->$title);
			$widget_subtitle = ((string) $translations->$subtitle=="") ? ((string) $translations->subtitle) : ((string) $translations->$subtitle);
		}
		/* -- ONLY STANDARD AND UPDATED WIDGETS WILL BE LOADED! --
		   Please have a look to /sections/none_Admin/none_widgets directory and
		   start to study the two files none_example.php and none_example.xml:
		   you'll find the Flatnuke standard structure to apply to your own widget.
		   -- TECHNICAL PARAMETHERS --
		   Mandatory fields in XML file:
			* id_name     -> unique id to apply to your widget
			* description -> long description of your widget
			* version     -> date formatted AAAAMMGG
			* name        -> author's name
			* title       -> widget's main title
			* subtitle    -> widget's subtitle
		*/
		if ($widget_id_name!="" AND $widget_descript!="" AND $widget_version!="" AND $widget_author!="" AND $widget_title!="" AND $widget_subtitle!="") {
			// widget credits
			$credits = "<p>$widget_descript</p>Version: $widget_version<br/>Author: $widget_author<br/>Mail: $widget_mail<br/>Web: $widget_website";
			// widget main content
			?>
			<div class="fncc-widget-item" id="<?php echo $widget_id_name ?>">
				<div class="panel panel-success">
					<div class="panel-heading"  >
						<span class="fncc-widget-title lead" data-toggle="tooltip" data-placement="bottom" data-animation="true" data-html="true" title="<?php echo $credits?>"><?php echo $widget_title ?></span>
					</div>
					<div class="panel-body">
						<div class="row" style="border-bottom:1px solid"><span class="fn-widget-subtitle"><?php echo $widget_subtitle ?></span></div>
						<div class="table">
							<?php include_once(get_fn_dir("sections")."/$mod/none_widgets/$current_widget") ?>
						</div>
					</div>
					<div class="panel-footer">
						<?php //echo $widget_footer ?>
					</div>
				</div>
			</div>
<?php
		}
	}
}

?><div style="clear:both;"></div>

<script>
$(document).ready(function() {
    $('.fncc-widget-title').tooltip();
});
</script>

<?php
/* 
 * function to create the badges
 * 
 * @author Alfredo Cosco
 * version 20140523 
 * 
 */

function fncc_create_badge($panel_style, $icon_name, $heading, $text,$subtext=null,$footerlink=null){
	?>
<div class="col-lg-3">
	<div class="panel panel-<?php echo $panel_style; ?>">
		<div class="panel-heading">
			<div class="row">
				<div class="col-xs-5">
					<i class="fa <?php echo $icon_name; ?> fa-5x"></i>
					<?php 
		if(isset($subtext)) {
			echo "<p class=\"announcement-text label label-".$panel_style."\">".$subtext."</p>\n";
			}
		?>
				</div>
				<div class="col-xs-7 text-right">
				<p class="announcement-heading"><?php echo $heading; ?></p>
				<p class="announcement-text"><?php echo $text; ?></p>
		
				</div>
			</div>
		</div>
		
			<div class="panel-footer announcement-bottom">
				<div class="row">
			  <?php 
			if(isset($footerlink)) {
				echo "<a href=\"$footerlink\">\n";
				echo "<div class=\"col-xs-6\">\n".$footerlink."</div>\n";
				echo "<div class=\"col-xs-6 text-right\"><i class=\"fa fa-arrow-circle-right\"></i></div>\n";
				echo "</a>";
				}
			?>
				</div>
			</div>
		
	</div>
</div>
	<?php
	}

/**
 * Returns the labels of months to use with graph
 *
 * @author Marco Segato <marco.segato@gmail.com>
 * @version 20130303
 *
 * @return string Labels of months
 */
function get_month_list() {
	// define variables
	global $mesi;
	$months_string = "";
	// import month's names from Flatnuke translations (only the first 3 chars)
	foreach ($mesi as $little_month) {
		$months_string .= substr($little_month,0,3).",";
	}
	// return the string without the last comma
	$months_string = preg_replace("/,$/","",$months_string);
	return $months_string;
}

/**
 * Returns the year for which to display statistics
 *
 * @author Marco Segato <marco.segato@gmail.com>
 * @version 20130303
 *
 * @return number Year to display
 */
function get_selected_year() {
	// default value is the curent year, but the user can request to display
	// specific data for another year with a GET action
	$year = getparam("year",PAR_GET,SAN_FLAT);
	// checks user's choice
	if($year=="")
		$year_sel = date("Y");
	else
		$year_sel = $year;
	// returns the value
	return $year_sel;
}

/**
 * Extracts statistic data for the chosen year.
 *
 * @author Marco Segato <marco.segato@gmail.com>
 * @version 20130303
 *
 * @param $year Number of the year to work with
 * @return string The list of values for each month, separated with comma
 */
function get_monthly_stats($year) {
	// define variables
	$year  = getparam($year,PAR_NULL,SAN_FLAT);
	$stats = "";
	// check existance and open the file with statistics
	if (file_exists(get_fn_dir("var")."/flatstat/$year/generale.php")){
		$fd = file (get_fn_dir("var")."/flatstat/$year/generale.php");
		// get monthly stats for the selected year
		for ($i=0 ; $i<count($fd); $i++){
			if(trim($fd[$i])!='') {
				$tmp=explode("|",$fd[$i]);
				// build the string [data1,data2,data3,...] with data
				$stats .= trim($tmp[1]).",";
			}
		}
		// return the string without the last comma
		$stats = preg_replace("/,$/","",$stats);
	} else $stats = "0,0,0,0,0,0,0,0,0,0,0,0";
	// return the string
	return $stats;
}

/**
 * Creates links to show all the years with statistic data
 *
 * @author Marco Segato <marco.segato@gmail.com>
 * @version 20130303
 *
 * @return string The links to all the years
 */
function get_years_links() {
	// define variables
	$years       = array();
	$years_links = "";
	// find all the years with statistic data and build an array
	$handle = opendir(get_fn_dir("var")."/flatstat");
	while ($file = readdir($handle)) {
	  if (!( $file=="." or $file==".." ) and (!preg_match("/^\./",$file)and ($file!="CVS"))) {
		if (is_dir(get_fn_dir("var")."/flatstat/$file"))
		array_push($years, $file);
		}
	}
	closedir($handle);
	// sort years in natural order
	sort($years);
	// build links with AJAX
	for ($j=1 ;$j<count($years); $j++){
		if (file_exists(get_fn_dir("var")."/flatstat/".($years[$j])."/generale.php")){
			$years_links .= "&nbsp;<a href=\"javascript:jQueryFNcall('"."sections/"._FN_MOD."/none_widgets/".(basename(__FILE__))."?mod="._FN_MOD."&amp;year=$years[$j]','GET','year_chart');\">$years[$j]</a>";
		}
	}
	// return the string
	return $years_links;
}

?>
