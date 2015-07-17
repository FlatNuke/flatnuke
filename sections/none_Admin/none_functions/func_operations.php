<?php
/*
 * Print forms to manage Flatnuke general configuration
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * @version 20140511 
 * 
 * Bootstrap compatible, form redesigned with tabs, introduced helpers for the form
 * 
 */
function fncc_generalconf() {
	// security conversions
	$mod = getparam("mod", PAR_GET, SAN_FLAT);
	// check file existance
	$file = "config.php";
	if(file_exists($file)) {
		
		// scan configuration file to find all settings
		$settings  = array();
		$conf_file = file($file);
		//ALDO BOCCACCI:
		//fixes for configuration lines with multiple spaces on the right, left
		//or in the middle of the line
		for($i=0;$i<count($conf_file);$i++) {
			$conf_line = trim($conf_file[$i]);
			//remove comments before value declaration
			$conf_line = trim(preg_replace("/^\/\*.*\*\//","",$conf_line));
			if(preg_match("/^\\\$./",$conf_line))	{// take only rows starting with '$'
				$line_tmp = explode(";", $conf_line);// purge strings from eventual comments on the right
				$line = explode("=", $line_tmp[0]);// split variable from its value
				// build array with settings [variable name, value]
				$settings[str_replace("$","",trim($line[0]))] = htmlentities(trim($line[1],"\" "),ENT_COMPAT,_CHARSET);
			}
		}	//print_r($settings);	//-> TEST
		// scan for installed themes (do not list hidden ones)
		$themes_array = array();
		$theme_num    = 0;

		$themes = glob(get_fn_dir("themes")."/*");
		if (!$themes) $themes = array(); // glob may returns boolean false instead of an empty array on some systems

		foreach ($themes as $theme_one){
			if(is_dir($theme_one) AND $theme_one!="CVS" AND $theme_one!="." AND $theme_one!=".." AND !stristr($theme_one,"none_")) {
				$themes_array[$theme_num] = $theme_one;
				$theme_num++;
			}
		}
		if($theme_num>0) {
			sort($themes_array);
		}	//print_r($themes_array);	//-> TEST
		// scan for installed languages
		$languages_array = array();
		$language_num    = 0;

		$languages = glob("languages/*.php");
		if (!$languages) $languages = array(); // glob may returns boolean false instead of an empty array on some systems

		foreach ($languages as $language_one){
			if(is_file($language_one) AND $language_one!="CVS" AND $language_one!="." AND $language_one!="..") {
				$languages_array[$language_num] = $language_one;
				$language_num++;
			}
		}
		if($language_num>0) {
			sort($languages_array);
		}
			//print_r($languages_array);	//-> TEST
		//open the form
/**
 * Print the General Configuration page
 * 1)	Prepare data for the form data series: mainly arrays
 * 2)	Print the html
 * 3)	Add last changes with jQuery/Javascript
 * **/
 
 // 1)	Prepare data for the form data series: mainly arrays
 
	//languages array
	foreach ($languages_array as $mylanguage) {
		$mylanguage = preg_replace("/^languages\//i","",$mylanguage);
		$mylanguage = str_replace(".php","",$mylanguage);
		//$checked = ($settings['lang'] == $mylanguage) ? ("checked") : ("");
		$label="<img src='images/languages/$mylanguage.png' alt='$mylanguage' title='$mylanguage' />";
		$langs[$mylanguage]=$label;
		}
	
	//themes array
	foreach ($themes_array as $mytheme) {
		$mytheme = preg_replace("/^".get_fn_dir("themes")."\//","",$mytheme);
		$screenshot = (file_exists(get_fn_dir("themes")."/$mytheme/screenshot.png")) ? (get_fn_dir("themes")."/$mytheme/screenshot.png") : (get_fn_dir("sections")."/$mod/none_images/no_preview.png");
		
		$mythemes[$mytheme]="<img class=\"img-thumbnail\" src=\"".$screenshot."\" alt=\"".$mytheme."\">";
		}

	//registration radio labels array: no/yes/mail
	$reguserlabels=array('_FNCC_NO','_FNCC_YES','_FEMAIL');
	
	//news editors (bbcode, fckeditor,ckeditor)
	$editors=array("bbcode"=>"bbcode","fckeditor"=>"FCKeditor","ckeditor"=>"CKeditor");

// 2)	Print the HTML
		?>

		<?php
		//open the form			
		//fncc_generic_form_open('fnccconf-form');
		echo build_fnajax_link($mod, "", "fn_adminpanel", "post", "fnccconf-form");
		
		?>
		<!-- Nav tabs -->
		<ul class="nav nav-tabs">
		  <li class="active"><a href="#siteinfo" data-toggle="tab">Site info</a></li>
		  <li><a href="#admin" data-toggle="tab">Admin</a></li>
		  <li><a href="#users" data-toggle="tab">Users</a></li>
		  <li><a href="#news" data-toggle="tab">News</a></li>
		  <li><a href="#forum" data-toggle="tab">Forum</a></li>
		</ul>
		<div class="tab-content">
		  <div class="tab-pane fade in active" id="siteinfo">		
			<?php 
			//site name field
			echo fncc_input_text('sitename',$settings['sitename'],'100');
			//site description field
			echo fncc_input_text('sitedescription',$settings['sitedescription'],'500');
			//keywords field
			echo fncc_input_text('keywords',$settings['keywords'],'1500');
			//language field			
			echo fncc_input_radio('lang',$settings['lang'],$langs);
			//theme field shows selectable themes
			echo fncc_input_radio('theme',$settings['theme'],$mythemes);	
			?>		
		  </div>
		  <div class="tab-pane fade" id="admin">
			<?php 
			// admin name
			echo fncc_input_text('admin',$settings['admin'],'100');
			//admin mail
			echo fncc_input_text('admin_mail',$settings['admin_mail'],'100','email'); 
			//home section
			echo fncc_input_text('home_section',$settings['home_section'],'100');			
			//time zone
			echo fncc_input_text('fuso_orario',$settings['fuso_orario'],'4');
			//maintenance status
			echo fncc_input_radio('maintenance',$settings['maintenance']);
			?>				  
		  </div>
		  <div class="tab-pane fade" id="users">
			<?php
			//registration no/yes/mail
			echo fncc_input_radio('reguser',$settings['reguser'],$reguserlabels);		
			//remember login
			echo fncc_input_radio('remember_login',$settings['remember_login']);		
			// guest can add news
			echo fncc_input_radio('guestnews',$settings['guestnews']);
			//guest can add comments
			echo fncc_input_radio('guestcomment',$settings['guestcomment']);
				?> 
		  </div>
		  <div class="tab-pane fade" id="news">
			<?php
			//news per page
			echo fncc_input_number('newspp',$settings['newspp'],'3',$range=array(1,999));			
			//news editors (bbcode, fckeditor,ckeditor)
			echo fncc_input_radio('news_editor',$settings['news_editor'],$editors);
			//news moderators
			echo fncc_input_text('news_moderators',$settings['news_moderators'],'500');
			?>
		  </div>	  
		  <div class="tab-pane fade" id="forum">
			  <?php
			  //forum topic per page
			  echo fncc_input_number('topicperpage',$settings['topicperpage'],'3',$range=array(1,999));
			  //forum post per page
			  echo fncc_input_number('postperpage',$settings['postperpage'],'3',$range=array(1,999));
			  //memeber per page
			  echo fncc_input_number('memberperpage',$settings['memberperpage'],'3',$range=array(1,999));
			  //forum moderators list
			  echo fncc_input_text('forum_moderators',$settings['forum_moderators'],'500');
			  ?>	  
		  </div>
		</div>
		<input type="hidden" name="conf_mod" value="modgeneralconf">
		<input type="hidden" name="conf_file" value="<?php echo $file?>">
		<div class="clearfix"></div>
		<div class="form-actions">
		<?php
		// check writing permissions
		if(is_writeable($file))	{
			?>
			<p class="text-danger">
				<?php echo _FNCC_WARNINGDOC?>
			</p>
			<input id="fncc_conf_submit" type="submit" class="btn btn-primary" value="<?php echo _MODIFICA?>">
			<?php
		} else {
			?><p class="text-danger">
				<?php echo _FNCC_WARNINGRIGHTS?>
			</p><?php
		}
		?>
		</div>
		</form>

<!--3)	Add last changes with jQuery/Javascript-->
    	<script>
		$("#fnccconf-form").data('toggle','validator');
		$("#fnccconf-form").validator();
		//trasform keywords list in tags
		$('#keywords').tokenfield();

		//DOM changes for the theme field
		$( "#form-theme > label:first" ).after( '<div class="clearfix"></div>' );
		
		$('input[name="theme"]:visible').each(function() {
			var t = $(this).attr('value');
			$(this).after(t+ '<div class="clearfix"></div>' );
		});	

		//add help block to admin mail field
		$( "#form-admin_mail" ).append( '<p class="help-block">(<?php echo _FNCC_CONFADMINMAIL_CONTACT;?>)</p>' );	
		
		//render disabled radio for fckeditor and ckeditor if they are not installed
		$.ajax({
		url:'include/plugins/editors/FCKeditor/fckeditor.php',
		type:'HEAD',
		error:
		function(){
			$('input:radio[name="news_editor"][value="fckeditor"]').prop('disabled', true);
		},
		//success:function(){alert('yes');}
		});
		
		$.ajax({
		url:'include/plugins/editors/ckeditor/ckeditor.php',
		type:'HEAD',
		error:
		function(){
			$('input:radio[name="news_editor"][value="ckeditor"]').prop('disabled', true);
		},
		//success:function(){alert('yes');}
		});
				
		</script>
		<?php
	} else echo "<p class=\"text-danger\">"._FNCC_WARNINGNOFILE."</p>";
	?>
		
	<?php
}

