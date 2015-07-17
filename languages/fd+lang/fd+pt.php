<?php
/**
 * FD+:Portuguese Translation
 * @author Paulo Silva <correasilva_at_tugamail_dot_pt>
 */

//User interface
define("_FDNAME","Nome");
define("_FDSIZE","Tamanho");
define("_FDDATE","Date");
define("_FDVERSION", "Vers&atilde;o");
define("_FDDESC","Descri&ccedilla;&atilde;o");
define("_FDHITS","Hits");
define("_FDDOWNLOAD","DOWNLOAD!");
define("_FDSTAT","Descarregar Estat&iacute;sticas");
define("_FDSTATTITLE","Ver estat&iacute;sticas de downloads");
define("_FDDOWNLOADFILE","Descarregar o ficheiro: ");
define("_FDARCHIVEDIR","Direct&oacute;rio de arquivo");
/*define("_GOTOSECTION","Go to the section");*/
define("_FDUPLOADER","Enviado por");
define("_FDUPLOADERTITLE","Ver perfil do utilizador");
define("_FDSUMMARY","Summary");//need translation
define("_FDSUBDIRS","Subsections");//need translation
define("_FDRATING","Rating");//need translation
define("_FDRATEFILE","Rate this file");//need translation
define("_FDALREADYRATED","You have already rated this file!");//need translation
define("_FDVOTES","votes");//need translation
define("_FDTOVOTEAFILE","to vote a file");//need translation
define("_ENABLEVOTE","Enable vote");//need translation
//Date format
define("_FDDATEFORMAT","m/d/Y H:i:s");

//gpg sign
define("_FDGPGSIGN","assinatura gpg");
define("_FDGPGSIGNTITLE","Descarregar assinatura gpg do ficheiro: ");
define("_FDDELETESIG","Apagar assinatura");

//Admin
define("_FDARCHIVE","Arquivo");
define("_FDRESTORE","Recuperar");
define("_FDEDIT","Editar");
define("_FDDELETE","Apagar");
define("_FDHIDE","Esconder");
define("_FDSHOW","Mostrar");
define("_FDRENAME","Renomear");
define("_FDADDFILE","Adiconar ficheiro");
define("_FDARCHIVETITLE","Arquivar o ficheiro: ");
define("_FDRESTORETITLE","Recuperar o ficheiro: ");
define("_FDEDITTITLE","Editar o t&iacute;tulo para o ficheiro: ");
define("_FDDELETETITLE","Apagar o ficheiro: ");
define("_FDHIDETITLE","Esconder o ficheiro: ");
define("_FDSHOWTITLE","Fazer vis&iacute;vel o ficheiro: ");
define("_FDRENAMETITLE","Renomear o ficheiro: ");
define("_FDADDFILETITLE","Adicionar ficheiro nesta sec&ccedilla;&atilde;o.");
define("_FADMINPANEL","Admin panel");//need translation
define("_FDALLOWUSERUPLOAD","Allow users to propose files in this section");//need translation
define("_FDDONOTALLOWUSERUPLOAD","Don&#8217;t allow users to propose files in this section");//need translation
define("_FDUSERUPLOADPERMSREMOVED","User now cannot upload files in the section");//need translation
define("_FDUSERUPLOADPERMSADDED","User now can propose files in the section");//need translation
define("_FDBACK","Tr&aacute;s");

//Edit/Add file
define("_FDRETURN","Retornar");
define("_FDEDITDESC","Editar descri&ccedilla;&atilde;o do ficheiro: ");
define("_FDEDITHEADER","Nesta p&aacute;gina pode editar a descri&ccedilla;&atilde;o do ficheiro gerido por FlatDownloadPlus. As mudan&ccedilla;as ser&atilde;o gravadas no ficheiro: ");
define("_FDEDITFOOTER","Pode tamb&eacute;m mudar o ficheiro em si.<br>O novo ficheiro pode ter um nome diferente. Se o novo nome &eacute; diferente, fd+ mudar&aacute; a correspondente \".descri&ccedilla;&atilde;o\".<br> O ficheiro antigo ser&aacute; apagado.");
define("_FDEDITSAVE", "Gravar mudan&ccedilla;as");
define("_FDEDITDONE", "Mudan&ccedilla;as aplicadas");
define("_FDBASIC","Basic");//need translation
define("_FDADVANCED","Advanced");//need translation


//Rename File
define("_FDRENAMECHOOSE","Escolher um novo nome: ");
define("_FDRENAMEEXTLIMIT","N&atilde;o &eacute; poss&iacute;vel editar a extens&atilde;o do ficheiro.");
define("_FDRENAMEFILE","O ficheiro ");
define("_FDRENAMENOFD","n&atilde;o &eacute; um ficheiro FD+.");
define("_FDRENAMEOK","Ficheiro renomeado com sucesso.");
define("_FDRENAMENOTCHANGED","Nome do ficheiro n&atilde;o foi mudado.");
define("_FDRENAMEEXISTS1","J&aacute; existe ficheiro chamado");
define("_FDRENAMEEXISTS2","no direct&oacute;rio");
define("_FDRENAMECHANGENAME","Escolhe outro nome.");
define("_FDRENAMEALERT","<b>Aten&ccedilla;&atilde;o!</b> O ficheiro n&atilde;o p&ocirc;de ser renomeado");

//Delete file
define("_FDDELSURE","Tem certeza que quer apagar o ficheiro: ");
define("_FDDEL", "Apagar");
define("_FDCANC", "Cancelar");
define("_FDDELOK","Ficheiros apagados: ");
define("_FDAND","and");

