<?php
$bodycolor = "#ffffff";
$bgcolor1  = "#ffffff";
$bgcolor3  = "#ffffff";
$bgcolor2  = "#efefef";
$logo = "images/logo.png";
$backimage = "";

$forumbody   = "#ffffff";
$forumborder = "#dddddd";
$forumback   = "#ffffff";

define("_THEME_VER", 1);

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
	echo "<div class='block'>\n<div class='blocktop'></div>\n";
	echo "<div class='blocktitle'>\n<div class='inside'>&nbsp;</div>\n<div class='title'>$title</div>\n</div>\n";
	echo "<div class='blockcontent'>\n";
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
	echo "\n<a href=\"index.php\" class=\"list-group-item ".$class."\" title=\""._FINDH."\">".$menu_item."<span class=\"glyphicon glyphicon-chevron-right\"></span></a>";
	for ($i=0; $i < count($menu_links); $i++) {
		$father_sect = explode("/", $menu_names[$i]);
		$class = ($father_mod[0] == $father_sect[0]) ? "active":"";
		// add icons to the right
		$tmp = str_replace("</a>","<span class=\"glyphicon glyphicon-chevron-right\"></span></a>",$menu_links[$i]);
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

?>