/*
 * Read configuration files and print them ready to be managed
 * 
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20080726
 *
 * @param	string	$file	File name to modify
 * 
 * @author Alfedo Cosco 
 * @version 20140511
 * 
 * @changelog 2014 embedded Codemirror (http://codemirror.net/) as html,css, php editor
 * 
 */
function fncc_editconffile($file) {
	// security conversions
	$mod  = getparam("mod", PAR_GET,  SAN_FLAT);
	$file = getparam($file, PAR_NULL, SAN_FLAT);
	global $news_editor;
	if (!preg_match("/^fckeditor$|^bbcode$/i", $news_editor)) $news_editor = "bbcode";
	// check file existance
	if(file_exists($file)) {
		
		//open the form
		//fncc_generic_form_open();
		echo build_fnajax_link($mod, "", "fn_adminpanel", "post", "fncc-edit-form");

		echo "<div class=\"form-group\">";
		// manage MOTD file with FCKeditor
		if ($file==get_fn_dir("var")."/motd.php" AND $news_editor=="fckeditor" AND file_exists("include/plugins/editors/FCKeditor/fckeditor.php")) {
			include("include/plugins/editors/FCKeditor/fckeditor.php");
			$oFCKeditor = new FCKeditor('conf_body');
			$oFCKeditor->BasePath = "include/plugins/editors/FCKeditor/";
			$oFCKeditor->Value = file_get_contents($file);
			$oFCKeditor->Width = "100%";
			$oFCKeditor->Height = "400";
			$oFCKeditor->ToolbarSet = "Default";
			$oFCKeditor->Create();
		} else {
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			if($ext=='php') {$mode="application/x-httpd-php";}
			elseif($ext=='css'){$mode="text/css";}
			
			// manage standard files or FCKeditor is disabled
			echo "<textarea name='conf_body' class=\"form-control\" id=\"fncc-edit-".$ext."\" rows=\"25\">";
			echo htmlspecialchars(file_get_contents($file));
			echo "</textarea>";
			?>
			<script src="<?php echo get_fn_dir("sections")."/$mod/none_js"?>/codemirror/codemirror-compressed.js"></script>	
			<script>
				var editor = CodeMirror.fromTextArea(document.getElementById("fncc-edit-<?php echo $ext;?>"), {
				lineNumbers: true,
				mode: "<?php echo $mode;?>",
				matchBrackets: true,
				lineWrapping: true
				});
				editor.setSize('98%', 450);
			</script>
			<?php
		}
		?>
		</div>
		<input type="hidden" name="conf_mod" value="modbodyfile">
		<input type="hidden" name="conf_file" value="<?php echo $file?>">
		<?php
		// check writing permissions
		if(is_writeable($file))	{
			?><p class="text-danger">
				<?php echo _FNCC_WARNINGDOC?>
			</p>
			<input type="submit" value="<?php echo _MODIFICA?>" class="btn btn-primary" />
			<?php
		} else {
			?><p class="text-danger">
				<?php echo _FNCC_WARNINGRIGHTS?>
			</p><?php
		}
		?></form>
		<?php
	} else echo "<div class=\"txt-warning\">"._FNCC_WARNINGNOFILE."</div>";
}

/*
 * Manage Flatnuke poll
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20100101
 * 
 * @author Alfredo Cosco <orazio.nelson@gmail.com
 * @version 20140514 - structured to work with bootstrap and dataTables, add more poll options dinamically
 *  
 */
