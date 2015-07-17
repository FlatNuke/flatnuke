<?php
/*
 * @author Alfredo Cosco <orazio.nelson@gmail.com>, added "jQuery Version"
 * @version 20140522
 */
if (strpos($_SERVER['PHP_SELF'],basename(__FILE__))) {
	//Time zone
	if (function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
		@date_default_timezone_set(date_default_timezone_get());

	chdir("../../../");
	include_once("config.php");
	include_once("functions.php");
	include_once (get_fn_dir("sections")."/none_Admin/none_functions/func_interfaces.php");
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

	// security conversions
	$mod = getparam("mod", PAR_GET, SAN_FLAT);
	$req = getparam("REQUEST_URI", PAR_SERVER, SAN_NULL);
	// check if the GD library is installed or not
	if(function_exists("gd_info")) {
		$GDinfo = gd_info();
		$GDinfo = preg_replace('/[[:alpha:][:space:]()]+/i', '', $GDinfo['GD Version']);
	} else $GDinfo = _FNCC_NOGDLIB;
	// get siteurl
	$my_siteurl = $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];
	$my_siteurl = str_replace(get_fn_dir("sections")."/".$mod."/section.php","index.php",$my_siteurl);
	$protocol   = (isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS']=="on") ? ("https://") : ("http://");
	// print some informations about the site
	?>
	<div><strong><?php echo _FNCC_SITEURL?></strong><?php echo $protocol.$my_siteurl?></div>
	<div><strong><?php echo _FNCC_OS?></strong><?php echo PHP_OS?></div>
	<div><strong><?php echo _FNCC_WEBSERVER?></strong><?php echo $_SERVER["SERVER_SOFTWARE"]?></div>
	<hr>
	<div><strong><?php echo _FNCC_PHP?></strong><?php echo phpversion();?> - <a href="<?php echo get_fn_dir("sections")?>/<?php echo $mod?>/none_tools/phpinfo.php" target="new" title="PHP informations">PHPInfo</a></div>
	<div><strong><?php echo _FNCC_GDLIB?></strong><?php echo $GDinfo;?></div>
	<div><strong><?php echo _FNCC_FLATNUKE?></strong><?php echo FN_VERSION;?></div>
	<div><strong>jQuery version:</strong>&nbsp;<span class="fn-jq-version"></span>
	<script>
	$(document).ready(function()
	{ var v = jQuery.fn.jquery; 
	 $(".fn-jq-version").text(v)
	 });
	</script>
	<hr>
	</div>
	<?php
	$total_space   = round(disk_free_space("./")/1024/1024,2);
	$site_space    = round(fncc_getsize("./")/1024/1024,2);
	$perc_occupied = round($site_space*100/$total_space,1);
	$perc_free     = round(100-($site_space*100/$total_space),1);
	?>
	<div><strong><?php echo _FNCC_SERVERSPACE?></strong><?php echo "$total_space Mb";?></div>
	<div><strong><?php echo _FNCC_SITESPACE?></strong><?php echo "$site_space Mb";?></div>
	<div class="progress progress-striped">
		<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $perc_occupied?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $perc_occupied?>%;">
			<?php echo $perc_occupied?>%
		</div>
	</div>
	<hr>
	<div><strong><?php echo _FNCC_IP?></strong><?php echo $_SERVER["REMOTE_ADDR"]?></div>
	<div><strong><?php echo _FNCC_USERAGENT?></strong><?php echo $_SERVER["HTTP_USER_AGENT"]?></div>
<?php 

$widget_footer ="";
?>
