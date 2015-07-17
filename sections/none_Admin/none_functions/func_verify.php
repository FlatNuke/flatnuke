<?php

/*
 * Save Flatnuke general configuration
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20090918
 */
function fncc_modgeneralconf() {
	// security checks
	$conf_file         = getparam("conf_file",        PAR_POST, SAN_FLAT);
	$sitename          = getparam("sitename",         PAR_POST, SAN_FLAT);
	$sitedescription   = getparam("sitedescription",  PAR_POST, SAN_FLAT);
	$keywords          = getparam("keywords",         PAR_POST, SAN_FLAT);
	$theme             = getparam("theme",            PAR_POST, SAN_FLAT);
	$newspp            = getparam("newspp",           PAR_POST, SAN_FLAT);
	$admin             = getparam("admin",            PAR_POST, SAN_FLAT);
	$admin_mail        = getparam("admin_mail",       PAR_POST, SAN_FLAT);
	$lang              = getparam("lang",             PAR_POST, SAN_FLAT);
	$reguser           = getparam("reguser",          PAR_POST, SAN_FLAT);
	$guestnews         = getparam("guestnews",        PAR_POST, SAN_FLAT);
	$guestcomment      = getparam("guestcomment",     PAR_POST, SAN_FLAT);
	$remember_login    = getparam("remember_login",   PAR_POST, SAN_FLAT);
	$fuso_orario       = getparam("fuso_orario",      PAR_POST, SAN_FLAT);
	$maintenance       = getparam("maintenance",      PAR_POST, SAN_FLAT);
	$home_section      = getparam("home_section",     PAR_POST, SAN_FLAT);
	$topicperpage      = getparam("topicperpage",     PAR_POST, SAN_FLAT);
	$postperpage       = getparam("postperpage",      PAR_POST, SAN_FLAT);
	$memberperpage     = getparam("memberperpage",    PAR_POST, SAN_FLAT);
	$forum_moderators  = getparam("forum_moderators", PAR_POST, SAN_FLAT);
	$news_editor       = getparam("newseditor",       PAR_POST, SAN_FLAT);
	$news_moderators   = getparam("news_moderators",  PAR_POST, SAN_FLAT);
	// build new file
	$file = file($conf_file);
	$new_file = "";
	for($id=0;$id<count($file);$id++) {
		//ALDO BOCCACCI
		//Fixed for non regular lines
		$conf_line = trim($file[$id]);
		//remove comments before value declaration
		$conf_line = trim(preg_replace("/^\/\*.*\*\//i","",$conf_line));
		if(preg_match("/^\\$"."sitename/i",$conf_line)) {
			$new_file .= "$"."sitename = \"".stripslashes($sitename)."\";\n";
		} elseif(preg_match("/^\\$"."sitedescription/i",$conf_line)) {
			$new_file .= "$"."sitedescription = \"".$sitedescription."\";\n";
		} elseif(preg_match("/^\\$"."keywords/i",$conf_line)) {
			$new_file .= "$"."keywords = \"".$keywords."\";\n";
		} elseif(preg_match("/^\\$"."theme/i",$conf_line)) {
			$new_file .= "$"."theme = \"".$theme."\";\n";
		} elseif(preg_match("/^\\$"."newspp/i",$conf_line)) {
			$new_file .= "$"."newspp = ".$newspp.";\n";
		} elseif(preg_match("/^\\$"."admin.*=/i",$conf_line) AND !preg_match("/\\$"."admin_/i",$conf_line)) {
			$new_file .= "$"."admin = \"".$admin."\";\n";
		} elseif(preg_match("/^\\$"."admin_mail/i",$conf_line)) {
			$new_file .= "$"."admin_mail = \"".$admin_mail."\";\n";
		} elseif(preg_match("/^\\$"."lang/i",$conf_line)) {
			$new_file .= "$"."lang = \"".$lang."\";\n";
		} elseif(preg_match("/^\\$"."reguser/i",$conf_line)) {
			$new_file .= "$"."reguser = ".$reguser.";\n";
		} elseif(preg_match("/^\\$"."guestnews/i",$conf_line)) {
			$new_file .= "$"."guestnews = ".$guestnews.";\n";
		} elseif(preg_match("/^\\$"."guestcomment/i",$conf_line)) {
			$new_file .= "$"."guestcomment = ".$guestcomment.";\n";
		} elseif(preg_match("/^\\$"."remember_login/i",$conf_line)) {
			$new_file .= "$"."remember_login = ".$remember_login.";\n";
		} elseif(preg_match("/^\\$"."fuso_orario/i",$conf_line)) {
			$new_file .= "$"."fuso_orario = ".$fuso_orario.";\n";
		} elseif(preg_match("/^\\$"."maintenance/i",$conf_line)) {
			$new_file .= "$"."maintenance = ".$maintenance.";\n";
		} elseif(preg_match("/^\\$"."home_section/i",$conf_line)) {
			$new_file .= "$"."home_section = \"".$home_section."\";\n";
		} elseif(preg_match("/^\\$"."topicperpage/i",$conf_line)) {
			$new_file .= "$"."topicperpage = ".$topicperpage.";\n";
		} elseif(preg_match("/^\\$"."postperpage/i",$conf_line)) {
			$new_file .= "$"."postperpage = ".$postperpage.";\n";
		} elseif(preg_match("/^\\$"."memberperpage/i",$conf_line)) {
			$new_file .= "$"."memberperpage = ".$memberperpage.";\n";
		} elseif(preg_match("/^\\$"."forum_moderators/i",$conf_line)) {
			$new_file .= "$"."forum_moderators = \"".$forum_moderators."\";\n";
		} elseif(preg_match("/^\\$"."news_editor/i",$conf_line)) {
			$new_file .= "$"."news_editor = \"".$news_editor."\";\n";
		} elseif(preg_match("/^\\$"."news_moderators/i",$conf_line)) {
			$new_file .= "$"."news_moderators = \"".$news_moderators."\";\n";
		} else $new_file .= $file[$id];
	}
	// write the new file
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	if(is_admin()) {
		fnwrite($conf_file, $new_file, "wb", array("nonull"));
		fnlog("Site maintenance", "$ip||".get_username()."||Configuration changed.");
	} else {
		fnlog("Security", "$ip||".get_username()."||Tried to change site configuration.");
	}
}

