<?php

/**
 * This module shows maintenance mode on Flatnuke.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Marco Segato <segatom@users.sourceforge.net>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 *
 * @version 20090523 - CSS implementation - Lorenzo Caporale <piercolone@gmail.com>
 *
 */

if (preg_match("/maintenance.php/i", $_SERVER['PHP_SELF'])) {
	Header("Location: ../../index.php");
	die();
}

global $maintenance;

if ($maintenance == "1") {
	if(!is_admin()) {
		?><div id="FN-maint-structure" style="text-align:center">
			<div id="FN-maint-box">
			<img alt="Flatnuke CMS login" src="images/maintenance.png" /><br>
			<br>
			<span id="FN-maint-text">
				<b><?php echo _MAINT ?></b><br>
				<?php echo _MAINT_MSG ?>
			</span><br><br>
			<form action="sections/none_Login/section.php" method="post">
			<input type="hidden" name="action" value="login" />
			<label for="username" class="FN-maint-label"><?php echo _NOMEUTENTE ?>:</label><br>
			<input alt="username" name="nome" size="15" id="username" class="FN-maint-input" value="Username" onfocus="if(this.value=='Username'){this.value='';}" onblur="if(this.value==''){this.value='Username';}" /><br>
			<label for="password" class="FN-maint-label"><?php echo _PASSWORD ?>:</label><br>
			<input alt="password" name="logpassword" type="password" size="15" id="password" class="FN-maint-input" value="********" onfocus="javascript:this.value='';" /><br>
			<br>
			<input type="submit" value="<?php echo _LOGIN ?>" class="FN-maint-submit" />
			</form>
			</div>
		</div><?php
		$footer_elements = get_footer_array();
		echo $footer_elements['img_fn']." ";
		echo $footer_elements['img_w3c']." ";
		echo $footer_elements['img_css']." ";
		echo $footer_elements['img_rss']." ";
		echo $footer_elements['img_mail']."<br>";
		echo $footer_elements['legal']."<br>";
		echo $footer_elements['time'];
		exit();
	} else {
		echo "<div id=\"FN-maint-alert\">"._MAINT."</div>";
	}
}

?>
