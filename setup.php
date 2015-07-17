<?php

/*
 * Flatnuke http://www.flatnuke.org/
 * Installation script
 *
 * @author	Marco Segato <segatom@users.sourceforge.net>
 *
 * @version 20120211 - Fixed LFI to RCE bug with $lang variable (thanks to Jakub Galczyk http://hauntit.blogspot.com)
 *                     Fixed XSS bug with $step variable (thanks to Jakub Galczyk http://hauntit.blogspot.com)
 */

//Time zone
if (function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
	@date_default_timezone_set(date_default_timezone_get());

// load Flatnuke APIs
include('functions.php');
include('config.php');

if (!defined("_FN_MOD"))
	create_fn_constants();

// security checks
$step  = isset($_GET['step'])  ? getparam('step', PAR_GET, SAN_PARA) : 0;
$lang  = isset($_GET['lang'])  ? getparam('lang', PAR_GET, SAN_PARA) : (substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2));
$theme = isset($_GET['theme']) ? getparam('theme',PAR_GET, SAN_FLAT) : ($theme);

// load Flatnuke translations for current language
if ($lang!="" AND is_alphanumeric($lang) AND file_exists("languages/$lang.php")) {
	switch($lang) {
		case "de"||"es"||"fr"||"it"||"pt"	: include_once("languages/$lang.php");	break;
		default								: include_once("languages/en.php");		break;
	}
	switch($lang) {
		case "it"	: include_once("languages/admin/$lang.php");	break;
		default		: include_once("languages/admin/en.php");		break;
	}
} else {
	$lang = "en";
	include_once("languages/en.php");
	include_once("languages/admin/en.php");
}

//--> Start MAIN EXECUTION
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Flatnuke setup</title>
<link rel="stylesheet" href="include/css/none_setup.css" type="text/css">
<meta http-equiv="content-type" content="text/html; charset=<?php echo _CHARSET?>">
</head>
<body>
	<div align="center">
	<div id="FN-setup-box"><?php

	// stop the script if /var/firstinstall doesn't exist
	if(!file_exists(get_fn_dir("var")."/firstinstall")) {
		?><div id="FN-setup">
		<h2><?php echo _FN_SETUP_NOFIRSTINSTALL?></h2>
		</div>
		<div id="FN-setup-footer">
		<input type="button" class="FN-link" value="<?php echo _FINDH?>" onclick="window.location='index.php'" />
		</div><?php
	} else {
		// total number of script steps
		$tot_steps = (is_writable("config.php") AND is_writable("./")) ? (5) : (4);
		if(check_var($step, "digit") AND $step>=1 AND $step<=5) {
			switch($step) {
				// choose Flatnuke language
				default	: fn_choose_language();		break;
				// check for right permissions
				case 2	: fn_install_checks();		break;
				// register first user as administrator
				case 3	: fn_reg_admin();			break;
				// choose configuration (if config.php is writeable)
				case 4	: if(is_writable("config.php") AND is_writable("./")) {
							fn_main_config();
						  } else fn_ready_to_install();	break;
				// register administrator
				case 5	: fn_ready_to_install();	break;
			}
		} else {
			$step = 1;
			fn_choose_language();
		}
	}
?>
</div>
</div>
</body>
</html><?php
// End MAIN EXECUTION <--//


/**
 * Choose Flatnuke default language
 *
 * This form makes the user choose Flatnuke default language from a list.
 * The one proposed, is the default language of the browser in use.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 */
function fn_choose_language() {
	global $step, $tot_steps, $lang;
	$step = ($step==0) ? (1) : ($step);

	?><form name='f' action='?step=<?php echo ($step+1)?>&amp;lang=<?php echo $lang?>' method='post'>
	<div id="FN-setup">
	<h1>FLATNUKE SETUP</h1>
	<h3>STEP <?php echo $step."/".$tot_steps?></h3>
	<h2><?php echo _LANG?>:</h2>
	<select name='lang' onchange="window.location='?lang='+ document.f.lang.options[document.f.lang.selectedIndex].value"><?php
		$files = glob("languages/*.php");
		if (!$files) $files = array(); // glob may returns boolean false instead of an empty array on some systems
		foreach ($files as $cf) {
			$sv = preg_replace('/.php$/i', '', basename($cf));
			$s = ($lang==$sv) ? ("selected=\"selected\"") : ('');
			echo "<option value=\"$sv\" $s>[$sv]</option>\n";
		}
	?>
	</select>
	<img src="images/languages/<?php echo "$lang.png"?>" style="vertical-align:middle" alt="flag" title="<?php echo $lang?>">
	</div>
	<div id="FN-setup-footer">
		<input type="submit" class="FN-link" value="&gt;&gt;" />
	</div>
	</form>
	<?php
}