/*
 * Save standard text file
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20070724
 */
function fncc_modbodyfile() {
	// security checks
	$conf_file = getparam("conf_file", PAR_POST, SAN_FLAT);
	$conf_body = getparam("conf_body", PAR_POST, SAN_NULL);
	// write the new file
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	if(is_admin()) {
		fnwrite($conf_file, stripslashes($conf_body), "wb", array("nonull"));
		fnlog("Site maintenance", "$ip||".get_username()."||File $conf_file changed.");
	} else {
		fnlog("Security", "$ip||".get_username()."||Tried to save the file $conf_file.");
	}
}

/*
 * Save poll informations
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20070718
 */
function fncc_savepoll() {
	// security checks
	$fp_stato      = getparam("fp_stato",      PAR_POST, SAN_FLAT);
	$salva_domanda = getparam("salva_domanda", PAR_POST, SAN_FLAT);
	$array_opzioni = array();
	$array_opzioni = $_POST['salva_opzioni'];	//print_r($array_opzioni);	//-> TEST
	$array_voti    = array();
	$array_voti    = $_POST['salva_voti'];	//print_r($array_voti);	//-> TEST
	// get poll configuration
	require (get_fn_dir("sections")."/none_Sondaggio/config.php");
	$file_xml = get_file($sondaggio_file_dati);
	$opzioni  = get_xml_element("opzioni",$file_xml);
	$opzione  = get_xml_array("opzione",$opzioni);
	$commenti = get_xml_element("commenti",$file_xml);
	$commento = get_xml_array("commento",$commenti);
	// build new file
	$file_xml = "<?xml version='1.0' encoding='UTF-8'?>\n<sondaggio>\n";
	$file_xml .= "\t<attivo>$fp_stato</attivo>\n";
	$file_xml .= "\t<domanda>$salva_domanda</domanda>\n";
	$file_xml .= "\t<opzioni>\n";
	for($i=0; $i<count($array_opzioni); $i++) {
		if(!is_numeric($array_voti[$i])) {
			$array_voti[$i] = "0";
		}
		if($array_opzioni[$i]!="" AND $array_voti[$i]!="") {
			$file_xml .= "\t\t<opzione>\n";
			$file_xml .= "\t\t\t<testo>".$array_opzioni[$i]."</testo>\n";
			$file_xml .= "\t\t\t<voto>".$array_voti[$i]."</voto>\n";
			$file_xml .= "\t\t</opzione>\n";
		}
	}
	$file_xml .= "\t</opzioni>\n";
	if($commenti != "") {
		$file_xml .= "\t<commenti>\n";
		for($j=0; $j<count($commento); $j++) {
			$file_xml .= "\t\t<commento>\n";
			$file_xml .= "\t\t\t<by>".get_xml_element("by",$commento[$j])."</by>\n";
			$file_xml .= "\t\t\t<what>".get_xml_element("what",$commento[$j])."</what>\n";
			$file_xml .= "\t\t</commento>\n";
		}
		$file_xml .= "\t</commenti>\n";
	}
	$file_xml .= "</sondaggio>\n";
	// write the new file
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	if(is_admin()) {
		fnwrite($sondaggio_file_dati, stripslashes($file_xml), "w", array("nonull"));
		fnlog("Site maintenance", "$ip||".get_username()."||Poll $conf_file changed.");
	} else {
		fnlog("Security", "$ip||".get_username()."||Tried to change the poll.");
	}
}

