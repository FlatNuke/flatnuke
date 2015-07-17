<?php

/**
 * This module tries to recover files concerning
 * Flatnuke visitor statistics.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */

// security checks
if (preg_match("/stats_recover.php/i", $_SERVER['PHP_SELF'])) {
	header("Location: ../../index.php");
	die();
}

$var_dir = get_fn_dir("var");

// create main directory if it's not present
if (!file_exists("$var_dir/flatstat")) {
	if (!mkdir("$var_dir/flatstat", 0777)){
		exit();
	}
}

// create referers' file if it's not present
if (!file_exists("$var_dir/flatstat/referer.dat")) {
	@fnwrite("$var_dir/flatstat/referer.dat", "", "w");
}

// build array of years
$years_list = array();
$stats_dir  = opendir("$var_dir/flatstat");
while($file=readdir($stats_dir)) {
	if(get_file_extension($file)=="" AND !($file=="." OR $file=="..") AND $file!="CVS") {
		array_push($years_list, $file);
	}
}
closedir($stats_dir);
if(count($years_list)>0) {
	sort($years_list);
	//echo "<pre>";print_r($years_list);echo "</pre>";	//-> TEST
}

// check if current year is ok; if not present, create all the files needed
if(!in_array(date("Y"), $years_list)) {
	$cur_year  = date("Y");
	$init_year = "";
	if(mkdir("$var_dir/flatstat/$cur_year", 0777)) {
		for($month=1; $month<=12; $month++) {
			$init_month = "";
			$init_year .= "0|0\n";
			$daysInMonth = date("j", mktime(0,0,0,$i,0,$cur_year));
			for ($i=1; $i<=$daysInMonth; $i++) {
				$init_month .= "$i|0\n";
			}
			@fnwrite("$var_dir/flatstat/$cur_year/$month.php", "$init_month\n", "w", array("nonull"));
		}
		@fnwrite("$var_dir/flatstat/$cur_year/generale.php", "$init_year\n", "w", array("nonull"));
	}
}

// re-build statistics for every single year
foreach($years_list as $year) {
	$rebuild_month = "";
	for($month=1; $month<=12; $month++) {
		// create month file if it does not exists
		if(!file_exists("$var_dir/flatstat/$year/$month.php")) {
			$init = "";
			$daysInMonth = date("j", mktime(0,0,0,$i,0,$year));
			for ($i=1; $i<=$daysInMonth; $i++) {
				$init .= "$i|0\n";
			}
			@fnwrite("$var_dir/flatstat/$year/$month.php", "$init\n", "w", array("nonull"));
		} else {
			// re-build year's stats
			$file_month = file("$var_dir/flatstat/$year/$month.php");
			$tot_month  = 0;
			for($i=0; $i<count($file_month); $i++) {
				if (trim($file_month[$i])!="") {
					$string = explode("|", $file_month[$i]);
					$tot_month += "$string[1]";
				}
			}
			$rebuild_month .= "$month|$tot_month\n";
			@fnwrite("$var_dir/flatstat/$year/generale.php", $rebuild_month, "w+", array("nonull"));
		}
	}
}

// re-build general statistics for the main site
$tot_site = 0;
foreach($years_list as $year) {
	if(file_exists("$var_dir/flatstat/$year/generale.php")) {
		$file_year = file("$var_dir/flatstat/$year/generale.php");
		$tot_years = 0;
		for($i=0; $i<count($file_year); $i++) {
			if (trim($file_year[$i])!="") {
				$string = explode("|", $file_year[$i]);
				$tot_years += "$string[1]";
			}
		}
		$tot_site += $tot_years;
	}
}
@fnwrite("$var_dir/flatstat/totale.php", $tot_site, "w+", array("nonull"));

?>
