<?php
if (preg_match("/section.php/i",$_SERVER['PHP_SELF'])) {
	// let direct access in case of maintenance state --> ONLY for login action
	if(isset($_POST['action']) AND $_POST['action']=="login") {
		chdir("../../");
		include_once "config.php";
		include_once "functions.php";
		create_fn_constants();

		//Time zone
		if (function_exists("date_default_timezone_set"
			and function_exists("date_default_timezone_get")))
			@date_default_timezone_set(date_default_timezone_get());

		switch($lang) {
			case "de" OR "es" OR "fr" OR "it" OR "pt":
				include_once ("languages/$lang.php");
			break;
			default:
				include_once ("languages/en.php");
		}
		login();
		return;
	}
	// block any other direct access to this page
	else {
		Header("Location: ../../index.php");
		die();
	}
}

//scelgo l'azione da compiere
if (isset($_GET['action'])){
	$action = getparam("action",PAR_GET,SAN_FLAT);
	switch($action) {
		case "visreg":
			reg_interface();
		break;
		case "viewmembers":
			view_members();
		break;
		case "viewprofile":
			$user = getparam("user",PAR_GET,SAN_FLAT);
			if (trim($user)=="")
				view_profile(get_username());
			else view_profile($user);
		break;
		case "editprofile":
			edit_profile();
		break;
		case "deleteuser":
			delete_user();
		break;
		case "logout":
			logout();
		break;
		case "activateuser":
			$user =getparam("user",PAR_GET,SAN_FLAT);
			if (!is_alphanumeric($user)) die("User name is not valid!");
			$regcode = getparam("regcode",PAR_GET,SAN_NULL);
			if (!check_var($regcode,"digit")) die("The registration code is not valid!");
			activate_user($user,$regcode);
		break;
		case "passwordlost":
			password_lost();
		break;
		case "newpwd":
			$user    = trim(getparam("user", PAR_GET, SAN_FLAT));
			$regcode = trim(getparam("regcode", PAR_GET, SAN_FLAT));
			activate_newpwd($user, $regcode);
		break;
      }
}
else if (isset($_POST['action'])){
	$action = getparam("action",PAR_POST,SAN_FLAT);
	switch($action) {
	case "login":
		login();
	break;
	case "reguser":
		reguser();
	break;
	case "saveprofile":
		save_profile();
	break;
	case "sendnewpassword":
		send_new_password();
	break;
	}
}
else  {
	if (is_guest())
		login_interface();
	else view_profile(get_username());
}


/**
 * Visualizza l'interfaccia per effettuare il login
 *
 * Visualizza l'interfaccia per effettuare il login degli
 * utenti registrati, e propone un link per la registrazione
 * di nuovi utenti.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Marco Segato <segatom@users.sourceforge.net> | 20070317: ready to 2.5.9
 */
function login_interface(){
	?><div style="text-align:center">
	<fieldset>
	<legend><?php echo _FLOGIN?></legend>
	<form action="index.php?mod=none_Login" method="post">
	<input type="hidden" name="action" value="login" />
	<div style="text-align:right;padding:1em;margin-right:30%;padding:0.2em;">
	<label for="nome"><?php echo _NOMEUTENTE?></label> <input name="nome" type="text" id="nome"/>
	</div>
	<div style="text-align:right;padding:1em;margin-right:30%;padding:0.2em;">
	<label for="logpassword"><?php echo _PASSWORD?></label> <input name="logpassword" type="password" id="logpassword"/>
	</div>
	<?php
	// remember login checkbox
	global $remember_login;
	if($remember_login==1) {
		echo "<div style=\"margin:1em;\"><label for=\"rememberlogin\">"._REMEMBERLOGIN."</label>";
		echo "<input type=\"checkbox\" alt=\"remember_login\" name=\"rememberlogin\" id=\"rememberlogin\" /><br>";
		echo "</div>";
	} else echo "<br>";
	// login button
	?><input type="submit" value="<?php echo _LOGIN?>" />
	</form>
	</fieldset>
	<br><?php
	// link to register
	global $reguser;
	if ($reguser=="1" or $reguser=="2"){
		echo _NONREG;
		?><br><b><a href="index.php?mod=none_Login&amp;action=visreg" title="<?php echo _REGORA?>"><?php echo _REGORA?></a></b><?php
	}
	?></div><?php
}


/**
 * Interfaccia che permette la registrazione di un nuovo utente
 *
 * Visualizza l'interfaccia che permette di effettuare la
 * registrazione di un nuovo utente, richiedendo di compilare
 * una serie di campi informazione.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Marco Segato <segatom@users.sourceforge.net> | 20070317: ready to 2.5.9
 */
function reg_interface(){
	// deny fake regs
	if (isset($_GET['reguser']) or isset($_POST['reguser']) or isset($_COOKIE['reguser'])) die(_NONPUOI);
	// check if registering is permitted
	global $reguser;
	if ($reguser!="1" and $reguser!="2") die(_NONPUOI); // if not, die
	// print registration forms
	edit_user_interface("","reguser");
}


/**
 * Visualizza gli utenti registrati sul portale
 *
 * Visualizza una lista degli utenti registrati sul portale,
 * con alcune delle informazioni personali inserite.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Marco Segato <segatom@users.sourceforge.net> | 20070317: ready to 2.5.9
 */
function view_members(){
	// security convertions
	$page    = getparam("page", PAR_GET, SAN_FLAT);
	$myforum = getparam("myforum",PAR_COOKIE,SAN_FLAT);
	$req     = getparam("REQUEST_URI",PAR_SERVER, SAN_FLAT);
	if(strstr($req,"myforum="))
		die(_NONPUOI);
	// check number of page to display
	global $memberperpage;
	if ($page == "") $page = 0;
	if ($page != 0) $page -= 1;

	// check that current user is logged
	if (!is_guest()) {
		echo "<b>"._FUTENTI."</b>:<br><br>";
		?>
		<table style="border-collapse:collapse; width:95%;border-spacing: 10px;">
		<tbody>
		<tr class="forum-group-header">
		<td class="forum-group-table" style="white-space:nowrap;"><b><?php echo _NOMEUTENTE?></b></td>
		<td class="forum-group-table" style="white-space:nowrap;"><b><?php echo _LEVEL?></b></td>
		<td class="forum-group-table" style="white-space:nowrap;"><b><?php echo _FNOME?></b></td>
		<td class="forum-group-table" style="white-space:nowrap;"><b><?php echo _FEMAIL?></b></td>
		<td class="forum-group-table" style="white-space:nowrap;"><b><?php echo _FHOME?></b></td>
		</tr>
		<?php
		// start printing each user
		$members = array();
		$members = list_users();
		$profile = array();
		for($x=$page*$memberperpage; $x<$memberperpage*($page+1); $x++) {
			if (isset($members[$x])) {
				$user = str_replace(".php","",$members[$x]);
				$profile = load_user_profile($user);
				echo "<tr>";
				echo "<td class=\"forum-group-table\" style=\"white-space:nowrap;\"><a href=\"index.php?mod=none_Login&amp;action=viewprofile&amp;user=$user\">$user</a></td>";
				echo "<td class=\"forum-group-table centeredDiv\" style=\"white-space:nowrap;\">".$profile['level']."&nbsp;</td>";
				echo "<td class=\"forum-group-table\" style=\"white-space:nowrap;\">".$profile['name']."&nbsp;</td>";
				echo "<td class=\"forum-group-table centeredDiv\" style=\"white-space:nowrap;\">";
				// e-mail address
				if ($profile['mail']!="" and (($profile['hiddenmail']!="1" and !is_guest()) or is_admin())) {
					echo "<a href=\"mailto:".$profile['mail']."\"><span title=\"".$profile['mail']."\">"._ICONMAIL."</span></a>";
				}
				else
					echo "&nbsp;";

				echo "</td>";
				// web site address
				echo "<td class='forum-group-table' class=\"centeredDiv\" style='white-space:nowrap;'>";
				if($profile['homepage']!="") {
					echo "<a href=\"".$profile['homepage']."\" target=\"_blank\"><span title=\"".preg_replace("/^http\:\/\//s","",$profile['homepage'])."\">"._ICONHOME."</span></a>";
				}
				else
					echo "&nbsp;";

				echo "</td>";
				// end of user's informations
				echo "</tr>";
			}
		}
		// print pages to change view
		$pages = "";
		if(count($members)>$memberperpage){
			for ($thispagenum=1; $thispagenum<=ceil(count($members)/$memberperpage); $thispagenum++) {
				if($thispagenum != ($page+1))
					$pages .= "[<a href=\"index.php?mod=none_Login&amp;action=viewmembers&amp;page=".$thispagenum."\">".$thispagenum."</a>] ";
				else
					$pages .= "[".$thispagenum."] ";
			}
		}
		?></tbody>
		</table>
		<div style="width:95%; margin-top:1em; text-align:center;"><?php echo $pages;?></div><?php
	}
	// not available to unregistered users
	else echo  "<div style=\"width:95%; margin-top:1em; text-align:center;\"><b>"._FERRACC."</b></div>";
}


