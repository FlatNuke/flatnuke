<?php

/**
 * This widget will be loaded inside the Admin area in Flatnuke dashboard.
 * It displays accessing statistics of your site.
 *
 * @author Marco Segato <marco.segato@gmail.com>
 * @author Alfredo Cosco <orazio.nelson@gmailcom>
 * @version 201405 /now uses morris.js for charts
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */

// intercept direct access to this file & rebuild the right path
if (strpos($_SERVER['PHP_SELF'],basename(__FILE__))) {
	//Time zone
	if (function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
		@date_default_timezone_set(date_default_timezone_get());

	chdir("../../../");
	include_once("config.php");
	include_once("functions.php");

 	if (!defined("_FN_MOD"))
		create_fn_constants();

	// set again cookies values of the language
	$userlang = getparam("userlang", PAR_COOKIE, SAN_FLAT);
	if ($userlang!="" AND is_alphanumeric($userlang) AND file_exists("languages/$userlang.php")) {
		$lang = $userlang;
	}
	include_once("languages/$lang.php");
	// set again cookies values of the theme
	$usertheme = getparam("usertheme", PAR_COOKIE, SAN_FLAT);
	if ($usertheme!="" AND !stristr("..",$usertheme) AND is_dir(get_fn_dir("themes")."/$usertheme")) {
		$theme = $usertheme;
	}
	// set again charset: using ajax, it could be rewritten by the web server
	@header("Content-Type: text/html; charset="._CHARSET."");
}

?>
<div id="morris-bar-chart"></div>
<?php 

//$months=get_month_list();
//$months=explode(',', $months);
//$visitors=explode(',',get_monthly_stats(get_selected_year()));
        //var_dump($visitors);
        
$widget_footer = "<div>".get_years_links()."</div>";
echo $widget_footer;
?> 
 
<script>
$(function() {
    Morris.Bar({
        element: 'morris-bar-chart',
        data: [
        <?php 
        foreach($months as $k=>$v){
			echo "{";
			echo "m: '".$v."',";
			echo "u: '".$visitors[$k]."'";
			echo "},";
			}
        ?>
        ],
        xkey: 'm',
        ykeys: 'u',
        labels: ['Users'],
        hideHover: 'auto',
        resize: true
    });
});
</script>

