<?php

/************************************************************************/
/* FlatNuke - Flat Text Based Content Management System                 */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2003 by Simone Vellei                                  */
/* http://flatnuke.sourceforge.net                                      */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

if (preg_match("/login.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    die();
}


// Security checks
$req = getparam("REQUEST_URI", PAR_SERVER, SAN_FLAT);
if(strstr($req,"myforum="))
	die(_NONPUOI);

// user is not logged
if(_FN_IS_GUEST) {
	?>
	<script type="text/javascript">
	function validatelogin()
		{
			if(document.getElementsByName('nome')[0].value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._NOMEUTENTE?>');
					document.getElementsByName('nome')[0].focus();
					document.getElementsByName('nome')[0].value='';
					return false;
				}
			else if(document.getElementsByName('logpassword')[0].value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._PASSWORD?>');
					document.getElementsByName('logpassword')[0].focus();
					document.getElementsByName('logpassword')[0].value='';
					return false;
				}
			else return true;
		}
	</script>

	<div style="text-align:center">
	<form action="index.php?mod=none_Login" method="post" onsubmit="return validatelogin()">
	<input type="hidden" name="action" value="login" />
	<input type="hidden" name="from" value="home" />
	<label for="username"><?php echo _NOMEUTENTE ?>:</label><br>
	<?php //ho dovuto settare un timeout a causa di Firefox che altrimenti reimposta il valore
	  //di default "Username" prima dell'invio
	?>
	<input type="text" name="nome" id="username" style="color:gray" value="Username" onfocus="
	if (this.value=='Username'){this.value='';}
	if (document.getElementsByName('logpassword')[0].value='********'){document.getElementsByName('logpassword')[0].value=''}"
	onblur="if(this.value==''){setTimeout('document.getElementsByName(\'nome\')[0].value=\'Username\'',2000);}
<?php 	//rimosso perché creava problemi quando, dopo aver scritto il nome utente, si usciva dalla
	//casella di testo (rimetteva i pallini al posto della password)
	//if(document.getElementsByName('logpassword')[0].value==''){setTimeout('document.getElementsByName(\'logpassword\')[0].value=\'********\'',2000);}
?>
	" /><br>
	<label for="password"><?php echo _PASSWORD ?>:</label><br>
	<input name="logpassword" type="password" id="password" style="color:gray;" value="********" onfocus="javascript:this.value='';" /><br><?php
	// remember login checkbox
	global $remember_login;
	if($remember_login==1) {
		echo "<div style=\"margin:1em;\"><label for=\"rememberlogin\">"._REMEMBERLOGIN."</label>";
		echo "<input type=\"checkbox\" alt=\"remember_login\" name=\"rememberlogin\" id=\"rememberlogin\" /><br>";
		echo "</div>";
	} else echo "<br>";
	// login button
	?><input type="submit" value="<?php echo _LOGIN ?>" />
	</form><?php
	// link to register
	global $reguser;
	if ($reguser=="1" or $reguser=="2"){
		echo _NONREG
		?><br><a href="index.php?mod=none_Login&amp;action=visreg" title="<?php echo _REGORA?>"><b><?php echo _REGORA?></b></a>
                <br><a href="index.php?mod=none_Login&amp;action=passwordlost" title="<?php echo _NEWPWDSTRING?>"><b><?php echo _NEWPWDSTRING?></b></a><?php
	}
	// load external code
	echo "<div style='padding-left:0.5em;'>";
	load_php_code("include/blocks/login");
	echo "</div>";
	?></div><?php
}
// user is logged
elseif(_FN_IS_USER OR _FN_IS_ADMIN) {
	// print user name
	$username = _FN_USERNAME;
	print _BENVENUTO." <b><a href='index.php?mod=none_Login&amp;action=viewprofile&amp;user=$username' title=\""._VIEW_USERPROFILE."\">$username</a></b>!<br><br>";
	print "<div class=\"centeredDiv\">";
	print "<a href='index.php?mod=none_Login&amp;action=viewprofile&amp;user=$username' title=\""._VIEW_USERPROFILE."\">";
	// print avatar
	$img = _FN_USERAVATAR;
	if($img!="") {
		if(!stristr($img,"http://"))
			echo "<img src='forum/$img' alt='$username' style='max-width:95; border:0%' />";
		else echo "<img src='$img' alt='$username' style='max-width:95%; border:0' />";
	}
	else echo "<img src='forum/images/blank.png' alt='$username' style='max-width:95%; border:0' />";
	print "</a></div><br>";
	// print user level
	$level = _FN_USERLEVEL;
	if(!file_exists("themes/$theme/images/level_y.gif") OR !file_exists("themes/$theme/images/level_n.gif")) {
		$level_img_y = "images/useronline/level_y.gif";
		$level_img_n = "images/useronline/level_n.gif";
	} else {
		$level_img_y = "themes/$theme/images/level_y.gif";
		$level_img_n = "themes/$theme/images/level_n.gif";
	}
	print "<div style='position:relative;float:left;width:30px;'>0</div>";
	print "<div style='position:relative;float:right;width:30px;text-align:right;'>10</div>";
	print "<div style='position:relative;margin-left:0px;margin-right:0px;text-align:center;'><b>"._LEVEL." $level</b></div>";
	print "<div class=\"centeredDiv\">";
	print "<hr>";
	for($i=0; $i<$level; $i++) {
		print "<img src='$level_img_y' alt='level' />";
	}
	for($j=$i; $j<10; $j++) {
		print "<img src='$level_img_n' alt='level' />";
	}
	print "<hr>";
	print "</div>";
	if (_FN_IS_NEWS_MODERATOR and !_FN_IS_ADMIN){
		echo "<div style=\"padding-left:0.5em;\">";
		global $home_section;
		if ($home_section==""){
			echo "&#187;&nbsp;<a href=\"index.php?action=addnewsinterface\" title=\""._ADDNEWS."\">". _ADDNEWS." (Home&nbsp;page)</a>";
		}
			include_once("flatnews/include/news_functions.php");
		$proposednewsarray=load_proposed_news_list();
		if(count($proposednewsarray)>0) {
			$modstring="";
			if (_FN_MOD=="")
				$modstring = "mod=none_News&amp;";
			else $modstring = "mod="._FN_MOD."&amp;";
			?><br>&#187;&nbsp;<b><a href="index.php?mod=none_News&amp;action=manageproposednews" title="<?php echo _SEGNNOTIZIE; ?>"><?php echo _SEGNNOTIZIE ?> (<?php echo count($proposednewsarray)?>)</a></b><?php
		}
		?>
		</div>
		<?php
	}
	// administrator panel
	if(_FN_IS_ADMIN) {
		?><div style="padding-left:0.5em;">
		&#187;&nbsp;<a href="index.php?mod=none_Admin" title="<?php echo _MANAGESITE; ?>"><?php echo _MANAGESITE ?></a>
		<?php
		global $home_section;
		if ($home_section==""){
			echo "<br>&#187;&nbsp;<a href=\"index.php?action=addnewsinterface\" title=\""._ADDNEWS."\">". _ADDNEWS." (Home&nbsp;page)</a>";
		}
		include_once("flatnews/include/news_functions.php");
		$proposednewsarray=load_proposed_news_list();
		if(count($proposednewsarray)>0) {
			$modstring="";
			if (_FN_MOD=="")
				$modstring = "mod=none_News&amp;";
			else $modstring = "mod="._FN_MOD."&amp;";
			?><br>&#187;&nbsp;<b><a href="index.php?mod=none_News&amp;action=manageproposednews" title="<?php echo _SEGNNOTIZIE; ?>"><?php echo _SEGNNOTIZIE ?> (<?php echo count($proposednewsarray)?>)</a></b><?php
		}
		echo "<br>&#187;&nbsp;<a href=\"index.php?mod=fnnewsectinterface\" title=\""._FNCREATESECTION."\">"._FNCREATESECTION." (Home page)</a>";
		?></div>
		<hr style="text-align:center; width:100%" /><?php
	}
	// load external code
	echo "<div style='padding-left:0.5em;'>";
	load_php_code("include/blocks/login");
	echo "</div>";
	// logout
	echo "<div style=\"text-align:right\"><a href='index.php?mod=none_Login&amp;action=logout&amp;from=home' title=\""._LOGOUT."\"><b>"._LOGOUT."</b></a></div>";
}
else echo "Cookie mismatch, please <a href='index.php?mod=none_Login&amp;action=logout&amp;from=home'>delete your cookies!</a>";
?>
