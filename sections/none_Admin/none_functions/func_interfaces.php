<?php
/*
 * Format the parent items in the menu
 *
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * @version 201405
 * 
 */
function fncc_format_parent_item($icon_name,$label,$icon_size="fw") {
	echo "<a href=\"#\"><i class=\"fa fa-".$icon_name." fa-".$icon_size."\"></i>&nbsp;".$label."<span class=\"fa arrow\"></span></a>";
	}
	
function fncc_format_end_item($link_func_name, $icon_name,$label,$icon_size="fw") {
	$mod = getparam("mod", PAR_GET, SAN_FLAT);
	echo "<li>";
	echo build_fnajax_link($mod, "&amp;op=".$link_func_name, "fncc-adminpanel", "get");
	echo "<i class=\"fa fa-".$icon_name." fa-".$icon_size."\"></i>&nbsp;";
	echo $label;
	echo "</a></li>";
	}
	
/*
 * Print main menu with options' icons
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @author Alfredo Cosco <orazio.nelson@gmail.com>, rewrtote to work with admin theme and metisMenu
 * @version 201405
 */	
function fncc_main_new() {
	// security conversions
	$mod = getparam("mod", PAR_GET, SAN_FLAT);
	?>                         
		<li>
		<a href="index.php?mod=<?php echo $mod?>" title="Dashboard" accesskey="0">
			<i class="fa fa-dashboard fa-fw"></i>&nbsp;<?php echo _FNCC_DASHBOARD?>	
		</a>
		</li>
		<li>
			<?php fncc_format_parent_item('umbrella', _FNCC_FNOPTIONS)?>
			<ul class="nav nav-second-level">
			<?php 
			//GENERAL INFOS
			fncc_format_end_item('fnccinfo','info-circle',_FNCC_SERVERINFOS);
			//GENERAL CONFIGURATION
			fncc_format_end_item('fnccconf','cogs',_FNCC_GENERALCONF);
			?>
			<!-- FTP MANAGER -->
			<li><a href="#" onclick="window.open('sections/<?php echo $mod?>/none_tools/webadmin.php','','toolbar=no,scrollbars=yes,resizable=yes');">
				<i class="fa fa-folder-open fa-fw"></i>&nbsp;<?php echo _FNCC_FILEMANAGER?>
			</a></li>
			</ul>
			<!-- /.nav-second-level -->								
		</li>
		<li>
			<?php fncc_format_parent_item('pencil-square-o', _FNCC_MANAGEFN)?>
			<ul class="nav nav-second-level">
			<?php 
			//EDITING MOTD 
			fncc_format_end_item('fnccmotd','pencil-square-o',_FNCC_MOTD);
			//EDITING POLL
			fncc_format_end_item('fnccpolledit','tasks',_FNCC_POLL);
			//DOWNLOAD CONFIGURATION 
			fncc_format_end_item('fnccdownconf','download',_FNCC_DOWNCONF);
			?>			
			</ul>
		</li>
		<li>
			<?php fncc_format_parent_item('users', _FNCC_USERS)?>
			<ul class="nav nav-second-level">
			<?php 
			//MEMBERS LIST 
			fncc_format_end_item('fnccmembers','users',_FNCC_USERSLIST);
			//ADD A MEMBER
			fncc_format_end_item('fnccnewprofile','plus-square-o',_FNCC_ADDUSER);
			//WAITING MEMBER
			//fncc_format_end_item('fnccwaitingusers','user',_FNCC_DOWNCONF);
			?>
			<!--WAITING MEMBER-->
				<li>
					<?php echo build_fnajax_link($mod, "&amp;op=fnccwaitingusers", "fncc-adminpanel", "get"); ?>
					<i class="fa fa-user fa-fw"></i>&nbsp;<?php echo _FNCC_USERSTOACTIVATE?> (<?php echo fncc_countwaitingusers()?>)
					</a>
				</li>			
			</ul>
		</li>
		<li>
			<?php fncc_format_parent_item('lock', _FNCC_SECURITY)?>
			<ul class="nav nav-second-level">
			<?php 
			//BACKUPS 
			fncc_format_end_item('fnccbackup','archive',_FNCC_BACKUPS);
			//VIEW LOGS
			fncc_format_end_item('fncclogs','th-list',_FNCC_LOGS);
			//MANAGE BLACKLISTS
			fncc_format_end_item('fnccblacklists','exclamation-triangle',_FNCC_BLACKLISTS);
			?>						
			</ul>
		</li>
		<li>
			<?php fncc_format_parent_item('picture-o ', _FNCC_THEME)?>
			<ul class="nav nav-second-level">
			<?php 
			//THEME STRUCTURE 
			fncc_format_end_item('fnccthemestruct','wrench',_FNCC_THEMESTRUCTURE);
			//THEME PERSONALIZATION
			fncc_format_end_item('fnccthemestyle','puzzle-piece',_FNCC_THEMESTYLE);
			//CSS EDITING
			fncc_format_end_item('fnccthemecss','css3',_FNCC_CSSTHEME);
			//FORUM CSS EDITING
			fncc_format_end_item('fnccforumcss','css3',_FNCC_CSSFORUM);
			?>				
			</ul>
		</li>
		<li>
			<?php fncc_format_parent_item('coffee ', 'Plugins')?>
			<ul class="nav nav-second-level">
				<?php fncc_get_thirdparty_plugins(); ?>
			</ul>
		</li>
	<?php
}


