<?php

/*
 * FlatNuke Contact Section
 * Copyright (C) 2006 Marco Segato
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the license, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, inc., 59 Temple Place - Suite 320, Boston, MA  02111-1207, USA
 */

/*
 * Flatnuke (http://www.flatnuke.org/) section for contacting the administrator
 *
 * Author    Marco Segato  <segatom@users.sourceforge.net>
 * Website   http://marcosegato.altervista.org/
 * Version   1.2
 * Date      20110720
 *
 * Some code included is from Giovanni Piller Cottrer <giovanni.piller@gmail.com> http://gigasoft.altervista.org/
 * Library captcha.php is taken from Simple PHP Blog project: see http://www.simplephpblog.com/ for license terms
 *
 */

###########################################
############# SECURITY CHECKS #############
###########################################

// do not let users directly access this file
if (preg_match("/section.php/i",$_SERVER['PHP_SELF'])) {
	Header("Location: ../../index.php");
	die();
}

// sanitizing variables ---> requires Flatnuke >= 2.5.7
$req     = getparam("REQUEST_URI", PAR_SERVER, SAN_NULL);
$req     = str_replace("&", "&amp;", $req);
$mod     = _FN_MOD;
$name    = getparam("name",        PAR_POST,   SAN_FLAT);
$contact = getparam("contact",     PAR_POST,   SAN_FLAT);
$subject = getparam("subject",     PAR_POST,   SAN_FLAT);
$message = getparam("message",     PAR_POST,   SAN_NULL);
$captcha = getparam("captcha",     PAR_POST,   SAN_FLAT);

$req = strip_tags($req);
$name = strip_tags($name);
$contact = strip_tags($contact);
$subject = strip_tags($subject);
$message = strip_tags($message);
$captcha = strip_tags($captcha);

#########################################
############# CONFIGURATION #############
#########################################

// include Flatnuke main configuration
global $lang;
global $admin_mail;


##########################################
############# MAIN EXECUTION #############
##########################################