/*
 * Archive poll and build a new one
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20061101
 */
function fncc_archpoll() {
	// get poll configuration
	require (get_fn_dir("sections")."/none_Sondaggio/config.php");
	// save actual poll in the archive directory
	copy($sondaggio_file_dati, $percorso_vecchi."/".time().".xml");
	// build new poll
	$new_poll = "<?xml version='1.0' encoding='UTF-8'?>\n<sondaggio>\n\t<attivo>n</attivo>\n\t<domanda>"._FP_NUOVOSONDAGGIO."</domanda>\n\t<opzioni>\n";
	for($i=1; $i<4; $i++) {
		$new_poll .= "\t\t<opzione>\n\t\t\t<testo>"._FP_OPZIONE."$i</testo>\n\t\t\t<voto>$i</voto>\n\t\t</opzione>\n";
	}
	$new_poll .= "\t</opzioni>\n</sondaggio>\n";
	// write the new file
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	if(is_admin()) {
		fnwrite($sondaggio_file_dati, stripslashes($new_poll), "w", array("nonull"));
		fnlog("Site maintenance", "$ip||".get_username()."||Poll $conf_file archived.");
	} else {
		fnlog("Security", "$ip||".get_username()."||Tried to archive the poll.");
	}
}

/*
 * Save FdPlus general configuration
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20080607
 */