/**
 * Check for Flatnuke permissions
 *
 * This form checks that all Flatnuke files have the right permissions to work.
 * At the end, all informations are summarized inside a table.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 */
function fn_install_checks() {
	global $step, $tot_steps, $lang;

	$welcome_sequence = array(
		'fn_test_firstinstall',
		'fn_test_var',
		'fn_test_sections',
		'fn_test_news',
	);

	?>
	<div id="FN-setup">
	<h1>FLATNUKE SETUP</h1>
	<h3>STEP <?php echo $step."/".$tot_steps?></h3>
	<h2><?php echo _FN_SETUP_CHECKPERMISSIONS?>:</h2>
	<table><?php
		$result_arr = array();
		for($i=0; $i<count($welcome_sequence); $i++) {
			array_push($result_arr, $welcome_sequence[$i]());
		}
		$sum = 0;
		$print_err = array();
		for($i=0; $i<count($welcome_sequence); $i++) {
			echo "<tr>";
			echo "\n<td>".$result_arr[$i][0]."</td>";
			if($result_arr[$i][1]==0) {
				echo "\n<td class='FN-control-ok'>OK</td>";
			} else {
				echo "\n<td class='FN-control-no'>"._ERROR."</td>";
				$sum++;
				array_push($print_err, $result_arr[$i][2]);
			}
			echo "</tr>";
		}
		//check if root dir is writable
		if (!is_writable(".")){
			echo "<tr>";
			echo "\n<td>"._FN_SETUP_PERMISSIONS." /</td>";
				echo "\n<td class='FN-control-warning'>NO</td>";
			echo "</tr>";
		}
		//check if file config.php is writable
		if (!is_writable("config.php")){
			echo "<tr>";
			echo "\n<td>"._FN_SETUP_PERMISSIONS." config.php</td>";
				echo "\n<td class='FN-control-warning'>NO</td>";
			echo "</tr>";
		}
		//check if download dir is writable
		if (!is_writable("download/")){
			echo "<tr>";
			echo "\n<td>"._FN_SETUP_PERMISSIONS." download/</td>";
				echo "\n<td class='FN-control-warning'>NO</td>";
			echo "</tr>";
		}
		//check if fd+ config file is writable
		if (!is_writable("download/fdconfig.php")){
			echo "<tr>";
			echo "\n<td>"._FN_SETUP_PERMISSIONS." download/fdconfig.php</td>";
				echo "\n<td class='FN-control-warning'>NO</td>";
			echo "</tr>";
		}

	?></table>
	<p><?php echo _FN_SETUP_RESULT?>:
	<?php
	if($sum==0) {
		?><b class="FN-control-ok">OK</b></p></div>
		<div id="FN-setup-footer">
		<input type='button' class="FN-link" value='&lt;&lt;' onclick="window.location='?step=<?php echo $step-1?>&amp;lang=<?php echo $lang?>';" />&nbsp;
		<input type='button' class="FN-link" value='&gt;&gt;' onclick="window.location='?step=<?php echo $step+1?>&amp;lang=<?php echo $lang?>';" />
		</div><?php
	} else {
		?><b class="FN-control-no"><?php echo _ERROR?></b></p><?php
		foreach($print_err as $text_err) {
			echo "<small>".$text_err."</small><br>";
		}
		?></div><div id="FN-setup-footer">
		<input type='button' class="FN-link" value='&lt;&lt;' onclick="window.location='?step=<?php echo $step-1?>&amp;lang=<?php echo $lang?>';" />&nbsp;
		<input type='button' class="FN-link" value='<?php echo _FN_SETUP_RELOAD?>' onclick="window.location=''" />
		</div><?php
	}
}

/**
 * Check /var/firstinstall permissions
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 *
 * @return array $result(title, 0|1, text_error)
 */
function fn_test_firstinstall() {
	$this_check = _FN_SETUP_PERMISSIONS." '".get_fn_dir("var")."/firstinstall'";
	if(file_exists(get_fn_dir("var")."/firstinstall")) {
		if(is_writable(get_fn_dir("var")."/firstinstall")) {
			$result = array($this_check, 0, "");
		} else {
			$result = array($this_check, 1, _FNCC_WARNINGRIGHTS);
		}
	} else {
		$result = array($this_check, 1, "'/".get_fn_dir("var")."/firstinstall' "._DOESNTEXISTS);
	}
	return $result;
}