function fncc_editpoll() {
	// security conversions
	$mod     = getparam("mod",     PAR_GET,    SAN_FLAT);
	$myforum = getparam("myforum", PAR_COOKIE, SAN_FLAT);
	include (get_fn_dir("sections")."/none_Sondaggio/config.php");
	// files' declarations
	$file_xml = get_file($sondaggio_file_dati);
	$attivo   = get_xml_element("attivo",$file_xml);
	$opzioni  = get_xml_element("opzioni",$file_xml);
	$opzione  = get_xml_array("opzione",$opzioni);
	// print html form
	
	//open the form
	//fncc_generic_form_open();
	echo build_fnajax_link($mod, "", "fn_adminpanel", "post", "fncc-edit-form");
	
	//poll status: open/closed
	$pollstatuslabels=array('n'=>'_FP_CHIUSO','y'=>'_FP_APERTO');
	echo fncc_input_radio('fp_stato',$attivo,$pollstatuslabels);
	// poll argument
	echo fncc_input_text('salva_domanda',get_xml_element("domanda",$file_xml),'200');
	// poll options
	?>
	
	<div class="form-group">
		<div class="table-responsive">
			<p class="help-block"><?php echo _FP_ISTRUZIONIMODIFICA?></p>
			<button class="btn btn-success" id="addRow" type="button">Aggiungi un'opzione</button>
	<?php
	echo "<table class=\"table table-striped table-bordered table-hover\" id=\"dataTables-editpoll\">";
	echo "<thead>
			<tr>
			<th>Num.</th>
			<th>Option</th>
			<th>"._FP_VOTI."</th>
			</tr>
		</thead><tbody>";


	for($n=0; $n<count($opzione); $n++) {	// print possible answers and votes (max 20)
		echo "<tr>";
		echo "<td>"; echo $n+1; echo "</td>";
		echo "<td><input class=\"form-control\" type='text' name='salva_opzioni[]' value='".get_xml_element("testo",$opzione[$n])."'></td>";
		echo "<td><input class=\"form-control\" type='text' name='salva_voti[]' value='".get_xml_element("voto",$opzione[$n])."'></td>";
		echo "</tr>";
	}

	echo "</tbody></table>";
	// save poll configuration
	?>
		</div><!--table-responsive-->
	</div>
	
	<div class="form-group">	
			<input type="hidden" name="conf_mod" value="savepoll">
			<input class="btn btn-primary" type="submit" value="<?php echo _FP_MODIFICA?>">
	</div>
	
	</form>
	<?php
	// archive poll
	//open the form
	//fncc_generic_form_open();
	echo build_fnajax_link($mod, "", "fn_adminpanel", "post", "fncc-archive-form");
	?>
	<div class="form-group">

			<input type="hidden" name="conf_mod" value="archpoll">
			<input class="btn btn-danger" type="submit" value="<?php echo _FP_CHIUDIARCHIVIA?>">

	</div>
	</form>
	<!-- the javascript-->

	<script>
    $(document).ready(function() {
        $('#dataTables-editpoll').dataTable({
		"searching": false,
		"paging":   false,
        "ordering": false,
        "info":     false
			});
    });

    $(document).ready(function() {
    var t = $('#dataTables-editpoll').DataTable();
    var counter = 4;
 
	var tstring = '<input class="form-control" type="text" name="salva_opzioni[]" value="">';
	var cstring = '<input class="form-control" type="text" name="salva_voti[]" value="">';
	
    $('#addRow').on( 'click', function () {
        t.row.add( [
            counter,
            tstring,
            cstring
        ] ).draw();
 
        counter++;
    } );
 
    // Automatically add a first row of data
    $('#addRow').click();
	} );
    
    </script>
	<?php
}

/*
 * Print forms to manage Flatnuke general configuration
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20080607
 * 
 * @author Alfredo Cosco <orazio.nelson@gmail.com
 * @version 20140514 - structured to work with bootstrap 
 * 
 */
function fncc_fdplusconf() {
	// security conversions
	$mod = getparam("mod", PAR_GET, SAN_FLAT);
	// check file existance
	$file = "download/fdconfig.php";
	if(file_exists($file)) {
		// scan configuration file to find all settings
		$settings  = array();
		$conf_file = file($file);
		//ALDO BOCCACCI:
		//fixes for configuration lines with multiple spaces on the right, left
		//or in the middle of the line
		for($i=0;$i<count($conf_file);$i++) {
			$conf_line = trim($conf_file[$i]);
			//remove comments before value declaration
			$conf_line = trim(preg_replace("/^\/\*.*\*\//i","",$conf_line));
			if(preg_match("/^\\\$./",$conf_line))	{			// take only rows starting with '$'
				$line_tmp = explode(";", $conf_file[$i]);// purge strings from eventual comments on the right
				$line = explode("=", $line_tmp[0]);// split variable from its value
				// build array with settings [variable name, value]
				$settings[str_replace("$","",trim($line[0]))] = htmlentities(trim($line[1],"\" "),ENT_COMPAT,_CHARSET);
			}
		}	//print_r($settings);	//-> TEST
		// scan for installed mime icon sets
		$icons_array = array();
		$icons_num   = 0;

		$icons = glob("images/mime/*");
		if (!$icons) $icons = array(); // glob may returns boolean false instead of an empty array on some systems

		foreach ($icons as $icon_one){
			if(is_dir($icon_one) AND !preg_match("/CVS/i",$icon_one) AND $icon_one!="." AND $icon_one!="..") {
				$icons_array[$icons_num] = $icon_one;
				$icons_num++;
			}
		}
		if($icons_num>0) {
			sort($icons_array);
		}	//print_r($icons_array);	//-> TEST
		
		
/**
 * Print the page
 * 1)	Prepare data for the form data series: mainly arrays
 * 2)	Print the html
 * 3)	Add last changes with jQuery/Javascript
 * **/
		
// 1)	Prepare data for the form data series: mainly arrays
		//Icons labels array
		foreach ($icons_array as $myicon) {
			$myicon = preg_replace("/^images\/mime\//i","",$myicon);
			$myicons[$myicon]=$myicon;
		}
		
// 2)	Print the HTML
		//open the form
		//fncc_generic_form_open();
		echo build_fnajax_link($mod, "", "fn_adminpanel", "post", "fncc-fdp-form");
		?>

		<ul class="nav nav-tabs">
		  <li class="active"><a href="#base" data-toggle="tab">Base settings</a></li>
		  <li><a href="#layout" data-toggle="tab">Layout settings</a></li>
		  <li><a href="#advanced" data-toggle="tab">Advanced Settings</a></li>
		  <li><a href="#permissions" data-toggle="tab">Users Permissions</a></li>
		</ul>
		<div class="tab-content">
		  <div class="tab-pane fade in active" id="base">
			<?php
			//extensions
			echo fncc_input_text('extensions',$settings['extensions'],'300');
			//maxfilesize
			echo fncc_input_number('maxFileSize',$settings['maxFileSize'],'9',array(1,2000000),true);
			// new file time
			echo fncc_input_number('newfiletime',$settings['newfiletime'],'4',array(1,2000),true);
			// print the list of available mime icon sets
			echo fncc_input_radio('icon_style',$settings['icon_style'],$myicons);
			//screenshots extension
			echo fncc_input_text('extscreenshot',$settings['extscreenshot'],'4');
			//VOTE
			echo fncc_input_radio('defaultvoteon',$settings['defaultvoteon']);
			?>
		  </div>
		  <div class="tab-pane fade" id="layout">
			<?php
			//SHOW UPLOADER
			echo fncc_input_radio('showuploader',$settings['showuploader']);
			//SHOW DNL LINK
			echo fncc_input_radio('showdownloadlink',$settings['showdownloadlink']);
			//SHOW FILES LIST
			echo fncc_input_radio('overview_show_files',$settings['overview_show_files']);
			//SHOW SUMMARY
			echo fncc_input_radio('section_show_header',$settings['section_show_header']);
			?>	
		  </div>
		  <div class="tab-pane fade" id="advanced">	
			<?php
			//signature estension
			echo fncc_input_text('extsig',$settings['extsig'],'4');
			//MD5
			echo fncc_input_radio('automd5',$settings['automd5']);
			//SHA1
			echo fncc_input_radio('autosha1',$settings['autosha1']);
			//ENABLE WEB ADMIN
			echo fncc_input_radio('enable_admin_options',$settings['enable_admin_options']);
			//User file limit
			echo fncc_input_number('userfilelimit',$settings['userfilelimit'],'4',array(1,2000));
			//USER WAITING FILE
			echo fncc_input_text('userwaitingfile',$settings['userwaitingfile'],'100');		
			?>
		  </div>
		  <div class="tab-pane fade" id="permissions">	
			<?php
			//other admins
			echo fncc_input_text('admins',$settings['admins'],'200');
			//User max file size
			echo fncc_input_number('usermaxFileSize',$settings['usermaxFileSize'],'9',array(1,1000000),true);
			//BLACKLISTED
			echo fncc_input_text('userblacklist',$settings['userblacklist'],'500');
			//Min level users allowed
			echo fncc_select('minlevel',$settings['minlevel'],range(0,10));
			?>
		  </div>
		</div>

		<input type="hidden" name="conf_mod" value="moddownconf">
		<input type="hidden" name="conf_file" value="<?php echo $file?>">
		<div class="clearfix"></div>
		<div class="form-group">
		<?php
		// check writing permissions
		if(is_writeable($file))	{
			?><p class="text-danger">
				<?php echo _FNCC_WARNINGDOC?>
			  </p>
			  <input type="submit" class="btn btn-primary" value="<?php echo _MODIFICA?>">
			<?php
		} else {
			?><p class="text-danger">
				<?php echo _FNCC_WARNINGRIGHTS?>
			</p><?php
		}
		?>
		</div>
		</form>
<!--3)	Add last changes with jQuery/Javascript-->
		<script>
			$('#extensions').tokenfield();
			$('#userwaitingfile').prop('readonly', true);
			$('#minlevel').css('width','auto');	
		</script>
		<?php
	} else echo "<p class=\"text-danger\">"._FNCC_WARNINGNOFILE."</p>";
}

