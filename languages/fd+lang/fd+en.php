<?php
/**
/* FD+:English Translation
/*
/* English language file for FD+0.7
/*
/* @author Aldo Boccacci <zorba_@tin.it> Author
/* @author Bjoern Splinter <Insites@gmail.com> Corrections
*/


//User interface
define("_FDNAME","Name");
define("_FDSIZE","Size");
define("_FDDATE","Date");
define("_FDVERSION", "Version");
define("_FDDESC","Description");
define("_FDHITS","Hits");
define("_FDDOWNLOAD","DOWNLOAD!");
define("_FDSTAT","Download Statistics");
define("_FDSTATTITLE","View download statistics");
define("_FDDOWNLOADFILE","Download the file: ");
define("_FDARCHIVEDIR","Archive dir");
/*define("_GOTOSECTION","Go to the section");*/
define("_FDUPLOADER","Uploaded by");
define("_FDUPLOADERTITLE","View user&#8217;s profile");
define("_FDSUMMARY","Summary");
define("_FDSUBDIRS","Subsections");
define("_FDRATING","Rating");
define("_FDRATEFILE","Rate this file");
define("_FDALREADYRATED","You have already rated this file!");
define("_FDVOTES","votes");
define("_FDTOVOTEAFILE","to vote a file");
define("_ENABLEVOTE","Enable vote");
//Date format
define("_FDDATEFORMAT","m/d/Y H:i:s");

//gpg sign
define("_FDGPGSIGN","gpg signature");
define("_FDGPGSIGNTITLE","Download the gpg signature of the file: ");
define("_FDDELETESIG","Delete signature");

//screenshot
define("_FDDELETESCREEN","Delete screenshot");
define("_FDSCREENSHOT","Screenshot of the file: ");

//Admin
define("_FDARCHIVE","Archive");
define("_FDRESTORE","Restore");
define("_FDEDIT","Edit");
define("_FDDELETE","Delete");
define("_FDHIDE","Hide");
define("_FDSHOW","Show");
define("_FDRENAME","Rename");
define("_FDPUBLISH","Publish");
define("_FDADDFILE","Add file");

define("_FDARCHIVETITLE","Archive the file: ");
define("_FDRESTORETITLE","Restore the file: ");
define("_FDEDITTITLE","Edit title for the file: ");
define("_FDDELETETITLE","Delete the file: ");
define("_FDHIDETITLE","Hide the file: ");
define("_FDSHOWTITLE","Make visible the file: ");
define("_FDRENAMETITLE","Rename the file: ");
define("_FDADDFILETITLE","Add file in this section.");
define("_FADMINPANEL","Admin panel");
define("_FDALLOWUSERUPLOAD","Allow users to propose files in this section");
define("_FDDONOTALLOWUSERUPLOAD","Don&#8217;t allow users to propose files in this section");
define("_FDUSERUPLOADPERMSREMOVED","User now cannot propose files in the section");
define("_FDUSERUPLOADPERMSADDED","User now can propose files in the section");
define("_FDBACK","Back");

//Edit/Add file
define("_FDRETURN","Return");
define("_FDEDITDESC","Edit the description of the file: ");
define("_FDEDITHEADER","In this page you can edit the description of the file managed by FlatDownloadPlus. Changes will be saved in the file: ");
define("_FDEDITFOOTER","You can also change the file itself.<br>The new file may have a different name. If the new name is different, fd+ will change the correspondent \".description\".<br> The old file will be deleted.");
define("_FDEDITSAVE", "Save changes");
define("_FDEDITDONE", "Changes applied");
define("_FDSHOWINBLOCKS","Show file in statistics blocks");
define("_FDBLOCKS","Blocks");
define("_FDUSERLABEL","Label");
define("_FDUSERVALUE","Value");
define("_FDUSERBOTH","Both required");
define("_FDBASIC","Basic");
define("_FDADVANCED","Advanced");

//Rename File
define("_FDRENAMECHOOSE","Choose a new name: ");
define("_FDRENAMEEXTLIMIT","It isn&#8217;t possible to change the extension of the file.");
define("_FDRENAMEFILE","The file ");
define("_FDRENAMENOFD","isn&#8217;t a FD+&#8217;s file.");
define("_FDRENAMEOK","File succesfully renamed.");
define("_FDRENAMENOTCHANGED","File name not changed.");
define("_FDRENAMEEXISTS1","Already exist a file called");
define("_FDRENAMEEXISTS2","in the dir");
define("_FDRENAMECHANGENAME","Choose another name.");
define("_FDRENAMEALERT","<b>Attention!</b> I cannot rename the file");