/*
 * Print Flatnuke AJAX link/form
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20130216
 *
 * @param   string	$mod       FN mod to go to (GET)
 * @param   string	$option    FN option to execute (GET)
 * @param   string	$target    DIV target where to write results
 * @param   string	$method    'get' or 'post'
 * @param   string	$form      Form name from which keeping POST variables
 * @param	string	class	   Add one or more classes as in html attribute class: sep by space
 * @return  string	$fnajax    HTML code to print
 */
function build_fnajax_link($mod, $option, $target, $method, $form="", $class=NULL) {
	// security conversions
	$mod    = getparam($mod,    PAR_NULL, SAN_FLAT);
	$option = getparam($option, PAR_NULL, SAN_FLAT);
	$target = getparam($target, PAR_NULL, SAN_FLAT);
	$method = strtolower(getparam($method, PAR_NULL, SAN_FLAT));
	$class = getparam($class, PAR_NULL, SAN_FLAT);

	if(strlen($class)>0){$class=" class=\"".$class."\"";}
	else {$class="";}
	// build the link
	switch($method) {
	case "get":
		$fnajax = "<a href=\"javascript:jQueryFNcall('".get_fn_dir("sections")."/$mod/section.php?mod=$mod"."$option','$method','$target');\"".$class.">";
	break;
	case "post":
		$fnajax = "\n<form id=\"$form\" action=\"javascript:jQueryFNcall('".get_fn_dir("sections")."/$mod/section.php?mod=$mod','$method','$target','$form');\" role=\"form\"".$class.">\n";
	break;
	default: $fnajax = "";
	}
	// return string result
	return($fnajax);
}

/*
 * Print general informations about the site and the hosting
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20100102
 * 
 * @author Alfredo Cosco <orazio.nelson@gmail.com>, added "jQuery Version"
 * @version 20140522
 */
function fncc_info() {
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
		<div><strong><?php echo _FNCC_PHP?></strong><?php echo phpversion();?> - <a href="<?php echo get_fn_dir("sections")?>/<?php echo $mod?>/none_tools/phpinfo.php" target="new" title="PHP informations">PHPInfo</a></div>
		<div><strong><?php echo _FNCC_GDLIB?></strong><?php echo $GDinfo;?></div>
		<div><strong><?php echo _FNCC_FLATNUKE?></strong><?php if(function_exists("get_fn_version")) echo get_fn_version(); else echo _FNCC_FNUNKNOWN;?></div>
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
		<div><strong><?php echo _FNCC_SERVERSPACE?></strong> <?php echo "$total_space Mb";?></div>
		<div><strong><?php echo _FNCC_SITESPACE?></strong> <?php echo "$site_space Mb";?></div>
		<div><strong>Spazio utilizzato:</strong> <?php echo $perc_occupied?>%</div>
		<div class="col-lg-4 progress progress-striped">
			<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $perc_occupied?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $perc_occupied?>%;">
			<?php echo $perc_occupied?>%
			</div>
		</div>
		<div class="clearfix"></div>
		<h2><?php echo _FNCC_MYINFOS?></h2>
		<div><strong><?php echo _FNCC_IP?></strong><?php echo $_SERVER["REMOTE_ADDR"]?></div>
		<div><strong><?php echo _FNCC_USERAGENT?></strong><?php echo $_SERVER["HTTP_USER_AGENT"]?></div>
<?php
}

/*
 * Return directory size
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20061101
 *
 * @param	string	$dirpath	Path of the directory to check
 * @return	integer	$totalsize	Size of the directory
 */
function fncc_getsize($dirpath) {
	// security conversions
	$dirpath = getparam($dirpath, PAR_NULL, SAN_FLAT);
	// go calculate
	require_once("include/filesystem/DeepDir.php");
	$totalsize = 0;
	$dir = new DeepDir();
	$dir->setDir($dirpath);
	$dir->load();
	foreach($dir->files as $n => $pathToFile)
		$totalsize += filesize($pathToFile);
	return $totalsize;
}