/**
 * Visualizza il profilo dell'utente
 *
 * Visualizza il profilo dell'utente selezionato presentandone
 * le piu` significative informazioni personali inserite.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Marco Segato <segatom@users.sourceforge.net> | 20070317: ready to 2.5.9
 *
 * @param string $user Profilo utente da visualizzare
 */
function view_profile($user=""){
	if (is_guest()){
		login_interface();
		return;
	}
	// secutiry checks
	// Username must be taken from function's parameter. If it's take from GET variable
	// there are some problems in viewing profile
	$user    = getparam($user, PAR_NULL, SAN_FLAT);
	$myforum = get_username();
	$addr    = getparam("REMOTE_ADDR",PAR_SERVER, SAN_FLAT);
	global $lang;
	// check profile exists
	if(!file_exists(get_fn_dir("users")."/$user.php")) {
		?><div style="width:95%; margin-top:1em; text-align:center;">
		<br><b><?php echo _NORESULT?></b><br>
		</div><?php
		fnlog("Viewprofile", $addr."||".$myforum."||Tried to view unexistant $user profile.");
		return;
	}
	// print profile's informations
	?><div style="width:95%; margin-top:1em; text-align:center;"><?php
	// check that current user is logged
	if(!is_guest()) {
		$profile = load_user_profile($user);
		// username
		echo "<b>$user</b><br>";
		// avatar
		$img = $profile['avatar'];
		if($img!="") {
			if(!stristr($img,"http://"))
				echo "<img src='forum/$img' alt='$user' style='max-width:120px; border:0' />";
			else echo "<img src='$img' alt='$user' style='max-width:120px; border:0' />";
		} else echo "<img src='forum/images/blank.png' alt='$user' style='max-width:120px; border:0' />";
		echo "<br><br>";
		$style1 = "font-style:bold; padding:0.2em;";
		$style2 = "padding:0.2em;";
		?>
		<table class=\"centeredDiv\" style="border-collapse:collapse; border:1px; width:70%">
		<tbody>
		<tr>
		<td width="30%" style="<?php echo $style1?>"><b><?php echo _FNOME?>:</b></td>
		<td style="<?php echo $style2?>"><?php echo $profile['name']?></td>
		</tr><?php
		if($profile['hiddenmail']=="0" OR is_admin() OR $myforum==$user) {
			?><tr>
			<td style="<?php echo $style1?>"><b><?php echo _FEMAIL?>:</b></td>
			<td style="<?php echo $style2?>"><a href="mailto:<?php echo $profile['mail']?>"><?php echo $profile['mail']?></a></td>
			</tr><?php
		}
		?><tr>
		<td style="<?php echo $style1?>"><b><?php echo _FHOME?>:</b></td>
		<td style="<?php echo $style2?>"><?php
		if (trim($profile['homepage'])!=""){
			echo "<a href=\"".$profile['homepage']."\" target=\"_blank\">".preg_replace("/^http\:\/\//s","",$profile['homepage'])."</a>";
		}
		?></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><b><?php echo _FPROFES?>:</b></td>
		<td style="<?php echo $style2?>"><?php echo $profile['work']?></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><b><?php echo _FPROV?>:</b></td>
		<td style="<?php echo $style2?>"><?php
			if(trim($profile['from'])!="") {
				?><a href="http://maps.google.<?php echo $lang?>/maps?f=q&hl=<?php echo $lang?>&q=<?php echo $profile['from']?>" target="new" title="Google Maps"><?php echo $profile['from']?></a><?php
			}
		?></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><b>Jabber / Google Talk:</b></td>
		<td style="<?php echo $style2?>"><?php echo $profile['jabber']?></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><b>Skype:</b></td>
		<td style="<?php echo $style2?>"><?php echo $profile['skype']?></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><b>ICQ:</b></td>
		<td style="<?php echo $style2?>"><?php echo $profile['icq']?></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><b>MSN:</b></td>
		<td style="<?php echo $style2?>"><?php echo $profile['msn']?></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><b><?php echo _FNPRESENTATION; ?>:</b></td>
		<td style="<?php echo $style2?>"><?php echo tag2html($profile['presentation']); ?></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><b><?php echo _LEVEL?>:</b></td>
		<td style="<?php echo $style2?>"><?php echo $profile['level']?></td>
		</tr>
		</tbody>
		</table>
		<br>
		<?php
		// admins can delete profiles
		if(is_admin()) {
			echo "<b><a href='#' onclick=\"check('index.php?mod=none_Login&amp;action=deleteuser&amp;user=$user')\">"._FDELUTENTE."</a></b>&nbsp;|&nbsp;";
		}
		// link to edit profile if it's me or admins
		if (is_admin() OR $myforum==$user) {
			echo "<b><a href=\"index.php?mod=none_Login&amp;action=editprofile&amp;user=$user\">"._FMODPROF."</a></b>";
		}
	}
	// not available to unregistered users
	else echo "<b>"._FERRACC."</b>";
	?></div><?php
}


/**
 * Funzione per effettuare il login
 *
 * Funzione per effettuare il login ed impostare
 * i cookies con le informazioni di autenticazione.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Marco Segato <segatom@users.sourceforge.net> | 20070317: ready to 2.5.9
 */