/**
 * Check var directory permissions
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 *
 * @return array $result(title, 0|1, text_error)
 */
function fn_test_var() {
	$this_check = _FN_SETUP_PERMISSIONS." '".get_fn_dir("var")."'";
	$fp = @fopen(get_fn_dir("var")."/deleteme","w+");
	if($fp != null) {
		fclose($fp);
		unlink(get_fn_dir("var")."/deleteme");
		$result = array($this_check, 0, "");
	} else {
		$result = array($this_check, 1, _FNCC_WARNINGRIGHTS);
	}
	return $result;
}

/**
 * Check news directory permissions
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 *
 * @return array $result(title, 0|1, text_error)
 */
function fn_test_news() {
	$this_check = _FN_SETUP_PERMISSIONS." '".get_fn_dir("sections")."/none_News/'";
	$fp = @fopen(get_fn_dir("sections")."/none_News/deleteme","w+");
	if($fp != null) {
		fclose($fp);
		unlink(get_fn_dir("sections")."/none_News/deleteme");
		$result = array($this_check, 0, "");
	} else {
		$result = array($this_check, 1, _FNCC_WARNINGRIGHTS);
	}
	return $result;
}

/**
 * Check sections directory permissions
 *
 * @author Aldo Boccaccy (from code by Marco Segato)
 *
 * @return array $result(title, 0|1, text_error)
 */
function fn_test_sections() {
	$this_check = _FN_SETUP_PERMISSIONS." '".get_fn_dir("sections")."/'";
	$fp = @fopen(get_fn_dir("sections")."/deleteme","w+");
	if($fp != null) {
		fclose($fp);
		unlink(get_fn_dir("sections")."/deleteme");
		$result = array($this_check, 0, "");
	} else {
		$result = array($this_check, 1, _FNCC_WARNINGRIGHTS);
	}
	return $result;
}

/**
 * Register administrator profile
 *
 * After updating the new configuration chosen by the user, this form
 * ends the installation, and tells the user to register administrator profile.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 */
function fn_reg_admin() {
	global $step, $tot_steps, $lang;
	?>
	<script type="text/javascript">
	function validate()
		{
			if(document.getElementsByName('nome')[0].value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._NOMEUTENTE?>');
					document.getElementsByName('nome')[0].focus();
					document.getElementsByName('reregpass')[0].value='';
					return false;
				}
			else if(document.getElementsByName('regpass')[0].value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._PASSWORD?>');
					document.getElementsByName('regpass')[0].focus();
					document.getElementsByName('regpass')[0].value='';
					return false;
				}
			else if(document.getElementsByName('regpass')[0].value!=
				document.getElementsByName('reregpass')[0].value)
				{
					alert('<?php echo _PASSERR?>');
					document.getElementsByName('reregpass')[0].focus();
					document.getElementsByName('reregpass')[0].value='';
					return false;
				}
			else return true;
		}
	</script>
	<form name='f' action='?step=<?php echo ($step+1)?>&amp;lang=<?php echo $lang?>' method='post' onsubmit="return validate()">
	<div id="FN-setup">
	<h1>FLATNUKE SETUP</h1>
	<h3>STEP <?php echo $step."/".$tot_steps?></h3>
	<h2><?php echo _FN_SETUP_ADMINPROFILE?>:</h2>
	<p class="p"><?php echo _FN_SETUP_ADMINTXT?></p>
	<table>
	<tr>
		<td>* <label for="nome"><b><?php echo _NOMEUTENTE?></b></label></td>
		<td><input name="nome" type="text" id="nome"></td>
	</tr>
	<tr>
		<td>* <label for="regpass"><b><?php echo _PASSWORD?></b></label></td>
		<td><input name="regpass" type="password" id="regpass"></td>
	</tr>
	<tr>
		<td>*&nbsp;<label for="reregpass"><b><?php echo _REPEATPASSWORD?></b></label></td>
		<td><input name="reregpass" type="password" id="reregpass"></td>
	</tr>
	<tr>
		<td>&nbsp;&nbsp;<label for="mail"><b>Email</b></label></td>
		<td><input name="email" type="text" id="email"></td>
	</tr>
	<tr>
		<td><label for="avatar"><b><?php echo _FAVAT?></b></label></td>
		<td style="text-align:center">
			<img name="avatar_img" src="forum/images/blank.png" alt="avatar" style="max-width:120px;border:0" id="avatar_img" />
			<br>
			<select name="avatar" id="avatar" onchange='document.avatar_img.src="forum/images/"+this.options[this.selectedIndex].value'>
			<option value="">----</option><?php
				$modlist = array();
				$handle = opendir('forum/images');
				while ($file = readdir($handle)) {
					if (!( $file=="." or $file==".." )) {
						array_push($modlist, $file);
					}
				}
				closedir($handle);
				if(count($modlist)>0)
					sort($modlist);
				for ($i=0; $i < sizeof($modlist); $i++) {
					echo "<option value=\"$modlist[$i]\">$modlist[$i]</option>\n";
				}
			?></select>
		</td>
	</tr>
	<tr>
	<td colspan="2">
	<?php echo _FAVATREM?>:<br>
	<input style="width: 100%" type="text" name="url_avatar" />
	<br><br><?php echo _FCAMPI?></td>
	</tr>
	</table>
	</div>
	<div id="FN-setup-footer">
	<input type='button' class='FN-link' value='&lt;&lt;' onclick="window.location='?step=<?php echo $step-1?>&amp;lang=<?php echo $lang?>';" />&nbsp;
	<input type='submit' class='FN-link' value='&gt;&gt;' />
	</div>
	</form>
	<?php
}