function fncc_savedownconf() {
	// security checks
	$conf_file            = getparam("conf_file",            PAR_POST, SAN_FLAT);
	$extensions           = getparam("extensions",           PAR_POST, SAN_FLAT);
	$maxFileSize          = getparam("maxFileSize",          PAR_POST, SAN_FLAT);
	$icon_style           = getparam("icon_style",           PAR_POST, SAN_FLAT);
	$newfiletime          = getparam("newfiletime",          PAR_POST, SAN_FLAT);
	$automd5              = getparam("automd5",              PAR_POST, SAN_FLAT);
	$autosha1             = getparam("autosha1",             PAR_POST, SAN_FLAT);
	$showuploader         = getparam("showuploader",         PAR_POST, SAN_FLAT);
	$extsig               = getparam("extsig",               PAR_POST, SAN_FLAT);
	$extscreenshot        = getparam("extscreenshot",        PAR_POST, SAN_FLAT);
	$admins               = getparam("admins",               PAR_POST, SAN_FLAT);
	$enable_admin_options = getparam("enable_admin_options", PAR_POST, SAN_FLAT);
	$showdownloadlink     = getparam("showdownloadlink",     PAR_POST, SAN_FLAT);
	$overview_show_files  = getparam("overview_show_files",  PAR_POST, SAN_FLAT);
	$section_show_header  = getparam("section_show_header",  PAR_POST, SAN_FLAT);
	$defaultvoteon        = getparam("defaultvoteon",        PAR_POST, SAN_FLAT);
	$usermaxFileSize      = getparam("usermaxFileSize",      PAR_POST, SAN_FLAT);
	$userfilelimit        = getparam("userfilelimit",        PAR_POST, SAN_FLAT);
	$userwaitingfile      = getparam("userwaitingfile",      PAR_POST, SAN_FLAT);
	$userblacklist        = getparam("userblacklist",        PAR_POST, SAN_FLAT);
	$minlevel             = getparam("minlevel",             PAR_POST, SAN_FLAT);
	// build new file
	$file = file($conf_file);
	$new_file = "";
	for($id=0;$id<count($file);$id++) {
		//ALDO BOCCACCI
		//Fixed for non regular lines
		$conf_line = trim($file[$id]);
		if(preg_match("/^\\$"."extensions/i",$conf_line)) {
			$new_file .= "$"."extensions = \"".str_replace(" ", "", $extensions)."\";\n";
		} elseif(preg_match("/^\\$"."maxFileSize/i",$conf_line)) {
			$new_file .= "$"."maxFileSize = \"".$maxFileSize."\";\n";
		} elseif(preg_match("/^\\$"."icon_style/i",$conf_line)) {
			$new_file .= "$"."icon_style = \"".$icon_style."\";\n";
		} elseif(preg_match("/^\\$"."newfiletime/i",$conf_line)) {
			$new_file .= "$"."newfiletime = ".$newfiletime.";\n";
		} elseif(preg_match("/^\\$"."automd5/i",$conf_line)) {
			$new_file .= "$"."automd5 = ".$automd5.";\n";
		} elseif(preg_match("/^\\$"."autosha1/i",$conf_line)) {
			$new_file .= "$"."autosha1 = ".$autosha1.";\n";
		} elseif(preg_match("/^\\$"."showuploader/i",$conf_line)) {
			$new_file .= "$"."showuploader = \"".$showuploader."\";\n";
		} elseif(preg_match("/^\\$"."extsig/i",$conf_line)) {
			$new_file .= "$"."extsig = \"".$extsig."\";\n";
		} elseif(preg_match("/^\\$"."extscreenshot/i",$conf_line)) {
			$new_file .= "$"."extscreenshot = \"".$extscreenshot."\";\n";
		} elseif(preg_match("/^\\$"."admins/i",$conf_line)) {
			$new_file .= "$"."admins = \"".str_replace(" ", "", $admins)."\";\n";
		} elseif(preg_match("/^\\$"."enable_admin_options/i",$conf_line)) {
			$new_file .= "$"."enable_admin_options = \"".$enable_admin_options."\";\n";
		} elseif(preg_match("/^\\$"."showdownloadlink/i",$conf_line)) {
			$new_file .= "$"."showdownloadlink = \"".$showdownloadlink."\";\n";
		} elseif(preg_match("/^\\$"."overview_show_files/i",$conf_line)) {
			$new_file .= "$"."overview_show_files = \"".$overview_show_files."\";\n";
		} elseif(preg_match("/^\\$"."section_show_header/i",$conf_line)) {
			$new_file .= "$"."section_show_header = \"".$section_show_header."\";\n";
		} elseif(preg_match("/^\\$"."defaultvoteon/i",$conf_line)) {
			$new_file .= "$"."defaultvoteon = \"".$defaultvoteon."\";\n";
		} elseif(preg_match("/^\\$"."usermaxFileSize/i",$conf_line)) {
			$new_file .= "$"."usermaxFileSize = ".$usermaxFileSize.";\n";
		} elseif(preg_match("/^\\$"."userfilelimit/i",$conf_line)) {
			$new_file .= "$"."userfilelimit = \"".$userfilelimit."\";\n";
		} elseif(preg_match("/^\\$"."userwaitingfile/i",$conf_line)) {
			$new_file .= "$"."userwaitingfile = \"".$userwaitingfile."\";\n";
		} elseif(preg_match("/^\\$"."userblacklist/i",$conf_line)) {
			$new_file .= "$"."userblacklist = \"".str_replace(" ", "", $userblacklist)."\";\n";
		} elseif(preg_match("/^\\$"."minlevel/i",$conf_line)) {
			$new_file .= "$"."minlevel = \"".$minlevel."\";\n";
		} else $new_file .= $file[$id];
	}
	// write the new file
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	if(is_admin()) {
		fnwrite($conf_file, $new_file, "wb", array("nonull"));
		fnlog("Site maintenance", "$ip||".get_username()."||FdPlus configuration changed.");
	} else {
		fnlog("Security", "$ip||".get_username()."||Tried to change FdPlus configuration.");
	}
}

