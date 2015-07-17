
function replace_tags(x) {
	// emoticons
	x = x.replace(/\[:\)\]/g, "<img src='forum/emoticon/01.png' alt=':)' />");
	x = x.replace(/\[:\(\]/g, "<img src='forum/emoticon/02.png' alt=':(' />");
	x = x.replace(/\[:o\]/g, "<img src='forum/emoticon/03.png' alt=':o' />");
	x = x.replace(/\[:p\]/g, "<img src='forum/emoticon/04.png' alt=':p' />");
	x = x.replace(/\[:D\]/g, "<img src='forum/emoticon/05.png' alt=':D' />");
	x = x.replace(/\[:\!\]/g, "<img src='forum/emoticon/06.png' alt=':!' />");
	x = x.replace(/\[:O\]/g, "<img src='forum/emoticon/07.png' alt=':O' />");
	x = x.replace(/\[8\)\]/g, "<img src='forum/emoticon/08.png' alt='8)' />");
	x = x.replace(/\[;\)\]/g, "<img src='forum/emoticon/09.png' alt=';)' />");
	x = x.replace(/\[rolleyes\]/g, "<img src='forum/emoticon/rolleyes.png' alt=':rolleyes:' />");
	x = x.replace(/\[neutral\]/g, "<img src='forum/emoticon/neutral.png' alt=':|' />");
	x = x.replace(/\[:x\]/g, "<img src='forum/emoticon/mad.png' alt=':x' />");
	x = x.replace(/\[O:\)\]/g, "<img src='forum/emoticon/angel.png' alt='O:)' />");
	x = x.replace(/\[whistle\]/g, "<img src='forum/emoticon/whistle.png' alt='whistle' />");
	x = x.replace(/\[eh\]/g, "<img src='forum/emoticon/eh.png' alt='whistle' />");
	x = x.replace(/\[evil\]/g, "<img src='forum/emoticon/evil.png' alt=':evil:' />");
	x = x.replace(/\[idea\]/g, "<img src='forum/emoticon/idea.png' alt=':idea:' />");
	x = x.replace(/\[bier\]/g, "<img src='forum/emoticon/bier.png' alt=':bier:' />");
	x = x.replace(/\[flower\]/g, "<img src='forum/emoticon/flower.png' alt=':flower:' />");
	x = x.replace(/\[sboing\]/g, "<img src='forum/emoticon/sboing.png' alt=':sboing:' />");
	// formatting
 	x = x.replace(/(\n|\r)/g,"<br>");
	x = x.replace(/\[b\]/g, "<b>");
	x = x.replace(/\[\/b\]/g, "</b>");
	x = x.replace(/\[u\]/g, "<u>");
	x = x.replace(/\[\/u\]/g, "</u>");
	x = x.replace(/\[i\]/g, "<i>");
	x = x.replace(/\[\/i\]/g, "</i>");
	x = x.replace(/\[strike\]/g, "<span style='text-decoration:line-through;'>");
	x = x.replace(/\[\/strike\]/g, "</span>");
	x = x.replace(/\[quote\=(.+?)\](.+?)\[\/quote\]/g, "<blockquote><b>$1 wrote:</b><br>$2</blockquote>");
	x = x.replace(/\[\quote\]/g, "<blockquote>");
	x = x.replace(/\[\/quote\]/g, "</blockquote>");
	x = x.replace(/\[code\]/g, "<pre>");
	x = x.replace(/\[\/code\]/g, "</pre>");
	x = x.replace(/\[url[=]?(.*)\](.+)\[\/url\]/g, "<a title='$2' href='$1' target='blank_'>$2</a>");
	x = x.replace(/\[mail\](.+?)\[\/mail\]/g, "<a title='mail to $1' href='mailto:$1'>$1</a>");
	//x = x.replace(/\[img\](.+)\[\/img\]/g, "<img alt='$1' src='$1'>"); // NEVER activate it due to big security problems with fake images
	x = x.replace(/\[img\](.+)\[\/img\]/g, "--- IMAGE ---");
	// positioning
	x = x.replace(/\[left\]/g, "<div style='text-align:left;'>");
	x = x.replace(/\[\/left\]/g, "</div>");
	x = x.replace(/\[right\]/g, "<div style='text-align:right;'>");
	x = x.replace(/\[\/right\]/g, "</div>");
	x = x.replace(/\[center\]/g, "<div style='text-align:center;'>");
	x = x.replace(/\[\/center\]/g, "</div>");
	x = x.replace(/\[justify\]/g, "<div style='text-align:justify;'>");
	x = x.replace(/\[\/justify\]/g, "</div>");
	// colors
	x = x.replace(/\[red\]/g, "<span style='color:#ff0000'>");
	x = x.replace(/\[\/red\]/g, "</span>");
	x = x.replace(/\[green\]/g, "<span style='color:#00ff00'>");
	x = x.replace(/\[\/green\]/g, "</span>");
	x = x.replace(/\[blue\]/g, "<span style='color:#0000ff'>");
	x = x.replace(/\[\/blue\]/g, "</span>");
	x = x.replace(/\[pink\]/g, "<span style='color:#ff00ff'>");
	x = x.replace(/\[\/pink\]/g, "</span>");
	x = x.replace(/\[yellow\]/g, "<span style='color:#ffff00'>");
	x = x.replace(/\[\/yellow\]/g, "</span>");
	x = x.replace(/\[cyan\]/g, "<span style='color:#00ffff'>");
	x = x.replace(/\[\/cyan\]/g, "</span>");
	// dimensions
	x = x.replace(/\[size=50%\]/g, "<span style='font-size:50%;'>");
	x = x.replace(/\[size=75%\]/g, "<span style='font-size:75%;'>");
	x = x.replace(/\[size=100%\]/g, "<span style='font-size:100%;'>");
	x = x.replace(/\[size=150%\]/g, "<span style='font-size:150%;'>");
	x = x.replace(/\[size=200%\]/g, "<span style='font-size:200%;'>");
	x = x.replace(/\[\/size\]/g, "</span>");
	
	// return new string
	return x;
}


function insertTags(tag1, tag2, area) {
	var ie = ((navigator.userAgent.indexOf("MSIE")>-1)) ? true : false;
	var txta = document.getElementsByName(area)[0];
	txta.focus();
	if (document.selection) {
		if (ie){
			txta.value = txta.value + tag1 + tag2;
		}
		else {
		var sel  = document.selection.createRange();
		sel.text = tag2
			? tag1 + sel.text + tag2
			: tag1;
		}
	}
	else if (txta.selectionStart != undefined) {
		var before = txta.value.substring(0, txta.selectionStart);
		var sel    = txta.value.substring(txta.selectionStart, txta.selectionEnd);
		var after  = txta.value.substring(txta.selectionEnd, txta.textLength);
		txta.value = tag2
			? before + tag1 + sel + tag2 + after
			: before + "" + tag1 + "" + after;
	}
}

function insertTag(tag1, area) {
	var ie = ((navigator.userAgent.indexOf("MSIE")>-1)) ? true : false;
	var txta = document.getElementsByName(area)[0];
	txta.focus();
	if (txta.value=="") {
		txta.value = tag1;
	}
	else {
		txta.value = txta.value + ", " + tag1;
	}
}