/*
 * List all members of the site, with the possibility
 * to list them in order by name, by level or by time
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20130216
 * 
 * @author Alfredo Cosco <orazio.nelson@gmail.com
 * @version 20140514 - added dataTables themes and functionalities
 * 
 */
function fncc_userslist() {
	// security conversions
	$mod   = getparam("mod", PAR_GET, SAN_FLAT);
	$order = getparam("order", PAR_GET, SAN_FLAT);
	// variables
	global $fuso_orario;
	$time_fresh = 192;	// number of hours in a week
	// load members in an array
	$users = list_users();
	$members = array();
	for($i=0;$i<count($users);$i++) {
		array_push($members, array(
			"name"  => $users[$i],
			"level" => getlevel($users[$i],"home"),
			"time"  => filemtime(get_fn_dir("users")."/".$users[$i].".php")+(3600*$fuso_orario))
		);
	}	//echo "<pre>";print_r($members);echo "</pre>";	//-> TEST
	
	echo "<table class=\"table table-striped table-bordered table-hover\" id=\"dataTables-userslist\">";
	echo "<thead><tr></tr>";
		echo "<th>Id</td>";
		echo "<th>"._NOMEUTENTE."</th>";
		echo "<th> "._LEVEL."</th>";
		echo "<th> "._FNCC_CHANGEDATE."</th>";
	echo "</tr></thead><tbody>";
	for($i=0; $i<count($members); $i++) {
		$style_r = ($members[$i]['level']==10) ? ("style=\"font-weight:bold;\"") : ("");
		echo "<tr>";
		echo "<td>".($i+1)."</td>";
		$member = str_replace(".php", "", $members[$i]['name']);
		echo "<td><a href=\"index.php?mod=none_Login&amp;action=viewprofile&amp;user=$member\" title=\""._VIEW_USERPROFILE." $member\">$member</a></td>";
		// print image 'new.gif' if userprofile has been modified within 1 week
		if(time()-$members[$i]['time']<$time_fresh*3600) {
			$img_fresh = "<img src='images/mime/new.gif' alt='new' />";
		} else {
			$img_fresh = "";
		}
		echo "<td>".$members[$i]['level']."</td>";
		echo "<td>".date(" d.m.Y, H:i:s", $members[$i]['time'])." $img_fresh</td>";
		echo "</tr>";
	}
	?></tbody></table>
		<script>
		$(document).ready(function() {
        $('#dataTables-userslist').dataTable();
        });
		</script>
	<?php
}

/*
 * Add a new user profile
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20070716
 * 
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * @version 20140514 - structured to work with bootstrap and validator
 *  
 */
function fncc_newuserprofile() {
	global $reguser, $action;
	// security conversions
	$mod   = getparam("mod", PAR_GET, SAN_FLAT);
	// print fields to fill
	//open the form
	//fncc_generic_form_open();
	echo build_fnajax_link($mod, "", "fn_adminpanel", "post", "fncc-newuser-form");
	?>
	<!-- Nav tabs -->
	<ul class="nav nav-tabs">
		<li class="active"><a href="#fn-basic-um" data-toggle="tab">Base settings</a></li>
		<li><a href="#fn-advanced-um" data-toggle="tab">Advanced settings</a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane fade in active" id="fn-basic-um">
			<?php
			echo fncc_input_text('nome',NULL,'100');
			echo fncc_input_text('regpass',NULL,'100','password');
			echo fncc_input_text('reregpass',NULL,'100','password');
			echo fncc_input_text('anag',NULL,'100');
			echo fncc_input_text('email',NULL,'100','email');
			echo fncc_select('level','0',range(0,10));
			?>					
		</div>
		<div class="tab-pane fade" id="fn-advanced-um">			
			<div class="panel panel-default" style="border:none; margin:0;">
				<div class="panel-body" style="padding:0">
					<div class="panel-group" id="accordion">
						<div class="panel panel-info">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" class="lead">Anagrafica</a>
								</h4>
							</div>
							<div id="collapseOne" class="panel-collapse collapse in" style="height: auto;">
								<div class="panel-body">
									<?php
									echo fncc_input_text('homep',NULL,'100','url');
									echo fncc_input_text('prof',NULL,'100');
									echo fncc_input_text('prov',NULL,'100');
									?>
								</div>
							</div>
						</div>
						<div class="panel panel-success">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" class="collapsed lead">Social</a>
								</h4>
							</div>
							<div id="collapseTwo" class="panel-collapse collapse" style="height: 0px;">
								<div class="panel-body">
									<?php
									echo fncc_input_text('jabber',NULL,'100');
									echo fncc_input_text('skype',NULL,'100');
									echo fncc_input_text('icq',NULL,'100');
									echo fncc_input_text('msn',NULL,'100');
									?>
								</div>
							</div>
						</div>
						<div class="panel panel-warning">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#accordion" href="#collapseThree" class="collapsed lead">Personalizzazione</a>
								</h4>
							</div>
							<div id="collapseThree" class="panel-collapse collapse" style="height: 0px;">
								<div class="panel-body">
									<div class="form-group">
										<div class="col-sm-2">
											<label for="ava"><?php echo _FAVAT?></label>
											<img name="avatar" src="forum/images/blank.png" alt="avatar" id="avatar" class="img-thumbnail">
										</div>
										<div class="col-sm-7">											
										<select class="form-control" name="ava" onchange='document.avatar.src="forum/images/"+this.options[this.selectedIndex].value'>
										<option value="blank.png">----</option><?php
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
										<p class="help-block"><?php echo _FAVATREM?></p><?php
										echo "<input type=\"text\" name=\"url_avatar\" class=\"form-control\">";
									?>
										</div>
									</div>
									<div class="clearfix" style="margin-bottom:1em;"></div>
									<?php
									echo fncc_textarea('firma',NULL);
									echo fncc_textarea('presentation',NULL);
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- .panel-body -->
			</div>
			<!-- /.panel -->              
		</div>
	</div>
	<input type="hidden" name="conf_mod" value="saveprofile">
	<input type="submit" class="btn btn-primary" value="<?php echo _FINVIA?>">
	
	</form>
	<script>
	$('#fncc-newuser-form').data('toggle','validator');
	$('#reregpass').data({
		'match':'#regpass', 
		"match-error":"Whoops, these don't match"}).after(' <div class="help-block with-errors"></div>');
		
	$("#fncc-newuser-form").validator();
	//add help block and data-reguser attribute to email filed #form-email
	$( "#form-email" ).append( '<p class="help-block">(Email address we can contact you on)</p>' ).data('reguser',<?php echo $reguser?>);
	//set mail 'required' if registration is by mail
	var reguser = $('#form-email').data('reguser');	
	if(parseInt(reguser)==2) {$('#email').prop("required", true);
	$('label[for="email"]').addClass('required');
		}
	
	//set required fields
	$('#nome, #regpass, #reregpass').prop("required", true);
	// add .required class for labels, needs to add the *
	$('label[for="nome"], label[for="regpass"], label[for="reregpass"]').addClass('required');
		
	</script>
	<?php
}