//Delete file
define("_FDDELSURE","Are you sure you want to delete the file: ");
define("_FDDEL", "Delete");
define("_FDCANC", "Cancel");
define("_FDDELOK","Files deleted: ");
define("_FDAND","and");

//Archive file
define("_FDARCHIVEOK","File archived.");
define("_FDARCHIVEGO","Go to the archive directory.");
define("_FDRESTOREOK","The file was successfully restored");
define("_FDARCHIVERETURN","return to the previous section");
define("_GOTOARCHIVEDIR","Go to the archive section");

//Add file
define("_FDADDWHERE","Add new file in the directory: ");
define("_FDADDHEADER","Here you can add another file To the current folder");
define("_FDADDFOOTER","Choose a file on your hard-disk.");
define("_NOTVALIDEXT","The extension of this file is not suported by FD+. Add this extension in the variable &#036;extensions in the file \"fd+.php\"");

//save file
define("_FDSAVESIZE","<b>Attention!</b> filesize is ");

//move file
define("_FDMOVE","Move");
define("_FDMOVEFILE","Move the file ");
define("_FDMOVEFILESECTION"," to the section");
define("_FDMOVELINK","(click on the link for to move)");
define("_FDMOVECONFIRM","Move the file ");
define("_FDMOVEFAIL","I was not able to move the file ");
define("_FDMOVESUCCESS","succesfully moved");
define("_FDMOVETITLE","Move the file: ");
define("_FDDIR","The dir");


//creazione cartelle
define("_FDCREATESECTDOWNLOAD","Create Download section");
define("_FDCREATESECT","Create section");
define("_FDCREATESECTOK","succesfully created!");
define("_FDCREATEONLYDIR","I created the section, but I wasn&#8217;t able to create the file section.php");
define("_FDCREATEDIRERROR","<b>Attention!</b>: I cannot create the section: ");
define("_FDCHECKPERM","Check write permissions.");
define("_FDHIDDENSECT","hidden section");
define("_FDCHOOSESECTNAME","Choose the section&#8217;s name:");
define("_FDCREATESUBSECT","Create a section in: ");
define("_FDNOTWRITE","is not writeable!");


//upload file
define("_FDTOOBIG","<b>Attention!</b><br>The file will not be uploaded on the server because is too big.");
define("_FDUPLOADOK","File successfully uploaded.");
define("_FDUPLOADEXISTS","<b>Attention!</b> In the selected directory already esists a file with the same name. <br> If you want to modify this file or the related desctiption, use the function <b>Edit</b>.");
define("_FDFILENOTSELECT","Select a file!");

//Alert
define("_FDDESCTOOLONG","<b>Attention!</b> the description is too long.");
define("_FDNAMETOOLONG","<b>Attention!</b> the title is too long.");
define("_FDREADONLYDIR","<b>Attention:</b> check write permission of this directory.");
define("_FDERROR","<B>Error!</B>");

//error
define("_FDNONPUOI","<b>You may not do that!</b> (Security error.)<br>");
define("_FDERROR1","The uploaded file exceeds the upload_max_filesize directive in php.ini.");
define("_FDERROR2","The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.");
define("_FDERROR3","The uploaded file was only partially uploaded.");
define("_FDERROR4","No file was uploaded.");
define("_FDERROR6","Missing a temporary folder.");
define("_FDERROR7","Failed to write file to disk.");

//DOWNLOAD STATS
define("_FDSECT","Section");
define("_FDTOPSTATS", "Download&nbsp;Statistics");
//TOP DOWNLOAD
define("_FDTOPSTAT","Statistics");

//remote file/url
define("_FDADDURL","Add remote file");
define("_FDCHOOSEURL","Specify the url of the file:");

//FDuser
define("_FDWAITFORADMIN","The file will not be immediately visible. Wait for site&#8217;s administrator approval.");
define("_FDLIMIT","Raggiunto il limite massimo di file proponibili.<br> Attendi che l&#8217;amministratore pubblichi i file in attesa di approvazione."); //need translation
define("_FDPUBLISHFILES","Publish files");
define("_FDPROPOSE","Propose a file");
define("_FDWAITINGFILES","files waiting validation");

?>