if($contact==""){
	//javascript code by Aldo Boccacci
	?>
	<script type="text/javascript">
	function check_email(email){
		var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		if (!filter.test(email)) {
			return false;
		}
		else return true;

	}

	function validate_email_form()
		{
			if(document.getElementById('name').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._FNOME?>');
					document.getElementById('name').focus();
					document.getElementById('name').value='';
					return false;
				}
			if(document.getElementById('contact').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._CN_YOUR_EMAIL?>');
					document.getElementById('contact').focus();
					document.getElementById('contact').value='';
					return false;
				}
			if (!check_email(document.getElementById('contact').value)){
				alert('<?php echo _CN_ADDRESSERROR?>');
				document.getElementById('contact').focus();
				document.getElementById('contact').value='';
				return false;
			}
			if(document.getElementById('subject').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._CN_SUBJECT?>');
					document.getElementById('subject').focus();
					document.getElementById('subject').value='';
					return false;
				}
			if(document.getElementById('message').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._FMESS?>');
					document.getElementById('message').focus();
					document.getElementById('message').value='';
					return false;
				}
			if(document.getElementById('captcha').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._CN_ANTISPAM?>');
					document.getElementById('captcha').focus();
					document.getElementById('captcha').value='';
					return false;
				}
			else return TRUE;
		}
	</script>

	<?php
	// print the HTML form
	echo "<p>"._CN_TITLE."</p>\n
		<div style='margin-left: 1em'>\n
		<form action='$req' method='post' onsubmit=\"return validate_email_form()\">\n
			<label for='name'>"._FNOME.":</label><br><input type='text' name='name' id='name' style='width:60%' ";
			if (!is_guest()){
				$data = load_user_profile(_FN_USERNAME);
				if ($data['name']!="") echo "value=\"".$data['name']." ("._FN_USERNAME.")\"";
				else echo "value=\""._FN_USERNAME."\"";
			}
			else if (isset($_SESSION['name'])){
				echo "value=\"".strip_tags($_SESSION['name'])."\"";
				unset($_SESSION['name']);
			}
			echo "/><br><br>\n
			<label for='contact'>"._CN_YOUR_EMAIL.":</label><br><input type='text' name='contact' id='contact' style='width:60%' ";
			if (!is_guest()){
// 				$data = load_user_profile(_FN_USERNAME);
				if ($data['mail']!="") echo "value=\"".$data['mail']."\"";
				else if (isset($_SESSION['contact'])){
					echo "value=\"".strip_tags($_SESSION['contact'])."\"";
					unset($_SESSION['contact']);
				}
			}
			else if (isset($_SESSION['contact'])){
				echo "value=\"".strip_tags($_SESSION['contact'])."\"";
				unset($_SESSION['contact']);
			}
			echo "/><br><br>\n
			<label for='subject'>"._CN_SUBJECT.":</label><br><input type='text' name='subject' id='subject' style='width:95%'";
			if (isset($_SESSION['subject'])){
				echo "value=\"".strip_tags($_SESSION['subject'])."\" ";
				unset($_SESSION['subject']);
			}
			echo "/><br><br>\n
			<label for='message'>"._FMESS.":</label><br><textarea name='message' id='message' rows='20' cols='80' style='width:95%'>";
			if (isset($_SESSION['message'])){
				echo strip_tags($_SESSION['message']);
				unset($_SESSION['message']);
			}
			echo "</textarea><br>\n";

			include("include/captcha/fncaptcha.php");
			$fncaptcha = new fncaptcha();
			$fncaptcha->generateCode();
			$fncaptcha->printCaptcha("captcha","captcha");

			echo "<p>"._CN_ADVISORY."</p>\n
			<input type='submit' value='"._FP_FINVIA."' /> <input type='reset' value='"._CN_CLEAR."' />\n
		</form>\n
		</div>\n";
} else {
	// checking the value of anti-spam code inserted
	include("include/captcha/fncaptcha.php");
	$fncaptcha = new fncaptcha();
	$captchaok = $fncaptcha->checkCode($captcha);

	if(!$captchaok) {
		// anti-spam code is NOT right

		$_SESSION['subject'] = $subject;
		$_SESSION['message'] = $message;
		$_SESSION['name'] = $name;
		$_SESSION['contact'] = $contact;
		$_SESSION['selectcaptcha'] = "1";

		// back or automatic redirect to the index after 2 seconds
		?><div style="text-align:center;"><?php
		echo "<b>"._CN_CODERROR."</b><br>";
		?><p><a href="<?php echo $req?>"><?php
		echo _INDIETRO;
		?></a></p><meta http-equiv="Refresh" content="2; URL=<?php echo $req?>"></div><?php
		return;
	}
	// anti-spam code IS right: build the argument to pass to mail() function
	$message = "$name "._CN_WROTE.":\n".stripslashes($message)."\n\n"._CN_IP.": ".getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	$headers = "From: $contact\r";
	// checkin the mail address
	if(check_mail_address($contact)){
		global $sitename;
		$sendmail = mail($admin_mail, $subject." (from ".strip_tags($sitename).")", $message, $headers);
		if($sendmail){
			// the mail was correctly send, kill session security code
			echo "<div style=\"text-align:center;\"><br><b>"._CN_SENDOK."</b><br>";
			unset($_SESSION['security_code']);
			// back or automatic redirect to the index after 2 seconds
			?><p><a href="<?php echo $req?>"><?php echo _INDIETRO?></a></p><meta http-equiv="Refresh" content="2; URL=<?php echo $req?>"></div><?php
		} else {
			// the server do not support sending emails, kill session security code
			echo "<div style=\"text-align:center;\"><br><b>"._CN_SENDKO."</b><br>";
			unset($_SESSION['security_code']);
			?><p><a href="<?php echo $req?>"><?php echo _INDIETRO?></a></p><meta http-equiv="Refresh" content="2; URL=<?php echo $req?>"></div><?php
		}
	} else {
		// there was an error in the mail address, kill session security code
		echo "<div style=\"text-align:center;\"><br><b>"._CN_ADDRESSERROR."</b><br>";
		unset($_SESSION['security_code']);
		?><p><a href="<?php echo $req?>"><?php echo _INDIETRO?></a></p><meta http-equiv="Refresh" content="2; URL=<?php echo $req?>"></div><?php
	}

}


##########################################
############# FUNCTIONS USED #############
##########################################

/**
 * Check the validity and the availability of a mail address
 *
 * This function checks first of all if the mail address is written correctly;
 * moreover, some mail servers let check if the domain specified into the address
 * really exists; this feature is disabled by default: to turn it on, just change
 * $check_dns variable from FALSE to TRUE.
 *
 * @author Giovanni Piller Cottrer <giovanni.piller@gmail.com>
 *
 * @param string  $addr      Mail address to verify
 * @param boolean $check_dns Check DNS or not
 */
function check_mail_address($addr, $check_dns=FALSE) {
	if(preg_match('/^(\w|\.|\-)+@\w+(\.\w+)*\.[a-zA-Z]{2,4}$/',$addr)) {
		if($check_dns) {
			$host = explode('@', $addr);
			// Check for MX record
			if( checkdnsrr($host[1], 'MX') ) return TRUE;
			// Check for A record
			if( checkdnsrr($host[1], 'A') ) return TRUE;
			// Check for CNAME record
			if( checkdnsrr($host[1], 'CNAME') ) return TRUE;
		} else {
			return TRUE;
		}
	}
	return FALSE;
}

?>