/*
 * Manage profiles waiting for activation
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20130216
 */
function fncc_listwaiting() {
	// security conversions
	$mod   = getparam("mod",   PAR_GET, SAN_FLAT);
	$order = getparam("order", PAR_GET, SAN_FLAT);
	$user  = getparam("user",  PAR_GET, SAN_FLAT);
	// variables
	global $fuso_orario;
	$time_fresh = 192;	// number of hours in a week
	// load members in an array
	$waitinglist = array();
	$handle = opendir(get_waiting_users_dir());
	while($file = readdir($handle)) {
		if(preg_match("/^[0-9a-zA-Z]+\.php$/i", $file)) {
			$file = str_replace(".php", "", $file);
			array_push($waitinglist, $file);
		}
	}	//echo "<pre>";print_r($waitinglist);echo "</pre>";	//-> TEST
	closedir($handle);
	// check the number of profiles to activate
	if(count($waitinglist)==0) {
		echo _FNCC_NOUSERSTOACTIVATE;
		return;
	}
	$members = array();
	for($i=0;$i<count($waitinglist);$i++) {
		array_push($members, array("name" => $waitinglist[$i], "time" => filemtime(get_waiting_users_dir()."/".$waitinglist[$i].".php")+(3600*$fuso_orario)));
	}	//echo "<pre>";print_r($members);echo "</pre>";	//-> TEST
	// sort the array as chosen
	if(count($members)>0) {
		switch($order) {
			case "name_a":
				sort ($members); // ascending by name
			break;
			case "name_d":
				rsort ($members); // descending name
			break;
			case "time_a":
				usort($members, create_function('$a, $b', "return strnatcasecmp(\$a['time'], \$b['time']);")); // ascending by time
			break;
			case "time_d":
				usort($members, create_function('$a, $b', "return strnatcasecmp(\$a['time'], \$b['time']);")); // descending by time
				$members = array_reverse($members, FALSE);
			break;
			default: sort ($members);
		}
	}
	// print links to order the list
	$ord_name_a = build_fnajax_link($mod, "&amp;op=fnccwaitingusers&amp;order=name_a", "fn_adminpanel", "get");
	$ord_name_d = build_fnajax_link($mod, "&amp;op=fnccwaitingusers&amp;order=name_d", "fn_adminpanel", "get");
	$ord_time_a = build_fnajax_link($mod, "&amp;op=fnccwaitingusers&amp;order=time_a", "fn_adminpanel", "get");
	$ord_time_d = build_fnajax_link($mod, "&amp;op=fnccwaitingusers&amp;order=time_d", "fn_adminpanel", "get");
	// print the list of the profiles
	$style_h = " style=\"border:1px solid;border-collapse:collapse;font-weight:bold;text-align:center;\"";
	$style_c = " style=\"border-bottom:1px solid;padding-left:1.5em;";
	echo "<table cellspacing='0' cellpadding='0' style='width:100%'><tbody>";
	echo "<tr>";
		echo "<td ".$style_h.">Id</td>";
		echo "<td ".$style_h.">$ord_name_a&#8595;</a> "._NOMEUTENTE." $ord_name_d&#8593;</a></td>";
		echo "<td ".$style_h.">$ord_time_a&#8595;</a> "._FNCC_CHANGEDATE." $ord_time_d&#8593;</a></td>";
	echo "</tr>";
	for($i=0; $i<count($members); $i++) {
		echo "<tr>";
		echo "<td ".$style_c."text-align:right;padding-right:0.5em;\">".($i+1)."</td>";
		$member = str_replace(".php", "", $members[$i]['name']);
		$link = build_fnajax_link($mod, "&amp;op=fnccwaitingusers&amp;user=$member", "fn_adminpanel", "get");
		echo "<td $style_c\">".$link.$member."</a></td>";
		// print image 'new.gif' if userprofile has been registered within 1 week
		if(time()-$members[$i]['time']<$time_fresh*3600) {
			$img_fresh = "<img src='images/mime/new.gif' alt='new' />";
		} else {
			$img_fresh = "";
		}
		echo "<td  ".$style_c."\">".date(" d.m.Y, H:i:s", $members[$i]['time'])." $img_fresh</td>";
		echo "</tr>";
	}
	?></tbody></table><?php
	// print all the details of the profile chosen
	switch("$user") {
		case "":
			continue;
		break;
		default:
			$user_xml = array();
			$user_xml = load_user_profile($user, 1);	//echo "<pre>";print_r($user_xml);echo "</pre>"; //-> TEST
			$detstyle1 = "float:left;height:2em;width:25%;";
			$detstyle2 = "float:left;height:2em;width:60%;";
			?><p><div><b><?php echo $user?></b></div></p>
			<div id='user_profile' style='width:85%;padding: 1em 0 0.5em 15%;border:1px dashed;'>
			<div id='password' style='float:left;width:100%;'>
				<div style='<?php echo $detstyle1?>'><?php echo _PASSWORD?></div>
				<div style='<?php echo $detstyle2?>'>&nbsp;<?php echo $user_xml['password']?></div>
			</div>
			<div id='name' style='float:left;width:100%;'>
				<div style='<?php echo $detstyle1?>'><?php echo _FNOME?></div>
				<div style='<?php echo $detstyle2?>'>&nbsp;<?php echo $user_xml['name']?></div>
			</div>
			<div id='mail' style='float:left;width:100%;'>
				<div style='<?php echo $detstyle1?>'><?php echo _FEMAIL?></div>
				<div style='<?php echo $detstyle2?>'>&nbsp;<?php echo $user_xml['mail']?></div>
			</div>
			<div id='mail' style='float:left;width:100%;'>
				<div style='<?php echo $detstyle1?>'><?php echo _HIDDENMAIL?></div>
				<div style='<?php echo $detstyle2?>'><input id="hiddenmail" type="checkbox" disabled='disabled' <?php if ($user_xml['hiddenmail']=="1") echo "checked";?> /></div>
			</div>
			<div id='homepage' style='float:left;width:100%;'>
				<div style='<?php echo $detstyle1?>'><?php echo _FHOME?></div>
				<div style='<?php echo $detstyle2?>'>&nbsp;<?php echo $user_xml['homepage']?></div>
			</div>
			<div id='work' style='float:left;width:100%;'>
				<div style='<?php echo $detstyle1?>'><?php echo _FPROFES?></div>
				<div style='<?php echo $detstyle2?>'>&nbsp;<?php echo $user_xml['work']?></div>
			</div>
			<div id='from' style='float:left;width:100%;'>
				<div style='<?php echo $detstyle1?>'><?php echo _FPROV?></div>
				<div style='<?php echo $detstyle2?>'>&nbsp;<?php echo $user_xml['from']?></div>
			</div>
			<div id='avatar' style='float:left;width:100%;'>
				<div style='<?php echo $detstyle1?>'><?php echo _FAVAT?></div>
				<div style='<?php echo $detstyle2?>'>&nbsp;<?php echo $user_xml['avatar']?></div>
			</div>
			<div id='sign' style='float:left;width:100%;'>
				<div style='<?php echo $detstyle1?>'><?php echo _FFIRMA?></div>
				<div style='<?php echo $detstyle2?>'>&nbsp;<?php echo $user_xml['sign']?></div>
			</div>
			<div id='level' style='float:left;width:100%;margin-bottom:1.5em;'>
				<div style='<?php echo $detstyle1?>border-bottom:1px solid;'><?php echo _LEVEL?></div>
				<div style='<?php echo $detstyle2?>border-bottom:1px solid;'>&nbsp;<?php echo $user_xml['level']?></div>
			</div>
			<div id='regmail' style='float:left;width:100%;'><?php
				echo build_fnajax_link($mod, "", "fn_adminpanel", "post", "form_updatewaiting");
				?><input type="hidden" name="conf_mod" value="updatewaiting" />
				<input type="hidden" name="user" value="<?php echo $user?>" />
				<div style='<?php echo $detstyle1?>'><?php echo _FNCC_REGMAIL?></div>
				<div style='float:left;height:2em;width:55%;margin-right:2px;'><input name='regmail' type='text' style='width:100%' value='<?php echo $user_xml['regmail']?>' /></div><?php
				$url_mod = "<button type='submit' title=\""._FNCC_REGMAILDES."\">";
				$url_mod .= "<img src='".get_fn_dir("sections")."/$mod/none_images/save.png' alt='save' style=\"border:0;\" />";
				$url_mod .= "</button>\n";
				?><div style='float:left'><?php echo $url_mod?></div>
				</form>
			</div>
			<div id='regcode' style='float:left;width:100%;'><?php
				echo build_fnajax_link($mod, "", "fn_adminpanel", "post", "form_sendactivation");
				?><input type="hidden" name="conf_mod" value="sendactivation" />
				<input type="hidden" name="mod" value="<?php echo $mod?>" />
				<input type="hidden" name="user" value="<?php echo $user?>" />
				<input type='hidden' name='regcode' value='<?php echo $user_xml['regcode']?>' />
				<input type="hidden" name="mail" value="<?php echo $user_xml['regmail']?>" />
				<div style='<?php echo $detstyle1?>'><?php echo _FNCC_REGCODE?></div>
				<div style='float:left;height:2em;width:55%;margin-right:2px;'><input type='text' style='width:100%' disabled='disabled' value='<?php echo $user_xml['regcode']?>' /></div><?php
				$url_reg = "<button type='submit' title=\""._FNCC_REGCODEDES."\">";
				$url_reg .= "<img src='forum/icons/mail.png' alt='mail' style=\"border:0;\" />";
				$url_reg .= "</button>\n";
				?><div style='float:left'><?php echo $url_reg?></div>
				</form>
			</div>
			<p style='margin-left:30%;font-weight:bold'><?php
			echo build_fnajax_link($mod, "&amp;op=fnccwaitingusers&amp;get_act=deletewaiting&amp;deluser=$user", "fn_adminpanel", "get");
			echo _ELIMINA."</a> | ";
			echo "<a href='index.php?mod=none_Login&amp;action=activateuser&amp;user=$user&amp;regcode=".$user_xml['regcode']."'>"._FNCC_ACTIVATE."</a>";
			?></p>
			</div><?php
		break;
	}
}

