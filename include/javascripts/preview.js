// declare variables
var dom = (document.getElementById) ? true : false;
var ns5 = ((navigator.userAgent.indexOf("Gecko")>-1) && dom) ? true: false;
var ie5 = ((navigator.userAgent.indexOf("MSIE")>-1) && dom) ? true : false;
var ns4 = (document.layers && !dom) ? true : false;
var ie4 = (document.all && !dom) ? true : false;
var nodyn = (!ns5 && !ns4 && !ie4 && !ie5) ? true : false;

// preview news
function news_preview() {
	myclose = "<div style='float:right;border:1px solid;background-color:#ffff00;padding:2px;'>";
	myclose += "<a href='javascript:;' style='text-decoration:none;' onclick='prevshow();'><b>X</b></a></div>";
	// title
	if (getElement("news_title").value != "") {
	    getElement("fnpreview").innerHTML = myclose + "<h2 style=\"border-bottom: 1px solid;\">" + getElement("news_title").value + "</h2>";
	} else {
		getElement("fnpreview").innerHTML = myclose + "&nbsp;"
	}
	// header
	if (getElement("news_header").value != "") {
		head = replace_tags(getElement("news_header").value);
		getElement("fnpreview").innerHTML += head + "<br><br>";
	} else {
		getElement("fnpreview").innerHTML += "&nbsp;<br><br>";
	}
	// body
	if (getElement("news_body").value != "") {
		body = replace_tags(getElement("news_body").value);
		getElement("fnpreview").innerHTML += body;
	} else {
		getElement("fnpreview").innerHTML += "&nbsp;";
	}
}

// preview forum topics
function forum_preview() {
	myclose = "<div style='float:right;border:1px solid;background-color:#ffff00;padding:2px;'>";
	myclose += "<a href='javascript:;' style='text-decoration:none;' onclick='prevshow();'><b>X</b></a></div>";
	// title
	if (getElement("ffsubj").value != "") {
	    getElement("fnpreview").innerHTML = myclose + "<b style=\"border-bottom: 1px solid;\">" + getElement("ffsubj").value + "</b><br><br>";
	} else {
		getElement("fnpreview").innerHTML = myclose + "&nbsp;"
	}
	
	// body
	if (getElement("ffbody").value != "") {
		body = replace_tags(getElement("ffbody").value);
		getElement("fnpreview").innerHTML += body;
	} else {
		getElement("fnpreview").innerHTML += "&nbsp;";
	}
}

/* ----------------
 * SHARED UTILITIES
 * ----------------
 */

// return element value
function getElement(x) { 
	return document.getElementById(x); 
}

// manage preview box position at window onload time
addLoadEvent(function(){
	tooltip = (ns4)? document.fnpreview.document: (ie4)? document.all['fnpreview']: (ie5||ns5)? document.getElementById('fnpreview'): null;
	if(tooltip) {
		tipcss = (ns4)? document.fnpreview: tooltip.style;
		if(dom && ns5) {
			tipcss.position = 'fixed';
		} else {
			tipcss.position = 'absolute';
		}
	}
});

// view or hide preview box
function prevshow(){
	tooltip = (ns4)? document.fnpreview.document: (ie4)? document.all['fnpreview']: (ie5||ns5)? document.getElementById('fnpreview'): null;
	tipcss = (ns4)? document.fnpreview: tooltip.style;
	if(tipcss.visibility=='visible'){
		tipcss.visibility = 'hidden';
	} else {
		tipcss.visibility = 'visible';
	}
}

// view or hide copyright box
function copyrightshow(){
	tooltip = (ns4)? document.fncopyright.document: (ie4)? document.all['fncopyright']: (ie5||ns5)? document.getElementById('fncopyright'): null;
	tipcss = (ns4)? document.fncopyright: tooltip.style;
	if(tipcss.visibility=='visible'){
		tipcss.visibility = 'hidden';
	} else {
		tipcss.visibility = 'visible';
	}
}

// view or hide div element
function ShowHideDiv(x){
	if(document.getElementById(x).style.display==''){
		document.getElementById(x).style.display='none';
	} else {
	document.getElementById(x).style.display='';
	}
}