function login(){
	global $remember_login;
	// security fixes
	$nome          = getparam("nome",PAR_POST,SAN_FLAT);
	$logpassword   = getparam("logpassword",PAR_POST,SAN_FLAT);
	$rememberlogin = getparam("rememberlogin", PAR_POST, SAN_FLAT);
	$url           = getparam("PHP_SELF",PAR_SERVER, SAN_FLAT);
	$addr          = getparam("REMOTE_ADDR",PAR_SERVER, SAN_FLAT);
	$nome = str_replace("<","",$nome);
	$nome = str_replace(">","",$nome);
	// md5 input password
	$lpass = md5($logpassword);

	// unregistered or blank user
	if(!file_exists(get_fn_dir("users")."/$nome.php") || ($nome=="")) {
		if (file_exists(get_waiting_users_dir()."/$nome.php")){
			echo "<div style=\"text-align: center;\"><b>"._WAITINGUSERLOGIN;
			echo "</b><br>"._CONTACTADMIN."<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
			return;
		}

		// rebuild correct path
		$url = str_replace("/sections/none_Login/section.php", "", $url);
		$url = str_replace("/index.php", "", $url);
		// goto registration module
		?><div style="width:95%;margin:1em;text-align:center;">
		<b><?php echo $nome?></b> <?php echo _FNONREG?>
		<br><br><?php
		// link to register
		global $reguser;
		if ($reguser=="1" or $reguser=="2"){
			echo _NONREG;
 			?><br><b><a href="<?php echo $url?>/index.php?mod=none_Login&amp;action=visreg" title="<?php echo _REGORA?>"><?php echo _REGORA?></a></b><?php
		}
		?></div><?php
	}
	// registered user
	else {
		$profile = load_user_profile($nome);
		$passwd = $profile['password'];
		// password is correct
		if($passwd==$lpass){
			// rebuild correct path for the cookie
			$path = pathinfo($url);
			$path = str_replace("\\","/",$path);
			$url = str_replace("/forum","",$path["dirname"]);
			$url = str_replace("/sections/none_Login","",$path["dirname"]);
			if($url=="")
				$url="/";
			// manage cookies' life
			if($remember_login==0 OR $rememberlogin=="") {
				setcookie("myforum",$nome,0,"$url");
				setcookie("secid",md5($nome.$passwd),0,"$url");
			} else {
				setcookie("myforum",$nome,time()+99999999,"$url");
				setcookie("secid",md5($nome.$passwd),time()+99999999,"$url");
			}
			// log action
			$myforum = getparam("myforum",PAR_COOKIE,SAN_FLAT);
			fnlog("Login", $addr."||".$myforum."||User $nome login.");
			// go back to ref page
			?><script>window.location='<?php echo getparam("HTTP_REFERER", PAR_SERVER, SAN_FLAT)?>';</script><?php
	 	}
		// wrong password
		else {
			?><div style="width:95%;margin:1em;text-align:center;">
			<b><?php echo $nome?></b> <?php echo _FERRPASS?>
			<br><br>
			<a href="javascript:history.back()">&lt;&lt;&nbsp;<?php echo _INDIETRO?></a>
			</div><?php
		}
	}
}


/**
 * Modifica il profilo dell'utente
 *
 * Permette di modificare, dopo i necessari controlli sulle
 * autorizzazioni a farlo, il profilo utente selezionato.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Marco Segato <segatom@users.sourceforge.net> | 20070317: ready to 2.5.9
 *
 * @param string $user Profilo utente da modificare
 */
function edit_profile($user=""){
	// security checks
	$user    = getparam("user", PAR_GET, SAN_FLAT);
	$myforum = getparam("myforum",PAR_COOKIE,SAN_FLAT);
	$addr    = getparam("REMOTE_ADDR",PAR_SERVER, SAN_FLAT);
	$req     = getparam("REQUEST_URI",PAR_SERVER, SAN_FLAT);
	if(strstr($req,"myforum=")) die(_NONPUOI);
	// check profile exists
	if(!file_exists(get_fn_dir("users")."/$user.php") OR $user=="") {
		?><div style="width:95%; margin:1em; text-align:center;">
		<b><?php echo _NORESULT?></b>
		</div><?php
		fnlog("Editprofile", $addr."||".$myforum."||Tried to edit unexistant $user profile.");
		return;
	}

	// who can edit my account? admins or myself ;)
	if( is_admin() OR (is_user() AND $user==$myforum) ) {
		edit_user_interface($user,"edit");
	} // unauthorized tries
	else {
		fnlog("Editprofile", $addr."||".$myforum."||Tried to edit ".$user." profile.");
		// go back home
		?><script>window.location='index.php';</script><?php
	}
}


/**
 *
 *
 */
