
/*
 *
 * jQueryFNcall v.20130302
 * by Marco Segato - http://marcosegato.altervista.org
 *
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 *
 *
 * Table of Contents
 * =================
 * Help
 *
 * @@ parameters :
 * urltocall  = name of your cgi or other
 * method     = GET or POST, default is GET
 * target     = name of your dynamic target DIV or other
 * paramform  = name of your dynamic source FORM in which POST parameters are defined
 *
 * @@ usage :
 * <a href="javascript:jQueryFNcall('urltocall','GET','target');">my_link</a>
 * <form id="formName" action="javascript:jQueryFNcall('urltocall', 'POST', 'target', 'paramform');">
 *
 */

function jQueryFNcall(urltocall, method, target, paramform) {
	$.ajax({
		// URL to call
		url  : urltocall,
		// call method, GET or POST (default is GET)
		type : method,
		// call parameters
		data : $('#'+paramform).serialize(),
		// display a warning while loading the results
		beforeSend: function() {
			$('#'+target).html("<img src=\"images/loading.gif\" alt=\"Loading...\" />");
		},
		// display final results
		success:function(data) {
			$('#'+target).html(data);
			if(method=='post'){
				$('html, body').animate({scrollTop: ($('body').offset().top)},500);
				$('#form-success').fadeIn(700).delay(300).fadeOut(700);
				}
		},
		// display a warning when some error occurs
		error: function(data) {
			$('#'+target).html("<strong>Error loading the page...</strong>");
			
		},
		//cache: false
	});
}

