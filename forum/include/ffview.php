<?php
if (preg_match("/ffview.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    fd_die("You cannot call ffview.php!",__FILE,__LINE);
}

/**
 * Flatforum: un forum integrato nella struttura di Flatnuke
 *
 * Autore: Aldo Boccacci
 * sito web: www.aldoboccacci.it
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


/**
 * Pagina principale del forum
 * @param string $root la root del forum
 * @author Aldo Boccacci
 * @since 0.1
 */
function forum_overview($root){
$mod = _FN_MOD;
global $fuso_orario,$mesi,$giorni,$theme,$bgcolor2,$bgcolor3;

view_forum_header();
if (_FN_IS_ADMIN){
	echo "<br><br><div align=\"center\"><form action=\"index.php?mod=$mod\" method=\"post\">
<input type=\"hidden\" name=\"ffaction\" value=\"ffcontrolpanel\" />
<input type=\"submit\" value=\""._FFCONTROLPANEL."\">
</form></div>";

}

if (_FN_IS_ADMIN){
?>
<div style="text-align : center;">
<form action="index.php?mod=<?php echo $mod; ?>" method="post">
<input type="hidden" name="ffaction" value="newgroup" />
<input type="submit" value="<?php echo _NEWGROUP; ?>"<?php
if (!is_writable(get_forum_root())) echo "disabled=\"disabled\"";
?> />
</form>
<?php
if (!is_writable(get_forum_root())) echo _THEDIR." <b>".get_forum_root()."</b> "._NOTWRITABLE.": controllare i permessi.<br/><br/>";
?>
<form action="index.php?mod=<?php echo $mod; ?>" method="post">
<input type="hidden" name="ffaction" value="newargument" />
<input type="submit" value="<?php echo _NEWARGUMENT; ?>" <?php

if (count(list_forum_groups(get_forum_root()))==0) echo "disabled=\"disabled\"";
?> />
<?php if (count(list_forum_groups(get_forum_root()))==0) echo "<br><i>"._CREATEGROUPS."</i>";?>
</form>
</div>
<?php
}//fine controllo admin
if (!_FN_IS_ADMIN) echo "<br><br>";
view_ffmotd();
//stampo i gruppi + gli argomenti
$groups=array();
$group = "";
$groups = list_forum_groups(get_forum_root());
//x windows
// $groups[]= "NULL";
	foreach($groups as $group){
		ff_view_group(get_forum_root(),$group);
		echo "<br>";
	}

echo "<br/>";
module_copyright("Flatforum",get_ff_version(),"<b>Aldo Boccacci</b> aka Zorba","zorba_(AT)tin.it", "http://www.aldoboccacci.it", "Gpl version 2.0");
}

/**
 * Visualizza l'argomento del forum indicato
 *
 * @param string $root la root del forum
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di cui restituire i topics
 * @author Aldo Boccacci
 * @since 0.1
 */
function forum_view_argument($root,$group,$argument){
	if (!check_path($root,get_forum_root(),"false")) ff_die("forum root is invalid!",__FILE__,__LINE__);
	if (!check_path($group,"","false")) ff_die("forum group is invalid!",__FILE__,__LINE__);
	if (!check_path($argument,"","false")) ff_die("forum argument is invalid!",__FILE__,__LINE__);

// 	print_r(load_topics_list($group,$argument));

	$mod = _FN_MOD;
	$group = getparam("group",PAR_GET,SAN_FLAT);
	if (!check_path($group,"","false")) ff_die("\$group is invalid! (".strip_tags($group).")",__FILE__,__LINE__);
	$argument = getparam("argument",PAR_GET,SAN_FLAT);
	if (!check_path($argument,"","false")) ff_die("\$argument is invalid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$page = getparam("page",PAR_GET,SAN_FLAT);
	if (!check_var($page,"digit") and trim($page)!="") ff_die("\$page is invalid! (".strip_tags($page).")",__FILE__,__LINE__);
	if ($page=="") $page="1";

	if (!user_can_view_argument(get_forum_root(),$group,$argument)){
		echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."\" >";
		die();
	}

	global $postperpage,$topicperpage,$postperpage,$theme,$giorni,$mesi,$fuso_orario,$bgcolor2,$bgcolor3;

	view_forum_header();

	//CARICO I DATI
	$topicstmp = array();
	$topics = array();
// 	$topicstmp=fast_list_argument_topics(get_forum_root(),$group,$argument);
	$topicstmp=load_topics_list($group,$argument);
	$topic="";
	foreach ($topicstmp as $topic){
		if (!preg_match("/hide_/i",basename($topic))){
			$topics[]=$topic;
		}
		else if (is_forum_moderator()){
			$topics[]=$topic;
		}
	}

	//INTESTAZIONE GRUPPO
	echo "<table style=\"width:100%;border-collapse: collapse;border:0px;\"><tr><td width=\"33%\">";

	if (count($topics)>$topicperpage){

		$pagescount = ceil(count($topics)/$topicperpage);
		$link = "index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument);
		ff_page_selector($page,$pagescount,$link);
	}

	echo "</td><td width=\"34%\">";
	//al centro nulla
	echo "</td><td width=\"33%\">";
	if (!_FN_IS_GUEST){
		if (argument_is_locked(get_forum_root(),$group,$argument)){
			if (is_forum_moderator()){
				echo "<div><span class=\"forum-new\" >"._ICONLOCK."<a style=\"font-size: 120%;\" href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;ffaction=newtopic\"  title='"._FCREATOP."'/>"._FNUOVOTOP."</a></span></div>";
			}
			else echo "<div><span title=\""._ARGUMENTLOCKED."\">"._ICONLOCK."</span>&nbsp;"._ARGUMENTLOCKED."<br><br></div>";
		}
		else {
			echo "<div><br><span class='forum-new'><a style='font-size: 120%;' href='index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;ffaction=newtopic' title='"._FCREATOP."'>"._FNUOVOTOP."</a></span><br><br></div>";
		}
	}//fine controllo guest

	echo "</td></tr></table><br><table";

	if (file_exists("themes/$theme/forum.css")){
		echo " class=\"forum-arg-table\"";
	}
	else echo " style=\"width:100%;border-collapse: collapse;border:1px solid $bgcolor2;\" ";

	echo " cellspacing='0'><tr><td";

	if (file_exists("themes/$theme/forum.css")){
		echo " class=\"forum-arg-icon-header\"";
	}
	else echo " style=\"background-color : $bgcolor3;\"";

	echo "><!--icona--></td><td";

	if (file_exists("themes/$theme/forum.css")){
		echo " class=\"forum-arg-mess-header\"";
	}
	else echo " style=\"background-color : $bgcolor3;\"";

	echo "><b>"._FTITTOP."</b></td><td";

	if (file_exists("themes/$theme/forum.css")){
		echo " class=\"forum-arg-pages-header\"";
	}
	else echo " style=\"background-color : $bgcolor3;\"";

	echo "><b>"._FNPAG."</b></td><td";

	if (file_exists("themes/$theme/forum.css")){
		echo " class=\"forum-arg-msg-count-header\"";
	}
	else echo " style=\"background-color : $bgcolor3;\"";

	echo "><b>"._FNMESS."</b></td><td";

	if (file_exists("themes/$theme/forum.css")){
		echo " class=\"forum-arg-visits-header\"";
	}
	else echo " style=\"background-color : $bgcolor3;\"";
	echo "><b>"._VISITS."</b></td><td";

	if (file_exists("themes/$theme/forum.css")){
		echo " class=\"forum-arg-firstpost-header\"";
	}
	else echo " style=\"background-color : $bgcolor3;\"";

	echo "><b>"._FINIPOST."</b></td><td";

	if (file_exists("themes/$theme/forum.css")){
		echo " class=\"forum-arg-lastpost-header\"";
	}
	else echo " style=\"background-color : $bgcolor3;\"";

	echo "><b>"._FULTPOST."</b></td></tr>";

	$topic="";
	$count=(($page-1) * $topicperpage);
	$oldcount = $count;

	for ($count; $count<($topicperpage+$oldcount); $count++) {

		if (isset($topics[$count])) $topic = $topics[$count];
		else continue;

		$topicdata=array();
		$topicdata = fast_load_topic(trim($topic));

		if (!is_forum_moderator() and !topic_is_visible($topic)) continue;

		echo "<tr>";
		echo "<td class=\"forum-arg-icon\">";

		if (preg_match("/top_/i",basename($topic)))
			echo _ICONONTOP;
		else
			echo "<img src='forum/icons/normal.png' />";

		if (topic_is_locked($topic))
			echo "<br><span title='"._TOPICLOCKED."'>"._ICONLOCK."</span>";

		echo "</td>";
		echo "<td class='forum-arg-mess' align='left'>";
		//se e' nascosto
		if (is_forum_moderator() and !topic_is_visible($topic))
			echo "<span style=\"color : #ff0000; text-decoration : line-through;\">";
		echo "<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=".basename($topic)."&amp;page=last\" title='"._VIEWTOPICTITLE.": ".$topicdata['properties']['topictitle']."'>".$topicdata['properties']['topictitle']."</a>";
		//se e' nascosto
		if (is_forum_moderator() and !topic_is_visible($topic))
			echo "</span>";
		echo "</td>";
		//conto pagine
		echo "<td class=\"forum-arg-pages\" align=\"center\">";
// 		echo count($topicdata['posts']);
		if (count($topicdata['posts'])>$postperpage){
			$pagescount = ceil(count($topicdata['posts'])/$postperpage);
			for ($countpages=1;$countpages<$pagescount+1;$countpages++){
				echo "[<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=".basename($topic)."&amp;page=$countpages\" title=\""._GOTOTHEPAGE." $countpages\">$countpages</a>]";
				//sistema

				if (($countpages/3) == round($countpages/3)) echo "<br>";
			}
		}
		else echo "[<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=".basename($topic)."&amp;page=1\" title=\""._GOTOTHEPAGE." 1\">1</a>]";
		echo "</td>";
		echo "<td class=\"forum-arg-msg-count\" align=\"center\">".count($topicdata['posts'])."</td>";
		echo "<td class=\"forum-arg-visits\" align=\"center\">".$topicdata['properties']['hits']."</td>";

		$postime = $topicdata['posts']['0']['time'];
		echo "<td class=\"forum-arg-firstpost\" align=\"center\">".$topicdata['posts']['0']['poster']."<br/>";
		//stampo la data del post
// 		echo $giorni[date("w",$postime+(3600*$fuso_orario))];
		echo date(" d ",$postime+(3600*$fuso_orario));
		$tmp=date(" m",$postime+(3600*$fuso_orario));
		echo $mesi[$tmp-1];
		echo date(" y ",$postime+(3600*$fuso_orario));
		echo "<br>";
		echo date(" H:i:s ",$postime+(3600*$fuso_orario));

		echo "</td>";
		$latest = count($topicdata['posts'])-1;
		echo "<td class=\"forum-arg-lastpost\" align=\"center\">".$topicdata['posts'][$latest]['poster']."<br/>";
		$postime = $topicdata['posts'][$latest]['time'];
		//stampo la data dell'ultimo post
// 		echo $giorni[date("w",$postime+(3600*$fuso_orario))];
		echo date(" d ",$postime+(3600*$fuso_orario));
		$tmp=date(" m",$postime+(3600*$fuso_orario));
		echo $mesi[$tmp-1];
		echo date(" y ",$postime+(3600*$fuso_orario));
		echo "<br>";
		echo date(" H:i:s ",$postime+(3600*$fuso_orario));

		echo "</td>";
		echo "</tr>";

		if (is_forum_moderator()){
			echo "<tr><td colspan=\"7\" align=\"center\">";
			if (_FN_IS_ADMIN){
				echo "<a href=\"index.php?mod=modcont&amp;file=".rawurlencodepath($topic)."&amp;from=index.php?mod=".rawurlencodepath($mod).rawurlencode("&")."group=".rawurlencodepath($group).rawurlencode("&")."argument=".rawurlencodepath($argument)."\" title='"._EDITTOPIC.": ".$topicdata['properties']['topictitle']."'>"._ICONMODIFY._MODIFICA."</a>";

				echo "&nbsp;<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;topicpath=".rawurlencodepath($topic)."&amp;ffaction=movetopicinterface\" title=\""._MOVETOPIC.": ".$topicdata['properties']['topictitle']."\">"._ICONMOVE._MOVE."</a>";

				echo "&nbsp;<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;topicpath=".rawurlencodepath($topic)."&amp;ffaction=deletetopicinterface\" title=\""._DELETETOPIC.": ".$topicdata['properties']['topictitle']."\">"._ICONDELETE._ELIMINA."</a>";
			}

			if (is_forum_moderator()){
				if (!preg_match("/top_/i",basename($topic))){
					echo "&nbsp;<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=".basename($topic)."&amp;ffaction=ontop\" title=\""._STICKYTOPIC.": ".$topicdata['properties']['topictitle']."\">"._ICONONTOP._STICKY."</a>";
				}
				else {
					echo "&nbsp;<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=".basename($topic)."&amp;ffaction=normal\" title=\""._UNSTICKYTOPIC.": ".$topicdata['properties']['topictitle']."\">"._ICONNORMAL._UNSTICKY."</a>";
				}

				//NASCONDI/MOSTRA TOPIC
				if (topic_is_visible($topic)){
					echo "&nbsp;<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=".basename($topic)."&amp;ffaction=hide\" title=\""._HIDETOPIC.": ".$topicdata['properties']['topictitle']."\">"._ICONHIDE._HIDE."</a>";
				}
				else {
					echo "&nbsp;<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=".basename($topic)."&amp;ffaction=show\" title=\""._SHOWTOPIC.": ".$topicdata['properties']['topictitle']."\">"._ICONSHOW._SHOW."</a>";
				}

				//LOCKING
				//se e' bloccato l'argomento non posso agire sul singolo topic
				if (!argument_is_locked(get_forum_root(),$group,$argument)){
					if (topic_is_locked($topic)){
						echo "&nbsp;<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=".basename($topic)."&amp;ffaction=unlock\" title=\""._UNLOCKTOPIC.": ".$topicdata['properties']['topictitle']."\">"._ICONUNLOCK._UNLOCK."</a>";
					}
					else {
						echo "&nbsp;<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=".basename($topic)."&amp;ffaction=lock\" title=\""._LOCKTOPIC.": ".$topicdata['properties']['topictitle']."\">"._ICONLOCK._LOCK."</a>";
					}
				}
				else {
					echo "&nbsp;<span title=\""._ARGUMENTLOCKED."\">"._ICONLOCK."</span>"._ARGUMENTLOCKED;
				}
			}
			echo "</td></tr>";

		}

	}

	echo "</table>";

	//INTESTAZIONE GRUPPO
	echo "<br><table style=\"width:100%;border-collapse: collapse;border:0px;\"><tr><td width=\"33%\">";

	if (count($topics)>$topicperpage){

		$pagescount = ceil(count($topics)/$topicperpage);
		$link = "index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument);
		ff_page_selector($page,$pagescount,$link);
	}

	echo "</td><td width=\"34%\">";
	//al centro nulla
	echo "</td><td width=\"33%\">";
	if (!_FN_IS_GUEST){
		if (argument_is_locked(get_forum_root(),$group,$argument)){
			if (is_forum_moderator()){
				echo "<div><span class=\"forum-new\" >"._ICONLOCK."<a style=\"font-size: 120%;\" href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;ffaction=newtopic\" title='"._FCREATOP."' />"._FNUOVOTOP."</a></span></div>";
			}
			else echo "<div><span title=\""._ARGUMENTLOCKED."\">"._ICONLOCK."</span>&nbsp;"._ARGUMENTLOCKED."<br><br></div>";
		}
		else {
			echo "<div><br><span class='forum-new'><a style='font-size: 120%;' href='index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;ffaction=newtopic' title='"._FCREATOP."'>"._FNUOVOTOP."</a></span><br><br></div>";
		}
	}//fine controllo guest
	echo "</td></tr></table>";

	if (count($topics)>$topicperpage){
		//Per accessibilità
		echo "<div style=\"text-align: center;\"><noscript><br><br>";

		for ($count=1; $count<$pagescount+1;$count++){
			if ($page == $count) echo "[$count]";
			else echo "[<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;page=$count\" title=\""._GOTOTHEPAGE." $count\">$count</a>]";
			if (($count/20) == round($count/20)) echo "<br>";
		}

		echo "</noscript>";
		echo "</div>";
	}
}

/**
 * Funzione per visualizzare un gruppo con tutti i suoi argomenti
 *
 */
function ff_view_group($root,$group){
	if (!check_path($root,get_forum_root(),"false")) ff_die("forum root is not valid!",__FILE__,__LINE__);
	if (!check_path($group,"","false")) ff_die("forum group is not valid!",__FILE__,__LINE__);
	$mod = _FN_MOD;

	global $fuso_orario,$mesi,$giorni,$theme,$bgcolor2,$bgcolor3;

	if (isset($_GET['group'])) view_forum_header();
	if (isset($_GET['group'])) echo "<br>";

	echo "<table ";
	if (file_exists("themes/$theme/forum.css")){
		echo "class=\"forum-group-table\" ";
	}
	else echo "style=\"width:100%;border-collapse: collapse;border:1px solid  $bgcolor2;\" ";

	echo  "cellspacing=\"0\">";

	echo "<tr><td colspan=\"5\"";

	if (file_exists("themes/$theme/forum.css")){
		echo " class=\"forum-group-header\"";
	}
	else echo "style=\"background-color : $bgcolor3;\"";

	echo "><span ><a ";

	if (file_exists("forum/$theme/forum.css")){
		echo " class=\"forum-group-name\" ";
	}
	else echo " style=\"font-size: 140%; font-weight : bolder;\" ";

	$groupname= "";
	$groupname = str_replace("_"," ",preg_replace("/^[0-9]*_/i","",$group));

	echo " href=\"index.php?mod=$mod&amp;group=$group\" title=\""._VIEWGROUPTITLE.": $groupname\">$groupname</a></span>";

	//opzioni di amministrazione per i gruppi
	if (_FN_IS_ADMIN){
		echo "&nbsp;&nbsp;<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;ffaction=renamegroupinterface\" title=\""._RENAMEGROUP.": $groupname\">"._ICONRENAME._RENAMEGROUP."</a>";
		echo "&nbsp;<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;ffaction=deletegroupinterface\" title=\""._DELETEGROUP.": $groupname\">"._ICONDELETE._DELETEGROUP."</a>";
	}

	echo "</td></tr>";

		?>
<!--intestazione -->
<tr class="forum-group-title">
<td class="forum-group-icon-header"></td>
<td class="forum-group-arg-header" width="50%"><b><?php echo _ARGUMENT; ?></b></td>
<td class="forum-group-topics-header"><b><?php echo _TOPICS; ?></b></td>
<td class="forum-group-msg-header"><b><?php echo _FNMESS; ?></b></td>
<td class="forum-group-latest-msg-header"><b><?php echo _FULTPOST; ?></b></td>
</tr>
<?php

	$arguments=array();
	$argument="";
// 	$arguments[] = "NULL";
	$temparguments=array();
	$temparguments = list_group_arguments(get_forum_root(),$group);

	foreach ($temparguments as $argument){
		if ($argument=="NULL") continue;
		$argumentdata = array();
		$argumentdata = load_argument_props(get_forum_root(),$group,$argument);
		if (!user_can_view_argument(get_forum_root(),$group,$argument,$argumentdata)) continue;

		$argumentstats = array();
		$argumentstats = load_argument_stats($group,$argument);

		$argumentname="";
		$argumentname= str_replace("_"," ",preg_replace("/^[0-9]*_/i","",$argument));

		echo "<tr>";
		echo "<td class=\"forum-group-icon\"><img src=\"".$argumentdata['icon']."\" alt=\"icon\" />";
		if (argument_is_locked(get_forum_root(),$group,$argument)){
			echo "<br/><span title=\""._ARGUMENTLOCKED."\">"._ICONLOCK."</span>";
		}
		echo "</td>";
		echo "<td class=\"forum-group-arg\" valign=\"top\"><a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."\" title=\""._VIEWARGUMENTTITLE.": $argumentname\">$argumentname</a>";
		if ($argumentdata['description']!="")
			echo "<br><i>".$argumentdata['description']."</i>";
		echo "</td>";
		echo "<td class=\"forum-group-topics\" align=\"center\" valign=\"top\">".$argumentstats['topics']."</td>";
		echo "<td class=\"forum-group-msg\" align=\"center\" valign=\"top\">".$argumentstats['posts']."</td>";

		if (trim($argumentstats['lastpost'])!=""){
			$data = fast_load_topic($argumentstats['lastpost']);
			$latest = count($data['posts']);
			$latestpost = $data['posts'][$latest-1];
		}
		echo "<td class=\"forum-group-latest-msg\" align=\"center\" valign=\"top\">";
		if ($argumentstats['topics']!=0){
			echo "<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=".basename($argumentstats['lastpost'])."&amp;page=last\" title=\""._VIEWTOPICTITLE.": ".$data['properties']['topictitle']."\"><b>".$latestpost['poster']."</b><br>";
			$postime = $latestpost['time'];
			//stampo la data dell'ultimo post
// 			echo $giorni[date("w",$postime+(3600*$fuso_orario))];
			echo date(" d",$postime+(3600*$fuso_orario));
			echo "&nbsp;";
			$tmp=date("m",$postime+(3600*$fuso_orario));
			echo $mesi[$tmp-1];
			echo "&nbsp;";
			echo date("Y ",$postime+(3600*$fuso_orario));
			echo "<br>";
			echo date(" H:i:s ",$postime+(3600*$fuso_orario));
			echo "</a>";
		}
		else echo _NOTOPICS."!<br><br><br>";
		echo "</td></tr>";
		if (_FN_IS_ADMIN){
			echo "<tr><td colspan=\"5\" align=\"center\">";

			echo "&nbsp;<a href=\"index.php?mod=".rawurlencode($mod)."&amp;group=".rawurlencode($group)."&amp;argument=".rawurlencode($argument)."&amp;ffaction=renameargument\" title=\""._RENAMEARGTITLE.": $argumentname\">"._ICONRENAME._RENAME."</a>";

			echo "&nbsp;<a href=\"index.php?mod=".rawurlencode($mod)."&amp;group=".rawurlencode($group)."&amp;argument=".rawurlencode($argument)."&amp;ffaction=editargument\" title=\""._EDITARGTITLE.": $argumentname\">"._ICONMODIFY._MODIFICA."</a>";

			echo "&nbsp;<a href=\"index.php?mod=".rawurlencode($mod)."&amp;group=".rawurlencode($group)."&amp;argument=".rawurlencode($argument)."&amp;ffaction=moveargumentinterface\" title=\""._MOVEARGTITLE.": $argumentname\">"._ICONMOVE._MOVE."</a>";

			echo "&nbsp;<a href=\"index.php?mod=".rawurlencode($mod)."&amp;group=".rawurlencode($group)."&amp;argument=".rawurlencode($argument)."&amp;ffaction=deleteargumentinterface\" title=\""._DELETEARGTITLE.": $argumentname\">"._ICONDELETE._ELIMINA."</a>";

			if (argument_is_locked($root,$group,$argument)){
				echo "&nbsp;<a href=\"index.php?mod=".rawurlencode($mod)."&amp;group=".rawurlencode($group)."&amp;argument=".rawurlencode($argument)."&amp;ffaction=unlockargument\" title=\""._UNLOCKARGTITLE.": $argumentname\">"._ICONUNLOCK._UNLOCK."</a>";
			}
			else {
				echo "&nbsp;<a href=\"index.php?mod=".rawurlencode($mod)."&amp;group=".rawurlencode($group)."&amp;argument=".rawurlencode($argument)."&amp;ffaction=lockargument\" title=\""._LOCKARGTITLE.": $argumentname\">"._ICONLOCK._LOCK."</a>";
			}

			echo "</td></tr>";
		}
	}

	echo "</table>";
}
/**
 * Visualizza il topic
 *
 * @param string $root la root del forum
 * @param string $group il gruppo di riferimento
 * @param string $argument l'argomento del topic
 * @param string $topic il file contenente il topic
 * @author Aldo Boccacci
 * @since 0.1
 */
function forum_view_topic($root,$group,$argument,$topic){
	if (!check_path($root,get_forum_root(),"false")) ff_die("forum root is not valid!",__FILE__,__LINE__);
	if (!check_path($group,"","false")) ff_die("forum group is not valid!",__FILE__,__LINE__);
	if (!check_path($argument,"","false")) ff_die("forum argument is not valid!",__FILE__,__LINE__);
	if (!check_path($topic,"","true")) ff_die("forum topic is not valid!",__FILE__,__LINE__);
	$topicpath="";
	$topicpath= "$root/$group/$argument/$topic";
	if (!check_path($topicpath,"","true")) ff_die("forum topicpath is not valid!",__FILE__,__LINE__);

	if (!is_file($topicpath)) {
		echo "Il percorso richiesto non e' un file: ".strip_tags($topicpath);
	}

	$page = getparam("page",PAR_GET,SAN_FLAT);
	if (!check_var($page,"digit") and trim($page)!=""and trim($page)!="last") ff_die("\$page is invalid! (".strip_tags($page).")",__FILE__,__LINE__);
	if ($page=="") $page="1";

	$mod = _FN_MOD;

	if (!user_can_view_argument(get_forum_root(),$group,$argument)){
		echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."\" >";
		die();
	}

	global $theme,$postperpage,$fuso_orario,$giorni,$mesi,$bgcolor2,$bgcolor3;

	update_topic_hits($topicpath);

	$topicdata = array();
	$topicdata = load_topic($topicpath);
	if (trim($page)=="last") $page=ceil(count($topicdata['posts'])/$postperpage);

	if (!is_forum_moderator() and $topicdata['properties']['hide']=="true"){
		ff_die("Only admins and moderators can view hidden topics!");
	}

	view_forum_header();

	//INTESTAZIONE
	echo "<table style=\"width:100%;border-collapse: collapse;border:0px;\"><tr><td width=\"33%\">";

	if (count($topicdata['posts'])>$postperpage){

		$pagescount = ceil(count($topicdata['posts'])/$postperpage);
		$link = "index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=".basename($topic);
		ff_page_selector($page,$pagescount,$link);
	}

	echo "</td><td width=\"34%\">";
	//al centro nulla
	echo "</td><td width=\"33%\">";
	if (!_FN_IS_GUEST){
		if (!is_forum_moderator() and topic_is_locked($topicpath)){ // non può rispondere
			echo "<div align=\"right\"><span title='"._TOPICLOCKED."'>"._ICONLOCK."</span>&nbsp;"._TOPICLOCKED."</div>";
		}
		else { // può rispondere
			echo "<div>";
			if (is_forum_moderator() and topic_is_locked($topicpath))
				echo "<span title='"._TOPICLOCKED."'>"._ICONLOCK."</span>&nbsp;";
			echo "<span class=\"forum-new\"><a style=\"font-size: 140%;\" href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic&amp;ffaction=newpost\" title=\""._FRISP."\">"._FRISP."</a>";
			echo "</span></div>";
		}
	}
	echo "</td></tr></table>";


	echo "<br><table  ";

	if (file_exists("themes/$theme/forum.css")){
		echo "class=\"forum-topic-table\" ";
	}
	else echo "style=\"width:100%;border-collapse: collapse;border:1px solid $bgcolor2;\" ";

	echo ">";



	echo "<tr><td ";

	if (file_exists("themes/$theme/forum.css")){
		echo " class=\"forum-topic-user-header\" ";
	}
	else echo "style=\"background-color : $bgcolor3;\" ";

	echo "width=\"120\" align=\"center\"><b>"._FUTENTE."</b></td><td ";

	if (file_exists("themes/$theme/forum.css")){
		echo " class=\"forum-topic-post-header\" ";
	}
	else echo "style=\"background-color : $bgcolor3;\" ";

	echo " align=\"center\"><b>"._FMESS."</b></td></tr>";

	$posts = $topicdata['posts'];
	$post=array();

	//POST ON TOP
	$postontop = "";
	$postontop = $topicdata['properties']['postontop'];
	if ($postontop!="" and isset($posts[$postontop])){
		view_post($posts[$postontop],$topicdata,$group,$argument,$topic,$postontop);
	}

	//x ogni messaggio
	$count=(($page-1) * $postperpage);
	$oldcount = $count;
	for ($count;$count <($postperpage+$oldcount); $count++){
		if (isset($posts[$count])) $post = $posts[$count];
		else continue;
		//mostro il post (a meno che non lo abbia già mostrato in cima)
		if ($postontop==$count) {
			if ($postontop!="") continue;
		}
		view_post($post,$topicdata,$group,$argument,$topic,$count);


	}

	echo "</table><br>";



	if (!_FN_IS_GUEST){
		//avviso via mail
		echo "<div style=\"text-align: center;\">";
		echo "<form action=\"index.php?mod=$mod&amp;ffaction=emailalertadd\" method=\"post\">";
			echo "<input type=\"hidden\" name=\"ffgroup\" readonly=\"readonly\" value=\"$group\" />
			<input type=\"hidden\" name=\"ffargument\" readonly=\"readonly\" value=\"$argument\" />
			<input type=\"hidden\" name=\"fftopic\" readonly=\"readonly\" value=\"$topic\" />";

		//se e' non in lista gli permetto di aggiungersi
		if (!in_array(_FN_USERNAME, $topicdata['properties']['emailalert'])){

			echo "<input type=\"hidden\" name=\"ffaction\" readonly=\"readonly\" value=\"alertuser\" />";
			echo _MAILALERT."&nbsp;";
		}
		else {
			echo "<input type=\"hidden\" name=\"ffaction\" readonly=\"readonly\" value=\"removealertuser\" />";
			echo _REMOVEMAILALERT."&nbsp;";


		}

		echo "<input type=\"submit\" value=\"OK\" />";
		echo "</form></div><br>";
	}

	//FONDO PAGINA
	echo "<table style=\"width:100%;border-collapse: collapse;border:0px;\"><tr><td width=\"33%\">";

	if (count($topicdata['posts'])>$postperpage){

		$pagescount = ceil(count($topicdata['posts'])/$postperpage);
		$link = "index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=".basename($topic);
		ff_page_selector($page,$pagescount,$link);
	}

	echo "</td><td width=\"34%\">";
	//al centro nulla
	echo "</td><td width=\"33%\">";
	if (!_FN_IS_GUEST){
		if (!is_forum_moderator() and topic_is_locked($topicpath)){ // non può rispondere
			echo "<div align=\"right\"><span title='"._TOPICLOCKED."'>"._ICONLOCK."</span>&nbsp;"._TOPICLOCKED."</div>";
		}
		else { // può rispondere
			echo "<div align=\"right\">";
			if (is_forum_moderator() and topic_is_locked($topicpath))
				echo "<span title='"._TOPICLOCKED."'>"._ICONLOCK."</span>&nbsp;";
			echo "<span class=\"forum-new\"><a style=\"font-size: 140%;\" href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic&amp;ffaction=newpost\" title=\""._FRISP."\">"._FRISP."</a>";
			echo "</span></div>";
		}
	}
	echo "</td></tr></table>";


}

/**
 * Visualizza il profilo dell'utente nella visualizzazione dei messaggi
 * @param string $user il nome dell'utente
 * @author Aldo Boccacci
 * @since 0.1
 */
function forum_view_user_profile($user){
	if (!is_alphanumeric($user))
		return "profilo non valido!";

	if (!file_exists(get_fn_dir("users")."/$user.php")){
	echo "<div style=\"align: center;\"><br><b>$user</b></div>";
	return;
	}

	global $theme;

	$userdata = array();
	$userdata = load_user_profile("$user");

// 	echo "<img src=\"forum/".$userdata['avatar']."\" alt='avatar' border='0' style='max-width:120px;' /><br />";
	$img = $userdata['avatar'];

	// save_user_profile imposta sermpre blank.png se non specificata...
	// 	if($img!="") { // ...quindi questo controllo è inutile
	if(!stristr($img,"http://"))
		echo "<img src='forum/$img' alt='$user' style='max-width:120px' />";
	else
		echo "<img src='$img' alt='$user' style='max-width:120px' />";
// 	}
// 	else echo "<img src='forum/images/blank.png' alt='$user' border='0' style='max-width:120px' />";

	echo "<br><b>$user</b><br><br>";

	// tabella per livello
	$level=$userdata['level'];
	if(!file_exists("themes/$theme/images/level_y.gif") OR !file_exists("themes/$theme/images/level_n.gif")) {
		$level_img_y = "images/useronline/level_y.gif";
		$level_img_n = "images/useronline/level_n.gif";
	} else {
		$level_img_y = "themes/$theme/images/level_y.gif";
		$level_img_n = "themes/$theme/images/level_n.gif";
	}

	echo "<div style='position:relative;float:left;width:30px;'>0</div>";
	echo "<div style='position:relative;float:right;width:30px;text-align:right;'>10</div>";
	echo "<div style='position:relative;margin-left:0px;margin-right:0px;text-align:center;'><b>"._LEVEL." $level</b></div>";
	echo "<div class=\"centeredDiv\">";
	echo "<hr size='1' noshade width='100%' />";
	for($i=0; $i<$level; $i++) {
		echo "<img align='middle' src='$level_img_y' alt='level' />";
	}
	for($j=$i; $j<10; $j++) {
		echo "<img align='middle' src='$level_img_n' alt='level' />";
	}
	echo "<hr>";

	echo "</div>";
	echo "<a href=\"index.php?mod=none_Login&amp;action=viewprofile&amp;user=$user\" title='"._VIEW_USERPROFILE."'>"._ICONPROFILE."</a>";

	if (!_FN_IS_GUEST AND trim($userdata['mail'])!=""){
		if ($userdata['hiddenmail']=="0" or _FN_IS_ADMIN) echo "&nbsp;<a href=\"mailto:".$userdata['mail']."\" title=\"manda una e-mail all'utente\">"._ICONMAIL."</a>";
	}

	if (trim($userdata['homepage'])!=""){
		echo "&nbsp;<a href=\"".$userdata['homepage']."\" target=blank title=\"home page dell'utente\">"._ICONHOME."</a>";
	}

	echo "<div style='align:center;border:0;'>\n";
	if (trim($userdata['jabber'])!=""){
		?><a href="xmpp:<?php echo $userdata['jabber']?>"><img src="images/useronline/im_jabber.png" alt="Jabber" title="Jabber" style='border:0' /></a><?php
	}
	if (trim($userdata['skype'])!=""){
		?>
		<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>
		<a href="skype:<?php echo $userdata['skype']?>?chat" onclick="return skypeCheck();"><img src="http://mystatus.skype.com/smallicon/<?php echo $userdata['skype']?>" alt="Skype" title="Skype" style='border:0' /></a><?php
	}
	if (trim($userdata['icq'])!=""){
		?>&nbsp;<a href="http://people.icq.com/people/cmd.php?uin=<?php echo $userdata['icq']?>&amp;action=message"><img src="http://status.icq.com/online.gif?icq=<?php echo $userdata['icq']?>&amp;img=26" alt="ICQ" title="ICQ" style='border:0' /></a><?php
	}
	if (trim($userdata['msn'])!=""){
		?>&nbsp;<a href="msnim:chat?contact=<?php echo $userdata['msn']?>"><img src="images/useronline/im_msn.png" alt="MSN" title="MSN" style='border:0' /></a><?php
	}
	echo "\n</div>\n";
}
/**
 * Mostra l'intestazione del forum con le opzioni generali.
 * @author Aldo Boccacci
 * @since 0.1
 */
function view_forum_header(){

	global $reguser,$forum_moderators,$bgcolor2,$bgcolor3,$theme;

	$root="";
	$root = get_forum_root();
	if (isset($_GET['group'])){
		$group = getparam("group",PAR_GET,SAN_FLAT);
		if (!check_path($group,"","false")) ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	}
	else $group="";

	if (isset($_GET['argument'])){
		$argument = getparam("argument",PAR_GET,SAN_FLAT);
		if (!check_path($argument,"","false")) ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);
	}
	else $argument="";

	if (isset($_GET['topic'])){
		$topic = getparam("topic",PAR_GET,SAN_FLAT);
		if (!check_path($topic,"","true")) ff_die("\$topic is not valid! (".strip_tags($topic).")",__FILE__,__LINE__);
	}
	else $topic="";

	$mod = _FN_MOD;

	$search_plugins_dir= "include/search/";
	$GLOBALS['search_plugins_dir'] =$search_plugins_dir;
	?><br/>
	<table
	<?php
	if (file_exists("themes/$theme/forum.css")){
		echo "class=\"forum-header-table\" ";
	}
	else echo "style=\"width:100%;border-collapse: collapse;border:1px solid $bgcolor2\" cellspacing=\"0\" ";
	?>
	>
	<tr><td class="forum-header-search" colspan="5" style="text-align:center">
		<script type="text/javascript">
	function validateforumsearch()
		{
			if(document.getElementById('findforum').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._CERCA?>');
					document.getElementById('findforum').focus();
					document.getElementById('findforum').value='';
					return false;
				}
			else return true;
		}
	</script>
	<form action="index.php?mod=none_Search" method="post" onsubmit="return validateforumsearch()">
	<input type="hidden" name="method" value="AND" />
	<input type="hidden" name="mod" value="none_Search" />
	<label for="findforum" ><?php echo _CERCA;?>:</label>
	<input type="text" name="find" id ="findforum"/>
	&nbsp;&nbsp;
	<label for="where"><?php echo _CERCASTR; ?></label>
	<select name="where" id="where">
	<option value="allsite" selected><?php echo _ALLSITE; ?></option>
	<?php
	global $search_plugins_dir;
	$plugin="";

	$plugins = glob("$search_plugins_dir/*.php");
	if (!$plugins) $plugins = array(); // glob may returns boolean false instead of an empty array on some systems

	foreach ($plugins as $plugin){
		$plugin_name ="";
		$plugin_name = preg_replace("/\.php$/i","",basename($plugin));
		if (preg_match("/^none_/i",$plugin_name)) continue;
		echo "<option value=\"$plugin_name\">".preg_replace("/^[0-9]*_/i","",$plugin_name)."</option>\n";
	}
	?>
	</select>
	&nbsp;&nbsp;
	<input type="radio" value="AND" id="AND" name="method" checked="checked" /><label for="AND">AND</label>
	<input type="radio" value="OR" id="OR" name="method" /><label for="OR">OR</label>
	&nbsp;&nbsp;<input type="submit" value="<?php echo _CERCA?>" />
	</form></td></tr>
	<!--funzioni per gli utenti -->
	<tr>
	<td class="forum-header-wellcome">
	<?php echo _BENVE;
	if (_FN_IS_GUEST) {
		echo " "._SCON;
	}
	else {
		echo " <b>"._FN_USERNAME."</b>";
	}
	echo "</td>";

	if ($reguser=="1" and _FN_IS_GUEST){
		echo "<td class=\"forum-header-edit-profile\"><a href=\"index.php?mod=none_Login&amp;action=visreg\" title=\""._REGORA."\">"._REGORA."</a></td>";
	}
	else {
		echo "<td class=\"forum-header-edit-profile\"><a href=\"index.php?mod=none_Login&amp;action=editprofile&amp;user="._FN_USERNAME."\" title=\"modifica il tuo profilo\">"._FMODPROF."</a></td>";
	}

	if (_FN_IS_GUEST){
		echo "<td class=\"forum-header-enter\"><b><a href=\"index.php?mod=none_Login\" title=\""._LOGIN."\">"._LOGIN."</a></b></td>";
	}

	else {
		echo "<td class=\"forum-header-enter\"><a href='index.php?mod=none_Login&amp;action=logout&amp;from=home' title=\""._LOGOUT."\"><b>"._LOGOUT."</b></a></td>";
	}

	$rules="";
	if (file_exists(get_forum_root()."/rules.php"))
		$rules = get_file(get_forum_root()."/rules.php");
	echo "<td class=\"forum-header-help\">";
	if (file_exists(get_forum_root()."/rules.php") and trim($rules)!=""){

		echo "<b><a href=\"index.php?mod=$mod&amp;ffaction=viewrules\" title=\"Visualizza il regolamento del Forum\">Regolamento</a></b>";
	}
	else {
		echo "<a href=\"#\" onClick=\"Helpwindow=window.open('forum/help.php','Help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=700,height=600,left=200,top=100')\" title=\""._FGUIDA."\">"._FGUIDA."</a>";
	}
	echo "</td>";

	//visualizzazione utenti
	echo "<td class=\"forum-header-members\">
	<a href=\"index.php?mod=none_Login&amp;action=viewmembers\" title=\"visualizza i profili degli utenti registrati\"><b>".count(list_users())."</b> "._FUTENTI."</a></td>";
	?>

	</tr>

	</table>


	<?php

	if ($group==""){
		$admins = list_admins();
		if ($admins!=0){
			echo "<br><b>"._ADMINS.": </b>";
			for ($countadmins=0;$countadmins<count($admins);$countadmins++){
				echo "<a href=\"index.php?mod=none_Login&amp;action=viewprofile&amp;user=".$admins[$countadmins]."\" title=\"visualizza il profilo dell'utente\">".$admins[$countadmins]."</a>";
				if ($countadmins!=(count($admins)-1)) echo ", ";
			}

		}

		$moderators = list_forum_moderators();
		if (count($moderators)>0){
			echo "<br><b>"._MODERATORS.":</b> ";
			for ($countmoderators=0;$countmoderators<count($moderators);$countmoderators++){
				echo "<a href=\"index.php?mod=none_Login&amp;action=viewprofile&amp;user=".$moderators[$countmoderators]."\" title=\"visualizza il profilo dell'utente\">".$moderators[$countmoderators]."</a>";
				if ($countmoderators!=(count($moderators)-1)) echo ", ";
			}
		}
	}

	if ($group=="") return;

	$topicdata = array();
	$topicdata = load_topic(get_forum_root()."/$group/$argument/$topic");

	echo "<b><br>"._BROWSE.":</b><br>";
	echo "[ <a href=\"index.php?mod=".rawurlencodepath($mod)."\" title=\""._VIEWFORUMHOME."\"><b>".preg_replace("/^[0-9]*_/i","",basename($mod))."</b></a>";
	if ($group!="")
		echo " / <a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."\" title=\""._VIEWGROUPTITLE.": ".str_replace("_"," ",preg_replace("/^[0-9]*_/i","",$group))."\"><b>".str_replace("_"," ",preg_replace("/^[0-9]*_/i","",$group))."</b></a> ";
	if ($argument!="" and $group!=""){
		if ($topic!="")
			echo "/ <a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."\" title=\""._VIEWARGUMENTTITLE.": ".str_replace("_"," ",preg_replace("/^[0-9]*_/i","",$argument))."\"><b>".str_replace("_"," ",preg_replace("/^[0-9]*_/i","",$argument))."</b></a>";
		else echo "/ ".str_replace("_"," ",preg_replace("/^[0-9]*_/i","",$argument));
	}

	if ($group!="" and $argument!="" and $topic!="")
		echo " / ".$topicdata['properties']['topictitle'];
	echo " ]<br><br>";
}

/**
 * Visualizza il post indicato
 *
 */
function view_post($post,$topicdata,$group,$argument,$topic,$count){
	if (!is_array($post)) fd_die("\$post is not an array!",__FILE__,__LINE__);
	if (!is_array($topicdata)) fd_die("\$topicdata is not an array!",__FILE__,__LINE__);
	$mod = _FN_MOD;

	global $mesi,$giorni,$fuso_orario,$theme;

	//stampo l'utente
		echo "<tr><td class=\"forum-topic-user\" align=\"center\" valign=\"top\"";

		if (!_FN_IS_GUEST) echo " rowspan=\"2\"";

		echo ">";
		forum_view_user_profile($post['poster']);
		echo "</td>";

		//stampo il messaggio
		echo "<td class=\"forum-topic-post\" valign=\"top\">";
		$postime = $post['time'];
		echo $giorni[date("w",$postime+(3600*$fuso_orario))];
		echo date(" d ",$postime+(3600*$fuso_orario));
		$tmp=date(" m",$postime+(3600*$fuso_orario));
		echo $mesi[$tmp-1];
		echo date(" Y ",$postime+(3600*$fuso_orario));
		echo date(" H:i:s ",$postime+(3600*$fuso_orario));
		echo "<br><br>";

		if ($post['lasteditposter']!="" and $post['lastedit']!=""){
			echo "<i>"._LASTEDITBY." <b>".$post['lasteditposter']."</b>  (";
			$postime = $post['lastedit'];
			echo $giorni[date("w",$postime+(3600*$fuso_orario))];
			echo date(" d ",$postime+(3600*$fuso_orario));
			$tmp=date(" m",$postime+(3600*$fuso_orario));
			echo $mesi[$tmp-1];
			echo date(" Y ",$postime+(3600*$fuso_orario));
			echo date(" H:i:s",$postime+(3600*$fuso_orario));
			echo ")</i><br><br>";
		}

		if ($topicdata['properties']['postontop']==$count)
			if ($topicdata['properties']['postontop']!="")
				echo _ICONONTOP."&nbsp;";
		echo "<b>".$post['postsubj']."</b><br><br>";

		$postbody = preg_replace("/&#91;/i","[",$post['postbody']);
		$postbody = preg_replace("/&#93;/i","]",$postbody);
		$postbody = preg_replace("/\n/i","<br>",$postbody);
		$postbody = preg_replace("/\r/i","",$postbody);
		echo tag2html($postbody);


		$userdata = array();
		if (file_exists(get_fn_dir("users")."/".$post['poster'].".php")){
			$userdata = load_user_profile($post['poster']);
			if (preg_replace("/^#/","",$userdata['sign'])!=""){
				echo "<br><br>--<br>";
				echo tag2html(preg_replace("/^#/","",$userdata['sign']));
			}
		}

		echo "</td></tr>";
		if (!_FN_IS_GUEST) echo "<tr><td class=\"forum-topic-post-options\">";



		if (!_FN_IS_GUEST){
			if (!is_forum_moderator() and $topicdata['properties']['locked']=="true") {
				echo "<span title='"._TOPICLOCKED."'>"._ICONLOCK."</span>&nbsp;"._TOPICLOCKED;
			}
			else echo "&nbsp;<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic&amp;ffaction=newpost&amp;quote=$count\" title=\""._QUOTEPOST."\">"._ICONQUOTE._QUOTE."</a>";
		}

		if (_FN_IS_ADMIN or (_FN_USERNAME==$post['poster']) or is_forum_moderator()){
			echo "&nbsp;<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic&amp;ffaction=editpost&amp;quote=$count\" title=\""._EDITPOST."\">"._ICONMODIFY._MODIFICA."</a>";
		}
		if (_FN_IS_ADMIN){
			echo "&nbsp;<a href=\"#\" onclick=\"check('index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic&amp;ffaction=deletepost&amp;number=$count')\" title=\""._DELETEPOST."\">"._ICONDELETE._ELIMINA."</a>";
		}
		if (is_forum_moderator()){
			if ($topicdata['properties']['postontop']=="" or $topicdata['properties']['postontop']!=$count)
				echo "&nbsp;<a href=\"#\" onclick=\"check('index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic&amp;ffaction=setpostontop&amp;number=$count')\" title=\""._STICKYPOST."\">"._ICONONTOP._STICKY."</a>";
			else
				echo "&nbsp;<a href=\"#\" onclick=\"check('index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic&amp;ffaction=removepostontop')\" title=\""._NORMALPOST."\">"._ICONNORMAL._NORMAL."</a>";

		}

		if (!_FN_IS_GUEST) echo "</td></tr>";
}

/**
 * se e' presente il file get_forum_root()/ffmotd.php
 * visualizza il testo contenuto nella pagina di riepilogo generale del forum
 */
function view_ffmotd(){
	if (!file_exists(get_forum_root()."/ffmotd.php")) return;

	$string = "";
	$string = get_file(get_forum_root()."/ffmotd.php");

	if (trim($string)=="") return;

	echo "<fieldset>
<legend>Forum</legend>$string</fieldset><br>";

}

/**
 * Funzione per mostrare l'intero thread della discussione nella pagina di risposta
 *
 * @param string $root la root del forum
 * @author Aldo Boccacci & Alfredo Cosco
 * @since 2.6.1
 */
function forum_view_topic_thread($root){
if (!check_path($root,get_forum_root(),"false")) ff_die("forum root is not valid!",__FILE__,__LINE__);

	global $bgcolor2,$bgcolor3;

	$group = getparam("group",PAR_GET,SAN_FLAT);
	if (!check_path($group,"","false")) ff_die("forum group is not valid!",__FILE__,__LINE__);

	$argument = getparam("argument",PAR_GET,SAN_FLAT);
	if (!check_path($argument,"","false")) ff_die("forum argument is not valid!",__FILE__,__LINE__);

	$topic = getparam("topic",PAR_GET,SAN_FLAT);
	if (!check_path($topic,"","true")) ff_die("forum topic is not valid!",__FILE__,__LINE__);

	$topicpath="";
	$topicpath= "$root/$group/$argument/$topic";
	if (!check_path($topicpath,"","true")) ff_die("forum topicpath is not valid!",__FILE__,__LINE__);

	if (!is_file($topicpath)) {
		echo "Il percorso richiesto non e' un file: ".strip_tags($topicpath);
	}

	$page = getparam("page",PAR_GET,SAN_FLAT);
	if (!check_var($page,"digit") and trim($page)!=""and trim($page)!="last") ff_die("\$page is invalid! (".strip_tags($page).")",__FILE__,__LINE__);
	if ($page=="") $page="1";

	$mod = _FN_MOD;

	if (!user_can_view_argument(get_forum_root(),$group,$argument)){
		echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."\" >";
		die();
	}

	global $theme,$postperpage,$fuso_orario,$giorni,$mesi,$bgcolor2,$bgcolor3;

	$topicdata = array();
	$topicdata = load_topic($topicpath);

	if (trim($page)=="last") $page=ceil(count($topicdata['posts'])/$postperpage);

	if (!is_forum_moderator() and $topicdata['properties']['hide']=="true"){
		ff_die("Only admins and moderators can view hidden topics!");
	}


	echo "<br><br>\n<b>Thread:</b><br><br><div style=\"overflow:auto;height:400px;width:100%;border:1px;text-align:left\">";
	$posts = $topicdata['posts'];
	$post=array();


	for ($count=count($posts); $count>0; $count--){
		if ($count==$topicdata['properties']['postontop']) continue;
		$post = $posts[$count-1];
		echo "<b>".$posts[$count-1]['poster']."</b> - ";
		//time
		$postime = $post['time'];
		echo $giorni[date("w",$postime+(3600*$fuso_orario))];
		echo date(" d ",$postime+(3600*$fuso_orario));
		$tmp=date(" m",$postime+(3600*$fuso_orario));
		echo $mesi[$tmp-1];
		echo date(" Y ",$postime+(3600*$fuso_orario));
		echo date(" H:i:s ",$postime+(3600*$fuso_orario));
		echo "<br>";
		if ($post['lasteditposter']!="" and $post['lastedit']!=""){
			echo "<i>Ultima modifica di <b>".$post['lasteditposter']."</b>  (";
			$postime = $post['lastedit'];
			echo $giorni[date("w",$postime+(3600*$fuso_orario))];
			echo date(" d ",$postime+(3600*$fuso_orario));
			$tmp=date(" m",$postime+(3600*$fuso_orario));
			echo $mesi[$tmp-1];
			echo date(" Y ",$postime+(3600*$fuso_orario));
			echo date(" H:i:s",$postime+(3600*$fuso_orario));
			echo ")</i><br>";
		}

		echo "<b>".tag2html($post['postsubj'])."</b><br>";

		echo tag2html($post['postbody'])."<br><hr><br>";

	}

	//POST ON TOP
	$postontop = "";
	$postontop = $topicdata['properties']['postontop'];
	if ($postontop!="" and isset($posts[$postontop])){
		$post = $posts[$postontop];
		echo _ICONONTOP;
		echo "&nbsp;<b>".$post['poster']."</b> - ";
		//time
		$postime = $post['time'];
		echo $giorni[date("w",$postime+(3600*$fuso_orario))];
		echo date(" d ",$postime+(3600*$fuso_orario));
		$tmp=date(" m",$postime+(3600*$fuso_orario));
		echo $mesi[$tmp-1];
		echo date(" Y ",$postime+(3600*$fuso_orario));
		echo date(" H:i:s ",$postime+(3600*$fuso_orario));
		echo "<br>";
		if ($post['lasteditposter']!="" and $post['lastedit']!=""){
			echo "<i>Ultima modifica di <b>".$post['lasteditposter']."</b>  (";
			$postime = $post['lastedit'];
			echo $giorni[date("w",$postime+(3600*$fuso_orario))];
			echo date(" d ",$postime+(3600*$fuso_orario));
			$tmp=date(" m",$postime+(3600*$fuso_orario));
			echo $mesi[$tmp-1];
			echo date(" Y ",$postime+(3600*$fuso_orario));
			echo date(" H:i:s",$postime+(3600*$fuso_orario));
			echo ")</i><br>";
		}

		echo "<b>".tag2html($post['postsubj'])."</b><br>";

		echo tag2html($post['postbody'])."<br><hr><br>";
	}

	echo "</div>";
}

?>