function save_profile(){

	//controllo se e' possibile registrarsi sulo sito
	if (isset($_GET['reguser']) or isset($_POST['reguser']) or isset($_COOKIE['reguser'])) die(_NONPUOI);
	global $reguser;
	if ($reguser!="1" and $reguser!="2"){
		//posso continuare solo se sto modificando il mio profilo.
		//oppure se sto modificando il profilo di un altro in qualità di amministratore
		$nome="";
		$myforum="";
		$nome=getparam("user",PAR_POST,SAN_FLAT);
		$nome=str_replace("\r","",str_replace("\n","",$nome));
		$nome=str_replace(chr(10),"",str_replace(chr(13),"",$nome));
		$myforum=getparam("myforum",PAR_COOKIE,SAN_FLAT);
		if (!is_alphanumeric($nome)) die(_NONPUOI);
		if (!file_exists(get_fn_dir("users")."/$nome.php")) die(_NONPUOI."1");
		if (getlevel($myforum,"home")=="-1") die(_NONPUOI."2");
		if (!versecid($myforum)) die(_NONPUOI."2 1/2");
		if (($nome!=$myforum) and !is_admin()) die(_NONPUOI."3");
	}
	global $forumback,$forumborder;

	$myforum=getparam("myforum",PAR_COOKIE,SAN_FLAT);
	$nome=getparam("user",PAR_POST,SAN_FLAT);
	if(isset($_POST['regpass'])) $regpass=$_POST['regpass'];
		else $regpass="";
	if(isset($_POST['reregpass'])) $reregpass=$_POST['reregpass'];
		else $reregpass="";
	if(isset($_POST['anag'])) $anag=$_POST['anag'];
		else $anag="";
	if(isset($_POST['email'])) $email=$_POST['email'];
		else $email="";

	if (isset($_POST['hiddenmail']) and preg_match("/0|1|on/i",$_POST['hiddenmail'])){
		if ($_POST['hiddenmail']=="on")
			$hiddenmail = "1";
		else $hiddenmail= "0";
	}
	else $hiddenmail = "0";

	if(isset($_POST['homep'])) $homep=$_POST['homep'];
		else $homep="";
	if(isset($_POST['prof'])) $prof=$_POST['prof'];
		else $prof="";
	if(isset($_POST['prov'])) $prov=$_POST['prov'];
		else $prov="";
	if (isset($_POST['fnjabber'])) $jabber= $_POST['fnjabber'];
		else $jabber="";
	if (isset($_POST['fnskype'])) $skype= $_POST['fnskype'];
		else $skype="";
	if (isset($_POST['fnicq'])) $icq= $_POST['fnicq'];
		else $icq="";
	if (isset($_POST['fnmsn'])) $msn= $_POST['fnmsn'];
		else $msn="";
	if (isset($_POST['fnpresentation'])) $presentation= $_POST['fnpresentation'];
		else $presentation="";
	if(isset($_POST['ava'])) $ava=$_POST['ava'];
		else $ava="";
	if(isset($_POST['url_avatar']) AND $_POST['url_avatar']!=''){
		$parseUrl = parse_url(trim($_POST['url_avatar']));
   		$url_host = trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
		if(preg_match("/(jpg|jpeg|png|gif)/i",get_file_extension($_POST['url_avatar'])) )
			$url_avatar=$_POST['url_avatar'];
		else if(strcmp($url_host, "cdn.libravatar.org") == 0) {
			$url_avatar=$_POST['url_avatar'];
		}
		else
			$url_avatar="images/blank.png";
	} else $url_avatar="images/blank.png";
	if(isset($_POST['firma'])) $firma=$_POST['firma'];
		else $firma="";
	if(isset($_POST['level'])) $level=getparam("level",PAR_POST,SAN_FLAT);
		else $level="";
	if(!is_numeric($level)) $level="";
	//solo un amministratore può impostare un livello 10
	if ($level==10 and !is_admin())
		$level=0;

	$myforum=str_replace("\r","",str_replace("\n","",$myforum));
	$myforum=str_replace(chr(10),"",str_replace(chr(13),"",$myforum));
	$myforum=str_replace(".","",$myforum);
	$myforum=str_replace("/","",$myforum);
	$myforum=str_replace("\\","",$myforum);
	$regpass=str_replace("\r","",str_replace("\n","",$regpass));
	$regpass=str_replace(chr(10),"",str_replace(chr(13),"",$regpass));
	$anag=str_replace("\r","",str_replace("\n","",$anag));
	$anag=str_replace(chr(10),"",str_replace(chr(13),"",$anag));
	$nome=str_replace("\r","",str_replace("\n","",$nome));
	$nome=str_replace(chr(10),"",str_replace(chr(13),"",$nome));
	$email=str_replace("\r","",str_replace("\n","",$email));
	$email=str_replace(chr(10),"",str_replace(chr(13),"",$email));
	$homep=str_replace("\r","",str_replace("\n","",$homep));
	$homep=str_replace(chr(10),"",str_replace(chr(13),"",$homep));
	$prof=str_replace("\r","",str_replace("\n","",$prof));
	$prof=str_replace(chr(10),"",str_replace(chr(13),"",$prof));
	$prov=str_replace("\r","",str_replace("\n","",$prov));
	$prov=str_replace(chr(10),"",str_replace(chr(13),"",$prov));

	$jabber=str_replace("\r","",str_replace("\n","",$jabber));
	$jabber=str_replace(chr(10),"",str_replace(chr(13),"",$jabber));
	$jabber=str_replace("\r","",str_replace("\n","",$jabber));
	$jabber=str_replace(chr(10),"",str_replace(chr(13),"",$jabber));
	$skype=str_replace("\r","",str_replace("\n","",$skype));
	$skype=str_replace(chr(10),"",str_replace(chr(13),"",$skype));
	$skype=str_replace("\r","",str_replace("\n","",$skype));
	$skype=str_replace(chr(10),"",str_replace(chr(13),"",$skype));
	$icq=str_replace("\r","",str_replace("\n","",$icq));
	$icq=str_replace(chr(10),"",str_replace(chr(13),"",$icq));
	$icq=str_replace("\r","",str_replace("\n","",$icq));
	$icq=str_replace(chr(10),"",str_replace(chr(13),"",$icq));
	$msn=str_replace("\r","",str_replace("\n","",$msn));
	$msn=str_replace(chr(10),"",str_replace(chr(13),"",$msn));
	$msn=str_replace("\r","",str_replace("\n","",$msn));
	$msn=str_replace(chr(10),"",str_replace(chr(13),"",$msn));

	$presentation=str_replace("\r","",str_replace("\n","",$presentation));
	$presentation=str_replace(chr(10),"",str_replace(chr(13),"",$presentation));
	$presentation=str_replace("\r","",str_replace("\n","",$presentation));
	$presentation=str_replace(chr(10),"",str_replace(chr(13),"",$presentation));

	$ava=str_replace("\r","",str_replace("\n","",$ava));
	$ava=str_replace(chr(10),"",str_replace(chr(13),"",$ava));
	$url_avatar=str_replace("\r","",str_replace("\n","",$url_avatar));
	$url_avatar=str_replace(chr(10),"",str_replace(chr(13),"",$url_avatar));
	$firma=str_replace("\r","",str_replace("\n","",$firma));
	$firma=str_replace(chr(10),"",str_replace(chr(13),"",$firma));
	$level=str_replace("\r","",str_replace("\n","",$level));
	$level=str_replace(chr(10),"",str_replace(chr(13),"",$level));


	//registro limite
	if($level>10)
		$level=10;
	// io posso modificare il mio ma solo se mantengo il livello!!!
	// l'amministratore può modificare tutti i profili
	if((($nome==$myforum) and (getlevel($nome,"home")==$level) and versecid($nome)) or ((getlevel($myforum,"home")==10) and versecid($myforum))){

		?><br><br>
		<table style="border:0" cellspacing='0' cellpadding='0' bgcolor="<?php echo $forumborder?>" width="95%">
		<tbody><tr>
		<td>
		<table width='100%' style="border:0" cellspacing='1' cellpadding='3'>
		<tbody>
		<tr>
		<td bgcolor="<?php echo $forumback?>" colspan=5>
		<?php
		$nome=str_replace("<","",$nome);
		$nome=str_replace(">","",$nome);
		$nome=stripslashes($nome);
		$regpass=str_replace("<","",$regpass);
		$regpass=str_replace(">","",$regpass);
		$anag=str_replace("<","",$anag);
		$anag=str_replace(">","",$anag);
		$anag=stripslashes($anag);
		$email=str_replace("<","",$email);
		$email=str_replace(">","",$email);
		$email=stripslashes($email);
		$homep=str_replace("<","",$homep);
		$homep=str_replace(">","",$homep);
		$homep=stripslashes($homep);
		$prof=str_replace("<","",$prof);
		$prof=str_replace(">","",$prof);
		$prof=stripslashes($prof);
		$prov=str_replace("<","",$prov);
		$prov=str_replace(">","",$prov);
		$prov=stripslashes($prov);
		if ($url_avatar!="images/blank.png") {
			$ava = $url_avatar;
			$ava = str_replace("<", "", $ava);
			$ava = str_replace(">", "", $ava);
		} else {
			$ava = "images/".$ava;
			$ava = str_replace("<", "", $ava);
			$ava = str_replace(">", "", $ava);
		}
		$firma=str_replace("<","",$firma);
		$firma=str_replace(">","",$firma);
		$firma=stripslashes($firma);
		# Mette la pass in MD5
		if(($regpass=="")){
			$tempdata = array();
			$tempdata = load_user_profile($nome);
			$regpass = $tempdata['password'];
		}
		else{
			if ($regpass==$reregpass)
				$regpass = md5 ($regpass);
			else {
				echo "<div style='text-align: center;'>
				<b>"._PASSERR."</b><br>
				<a href=\"javascript:history.back()\">&lt;&lt;"._INDIETRO."</a>
				</div>";
				return;
			}
		}

		$firma=str_replace("\n","<br>",$firma);

		$old_data = load_user_profile($nome);
		$data = array();
		$data['password'] = $regpass;
		$data['name'] = $anag;
		$data['mail'] = $email;
		$data['hiddenmail'] = $hiddenmail;
		$data['homepage'] = $homep;
		$data['work'] = $prof;
		$data['from'] = $prov;
		$data['avatar'] = $ava;
		$data['sign'] = $firma;
		$data['jabber']=$jabber;
		$data['skype']=$skype;
		$data['icq']=$icq;
		$data['msn']=$msn;
		$data['presentation']=$presentation;
		$data['level'] = $level;
		$data['regdate'] = $old_data['regdate'];
		$data['lastedit'] = time();
		$data['lasteditby'] = $myforum;

		save_user_profile($nome,$data);
		fnlog("Saveprofile", getparam("REMOTE_ADDR",PAR_SERVER, SAN_FLAT)."||".$myforum."||User $myforum changed his profile.");?>
		<div style="text-align:center">
		<?php echo _FOKMODPROF?>
		</div>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</tbody>
		</table>
		<!-- automatic redirect to the message after 2 seconds-->
		<meta http-equiv="Refresh" content="1; URL=index.php?mod=none_Login&amp;action=viewprofile&amp;user=<?php echo $nome?>"><?php
	}
}

/**
 * Registra un nuovo profilo dell'utente
 *
 * Permette di creare, dopo i necessari controlli sulle
 * autorizzazioni a farlo, un nuovo profilo utente.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Marco Segato <segatom@users.sourceforge.net> | 20110507: use of captcha
 */
