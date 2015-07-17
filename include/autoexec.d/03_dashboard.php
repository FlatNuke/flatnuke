<?php
/**
 * This module shows Admin area in a dashboard on Flatnuke.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @author Lorenzo Caporale <piercolone@gmail.com>
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */

if (preg_match("/dashboard.php/i", $_SERVER['PHP_SELF'])) {
	Header("Location: ../../index.php");
	die();
}

// security checks
$mod = _FN_MOD;
global $sitename, $lang;

// access reserved to administrator
if($mod=="none_Admin" AND is_admin()) {
	// language definition
	switch($lang) {
		case "it":
			include_once ("languages/admin/$lang.php");
		break;
		case "fr":
			include_once ("languages/admin/$lang.php");
		break;
		default:
			include_once ("languages/admin/en.php");
	}
	// need some Flatnuke administration API
	include_once (get_fn_dir("sections")."/$mod/none_functions/func_interfaces.php");
	?>
	<body>
				<!--success floating label after submit-->
		<div id="form-success" class="fncc-form-success">
			<p class="fncc-message-success bg-success lead"><?php echo _FNCC_MESSAGE_SUCCESS; ?></p>
		</div>
		<div id="wrapper">
			<nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom: 0">
	            <div class="navbar-header">
	                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-collapse">
	                    <span class="sr-only">Toggle navigation</span>
	                    <span class="icon-bar"></span>
	                    <span class="icon-bar"></span>
	                    <span class="icon-bar"></span>
	                </button>
	                <a class="navbar-brand" href="index.php" title="<?php echo _FNCC_SHOWHOMEPAGE ?>"><span id="top-sitename"></span><?php echo $sitename; ?>&nbsp;&nbsp;<i class="fa fa-external-link-square"></i></a>
	            </div>
	            <ul class="nav navbar-top-links navbar-right">
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">
							<i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
						</a>
						<ul class="dropdown-menu dropdown-user">
							<li><a href="index.php?mod=none_Login&amp;action=viewprofile&amp;user=<?php echo get_username()?>"><i class="fa fa-user fa-fw"></i> <?php echo get_username()?> Profile</a>
							</li>
							<li><a href="javascript:jQueryFNcall('sections/none_Admin/section.php?mod=none_Admin&op=fnccconf','get','fncc-adminpanel');"><i class="fa fa-gears fa-fw"></i> Settings</a>
							</li>
							<li class="divider"></li>
							<li><a href="index.php?mod=none_Login&amp;action=logout&amp;from=home" title="<?php echo _LOGOUT ?>"><i class="fa fa-sign-out fa-fw"></i><?php echo _LOGOUT ?></a>
							</li>
						</ul>
						<!-- /.dropdown-user -->
					</li>
					<!-- /.dropdown -->
				</ul>
				<!-- /.navbar-top-links -->
				<div class="navbar-default navbar-static-side" role="navigation">
                <div class="sidebar-collapse">
                    <ul class="nav" id="side-menu">
                        <li class="sidebar-search">
                            <div class="input-group custom-search-form">
                                <input type="text" class="form-control" placeholder="Search...">
                                <span class="input-group-btn">
                                <button class="btn btn-default" type="button">
                                    <i class="fa fa-search"></i>
                                </button>
                            </span>
                            </div>
                            <!-- /input-group -->
                        </li>
                        <?php fncc_main_new(); ?>
                    </ul>
                    <!-- /#side-menu -->
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>				
        <div id="page-wrapper">
			<div id="fncc-adminpanel">
				<?php include_once (get_fn_dir("sections")."/$mod/none_widgets/section.php"); ?>
				<?php getflopt(); ?>
			</div>
        </div>
        <!-- /#page-wrapper -->
    </div>
    <!-- /#wrapper -->
    
	<script src="<?php echo get_fn_dir("sections")."/$mod/none_js"?>/metisMenu/jquery.metisMenu.js"></script>
	<script src="<?php echo get_fn_dir("sections")."/$mod/none_js"?>/morris/raphael-2.1.0.min.js"></script>
	<script src="<?php echo get_fn_dir("sections")."/$mod/none_js"?>/morris/morris.js"></script>
    <script src="<?php echo get_fn_dir("sections")."/$mod/none_js"?>/dataTables/jquery.dataTables.min.js"></script>
    <script src="<?php echo get_fn_dir("sections")."/$mod/none_js"?>/dataTables/dataTables.bootstrap.js"></script>
	<script src="<?php echo get_fn_dir("sections")."/$mod/none_js"?>/slider/jquery.nouislider.min.js"></script>
	<script src="<?php echo get_fn_dir("sections")."/$mod/none_js"?>/tokenfield/bootstrap-tokenfield.js"></script>
	<script src="<?php echo get_fn_dir("sections")."/$mod/none_js"?>/validator/validator.min.js"></script>	


    <!-- Page-Level Plugin Scripts - Blank -->
	

    <!-- SB Admin Scripts - Include with every page -->
    <script src="<?php echo get_fn_dir("sections")."/$mod/none_js"?>/sb-admin.js"></script>

<!--
Add slider: Flatnuke extends jQuery functions
@author Alfredo Cosco <orazio.nelson@gmail.com>
@version 20140514
-->
<script>
(function( $ ){
   $.fn.add_slider = function (keyword) {
	$('#form-'+keyword).after('<div id="'+keyword+'-slider" style="margin-bottom:1em;"></div>');
	var min = $('#'+keyword).attr('min');	
	var max = $('#'+keyword).attr('max');
	var v = $('#'+keyword).attr('value');
	var w = $('#'+keyword).css('width');		
	$('#'+keyword+'-slider').noUiSlider({
		start: parseInt(v),
		range: {
		  'min': parseInt(min),
		  'max': parseInt(max)
		},
		serialization: {
			lower: [
			$.Link({
				target: $('#'+keyword),
				format: {
				decimals: 0,
					}					
				})
			]
		}
	}).css('width', w);
	};
})( jQuery );
</script>
	</body>
	</html><?php
	exit();
}

?>