/**
 * Choose Flatnuke default configuration
 *
 * After creating the new administration profile, this form makes
 * the user choose Flatnuke default configuration, writing down
 * the most important paramethers.
 * Defult values are proposed from Flatnuke standard configuration.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 */
function fn_main_config() {
	global $step, $tot_steps, $lang;
	global $sitename, $sitedescription, $keywords, $theme;

	$nome    = getparam("nome",    PAR_POST, SAN_FLAT);
	$regpass = getparam("regpass", PAR_POST, SAN_FLAT);
	$reregpass = getparam("reregpass", PAR_POST, SAN_FLAT);
	$avatar  = getparam("avatar",  PAR_POST, SAN_FLAT);
	$email  = getparam("email",  PAR_POST, SAN_FLAT);
	$url_avatar = getparam("url_avatar", PAR_POST, SAN_FLAT);
	// create admin profile
	if($nome!="" AND $regpass!="" AND $reregpass!="") {
		if ($regpass==$reregpass){
			$user_array['name'] = strip_tags($nome);
			$user_array['password'] = md5($regpass);
			$user_array['mail'] = strip_tags($email);
			if ($url_avatar!="" AND preg_match("/(jpg|jpeg|png|gif)/i",get_file_extension($url_avatar)))
				$user_array['avatar'] = strip_tags($url_avatar);
			else $user_array['avatar'] = "images/".strip_tags($avatar);
			$user_array['level'] = "10";
			save_user_profile($nome, $user_array);
		}
	}
	?>
	<form name='f' action='?step=<?php echo ($step+1)?>&amp;lang=<?php echo $lang?>' method='post'>
	<div id="FN-setup">
	<h1>FLATNUKE SETUP</h1>
	<h3>STEP <?php echo $step."/".$tot_steps?></h3>
	<h2><?php echo _FNCC_DESGENERALCONF?>:</h2>
	<table>
		<tr><td style="width:25%"><?php echo _FNCC_CONFSITENAME?>&nbsp;</td><td><input style="width:100%" type="text" name="sitename" value="<?php echo $sitename?>" /></td></tr>
		<tr><td><?php echo _FNCC_CONFSITEDESCRIPTION?>&nbsp;</td><td><input style="width:100%" type="text" name="sitedescription" value="<?php echo $sitedescription?>" /></td></tr>
		<tr><td><?php echo _FNCC_CONFKEYWORDS?>&nbsp;</td><td><textarea style="width:100%" cols="30" rows="14" name="keywords"><?php echo $keywords?></textarea></td></tr>
		<tr><td><?php echo _FNCC_CONFADMINMAIL?>&nbsp;</td><td><input style="width:100%" type="text" name="emailadmin" value="<?php echo $email?>" /><br><i>(<?php echo _FNCC_CONFADMINMAIL_CONTACT;?>)</i></td></tr>
		<tr>
			<td><?php echo _FNCC_CONFTHEME?>&nbsp;</td>
			<td>
				<select name='theme' onchange="changepic(document.f.theme.options[document.f.theme.selectedIndex].text)"><?php
					$handle = opendir(get_fn_dir('themes'));
					$list_themes = array ();
					while ($file = readdir($handle)) {
						if (!($file=="." OR $file=="..") AND is_dir(get_fn_dir('themes')."/$file")) {
							if (file_exists(get_fn_dir('themes')."/$file/theme.php")) {
								array_push($list_themes, $file);
							}
						}
					}
					closedir($handle);
					foreach ($list_themes as $theme_) {
						echo "\n<option ";
						if ($theme == $theme_) {
							echo " selected='selected' ";
						}
						echo ">$theme_</option>";
					}
				?></select><?php
				if(file_exists(get_fn_dir('themes')."/$theme/screenshot.png")) {
					echo "\n<br>\n<img name=\"themeimg\" id=\"themeimg\" src='".get_fn_dir('themes')."/$theme/screenshot.png"."' alt='screenshot' title='screenshot'>";
				} else {
					echo "\n<br>\n<img name=\"themeimg\" id=\"themeimg\" src='".get_fn_dir('sections')."/none_Admin/none_images/no_preview.png"."' alt='screenshot' title='screenshot'>";
				}
			?></td>
		</tr>
	</table>
	</div>
	<div id="FN-setup-footer">
	<input type='button' class='FN-link' value='&lt;&lt;' onclick="window.location='?step=<?php echo $step-1?>&amp;lang=<?php echo $lang?>';" />&nbsp;
	<input type='submit' class='FN-link' value='&gt;&gt;' />
	</div>
	</form>
	<SCRIPT>


function changepic(theme) {
	document.getElementById('themeimg').src='<?php echo get_fn_dir('themes');?>/' + theme + '/screenshot.png';
}
// End -->
</SCRIPT><?php
}