function reguser(){
	//controllo se è possibile registrarsi sulo sito
	if (isset($_GET['reguser']) or isset($_POST['reguser']) or isset($_COOKIE['reguser'])) die(_NONPUOI);
	global $reguser,$sitename;
	if ($reguser!="1" and $reguser!="2") die(_NONPUOI);

	// security checks
	$req = getparam("REQUEST_URI", PAR_SERVER, SAN_NULL);
	$req = str_replace("&", "&amp;", $req);
	$captcha = getparam("captcha", PAR_POST, SAN_FLAT);
	$captcha = strip_tags($captcha);
	if($captcha!="") {
		// checking the value of anti-spam code inserted
		include("include/captcha/fncaptcha.php");
		$fncaptcha = new fncaptcha();
		$captchaok = $fncaptcha->checkCode($captcha);

		if(!$captchaok) {
			// anti-spam code is NOT right
			echo "<div style='text-align: center;'><b/>"._CAPTCHA_ERROR."</b><br>";
			// back or automatic redirect to the index after 2 seconds
			?><p><a href="javascript:history.back()">&lt;&lt;<?php echo _INDIETRO?></a></p></div><?php
			return;
		}
	} else {
		// no valid captcha value is passed, go away
		header("Location: index.php?mod=none_Login");
		return;
	}

	global $forumback,$forumborder;
	$nome=getparam("nome",PAR_POST,SAN_FLAT);
	if(!is_alphanumeric($nome)) {
		echo _FERRCAMPO."<br>";
		echo "<a href=\"javascript:history.back()\">&lt;&lt;"._INDIETRO."</a>";
		return;
	}
	if(isset($_POST['regpass'])) $regpass=$_POST['regpass'];
		else $regpass="";
	if(isset($_POST['reregpass'])) $reregpass=$_POST['reregpass'];
		else $reregpass="";
	if(isset($_POST['anag'])) $anag=$_POST['anag'];
		else $anag="";
	if(isset($_POST['email'])) $email=$_POST['email'];
		else $email="";
	if(isset($_POST['homep'])) $homep=$_POST['homep'];
		else $homep="";
	if(isset($_POST['prof'])) $prof=$_POST['prof'];
		else $prof="";
	if(isset($_POST['prov'])) $prov=$_POST['prov'];
		else $prov="";
	if(isset($_POST['ava'])) $ava=$_POST['ava'];
		else $ava="";
	if(isset($_POST['url_avatar']) AND $_POST['url_avatar']!=""){
		$parseUrl = parse_url(trim($_POST['url_avatar']));
   		$url_host=trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
		if(preg_match("/(jpg|jpeg|png|gif)/i",get_file_extension($_POST['url_avatar'])) )
			$url_avatar=$_POST['url_avatar'];
		else if(strcmp($url_host, "cdn.libravatar.org") == 0) {
			$url_avatar=$_POST['url_avatar'];
		} else $url_avatar="images/blank.png";
	} else $url_avatar="images/blank.png";
	if(isset($_POST['firma'])) $firma=$_POST['firma'];
		else $firma="";
	if (isset($_POST['hiddenmail']) and preg_match("/1|on/i",trim($_POST['hiddenmail']))){
		$hiddenmail = "1";
	} else $hiddenmail="0";

	$nome=str_replace("\r","",str_replace("\n","",$nome));
	$nome=str_replace(chr(10),"",str_replace(chr(13),"",$nome));
	$nome=str_replace(".","",$nome);
	$nome=str_replace("/","",$nome);
	$nome=str_replace("\\","",$nome);

	$regpass=str_replace("\r","",str_replace("\n","",$regpass));
	$regpass=str_replace(chr(10),"",str_replace(chr(13),"",$regpass));

	$reregpass=str_replace("\r","",str_replace("\n","",$reregpass));
	$reregpass=str_replace(chr(10),"",str_replace(chr(13),"",$reregpass));

	$anag=str_replace("\r","",str_replace("\n","",$anag));
	$anag=str_replace(chr(10),"",str_replace(chr(13),"",$anag));

	$email=str_replace("\r","",str_replace("\n","",$email));
	$email=str_replace(chr(10),"",str_replace(chr(13),"",$email));

	$homep=str_replace("\r","",str_replace("\n","",$homep));
	$homep=str_replace(chr(10),"",str_replace(chr(13),"",$homep));

	$prof=str_replace("\r","",str_replace("\n","",$prof));
	$prof=str_replace(chr(10),"",str_replace(chr(13),"",$prof));

	$prov=str_replace("\r","",str_replace("\n","",$prov));
	$prov=str_replace(chr(10),"",str_replace(chr(13),"",$prov));

	$ava=str_replace("\r","",str_replace("\n","",$ava));
	$ava=str_replace(chr(10),"",str_replace(chr(13),"",$ava));

	$url_avatar=str_replace("\r","",str_replace("\n","",$url_avatar));
	$url_avatar=str_replace(chr(10),"",str_replace(chr(13),"",$url_avatar));

	$firma=str_replace("\r","",str_replace("\n","",$firma));
	$firma=str_replace(chr(10),"",str_replace(chr(13),"",$firma));

	?>
	<br><br>
	<table cellspacing='0' cellpadding='0' bgcolor='<?php echo $forumborder?>' width="95%" style="border:0">
	<tbody><tr>
	<td>
	<table width='100%' cellspacing='1' cellpadding='3' style="border:0">
	<tbody>
	<tr>
	<td bgcolor="<?php echo $forumback?>" colspan=5>
	<?php
	if(!user_exists($nome,TRUE)){
		if(($nome=="") OR ($regpass=="") OR (stristr($nome," ")) OR (strlen($nome)>13) OR (stristr($nome,"\"")) OR (stristr($nome,"\\")) OR ($regpass != $reregpass) or !is_alphanumeric($nome)){
			print _FERRCAMPO."<br>
		<a href=\"javascript:history.back()\">&lt;&lt;"._INDIETRO."</a>";
		}
		else{
			// installing profile
			if(file_exists(get_fn_dir("var")."/firstinstall")) {
				if(count(list_users())==0) {
					$level=10;
				}
				else $level=0;
				unlink(get_fn_dir("var")."/firstinstall");
			}
			else {
				$level=0;
			}

			$nome=str_replace("<","",$nome);
			$nome=str_replace(">","",$nome);
			$nome=stripslashes($nome);
			$regpass=str_replace("<","",$regpass);
			$regpass=str_replace(">","",$regpass);
			$anag=str_replace(">","",$anag);
			$anag=str_replace("<","",$anag);
			$anag=stripslashes($anag);
			$email=str_replace("<","",$email);
			$email=str_replace(">","",$email);
			$email=stripslashes($email);
			$homep=str_replace("<","",$homep);
			$homep=str_replace(">","",$homep);
			$homep=stripslashes($homep);
			$prof=str_replace("<","",$prof);
			$prof=str_replace(">","",$prof);
			$prof=stripslashes($prof);
			$prov=str_replace("<","",$prov);
			$prov=str_replace(">","",$prov);
			$prov=stripslashes($prov);
			$ava=str_replace("<","",$ava);
			$ava=str_replace(">","",$ava);
			if ($ava=="")
				$ava="blank.png";
			if ($url_avatar!="images/blank.png") {
				$ava = $url_avatar;
				$ava = str_replace("<", "", $ava);
				$ava = str_replace(">", "", $ava);
			}
			else {
				$ava = str_replace("<", "", $ava);
				$ava = str_replace(">", "", $ava);
				$ava = "images/".$ava;
			}
			$firma=str_replace("<","",$firma);
			$firma=str_replace(">","",$firma);
			$firma=stripslashes($firma);
			# Mette la password in MD5
			$pass = "";
			$pass = $regpass;
			$regpass = md5 ($regpass);
			$firma=str_replace("\n","<br>",$firma);


			$data = array();
			$data['password'] = $regpass;
			$data['name'] = $anag;
			$data['mail'] = $email;
			$data['hiddenmail'] = $hiddenmail;
			$data['homepage'] = $homep;
			$data['work'] = $prof;
			$data['from'] = $prov;
			$data['avatar'] = $ava;
			$data['sign'] = $firma;
			$data['level'] = $level;
			$data['regmail'] = $email;
			$data['regdate'] = time();

			if ($reguser==2){

				//controllo che la mail non sia già stata usata per la registrazione di un altro utente
				if (in_array($email,list_reg_emails()) or in_array($email,list_reg_emails(1))){
					echo "<div style=\"text-align: center;\">"._THEMAIL." $email "._MAILUSED;
					echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt;"._INDIETRO."</a></div>";
					die();
				}

				//genero il codice di attivazione
				$data['regcode'] = mt_rand(1,99999999);
				//devo controllare la validità della mail
				//se non è valida interrompo e permetto di tornare indietro
				if (!check_mail($email)){
					echo "<div style=\"text-align: center;\"><b>"._ERREMAIL."!</b>";
					echo "<br><br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
					die();
				}

				//se l'e-mail è spammosa blocco tutto!
				if (is_spam($email,"emails")){
					echo "<b>"._ERREMAIL."</b> ("._ISSPAM.")";
					$addr = getparam("REMOTE_ADDR",PAR_SERVER, SAN_FLAT);
					fnlog("Registration","$addr||Failed registration with mail: \"".strip_tags($email)."\"");
					die();
				}

			}

			if ($reguser=="2") save_user_profile($nome,$data,1);
			else save_user_profile($nome,$data);

			$addr=getparam("REMOTE_ADDR",PAR_SERVER, SAN_FLAT);
			$myforum=getparam("myforum",PAR_COOKIE,SAN_FLAT);

			fnlog("Registration", $addr."||".$myforum."||New registered user $nome.");
			echo "<div align=\"center\">";
			if ($reguser==2){
				//stampo il link di attivazione a schermo (solo in fase di scrittura del codice)
				$url = "http://".$_SERVER['SERVER_NAME']."/".$_SERVER['SCRIPT_NAME']."?mod=none_Login&action=activateuser&user=$nome&regcode=".$data['regcode'];
// 				echo "<br><br>$url";

				$message = _IST_REG_MAIL."\n\n$url";

				if (@mail($email, _COMP_REG_MAIL." $sitename", $message,"FROM: $sitename <noreply@noreply>\r\nX-Mailer: Flatnuke on PHP/".phpversion())){
					echo _COMP_REG." <b>$email</b>";
					echo "<br>"._COMP_REG2;
					fnlog("Registration", $addr."||".$myforum."||Activation mail sent for $nome.");
				}
				else {
					echo _ACTIVATIONMAILNOTSENT;
					fnlog("Registration", $addr."||".$myforum."||Activation mail not sent for $nome.");
				}


			}
			else echo _FORAREG;
			?><br>
			<a href="index.php?mod=none_Login">&lt;&lt;<?php echo _LOGIN?></a></div><?php
		}
	}
	else{
		?><div style="text-align: center;"><b><?php echo _FUSERSCE?></b><br><br><a href="index.php?mod=none_Login&amp;action=visreg">&lt;&lt;<?php echo _INDIETRO?></a></div><?php
	}
	?>
	</td>
	</tr>
	</tbody>
	</table>
	</td>
	</tr>
	</tbody>
	</table><?php
}