/*
 * Manage Flatnuke backups
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20130216
 * 
 * @author Alfredo Cosco <orazio.nelson@gmail.com
 * @version 20140514 - structured to work with bootstrap, jquery
 *  
 */
function fncc_managebackups() {
	// security checks
	$mod = getparam("mod", PAR_GET, SAN_FLAT);
	$log = getparam("log", PAR_GET, SAN_FLAT);
	// list all types of backup allowed
	$backup_array = array(
		"news"    =>array("value"=>get_fn_dir("news"),    			"desc"=>_FNCC_BACKUPNEWS),
		"users"   =>array("value"=>get_fn_dir("users"),   			"desc"=>_FNCC_BACKUPUSERS),
		"var"     =>array("value"=>get_fn_dir("var"),     			"desc"=>_FNCC_BACKUPMISC." /".get_fn_dir("var")),
		"sections"=>array("value"=>get_fn_dir("sections"),			"desc"=>_FNCC_BACKUPSECT),
		"forum"   =>array("value"=>get_fn_dir("var")."/flatforum",	"desc"=>_FNCC_BACKUPFORUM),
		"site"    =>array("value"=>"./",                  			"desc"=>_FNCC_BACKUPSITE),
    );	//echo "<pre>";print_r($backup_array);echo "</pre>"; //-> TEST
	// print html forms
	
	foreach($backup_array as $k => $tosave) {
		echo build_fnajax_link($mod, "", "fn_adminpanel", "post", "form_tosave".$k);
		?><!--form action="index.php?mod=<?php echo $mod ?>" method="post" role="form" style="padding-top:20px;padding-bottom:20px;"-->
		<div class="form-group col-md-8">
				<label class="col-md-5" for="tosave<?php echo $k;?>"><i class="fa fa-floppy-o fa-2x text-success"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $tosave['desc']?></label>
				<div class="col-md-2">
				<input type="hidden" name="conf_mod" value="dobackup" />
				<input type="hidden" name="tosave" value="<?php echo $tosave['value']?>" id="tosave<?php echo $k;?>">
				<input type="submit" class="btn btn-success savers" value="<?php echo _FNCC_SAVE?>">
				</div>
		</div>
		</form><?php
	}
	// print cancel form
	echo build_fnajax_link($mod, "", "fn_adminpanel", "post", "form_cleanbackup");
	?><div class="form-group col-md-8">	
		<label class="col-md-5" for="bkup"><i class="fa fa-trash-o fa-2x text-warning"></i>
		&nbsp;&nbsp;<?php echo _FNCC_DELBACKUP." (<span class=\"listbackups\">".count(fncc_listbackups())."</span>)"?>
		</label>
		<div class="col-md-2">
		<input type="hidden" name="conf_mod" value="cleanbackup" id="bkup">
		<input type="submit" class="btn btn-warning" value="<?php echo _ELIMINA?>">
		</div>
	</div>
	</form>
	<script>
	$("form").not('#form_cleanbackup').on('submit', function() {
		$('.listbackups').empty();
		var fid = $(this).attr('id');
		
		$.post('<?php echo get_fn_dir("sections")."/$mod/none_functions/fncc_counters.php?mod=$mod&case=listbackups"?>', function(data) {
		//alert(data);
		$('.listbackups').text(parseInt(data)+1);
		});
		   //var $form = $(this);
		$('#'+fid).find('label').css('opacity','.65');
		$('#'+fid).find('input[type="submit"]').prop('disabled',true);	
   });	
   
   $('#form_cleanbackup').on('submit', function(){
		$('.listbackups').empty();	
		$('.listbackups').text('0');	
		$('input[type="submit"]').prop('disabled',false);	
		$('form').find('label').css('opacity','1');	
		});
	</script>
	<?php
}