/**
 * Write Flatnuke default configuration
 *
 * After updating the new configuration chosen by the user, this form
 * ends the installation, and tells the user to register administrator profile.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 */
function fn_ready_to_install() {
	global $step, $tot_steps, $lang;
	$ip = "IP unknown";

	$nome    = getparam("nome",    PAR_POST, SAN_FLAT);
	$regpass = getparam("regpass", PAR_POST, SAN_FLAT);
	$avatar  = getparam("avatar",  PAR_POST, SAN_FLAT);

	// create admin profile
	if($nome!="" AND $regpass!="") {
		$user_array['name'] = $nome;
		$user_array['password'] = md5($regpass);
		$user_array['avatar'] = "images/".$avatar;
		$user_array['level'] = "10";
		$user_array['regdate'] = time();
		save_user_profile($nome, $user_array);
	}

	$conf_file = "config.php";
	$sitename        = getparam("sitename",        PAR_POST, SAN_FLAT);
	$sitedescription = getparam("sitedescription", PAR_POST, SAN_FLAT);
	$keywords        = getparam("keywords",        PAR_POST, SAN_FLAT);
	$emailadmin      = getparam("emailadmin",      PAR_POST, SAN_FLAT);
	$theme           = getparam("theme",           PAR_POST, SAN_FLAT);

	// update configuration file
	if($sitename!="" AND $theme!="") {
		$file = file($conf_file);
		$new_file = "";
		for($id=0;$id<count($file);$id++) {
			if(preg_match("/^\\$"."sitename/i", $file[$id])) {
				$new_file .= "$"."sitename = \"".stripslashes($sitename)."\";\n";
			} elseif(preg_match("/^\\$"."sitedescription/i", $file[$id])) {
				$new_file .= "$"."sitedescription = \"".$sitedescription."\";\n";
			} elseif(preg_match("/^\\$"."keywords/i", $file[$id])) {
				$new_file .= "$"."keywords = \"".$keywords."\";\n";
			} elseif(preg_match("/^\\$"."admin_mail/i", $file[$id])) {
				$new_file .= "$"."admin_mail = \"".$emailadmin."\";\n";
			} elseif(preg_match("/^\\$"."theme/i", $file[$id])) {
				$new_file .= "$"."theme = \"".$theme."\";\n";
			} else $new_file .= $file[$id];
		}
		// write the new file
		fnwrite($conf_file, $new_file, "wb", array("nonull"));
	}

	// delete /var/firstinstall to end this installation script
	@unlink(get_fn_dir("var")."/firstinstall");
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	fnlog("Site maintenance", "$ip||".get_username()."||Installation completed.");

	?>
	<div id="FN-setup">
	<h1>FLATNUKE SETUP</h1>
	<h3>STEP <?php echo $step."/".$tot_steps?></h3>
	<h2><?php echo _FN_SETUP_END?></h2>
	</div>
	<div id="FN-setup-footer">
	<input type='button' class='FN-link' value='&lt;&lt;' onclick="window.location='?step=<?php echo $step-1?>&amp;lang=<?php echo $lang?>';" />&nbsp;
	<input type='button' class='FN-link' value='<?php echo _FINDH?>' onclick="window.location='index.php'" />
	</div>
	<?php
}

?>