/**
 * Eliminazione di un profilo utente
 *
 * Permette di eliminare, dopo i necessari controlli sulle
 * autorizzazioni a farlo, il profilo utente selezionato.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Marco Segato <segatom@users.sourceforge.net> | 20070317: ready to 2.5.9
 *
 * @param string $user Profilo utente da eliminare
 */
function delete_user($user=""){
	// security checks
	$myforum = getparam("myforum",PAR_COOKIE,SAN_FLAT);
	$user    = getparam("user", PAR_GET, SAN_FLAT);
	$addr    = getparam("REMOTE_ADDR",PAR_SERVER, SAN_FLAT);
	// who can delete my account? admins or myself ;)
	if( is_admin() OR (is_user() AND $user==$myforum) ) {
		// check if profile exists
		if(file_exists(get_fn_dir("users")."/".$user.".php") AND is_alphanumeric($user)) {
			unlink(get_fn_dir("users")."/".$user.".php");
			fnlog("Deleteuser", $addr."||".$myforum."||User ".$user." deleted.");
		} else {
			fnlog("Deleteuser", $addr."||".$myforum."||Tried to delete non-existant ".$user." profile.");
		}
		// if I delete my own account, clear cookies
		if($user==$myforum){
			logout();
		}
	}
	// unauthorized tries
	else {
		fnlog("Deleteuser", $addr."||".$myforum."||Tried to delete ".$user." profile.");
	}
	// back to login section
	?><script>window.location='index.php?mod=none_Login';</script><?php
}


/**
 * Esegue il logout da Flatnuke
 *
 * Esegue il logout dal portale Flatnuke e cancella tutti i
 * cookies contenenti le informazioni di autenticazione.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Marco Segato <segatom@users.sourceforge.net> | 20070317: ready to 2.5.9
 */
function logout(){
	// security checks
	$myforum = getparam("myforum",PAR_COOKIE,SAN_FLAT);
	$addr    = getparam("REMOTE_ADDR",PAR_SERVER, SAN_FLAT);
	$url     = getparam("PHP_SELF",PAR_SERVER, SAN_FLAT);
	// rebuild path for cookies
	$path = pathinfo($url);
	$url  = str_replace("/forum","",$path["dirname"]);
	if($url=="")
		$url="/";
	// set cookies to null
	setcookie("secid","",NULL,"$url");
	setcookie("myforum","",NULL,"$url");
	fnlog("Logout", $addr."||".$myforum."||User $myforum logout.");
	// go back
	//rimosso il codice per tornar alla pagina precedente perchè, se soggetta
	//a limiti di visione viene restituito un errore
	//getparam("HTTP_REFERER", PAR_SERVER, SAN_FLAT)
	?><script>window.location='index.php';</script>
	<noscript><div style="text-align: center;"><br><b><a href="index.php" title="Home page">Home page</a></b></div></noscript><?php
}


/**
 * Questa funzione mostra l'interfaccia per modificare o creare un profilo utente
 *
 * @author Aldo Boccacci
 * @author Marco Segato <segatom@users.sourceforge.net> | 20110507: use of captcha
 *
 * @since 2.6.2
 */