/*
 * Manage Flatnuke logs
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20130216
 * 
 * @author Alfredo Cosco <orazio.nelson@gmail.com
 * @version 20140514 - structured to work with bootstrap, log now are in TAB, tabs are dinamically loaded on demand 
 * 
 */
function fncc_managelogs() {
	// security checks
	$mod = getparam("mod", PAR_GET, SAN_FLAT);
	$log = getparam("log", PAR_GET, SAN_FLAT);

	$rewrite     = "false";
	
	// list of the log files
	$logs_array = array();
	$logs_dir = opendir(get_fn_dir("var")."/log");
	while($logs_file=readdir($logs_dir)) {
		if($logs_file!="." AND $logs_file!=".." ) {
			array_push($logs_array, str_replace(".php","",$logs_file));
		}
	} //echo "<pre>";print_r($logs_array);echo "</pre>";	//-> TEST
	closedir($logs_dir);
	if(count($logs_array)==0) {
		echo "<b>"._NORESULT." !</b>";
		return;
	} else {
		sort($logs_array);
	}
	
	?>
	<ul id="log-tab" class="nav nav-tabs">	
	<?php
	for($i=0;$i<count($logs_array);$i++) {
		echo "<li";
		if($i==0) echo " class=\"active\"";
		echo "><a href=\"#".$logs_array[$i]."\" class=\"".$logs_array[$i]."\" data-toggle=\"tab\">".$logs_array[$i]."</a></li>\n";
	}
	?>
	</ul>
	<div id="log-content" class="tab-content">
	<?php
		for($i=0;$i<count($logs_array);$i++) {
		echo "<div class=\"tab-pane fade in";
		if($i==0) echo " active";
		echo "\" id=\"".$logs_array[$i]."\"><textarea class=\"form-control\" name='log_content' readonly wrap='off' rows='20'></textarea></div>\n";
	}
	?>
	</div>

<?php
	// delete button
	echo build_fnajax_link($mod, "", "fn-adminpanel", "post", "form_cleanlog");
	?><input type="hidden" name="conf_mod" value="cleanlog" />
	<input type="hidden" name="logfile" value="">
	<input type="submit" class="form-control btn btn-warning" value="<?php echo _FNCC_CLEANLOG?>">
	</form>
	<script>
	//jliyn: just load if you need
	$('#log-tab a').click(function (e) {
	e.preventDefault();
	var key=$(this).attr('class');
	$('#'+key).find('textarea').load('<?php echo get_fn_dir("sections")."/$mod/"?>none_functions/fncc_parse_log_content.php?file='+key);
	$("#form_cleanlog").find('input[name="logfile"]').val(key);
	});
	$('div > .active').find('textarea').load('<?php echo get_fn_dir("sections")."/$mod/"?>none_functions/fncc_parse_log_content.php?file='+$('div > .active').attr('id'));
	</script>
	
	<?php
}

/*
 * Manage Flatnuke blacklists
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @version 20130216
 * 
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * @version 20140514 - structured to work with bootstrap, lists now are in TAB, tabs are dinamically loaded on demand 
 * 
 */
function fncc_manageblacklists() {
	// security checks
	$mod  = getparam("mod",  PAR_GET, SAN_FLAT);
	$list = getparam("list", PAR_GET, SAN_FLAT);
	$rewrite     = "false";
	// load content of the log you choosed
	if(isset($list) AND file_exists("include/blacklists/$list.php")) {
		$rewrite = $list;
		$content = get_file("include/blacklists/$list.php");
		
	}
	// list of the blacklist files
	$lists_array = array();
	$lists_dir = opendir("include/blacklists");
	while($lists_file=readdir($lists_dir)) {
		if($lists_file!="." AND $lists_file!="..") {
			array_push($lists_array, str_replace(".php","",$lists_file));
		}
	} //echo "<pre>";print_r($lists_array);echo "</pre>";	//-> TEST
	closedir($lists_dir);
	if(count($lists_array)==0) {
		echo "<b>"._NORESULT." !</b>";
		return;
	} else {
		sort($lists_array);
	}
	?>
	<ul id="blacklist-tab" class="nav nav-tabs">	
	<?php
	for($i=0;$i<count($lists_array);$i++) {
		echo "<li";
		if($i==0) echo " class=\"active\"";
		echo "><a href=\"#".$lists_array[$i]."\" class=\"".$lists_array[$i]."\" data-toggle=\"tab\">".$lists_array[$i]."</a></li>\n";
	}
	?>
	</ul>
	<div id="blacklist-content" class="tab-content">
	<?php
		for($i=0;$i<count($lists_array);$i++) {
		echo "<div class=\"tab-pane fade in";
		if($i==0) echo " active";
		echo "\" id=\"".$lists_array[$i]."\">";
		echo build_fnajax_link($mod, "", "fn-adminpanel", "post", "form-modblacklist-".$lists_array[$i]);
		echo "\n<textarea class=\"form-control\" name='conf_body' wrap='off' rows='20'>";
		echo "</textarea>\n";

	if(is_writeable("include/blacklists/".$lists_array[$i].".php"))	{
		?><div>
			<p class="text-danger"><?php echo _FNCC_WARNINGDOC?></p>
			<input type="hidden" name="conf_mod" value="modblacklist" />
			<input type="hidden" name="conf_file" value="include/blacklists/<?php echo $lists_array[$i]?>.php" />
			<input type="submit" class="form-control btn btn-warning" value="<?php echo _MODIFICA?>" />
		</div><?php
	} else {
		?><div>
			<p class="text-danger"><?php echo _FNCC_WARNINGRIGHTS?></p>
		</div><?php
	}
		
		
		echo "</form></div>";
		
	}
	?>
	</div>
	<script>
	//jliyn: just load if you need
	$('#blacklist-tab a').click(function (e) {
	e.preventDefault();
	var key=$(this).attr('class');
	$('#'+key).find('textarea').load('<?php echo get_fn_dir("sections")."/$mod/"?>none_functions/fncc_parse_blacklist_content.php?file='+key);
	});
	$('div > .active').find('textarea').load('<?php echo get_fn_dir("sections")."/$mod/"?>none_functions/fncc_parse_blacklist_content.php?file='+$('div > .active').attr('id'));
	</script>
	<?php
}

