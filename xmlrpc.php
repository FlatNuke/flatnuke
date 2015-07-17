<?php

include_once("functions.php");
include_once("shared.php");
include_once("include/xmlrpc/IXR.php");

//metaWeblog.newPost (blogid, username, password, struct, publish) returns string
/*function newPost($args) { // funzione obsoleta
	$addr = getparam("REMOTE_ADDR", PAR_SERVER, SAN_FLAT);

	$blogid   = $args[0];
	$username = $args[1];
	$password = $args[2];
	$struct   = $args[3];
	$publish  = $args[4];

	$r_pass = getpass($username);
	if($r_pass == null)
		return null;
	else {
		if($r_pass != md5($password))
			return null;
	}

	if(getlevel($username, "home") == 10){
		$struct['title'] = ($struct['title']!="") ? ($struct['title']) : ("New post");
		$string  = "<?xml version='1.0'?>\n";
		$string .= "<!DOCTYPE fn:news SYSTEM \"http://flatnuke.sourceforge.net/dtd/news.dtd\">\n";
		$string .= "<fn:news xmlns:fn=\"http://flatnuke.sourceforge.net/news\">\n";
		$string .= "\t<fn:title>".$struct['title']."</fn:title>\n";
		$string .= "\t<fn:avatar>".$struct['categories'][0].".png</fn:avatar>\n";
		$string .= "\t<fn:reads>0</fn:reads>\n";
		$string .= "\t<fn:header>".$struct['description']."</fn:header>\n";
		$string .= "\t<fn:body></fn:body>\n";
		$string .= "</fn:news>";
		$postid = time();
		fnwrite("news/".$postid.".xml", $string, "w", array("nonull"));
		fnlog("News", $addr."||".$username."||News $postid published.");
		//generate_RSS(); // needs to be replaced ...
		return ($postid);
	}
	return null;
}*/


/*
1. appkey   (string): Unique identifier/passcode of the application sending the post. (See access info.)
2. username (string): Login for the Blogger user who's blogs will be retrieved.
3. password (string): Password for said username.
*/
function getUsersBlogs($args) {
	include "config.php";
	$addr = getparam("REMOTE_ADDR", PAR_SERVER, SAN_FLAT);
	$blogid   = $args[0];
	$username = $args[1];
	$password = $args[2];

	$r_pass = getpass($username);
	if($r_pass == null)
		return null;
	else {
		if($r_pass != md5($password))
			return null;
	}

	$a = array();
	$a['url'] = $addr;
	$a['blogid'] = "1";
	$a['blogName'] = $sitename;
	$b = array($a);
	return($b);
}

//metaWeblog.getCategories (blogid, username, password) returns array
function getCategories($args) {
	$addr = getparam("REMOTE_ADDR", PAR_SERVER, SAN_FLAT);
	$blogid   = $args[0];
	$username = $args[1];
	$password = $args[2];

	$r_pass = getpass($username);
	if($r_pass == null)
		return null;
	else {
		if($r_pass != md5($password))
			return null;
	}

	$modlist = array();
	$handle = opendir('images/news');
	while ($file = readdir($handle)) {
		if (!( $file=="." or $file==".." ) and (!preg_match("/^\./",$file)and ($file!="CVS")) ) {
			array_push($modlist, $file);
		}
	}
	closedir($handle);
	if(count($modlist)>0)
		sort($modlist);

	$b = array();
	for ($i=0; $i < count($modlist); $i++) {
		$a = array();
		$a['description'] = str_replace("_"," ", preg_replace("/\..*/","",$modlist[$i]));
		$a['categoryName'] = str_replace("_"," ", preg_replace("/\..*/","",$modlist[$i]));
		$a['htmlUrl'] = "#";
		$a['rssUrl'] = "#";
		$b[$i] = $a;
	}
	return($b);
}