function edit_user_interface($user,$action){
	if (!is_alphanumeric($user) and $user!="") die(_NONPUOI);
	if (!preg_match("/edit|reguser/i",trim($action)))
		die();
	$profile = array();
	$profile = load_user_profile($user);

	global $reguser;

	?>
		<script src="include/javascripts/md5.js"></script>
		<script type="text/javascript">
	function show_gravatar()
	{
		if(document.getElementsByName('email')[0].value == '') {
			document.getElementsByName('avatar')[0].src="forum/images/"+document.getElementsByName('ava')[0].options[document.getElementsByName('ava')[0].selectedIndex].value;
			document.getElementsByName('url_avatar')[0].value="";
			return;
		}

		document.getElementsByName('avatar')[0].src="http://cdn.libravatar.org/avatar/" + calcMD5(document.getElementsByName('email')[0].value) + "?s=100";
		document.getElementsByName('url_avatar')[0].value="http://cdn.libravatar.org/avatar/" + calcMD5(document.getElementsByName('email')[0].value) + "?s=100";

	}
	function validate_reguser()
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
			<?php
			//se è obbligatoria anche la mail controllo che sia presente
			if ($reguser=="2" and $action=="reguser"){
				?>
			else if(document.getElementsByName('email')[0].value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._FEMAIL?>');
					document.getElementsByName('email')[0].focus();
					document.getElementsByName('email')[0].value='';
					return false;
				}
				<?php
			}
			?>
			if(document.getElementsByName('captcha')[0].value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": Antispam"?>');
					document.getElementsByName('captcha')[0].focus();
					document.getElementsByName('captcha')[0].value='';
					return false;
				}
			else return true;
		}

	function validate_edit(){
		if(document.getElementsByName('regpass')[0].value != document.getElementsByName('reregpass')[0].value) {
			alert('<?php echo _PASSERR?>');
			if (document.getElementsByName('regpass')[0].value=='') {
				document.getElementsByName('regpass')[0].focus();
				document.getElementsByName('regpass')[0].value='';
			}
			else {
				document.getElementsByName('reregpass')[0].focus();
				document.getElementsByName('reregpass')[0].value='';
			}
			return false;
		} else return true;

	}
	</script>

	<form action="index.php?mod=none_Login" method="post" name="<?php

	if ($action=="edit") echo "registra\" onsubmit=\"return validate_edit()";
	else if ($action=="reguser") echo "reguser\" onsubmit=\"return validate_reguser()";

	?>">
		<fieldset>
		<legend><?php

		if ($action=="edit") echo _FMODPROFTIT." $user";
		else if ($action=="reguser") echo _FREG." "._FCAMPI;

		?></legend><?php

		if ($action=="edit"){
			echo "<input type=\"hidden\" name=\"action\" value=\"saveprofile\" />
			<input type=\"hidden\" name=\"user\" value=\"$user\" />";
		}
		else if ($action=="reguser"){
			echo "<input type=\"hidden\" name=\"action\" value=\"reguser\" />";
		}

		$style1 = "padding:0.2em;";
		$style2 = "padding:0.2em;";
		?>
		<br>
		<table style="text-align:center: border:1px; width:70%;border-collapse:collapse">
		<tbody>
		<?php
		if ($action=="edit"){?>
		<tr>
		<td width="30%" style="<?php echo $style1?>"><label for="regpass"><?php echo _PASSWORD?><br>(<?php echo _FDIVERS?>)</label></td>
		<td style="<?php echo $style2?>"><input name="regpass" type="password" id="regpass" value="" /></td>
		</tr>
		<tr>
		<td width="30%" style="<?php echo $style1?>"><label for="reregpass"><?php echo _REPEATPASSWORD?><br>(<?php echo _FDIVERS?>)</label></td>
		<td style="<?php echo $style2?>"><input name="reregpass" type="password" id="reregpass" value="" /></td>
		</tr>
		<?php
		}//fine $action==edit
		else if ($action=="reguser"){
		?>
		<tr><td>
		<label for="nome"><b><span>*</span>&nbsp;<?php echo _NOMEUTENTE?></b></label></td><td><input name="nome" type="text" id="nome" value="Username" onfocus="
	if (this.value=='Username'){this.value='';}"/></td></tr>
	<tr><td><label for="regpass"><b><span>*</span>&nbsp;<?php echo _PASSWORD?></b></label></td><td><input name="regpass" type="password" id="regpass" /></td></tr>
	<tr><td><label for="reregpass"><b><span>*</span>&nbsp;<?php echo _REPEATPASSWORD?></b></label></td><td> <input name="reregpass" type="password" id="reregpass" /></td>
		</tr>
		<?php
		}//fine $action=="reguser"
		?>
		<tr>
		<td style="<?php echo $style1?>"><label for="anag"><?php echo _FNOME?></label></td>
		<td style="<?php echo $style2?>"><input name="anag" type="text" id="anag" value="<?php echo $profile['name']?>" /></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><label for="email"><?php

		if ($reguser=="2" and $action=="reguser") echo "<span>*</span>&nbsp;<b>";
		echo _FEMAIL;
		if ($reguser=="2" and $action=="reguser") echo "</b>";

		?></label></td>
		<td style="<?php echo $style2?>"><input name="email" type="text" id="email" value="<?php echo $profile['mail']?>" /></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><label for="hiddenmail"><?php echo _HIDDENMAIL?></label></td>
		<td style="<?php echo $style2?>"><input name="hiddenmail" type="checkbox" id="hiddenmail"<?php

		if ($profile['hiddenmail']=="1") echo "checked=\"checked\"";

		?>/></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><label for="homep"><?php echo _FHOME?></label></td>
		<td style="<?php echo $style2?>"><input name="homep" type="text" id="homep" value="<?php echo $profile['homepage']?>" /></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><label for="prof"><?php echo _FPROFES?></label></td>
		<td style="<?php echo $style2?>"><input name="prof" type="text" id="prof" value="<?php echo $profile['work']?>" /></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><label for="prov"><?php echo _FPROV?></label></td>
		<td style="<?php echo $style2?>"><input name="prov" type="text" id="prov" value="<?php echo $profile['from']?>" /></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><label for="fnjabber">Jabber / Google Talk</label></td>
		<td style="<?php echo $style2?>"><input name="fnjabber" type="text" id="fnjabber" value="<?php echo $profile['jabber']?>" /></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><label for="fnskype">Skype</label></td>
		<td style="<?php echo $style2?>"><input name="fnskype" type="text" id="fnskype" value="<?php echo $profile['skype']?>" /></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><label for="fnicq">ICQ</label></td>
		<td style="<?php echo $style2?>"><input name="fnicq" type="text" id="fnicq" value="<?php echo $profile['icq']?>" /></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><label for="fnmsn">MSN</label></td>
		<td style="<?php echo $style2?>"><input name="fnmsn" type="text" id="fnmsn" value="<?php echo $profile['msn']?>" /></td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><label for="ava"><?php echo _FAVAT?></label></td>
		<td style="<?php echo $style2?>"><?php
			// set path for local/remote images
			$img_dir = "";
			if(strstr($profile['avatar'],"images/")) {
				$img_dir = "forum/";
			}
			?><img src="<?php

			if ($action=="edit") echo $img_dir.$profile['avatar'];
			else if ($action=="reguser") echo "forum/images/blank.png";


			?>" alt="avatar" style="max-width:120px;border:0" id="avatar" />
			<br>
			<select name="ava" id="ava" onchange='document.avatar.src="forum/images/"+this.options[this.selectedIndex].value'>
			<option value="" disabled="disabled">----</option><?php
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
				echo "<option value=\"$modlist[$i]\" ";
				if(str_replace("images/","",$profile['avatar'])==$modlist[$i])
					echo "selected=\"selected\"";
				echo ">$modlist[$i]</option>\n";
			}
			?></select><br><br>
			<?php echo _FAVATREM?>:<br><?php
			if(strstr($profile['avatar'],"http://")){
				echo "<input type=\"text\" name=\"url_avatar\" value=".$profile['avatar']." />";
			}
			else {
				echo "<input type=\"text\" name=\"url_avatar\" />";
			}
			?>
			<br><br>
			<input name="use_gravatar" type="button" id="use_gravatar" onclick="show_gravatar()" value="<?php echo _USEGRAVATAR?>"/>
			<?php echo " "._GRAVATARINFO; ?>

		</td>
		</tr>
		<tr>
		<td style="<?php echo $style1?>"><label for="firma"><?php echo _FFIRMA?></label></td><?php
		$profile['sign'] = str_replace("<br>","\n",$profile['sign']);
		$profile['sign'] = chop($profile['sign']);
		?><td style="<?php echo $style2?>"><textarea name="firma" id="firma" rows="5" cols="23"><?php echo stripslashes($profile['sign']);?></textarea></td>
		</tr>

		<tr>
		<td style="<?php echo $style1?>"><label for="fnpresentation"><?php echo _FNPRESENTATION?></label></td><?php
		if ($action=="reguser"){
			?><td style="<?php echo $style2?>"><textarea name="fnpresentation" id="fnpresentation" rows="5" cols="23"></textarea></td><?php
		} else {
			?><td style="<?php echo $style2?>"><textarea name="fnpresentation" id="fnpresentation" rows="5" cols="23"><?php echo stripslashes($profile['presentation']);?></textarea></td><?php
		}
		?></tr>

		<?php // admins can manage user level
		if(is_admin() and get_username()!=$user and $user!=""){
			?><tr><td style="<?php echo $style1?>"><label for="level"><?php echo _LEVEL?></label></td>
			<td style="<?php echo $style2?>">
			<select name="level" id="level"><?php
				for($i=0; $i<11; $i++){
					if($profile['level']==$i)
						echo "<option value=\"$i\" selected=\"selected\">$i</option>";
					else echo "<option value=\"$i\">$i</option>";
				}
			?></select>
			</td></tr><?php
		} else {
			if ($action=="edit") echo "<tr>
				<td style=\"$style1\">"._LEVEL."</td>
				<td>".$profile['level']."
				<input id=\"level\" name=\"level\" value=\"".$profile['level']."\" type=\"hidden\" /></td></tr>";

		}
		?>
		</tbody>
		</table><?php
		if($action=="reguser") {
			echo "<div style='font-family:monospace;text-align:justify;padding:1em;'>"._REG_AGREEMENT_TERMS."</div>";
			// starting session for anti spam checks with captcha
			echo "<div style='padding:1em'>";
				include("include/captcha/fncaptcha.php");
				$fncaptcha = new fncaptcha();
				$fncaptcha->generateCode();
				$fncaptcha->printCaptcha("captcha","captcha");
			echo "</div>";
		}
		?><div style="text-align:center;padding:0.5em;"><input type="submit" value="<?php echo _FINVIA?>" /></div>
		</fieldset>
		</form><?php
}