/*
 * Helper:
 * Open the form in a html5 way
 * according to Bootstrap classes
 * 
 * DEPRECATED: USE jQueryfncall, this is just for testing 
 *
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * @version 20140511
 * 
 * @param   string	  $fields_flow    Bootstrap option, can be: horizontal, inline, default 'null'
 */
function fncc_generic_form_open($form_id, $fields_flow=null){
	$mod  = getparam("mod", PAR_GET,  SAN_FLAT);
	$fields_flow = getparam($fields_flow, PAR_NULL, SAN_FLAT);
	if(strlen($fields_flow)>0){$flow_class=" class=\"form-".$fields_flow."\""; var_dump($fields_flow);}
	else {$flow_class="";}
	echo "<form id=\"".$form_id."\" method=\"post\" role=\"form\"".$flow_class.">";
	}

/*
 * Helper:
 * Add a generic input for types: text, email, password
 * according to Bootstrap classes
 *
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * @version 20140511
 * 
 * @param   string	  $keyword	needs to create lang constant and fill-in input field attributes  
 * @param	string	  $value	fill-in value attribute, to not set a value use 'null' in arguments
 * @param	number	  $maxleght	fill-in the maxleght attribute
 */

function fncc_input_text($keyword,$value=NULL,$maxlenght='64',$input_type='text'){
	
	$constant=constant("_FNCC_CONF".strtoupper($keyword));
				
	$pattern="<div class=\"form-group\" id=\"form-".$keyword."\">";
	$pattern.="<label for=\"".$keyword."\">".$constant."</label>";
	$pattern.="<input type=\"".$input_type."\" class=\"form-control\" name=\"".$keyword."\" id=\"".$keyword."\" maxlength=\"".$maxlenght."\" value=\"".$value."\">";
	$pattern.="</div>";
	
	return $pattern;
	}

/*
 * Helper:
 * Add a generic input number
 * according to Bootstrap classes
 *
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * @version 20140511
 * 
 * @param   string	$keyword	needs to create lang constant and fill-in input field attributes  
 * @param	string	$value		fill-in value attribute, to not set a value use 'null' in arguments
 * @param	number	$maxleght	fill-in the maxleght attribute
 * @param	array	$range		min and max values
 * @param	bool	$slider		add a slider if true, default is false
 */

function fncc_input_number($keyword,$value=NULL,$maxlenght='6',$range,$slider=false){

	$constant=constant("_FNCC_CONF".strtoupper($keyword));
				
	$pattern="<div class=\"form-group\" id=\"form-".$keyword."\">";
	$pattern.="<label for=\"".$keyword."\">".$constant."</label>";
	$pattern.="<input type=\"number\" class=\"form-control\" style=\"width:10em\" name=\"".$keyword."\" id=\"".$keyword."\" maxlength=\"".$maxlenght."\" min=\"".$range[0]."\" max=\"".$range[1]."\" value=\"".$value."\">";
	$pattern.="</div>";
		if($slider == true){
         $pattern.="<script>$('#".$keyword."').add_slider('".$keyword."');</script>";
		}
	return $pattern;
	}
/*
 * Helper:
 * Add a select
 * according to Bootstrap classes
 *
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * @version 20140511
 * 
 * @param   string	$keyword	needs to create lang constant and fill-in input field attributes  
 * @param	string	$value		the selected value, to not set a value use 'null' in arguments
 * @param	array	$values		values array for the select
 */
function fncc_select($keyword,$value,$values)
	{
		$constant=constant("_FNCC_CONF".strtoupper($keyword));
	$pattern="<div class=\"form-group\">";
	$pattern.="<label for=\"".$keyword."\">".$constant."</label>";
	$pattern.="<select name=\"".$keyword."\" id=\"".$keyword."\" class=\"form-control\">";
	foreach($values as $k=>$v)
		{
		$selected = ($value==$v) ? ("selected") : ("");
		$pattern.= "<option value=\"".$v."\" $selected>".$v."</option>";
			}
	$pattern.="</select>";
	$pattern.="</div>";
	return $pattern;
	}

/*
 * Helper:
 * Add a list of radio button
 * according to Bootstrap classes
 *
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * @version 20140511
 * 
 * @param   string	$keyword	needs to create lang constant and fill-in input field attributes  
 * @param	string	$value		fill-in value attribute, to not set a value use 'null' in arguments
 * @values	array	$values		array(number=>'text constant for the label'). ZEROCONF: default is 0/1 - No/yes inputs	
 * 
 */
function fncc_input_radio($keyword,$value,$values=NULL){

	$constant=constant("_FNCC_CONF".strtoupper($keyword));
		
	$pattern="<div class=\"form-group\" id=\"form-".$keyword."\">";
	$pattern.="<label for=\"".$keyword."\">".$constant."</label>";
	
	if(!isset($values)){
		$pattern.="	<label class=\"radio-inline\">";
		$pattern.="<input type=\"radio\" name=\"".$keyword."\" id=\"".$keyword."_0\" value=\"0\">";
		$pattern.= _FNCC_NO;
		$pattern.="	</label>";
		$pattern.="	<label class=\"radio-inline\">";
		$pattern.="<input type=\"radio\" name=\"".$keyword."\" id=\"".$keyword."_1\" value=\"1\">";
		$pattern.= _FNCC_YES;
		$pattern.="	</label>";
		}
	else {
		foreach ($values as $k=>$v)
			{
			$pattern.="<label class=\"radio-inline\">";
			$pattern.="<input type=\"radio\" name=\"".$keyword."\" id=\"".$keyword."_".$k."\" value=\"".$k."\">";
				if (defined($v)) {
				$pattern.= constant($v);
				}
				else {
				$pattern.= $v;
				}
			$pattern.="	</label>";
			}
		}
	$pattern.="</div>";
	$pattern.= fncc_input_checked($keyword,$value);
	return $pattern;	
}

/*
 * Helper:
 * according to settings creates a jquery call to define wich radio/checkbox has to be checked
 *
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * @version 20140511
 * 
 * @param   string	  $keyword	the radio name  
 * @param	string	  $value	fill-in value attribute
 * @param	string	  $type		radio or checkbox
 */
function fncc_input_checked($keyword,$value,$type='radio'){
	$pattern="<script>";
	$pattern.="$('input:".$type."[name=\"".$keyword."\"][value=\"".$value."\"]').prop('checked', true);";
	$pattern.="</script>";
	return $pattern;	
}

/*
 * Helper:
 * create a textarea
 *
 * @author Alfredo Cosco <orazio.nelson@gmail.com>
 * @version 20140511
 * 
 * @param   string	  $keyword	needs to create lang constant and fill-in field attributes  
 * @param	array	  $value	fill-in value attribute, to not set a value use 'null' in arguments
 * 
 */
function fncc_textarea($keyword,$value=NULL){

	$constant=constant("_FNCC_CONF".strtoupper($keyword));
	 
	$pattern="<div class=\"form-group\">";
	$pattern.="<label for=\"".$keyword."\">".$constant."</label>";
	$pattern.="<textarea name=\"".$keyword."\" class=\"form-control\" id=\"".$keyword."\" rows=\"6\"></textarea>";
	$pattern.="</div>";
	return $pattern;
	}

?>