//metaWeblog.newPost (blogid, username, password, n.post) returns array
/*function getRecentPosts($args) { // funzione obsoleta
	$blogid   = $args[0];
	$username = $args[1];
	$password = $args[2];
	$npost    = $args[3];

	$r_pass = getpass($username);
	if($r_pass == null)
		return null;
	else {
		if($r_pass != md5($password))
			return null;
	}

	$modlist = array();
	$handle = opendir('news');
	while ($file = readdir($handle)) {
		if (!( $file=="." or $file==".." ) and (!preg_match("/^\./",$file) and ($file!="CVS")) and preg_match("/xml$/i",get_file_extension($file)) ) {
			array_push($modlist, $file);
		}
	}
	closedir($handle);
	if(count($modlist)>0)
		rsort($modlist);

	if($npost > count($modlist))
		$npost = count($modlist);
	$b = array();

	for ($i=0; $i < $npost; $i++) {
		$fd     = get_file("news/".$modlist[$i]);
		$postid = str_replace(".xml", "", $modlist[$i]);
		$header = utf8_encode(get_xml_element("fn:header",$fd));
		$title  = utf8_encode(get_xml_element("fn:title",$fd));
		$cat    = str_replace(".png","", get_xml_element("fn:avatar",$fd));
		$a = array();
		$dt = date('Ymd',$postid)."T".date('H:i:s', $postid);
		$dt = new IXR_Date($dt);
		$a['description'] = $header;
		$a['dateCreated'] = $dt;
		$a['title']       = $title;
		$a['postid']      = $postid;
		$c = array();
		$c[0] = $cat;
		$a['categories'] = $c;
		$b[$i] = $a;
	}
	return $b;
}*/


//metaWeblog.editPost (postid, username, password, struct, publish) returns boolean
/*function editPost($args) { // funzione obsoleta
	$postid   = $args[0];
	$username = $args[1];
	$password = $args[2];
	$struct   = $args[3];
	$publish  = $args[4];

	$r_pass = getpass($username);
	if($r_pass == null)
		return null;
	else {
		if($r_pass != md5($password))
			return null;
	}

	if(getlevel($username, "home") == 10){
		$lockfile = "news/$postid.xml";
		$string = get_file($lockfile);
		$string = preg_replace("/<fn:title>.*<\/fn:title>/i","<fn:title>".$struct['title']."</fn:title>",$string);
		$string = preg_replace("/<fn:avatar>.*<\/fn:avatar>/i","<fn:avatar>".$struct['categories'][0].".png</fn:avatar>",$string);
		$string = preg_replace("/<fn:header>.*<\/fn:header>/i","<fn:header>".$struct['description']."</fn:header>",$string);
		fnwrite($lockfile, $string, "w", array("nonull"));
	} else {
		return false;
	}
	return true;
}*/

//metaWeblog.editPost (postid, username, password) returns array
/*function getPost($args) { // funzione obsoleta
	$postid   = $args[0];
	$username = $args[1];
	$password = $args[2];

	if(!file_exists("news/$postid.xml"))
		return null;

	$r_pass = getpass($username);
	if($r_pass == null)
		return null;
	else {
		if($r_pass != md5($password))
			return null;
	}

	$fd     = get_file("news/$postid.xml");
	$header = get_xml_element("fn:header",$fd);
	$title  = get_xml_element("fn:title",$fd);
	$cat    = str_replace(".png","", get_xml_element("fn:avatar",$fd));
	$a  = array();
	$dt = date('Ymd',$postid)."T".date('H:i:s', $postid);
	$dt = new IXR_Date($dt);
	$a['description'] = $header;
	$a['dateCreated'] = $dt;
	$a['title']       = $title;
	$a['postid']      = $postid;
	$c = array();
	$c[0] = $cat;
	$a['categories'] = $c;
	return($a);
}*/

/*
0. appkey : currently ignored
1. postId : postId is a unique identifier for the post created. It is the value returned by blogger.newPost. postId will look like..."zoneId|convId|pathToWeblog|msgNum".
2. username : the email address you use as a username for the site. This user must have privileges to post to the weblog as either the weblog owner, or a member of the owner group.
3. password : the password you use for the site
4. publish : true/false. Ignored.
*/
/*function deletePost($args) { // funzione obsoleta
	$appkey   = $args[0];
	$postid   = $args[1];
	$username = $args[2];
	$password = $args[3];
	$publish  = $args[4];
	// security checks
	$addr = getparam("REMOTE_ADDR", PAR_SERVER, SAN_FLAT);
	$r_pass = getpass($username);
	if($r_pass == null)
		return null;
	else {
		if($r_pass != md5($password))
			return null;
	}
	// check file exists
	if(!file_exists("news/$postid.xml"))
		return false;
	// only admin can delete
	if(getlevel($username, "home") == 10){
		unlink("news/$postid.xml");
		fnlog("News", $postid."||".$username."||Deleted news $postid.");
		return true;
	} else {
		return false;
	}
}*/

$server = new IXR_Server(array(
    'metaWeblog.getPost' => 'getPost',
    'metaWeblog.newPost' => 'newPost',
    'metaWeblog.editPost' => 'editPost',
    'blogger.getUsersBlogs' => 'getUsersBlogs',
    'metaWeblog.getRecentPosts' => 'getRecentPosts',
    'metaWeblog.getCategories' => 'getCategories',
	'metaWeblog.deletePost' => 'deletePost'
));

?>