/**
 * Attiva l'utente specificato
 *
 * @param string $user l'utente da attivare
 * @param string $regcode il codice di attivazione da fornire
 * @since 2.6.2
 * @author Aldo Boccacci
 */
function activate_user($user,$regcode){
	$user = getparam($user,PAR_NULL,SAN_FLAT);
	$regcode = getparam($regcode,PAR_NULL,SAN_FLAT);
	$addr    = getparam("REMOTE_ADDR",PAR_SERVER, SAN_FLAT);
	if (!is_alphanumeric($user)) die("Username must be alphanumeric!");
	if (!check_var($regcode,"digit")) die("regcode must be a digit!");

	if (file_exists(get_waiting_users_dir()."/$user.php")){
		$userdata = array();
		$userdata = load_user_profile($user,1);

		if (trim($regcode)==trim($userdata['regcode'])){
			if (rename(get_waiting_users_dir()."/$user.php",get_fn_dir('users')."/$user.php")){
				echo "<div style=\"text-align: center;\">"._FORAREG;
				?><br>
				<a href="index.php?mod=none_Login">&lt;&lt;<?php echo _LOGIN?></a></div><?php
				fnlog("Registration","$addr||".get_username()."||User $user succesfully activate the profile");
			}
			else {
				fnlog("Registration","$addr||".get_username()."||It was impossible to rename the profile of the user $user");
			}
		}
		else {
			echo "Reg code is not valid!";
			fnlog("Registration","$addr||".get_username()."||Regcode of the user $user is not matching");
			die();
		}
	}
	else {
		fnlog("Registration","$addr||".get_username()."||The user $user isn't in the waiting list!");
		die("The user $user isn't in the waiting list!");
	}
}


/**
 * Interfaccia richiesta nuova password
 *
 * La funzione visualizza tutte le informazioni necessarie
 * all'utente per richiedere una nuova password.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @since 2.7.1
 */
function password_lost() {
	?><div style='text-align:justify;padding:1em;'><?php
	echo _NEWPWDFORM;
	?></div>
	<div style="text-align:center;padding:1em;">
	<form action="index.php?mod=none_Login" method="post">
		<input type="hidden" name="action" value="sendnewpassword" />
		<label for="email"><?php echo _FEMAIL?>: </label><input name="email" type="text" id="email" />
		<input type="submit" />
	</form>
	</div><?php
}


/**
 * Invio nuova password
 *
 * La funzione permette di inviare una nuova password
 * all'utente che ne ha fatto richiesta; viene spedita
 * una email che contiene l'URL da visitare per attivare
 * il cambio di password.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @since 2.7.1
 */
function send_new_password() {
	// security checks
	$email = trim(getparam("email", PAR_POST, SAN_FLAT));
	$addr  = getparam("REMOTE_ADDR",PAR_SERVER, SAN_FLAT);
	global $sitename;

	// get the list of all users' emails
	$emails_active = list_users_emails();
	//echo "<pre>"; print_r($emails_active); echo "</pre>"; //-> TEST
	echo "<div style='text-align:justify;padding:1em;'>";

	if(!in_array($email, $emails_active)) {
		echo _ERREMAIL;	// provided address doesn't exist
	} else {
		$user_arr  = array_keys($emails_active, $email);
		$user      = $user_arr[0];	// username
		$user_data = load_user_profile($user);	// user profile
		// create the new password for registration and save it
		$rand_pwd = rand(10000000, 99999999);
		$user_data['regcode'] = $rand_pwd;
		save_user_profile($user, $user_data);
		// try to send activation code to the user
		$url = "http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."?mod=none_Login&action=newpwd&user=$user&regcode=$rand_pwd";
		//echo "<p><a href=\"$url\">$url</a></p>"; //-> TEST
		$message = _NEWPWDMAIL;
		$message = str_replace("__USERNAME__", $user, $message);
		$message = str_replace("__SITEURL__", "http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'], $message);
		$message = str_replace("__URLCONFIRMPASSWORDCHANGE__", $url, $message);
		$message = str_replace("__REGCODE__", $rand_pwd, $message);
		if (@mail($email, _NEWPWDFROM." $sitename", $message,"FROM: $sitename <noreply@noreply>\r\nX-Mailer: Flatnuke on PHP/".phpversion())){
			echo _COMP_REG." <b>$email</b>";
			echo "<br>"._COMP_REG2;
			fnlog("New password", $addr."||||Activation mail for a new password sent to user '$user'.");
		} else {
			echo _ACTIVATIONMAILNOTSENT;
			fnlog("New password", $addr."||||Activation mail for a new password not sent to user '$user'.");
		}
	}
	echo "</div>";
}


/**
 * Attivazione nuova password richiesta
 *
 * La funzione permette di attivare la nuova password
 * inviata via email all'utente che ne ha fatto richiesta.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @since 2.7.1
 *
 * @param string $user    Nome utente
 * @param string $regcode Codice di attivazione
 */
function activate_newpwd($user, $regcode) {
	// security checks
	$user    = getparam($user,PAR_NULL,SAN_FLAT);
	$regcode = getparam($regcode,PAR_NULL,SAN_FLAT);
	$addr    = getparam("REMOTE_ADDR",PAR_SERVER, SAN_FLAT);

	echo "<div style='text-align:center;padding:1em;'>";
	if (file_exists(get_fn_dir("users")."/$user.php") AND is_alphanumeric($user)) {
		$userdata = load_user_profile($user);
		if ($regcode==$userdata['regcode'] AND check_var($regcode,"digit")) {
			// build new password
			$userdata['password'] = md5($regcode);
			// once activated, reset regcode
			$userdata['regcode']  = 0;
			save_user_profile($user, $userdata);
			fnlog("New password","$addr||||User '$user' succesfully activated the new password.");
		} else {
			fnlog("New password","$addr||||Regcode of the user '$user' is not matching.");
		}
	} else {
		fnlog("New password","$addr||||Tried to activate a new password for unexistant '$user' user.");
	}
	// reset cookies
	logout();
	header("Location: index.php?mod=none_Login");
	die();
}

?>