/*
 * Save new user profile
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20070718
 */
function fncc_saveprofile() {
	// security checks
	$nome = getparam("nome", PAR_POST, SAN_FLAT);
	// build array with user infos
	$data = array();
	$data['password']   = md5($_POST['regpass']);
	$data['name']       = $_POST['anag'];
	$data['mail']       = $_POST['email'];
	//may be not inizialized
	$data['hiddenmail'] = $_POST['hiddenmail'];
	$data['homepage']   = $_POST['homep'];
	$data['work']       = $_POST['prof'];
	$data['from']       = $_POST['prov'];
	$data['avatar']     = $_POST['ava'];
	$data['sign']       = $_POST['firma'];
	$data['level']      = $_POST['level'];
	$data['jabber']     = $_POST['jabber'];
	$data['skype']      = $_POST['skype'];
	$data['icq']        = $_POST['icq'];
	$data['msn']        = $_POST['msn'];
	$data['presentation'] = $_POST['presentation'];
	$data['regdate']    = time();
// 	$data['lasteditby'] = get_username();
	// manage avatar
	if(isset($_POST['url_avatar']) AND preg_match("/(jpg|jpeg|png|gif)/i",get_file_extension($_POST['url_avatar'])) ) {
		$data['avatar'] = $_POST['url_avatar'];
	} else $data['avatar'] = "images/".$data['avatar'];
	// save the new profile
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	if(is_admin()) {
		save_user_profile($nome, $data);
		fnlog("Site maintenance", "$ip||".get_username()."||New userprofile $nome registered by the administrator.");
	} else {
		fnlog("Security", "$ip||".get_username()."||Tried to register new $nome profile from Admin Panel.");
	}
}

/*
 * Delete a profile waiting for activation
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20070728
 */
function fncc_delwaiting() {
	// security checks
	$deluser = getparam("deluser", PAR_GET, SAN_FLAT);
	// write the new file
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	if(is_admin() AND file_exists(get_waiting_users_dir()."/$deluser.php")) {
		unlink(get_waiting_users_dir()."/$deluser.php");
		fnlog("Site maintenance", "$ip||".get_username()."||Deleted profile $deluser waiting for activation.");
	} else {
		fnlog("Security", "$ip||".get_username()."||Tried to delete the file $deluser.");
	}
}

/*
 * Update email address of a profile waiting for activation
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20070728
 */
function fncc_updatewaiting() {
	// security checks
	$regmail = getparam("regmail", PAR_POST, SAN_FLAT);
	$user    = getparam("user",    PAR_POST, SAN_FLAT);
	// update the file
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	if(is_admin() AND file_exists(get_waiting_users_dir()."/$user.php")) {
		if(check_mail($regmail)) {
			$user_xml = array();
			$user_xml = load_user_profile($user, 1);
			$user_xml['regmail'] = $regmail;
			save_user_profile($user, $user_xml, 1);
			fnlog("Site maintenance", "$ip||".get_username()."||Updated email address of profile $user waiting for activation.");
		} else {
			fnlog("Site maintenance", "$ip||".get_username()."||Can't update profile $user waiting for activation, email address is invalid.");
		}
	} else {
		fnlog("Security", "$ip||".get_username()."||Tried to update the profile $user waiting for activation.");
	}
}

/*
 * Send activation email to a profile waiting for activation
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20070728
 */