//Archive file
define("_FDARCHIVEOK","Ficheiro arquivado.");
define("_FDARCHIVEGO","Ir para o direct&oacute;rio de arquivo.");
define("_FDRESTOREOK","O ficheiro foi recuperado com sucesso");
define("_FDARCHIVERETURN","retornar para a sec&ccedilla;&atilde;o anterior");
define("_GOTOARCHIVEDIR","Ir para a sec&ccedilla;&atilde;o de arquivo");

//Add file
define("_FDADDWHERE","Adicionar novo ficheiro na directoria: ");
define("_FDADDHEADER","Aqui pode adicionar outro ficheiro para a pasta corrente");
define("_FDADDFOOTER","Escolhe um ficheiro no seu disco rigido.");
define("_NOTVALIDEXT","A extens&atilde;o deste ficheiro n&atilde;o &eacute; suportada por FD+. Adicionar esta extens&atilde;o na vari&aacute;vel &#036;extens&otilde;es do ficheiro \"fd+.php\"");

//save file
define("_FDSAVESIZE","<b>Aten&ccedilla;&atilde;o!</b> O tamanho do ficheiro &eacute; ");

//move file
define("_FDMOVE","Mover");
define("_FDMOVEFILE","Mover o ficheiro ");
define("_FDMOVEFILESECTION"," para a sec&ccedilla;&atilde;o");
define("_FDMOVELINK","(clicar no link para mover)");
define("_FDMOVECONFIRM","Mover o ficheiro ");
define("_FDMOVEFAIL","N&atilde;o &eacute; poss&iacute;vel mover o ficheiro ");
define("_FDMOVESUCCESS","movido com sucesso");
define("_FDMOVETITLE","Mover o ficheiro: ");
define("_FDDIR","O direct&oacute;rio");

//creazione cartelle
define("_FDCREATESECTDOWNLOAD","Sec&ccedilla;&atilde;o de Download criada");
define("_FDCREATESECT","Criar sec&ccedilla;&atilde;o");
define("_FDCREATESECTOK","criado com sucesso!");
define("_FDCREATEONLYDIR","Sec&ccedilla;&atilde;o criada, mas n&atilde;o foi possivel criar o ficheiro section.php");
define("_FDCREATEDIRERROR","<b>Aten&ccedilla;&atilde;o!</b>: N&atilde;o p&ocirc;de ser criada a sec&ccedilla;&atilde;o: ");
define("_FDCHECKPERM","Verificar permiss&otilde;es de escrita.");
define("_FDHIDDENSECT","esconder sec&ccedilla;&atilde;o");
define("_FDCHOOSESECTNAME","Escolher o nome da sec&ccedilla;&atilde;o:");
define("_FDCREATESUBSECT","Criar uma sec&ccedilla;&atilde;o em: ");
define("_FDNOTWRITE","is not writeable!");

//upload file
define("_FDTOOBIG","<b>Aten&ccedilla;&atilde;o!</b><br>Este ficheiro n&atilde;o pode ser enviado para o servidor por ser muito grande.");
define("_FDUPLOADOK","Ficheiro enviado com sucesso.");
define("_FDUPLOADEXISTS","<b>Aten&ccedilla;&atilde;o!</b> Na directoria seleccionada esiste um ficheiro com o mesmo nome. <br> Se quer modificar este ficheiro ou a descri&ccedilla;&atilde;o relacionada, usa a fun&ccedilla;&atilde;o <b>Editar</b>.");
define("_FDFILENOTSELECT","Seleccione um ficheiro!");

//Alert
define("_FDDESCTOOLONG","<b>Aten&ccedilla;&atilde;o!</b> a descri&ccedilla;&atilde;o &eacute; muito longa.");
define("_FDNAMETOOLONG","<b>Aten&ccedilla;&atilde;o!</b> o t&iacute;tulo &eacute; muito longo.");
define("_FDREADONLYDIR","<b>Aten&ccedilla;&atilde;o:</b> verificar permiss&atilde;o de escrita nesta directoria.");
define("_FDERROR","<B>Erro!</B>");

//error
define("_FDNONPUOI","<b>You may not do that!</b> (erro de seguran&ccedilla;a.)<br> FDPlus: linha ");
define("_FDERROR1","O ficheiro enviado excede o upload_max_filesize directive in php.ini.");
define("_FDERROR2","O ficheiro enviado excede o MAX_FILE_SIZE directive que foi especificado no formul&aacute;rio HTML.");
define("_FDERROR3","O ficheiro enviado o foi apenas parcialmente.");
define("_FDERROR4","Nenhum ficheiro foi enviado.");
define("_FDERROR6","Falta pasta tempor&aacute;ria.");
define("_FDERROR7","A escrita do ficheiro no disco falhou.");

//DOWNLOAD STATS
define("_FDSECT","Section");
define("_FDTOPSTATS", "Descarregar&nbsp;Estat&iacute;sticas");
//TOP DOWNLOAD
define("_FDTOPSTAT","Estat&iacute;sticas");


//remote file/url
define("_FDADDURL","Add remote file");
define("_FDCHOOSEURL","Specify the url of the file:");

//FDuser
define("_FDWAITFORADMIN","The file will not be immediately visible. Wait for site&#8217;s administrator approval.");//need translation
define("_FDLIMIT","Raggiunto il limite massimo di file proponibili.<br> Attendi che l&#8217;amministratore pubblichi i file in attesa di approvazione."); //need translation
define("_FDPUBLISHFILES","Publish files");//need translation
define("_FDPROPOSE","Propose a file");//need translation
define("_FDWAITINGFILES","files waiting validation");//need translation

?>