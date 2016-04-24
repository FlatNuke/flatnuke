<?php

$bgcolor1  = "#ffffff";
$bgcolor3  = "#ffffff";
$bgcolor2  = "#efefef";
$logo = "images/logo.png";

$forumborder = "#dddddd";
$forumback   = "#ffffff";

define("_THEME_VER", 4);

// open a normal table
function OpenTable() {
	echo "<div>";
}

// close a normal table
function CloseTable() {
	echo "</div>";
}

// open table title
function OpenTableTitle($title) {
	echo "<div class='section'>\n<h1>$title</h1>\n<div class='sectioncontent'>\n";
}

// close table title
function CloseTableTitle() {
	echo "</div>\n</div>\n";
}

// open a block
function OpenBlock($img, $title) {
	$img   = getparam($img, PAR_NULL, SAN_FLAT);
	$title = getparam($title, PAR_NULL, SAN_FLAT);
	?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php echo $title ?></h3>
		</div>
		<div class="panel-body">
	<?php
}

// open block of main menu
function OpenBlockMenu() {
  echo "<div class='blockmenu'>\n<div class='blockmenuc'>\n<div class='blocktop'></div>\n";
}

// close a block
function CloseBlock() {
  echo "</div>\n</div>\n";
}

// close block menu
function CloseBlockMenu() {
  echo "</div>\n</div>\n";
}


// function to create horizontal menu
function create_menu_horiz() {
	$mod = getparam("mod", PAR_GET, SAN_FLAT);
	$father_mod = explode("/", $mod);
	global $theme;
	// get menu items
	$menu_links = list_sections("sections", "links");
	$menu_names = list_sections("sections", "names");
	if($menu_links == null)
		return;
	// print menu
	$menu_item = _HOMEMENUTITLE;	// homesite
	$class = ($mod == "") ? "active":"";
	echo "\n<li class=\"".$class."\"><a href=\"index.php\" title=\""._FINDH."\">".$menu_item."</a></li>";
	for ($i=0; $i < count($menu_links); $i++) {
		$father_sect = explode("/", $menu_names[$i]);
		$class = ($father_mod[0] == $father_sect[0]) ? "active":"";
		echo "\n<li class=\"$class\">$menu_links[$i]</li>";
	}
}


// function to create block menu
function create_block_menu() {
	$mod = getparam("mod", PAR_GET, SAN_FLAT);
	$father_mod = explode("/", $mod);
	global $theme;
	// get menu items
	$menu_links = list_sections("sections", "links");
	$menu_names = list_sections("sections", "names");
	if($menu_links == null)
		return;
	// print menu
	$menu_item = _HOMEMENUTITLE;	// homesite
	$class = ($mod == "") ? "active":"";
	echo "\n<a href=\"index.php\" class=\"list-group-item ".$class."\" title=\""._FINDH."\">".$menu_item."<span class=\"fa fa-chevron-right\"></span></a>";
	for ($i=0; $i < count($menu_links); $i++) {
		$father_sect = explode("/", $menu_names[$i]);
		$class = ($father_mod[0] == $father_sect[0]) ? "active":"";
		// add icons to the right
		$tmp = str_replace("</a>","<span class=\"fa fa-chevron-right\"></span></a>",$menu_links[$i]);
		echo str_replace("title=","class=\"list-group-item $class\" title=",$tmp);
	}
}

// function to create footer site
function CreateFooterSite() {
	$footer_elements = get_footer_array();
	echo $footer_elements['img_fn']." ";
	echo $footer_elements['img_w3c']." ";
	echo $footer_elements['img_css']." ";
	echo $footer_elements['img_rss']." ";
	echo $footer_elements['img_mail'];
	echo "<br>".$footer_elements['legal'];
	echo "<br>".$footer_elements['time'];  // use it to test speed improvements
}

function create_motd_block(){
	global $theme;
	// print motd content if exists and if $postaction is not set!
	$postaction = getparam("newsaction",PAR_POST,SAN_FLAT);
	if(file_exists(get_fn_dir("var")."/motd.php") AND trim(get_file(get_fn_dir("var")."/motd.php"))!="" and $postaction=="") {
		echo "<div class=\"container col-lg-12 \">";
		echo "<div class=\"jumbotron\">";
		echo "<div id=\"fnmotd\">";
		// print motd image if exists
		if(file_exists("themes/$theme/images/motd.png")) {
			echo "<img src='themes/$theme/images/motd.png' class=\"motd\" alt='Motd' />";
		} else echo "<!-- MOTD image \"themes/$theme/images/motd.png\" not found -->";
		include (get_fn_dir("var")."/motd.php");

		//fix: when motd text is too short the image goes out of her area
		echo "<div style=\"clear:both;\"></div>";

		// print administration button o modify motd content
		if (_FN_IS_ADMIN){
			global $news_editor;
			echo "<br /><a href=\"index.php?mod=modcont&amp;from=index.php&amp;file="._FN_VAR_DIR."%2Fmotd.php";
			if ($news_editor=="fckeditor" AND file_exists("include/plugins/editors/FCKeditor/fckeditor.php"))
				echo "&amp;fneditor=fckeditor";
			else if ($news_editor=="ckeditor" AND file_exists("include/plugins/editors/ckeditor/ckeditor.php"))
				echo "&amp;fneditor=ckeditor";
			echo " \" title=\""._MODIFICA."\">"._ICONMODIFY._MODIFICA."</a>";
		}
		echo "</div>";
		echo "</div>";
		echo "</div>";
	}
}

function create_central_blocks($where) {
	$where = getparam($where, PAR_NULL, SAN_FLAT);
	echo "<div class='well'>";
	echo "<p>";
	load_php_code("blocks/center/".$where);
	echo "</p>";
	echo "</div>";
}

?>