/*
 * Returns the number of users waiting for activation
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20070726
 *
 * @return	integer	Number of users waiting
 */
function fncc_countwaitingusers() {
	$waitinglist = array();
	$handle = opendir(get_waiting_users_dir());
	while($file = readdir($handle)) {
		if(preg_match("/^[0-9a-zA-Z]+\.php$/i", $file)) {
			array_push($waitinglist, $file);
		}
	}	//echo "<pre>";print_r($waitinglist);echo "</pre>";	//-> TEST
	closedir($handle);
	return(count($waitinglist));
}


/*
 * Returns the list of backup files
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20070722
 *
 * @return	array	List of backup files created in /var directory
 */
function fncc_listbackups() {
	$backup_files = array();
	$handle = opendir(get_fn_dir("var"));
	while($file = readdir($handle)) {
		if(preg_match("/^backup_[a-zA-Z]+_[0-9]+\.zip$/i", $file)) {
			array_push($backup_files, $file);
		}
	}
	closedir($handle);
	return($backup_files);
}


/*
 * Section reserved to site admins only
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20100101
 */
function fncc_onlyadmin() {
	?><div style="padding: 2em 0.5em 2em 0.5em;text-align:center;"><h4><?php echo _FNCC_ONLYADMIN?></h4>
		<div><img src="images/maintenance.png" alt="lock"  /></div>
		<div><?php echo _FNCC_DESONLYADMIN?></div>
	</div><?php
	// log the attempt
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	fnlog("Security", "$ip||".get_username()."||Tried to access the administration panel.");
}

/*
 * Load the list of third party plugins in the dashboard
 *
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * @version 20140509
 */
function fncc_get_thirdparty_plugins() {
	// security conversions
	$mod = getparam("mod", PAR_GET, SAN_FLAT);
	$sections = get_fn_dir("sections");
	// check plugin's directory existance
	if(!is_dir("$sections/$mod/none_plugins")) {
		echo "The plugins directory does not exist, create the <i>none_plugins</i> directory in <i>$sections/$mod</i><br>";
	} else {
		// search for installed plugins
		$modlist   = array();
		$fileslist = array();
		$handle    = opendir("$sections/$mod/none_plugins");
		while ($tmpfile = readdir($handle)) {
			if(stristr($tmpfile,"none_")){
				if ( (!preg_match("/[.]/i",$tmpfile)) and is_dir("$sections/$mod/none_plugins/".$tmpfile)) {
					if ($tmpfile=="CVS") continue;
					array_push($modlist, $tmpfile);
				}
			}
		}
		closedir($handle);
		// order and print the list
		if(count($modlist)<=0) {
			echo "The plugins directory is empty";
		} else {
			

			
			$modlist = str_replace("none_","",$modlist);
			sort($modlist);
			foreach($modlist as $k=>$v) {
				
				if(file_exists($sections."/".$mod."/none_plugins/none_".$v."/pluginconf.php"))
				{
				include_once($sections."/".$mod."/none_plugins/none_".$v."/pluginconf.php");
				}
				
				$parsev  = str_replace("_", " ", $v);
				echo "<li>".build_fnajax_link("$mod/none_plugins/none_$v", "&amp;plugin=none_$v", "fncc-adminpanel", "get");
				if(strlen($picon)>0){
					echo "<i class=\"fa ".$picon." fa-fw\"></i>&nbsp;";
				} else {
					echo "<i class=\"fa fa-gift fa-fw\"></i>&nbsp;";
				}
				echo $parsev;
				echo "</a></li>";
			}
		}
	}
}

/*
 * 
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * @version 20140511
 * */
 
function fncc_create_module_header($title){
$header="<div class=\"row\">
	<div class=\"col-lg-12\">
		<h1 class=\"page-header fncc-title\">".$title."</h1>
    </div><!-- /.col-lg-12 -->
</div>
<!-- /.row -->
";	
echo $header;
	}

/*
 * 
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * @version 20140511
 * */
function fncc_create_module_page($title, $callback = null,$param=null) {
	
	//page header
	echo "
	<div class=\"row\">
		<div class=\"col-lg-12\">
			<h1 class=\"page-header fncc-title\">".$title."</h1>
		</div><!-- /.col-lg-12 -->
	</div><!-- /.row -->
	"; 
	//page
	echo "<div class=\"row\">
	<div class=\"col-lg-12\">";
	call_user_func($callback,$param);
	echo "</div><!-- /.col-lg-12 -->
	</div><!-- /.row -->";
	
	//return $page;
}


?>