function fncc_sendactivation() {
	// security checks
	$mod     = getparam("mod",     PAR_POST, SAN_FLAT);
	$mail    = getparam("mail",    PAR_POST, SAN_FLAT);
	$user    = getparam("user",    PAR_POST, SAN_FLAT);
	$regcode = getparam("regcode", PAR_POST, SAN_FLAT);
	global $sitename;
	// write the new file
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	// build url for the activation
	$url_act = "http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."?mod=none_Login&action=activateuser&user=$user&regcode=$regcode";
	$url_act = str_replace(get_fn_dir("sections")."/$mod/section.php", "index.php", $url_act);
	if(is_admin() AND file_exists(get_waiting_users_dir()."/$user.php")) {
		$message = _IST_REG_MAIL."\n\n$url_act";
		if(mail($mail, _COMP_REG_MAIL." $sitename", $message,"FROM: $sitename <noreply@noreply>\r\nX-Mailer: Flatnuke on PHP/".phpversion())) {
			fnlog("Site maintenance", $ip."||".get_username()."||Activation mail sent for $user.");
		} else {
			echo "<p>"._ACTIVATIONMAILNOTSENT."</p>";
			fnlog("Site maintenance", $ip."||".get_username()."||Activation mail not sent for $user.");
		}
	} else {
		fnlog("Security", "$ip||".get_username()."||Tried to send activation code of the profile $user waiting for activation.");
	}
}

/*
 * Clean log file
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20070826
 */
function fncc_cleanlog() {
	// security checks
	$logfile = getparam("logfile", PAR_POST, SAN_FLAT);
	// write the new file
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	if(is_admin()) {
		fnwrite(get_fn_dir("var")."/log/".$logfile.".php", "<?php exit(1);?>\n", "wb");
		fnlog("Site maintenance", "$ip||".get_username()."||Log $logfile cleaned.");
	} else {
		fnlog("Security", "$ip||".get_username()."||Tried to clean the file $logfile.");
	}
}

/**
 * Makes a backup of your FN site, or a part of it
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20070725
 */
function fncc_dobackup() {
	// security checks
	$tosave = getparam("tosave", PAR_POST, SAN_FLAT);
	// include necessary APIs
	include_once("forum/include/archive.php");
	include_once("include/filesystem/DeepDir.php");
	// create the backup archive
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	if(is_admin()) {
		switch($tosave) {
			case get_fn_dir("news"):			$tag = "news";	break;
			case get_fn_dir("users"):			$tag = "users";	break;
			case get_fn_dir("var"):				$tag = "var";	break;
			case get_fn_dir("sections"):		$tag = "sect";	break;
			case get_fn_dir("var")."/flatforum":$tag = "forum";	break;
			case "./":							$tag = "all";	break;
		}
		// create the list of files to save
		$dir = new DeepDir();
		$dir->setDir($tosave);
		$dir->load();
		// name of the archive
		$archive = get_fn_dir("var")."/backup_".$tag."_".date("Ymd").".zip";
		if(file_exists($archive)) {
			unlink($archive);
		}
		$backup = new zip_file($archive);
		$backup->set_options(array('inmemory'=>"0",'overwrite'=>1,'prepend','level'=>1));
		// add every single file to the archive
		foreach( $dir->files as $n => $pathToFile ){
			// exclude other backups and lockfiles
			if(!preg_match("/^backup_[a-zA-Z]+_[0-9]+\.zip$/i", basename($pathToFile)) AND !preg_match("/^lockfile$/i",dirname($pathToFile))) {
				$backup->add_files($pathToFile);
			}
		}
		$backup->create_archive();
		fnlog("Site maintenance", "$ip||".get_username()."||Created backup file $archive.");
		header("Location: $archive");
	} else {
		fnlog("Security", "$ip||".get_username()."||Tried to create a backup of $tosave.");
	}
}

/*
 *  Delete backup files on the server
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20070722
 */
function fncc_cleanbackup() {
	// delete files
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	if(is_admin()) {
		$backup_files = fncc_listbackups();
		foreach($backup_files as $todelete) {
			unlink(get_fn_dir("var")."/$todelete");
		}
		fnlog("Site maintenance", "$ip||".get_username()."||Admin deleted ".count($backup_files)." backup file/s.");
	} else {
		fnlog("Security", "$ip||".get_username()."||Tried to delete backup files.");
	}
}

?>
