<?php
/**
 * Fncaptcha: the class for manage captcha with Flatnuke
 *
 * Author: Aldo Boccacci
 * Web site: www.aldoboccacci.it
 *
 * Flatnuke's site: www.flatnuke.org
 *
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA
 */

class fncaptcha {
	/**
	 * If this is set to TRUE disable captcha
	 */
	var $disable = FALSE;

	/**
	* Actually do nothing
	*
	* @author Aldo Boccacci
	*/
	function fncaptcha(){
	}

	/**
	* Set the code
	*
	* @param string $code the verification code
	* @author Aldo Boccacci
	*/
	function setCode($code){
		global $disable;
		if ($disable)
			return;
		if (!session_id())
			session_start();
		$_SESSION[ 'security_code' ] = strip_tags($code);
	}

	/**
	* Return the verification code
	*
	* @return string the verification code
	* @author Aldo Boccacci
	*/
	function getCode(){
		global $disable;
		if ($disable)
			return "";
		if (isset($_SESSION[ 'security_code' ]))
			return $_SESSION[ 'security_code' ];
		else return "";
	}

	/**
	* Generate the verification code
	*
	* @return string the verification code
	* @author Aldo Boccacci
	*/
	function generateCode(){
		global $disable;
		if ($disable)
			return;
		if (!session_id())
			session_start();
		$_SESSION[ 'security_code' ] = rand(100000, 999999);
		return $_SESSION[ 'security_code' ];
	}

	/**
	* Check the verification code
	*
	* @param string $code the verification code
	* @return TRUE if the verification code i correct, FALSE otherwise
	* @author Aldo Boccacci
	*/
	function checkCode($code){
		global $disable;
		if ($disable)
			return TRUE;

		if (!session_id())
			session_start();

		if (isset($_SESSION['security_code']) AND $code==$_SESSION[ 'security_code' ]){
			unset($_SESSION['security_code']);
			return TRUE;
		}
		else{
			//unset($_SESSION['security_code']);
			return FALSE;
		}
	}

	/**
	* Check the verification code
	*
	* @return TRUE if the capthca is disabled, FALSE otherwise
	* @author Aldo Boccacci
	*/
	function isDisabled(){
		global $disable;
		if ($disable)
			return TRUE;
		else return FALSE;
	}

	/**
	 * Print the capthca image
	 *
	 * @param string $name the module name
	 * @param string $id the module id
	 * @author Aldo Boccacci
	 */
	function printCaptcha($name, $id){
		if ($this->disable)
			return;

		if ($name=="")
			$name = "captcha";
		if ($id=="")
			$id = "captcha";
		if(function_exists('imagecreate')) {
			$sec_image_code = "<img src='include/captcha/captcha.php' alt='antispam code' title='antispam code'>";
		} else {
			$sec_image_code = $_SESSION['security_code'];
		}
		echo "<label for='$id'><b>"._CN_ANTISPAM."<br>\t"; printf($sec_image_code); echo "</b></label><br>\n
			<input type='text' name='$name' id='$id' value='' /><br><br>";
	}
}
?>
