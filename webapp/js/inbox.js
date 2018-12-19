function axajaza_deletes() {
	if ($('messages') == null) return;
	as = $('messages').getElementsByTagName('a');
	for (var i=0;i<as.length;i++) {
		if (as[i].href.indexOf('deletemessage.php') > 0) {
			_not_this_addEvent(as[i],'click',function(e){delete_message(e);} );
		}
	}
}
axajaza_deletes();
function delete_message(e) {
	obj = get_object_from_event(e);
	_stop_e_Propagation(e); //Prevent clicking
	href = obj.parentNode.href;
	var re_getidfrom = new RegExp('id=(\\d+)&type=(\\w{2,3})');
	if ( (rez = re_getidfrom.exec(href)) != null ) {
		var sendstr = 'aj=1&id='+rez[1]+'&type='+rez[2];
		send_get_data('./deletemessage.php',sendstr,function(a,b,c){delete_message_answer(a,b,c);});
	}
    $j(obj).parents("table.pmMessage").remove();
}

function delete_message_answer(data, statusCode, statusMessage) {
	if (data != 1) {
		alert('A error occur! Message was not deleted! ('+data+')');
	}
}


//post_data('./login_check.php', sendstr ,function(a,b,c){login_getData(a,b,c);});

//This function will load dest url and will send the result to handler
//Example of use loadurl(url,handler), handler must take 
function send_get_data(dest,string,handler) {
	user_f_loadurl = handler;

    xmlhttp = getXMLobj();
    xmlhttp.onreadystatechange = function(){loadurl_response();};
    xmlhttp.open('GET', dest + '?' + string, true);
    xmlhttp.setRequestHeader("If-Modified-Since", "Sat, 1 Jan 2000 00:00:00 GMT");
    xmlhttp.setRequestHeader("Content-Type" , "application/x-www-form-urlencoded; charset=UTF-8" );
    xmlhttp.send(null);
	
    //Make loading icon visible
	loading_img.show();
}
function loadurl_response() {
    if (xmlhttp.readyState == 4) {
    	loading_img.hide();
    	user_f_loadurl(xmlhttp.responseText,xmlhttp.status,xmlhttp.statusText);
    }
}
function getXMLobj() {
	req = false;
    // branch for native XMLHttpRequest object
    if(window.XMLHttpRequest) {
    	try {
			req = new XMLHttpRequest();
        } catch(e) {
			req = false;
        }
    // branch for IE/Windows ActiveX version
    } else if(window.ActiveXObject) {
       	try {
        	req = new ActiveXObject("Msxml2.XMLHTTP");
      	} catch(e) {
        	try {
          		req = new ActiveXObject("Microsoft.XMLHTTP");
        	} catch(e) {
          		req = false;
        	}
		}
    }
	if(req) {
		return req;
	} else return false;
}

loading_img = new _loading_img();

function setCaretPosition(ctrl, pos) {
	if(ctrl.setSelectionRange) {
		ctrl.focus();
		ctrl.setSelectionRange(pos,pos);
	} else if (ctrl.createTextRange) {
		var range = ctrl.createTextRange();
		range.collapse(true);
		range.moveEnd('character', pos);
		range.moveStart('character', pos);
		range.select();
	}
}

(function($){
	$.wait = function(amount) {return $.Deferred(function(dfobj) {setTimeout(dfobj.resolve, amount)})}
	$('a.reply[href^="sendmessage.php"]').click(function(e) {
		_stop_e_Propagation(e);
		if ( !$(this).hasClass('clicked')) {
			var send_href = $(this).attr("href");
			var replyto = send_href.replace(/.*receiver=\d+&replyto=(\d+)/i, "$1");
			var messageTD = $(this).parents("table")[1];
			var username = $(messageTD).find('a[href^="userdetails.php"]').text();
			$(messageTD).find('span.success').hide();
			// tragem mesajul la care raspundem si il afisam ;]
			$(this).addClass('clicked');
			loading_img.show();
			$.ajax({
				url: '/sendmessage.php',
				type: 'POST',
				data: {ajax:1, replyto:replyto, action:'getMess'},
				success: function(data){
					$(messageTD).prepend(data);
					$.wait(500).then(function(){ setCaretPosition($(messageTD).find("textarea")[0], 0) });
					loading_img.hide();
				}
			});
		}
	});
	$('#abort').live('click', function() {
		if (confirm("Într-adevăr doriți să anulați expedierea mesajului?") != true) return;
		var messageTD = $(this).parents('table')[0];
		returnToNorm(messageTD);
	});

	$('#send').live('click', function() {
		//loading_img.show();
		var messageTD = $(this).parents('table')[0];
		var message = $(messageTD).find('textarea').val();
		
		$(messageTD).find('.sendmessage_loader').show();
		$(messageTD).find('input').attr('disabled', true);
		//returnToNorm(messageTD);

		var $replyA = $(messageTD).find('a[href^="sendmessage.php"]');

		var receiver = $replyA.attr("href").replace(/.*receiver=(\d+).*/i, "$1");
		var replyto = $replyA.attr("href").replace(/.*replyto=(\d+)/i, "$1");
		$.ajax({
			url: '/takemessage.php',
			type: 'POST',
			data: {ajax:1, origmsg:replyto, receiver:receiver, msg:message},
			timeout: 10000,
			success: function(data){
				//loading_img.hide();
				if ( ( data.indexOf("Eroare") != -1 ) || ( data.indexOf("Ошибка") != -1 ) ) {
					$(messageTD).find('.sendmessage_loader').hide();
					$(messageTD).find('input').attr('disabled', false);
					$(messageTD).find('span.error').show().html(data);
				} else {
					$(messageTD).find('span.success').show().html(data);
					returnToNorm(messageTD);
				}
			},
			error: function() {
				$(messageTD).find('.sendmessage_loader').hide();
				$(messageTD).find('input').attr('disabled', false);
				$(messageTD).find('span.error').show().text("<div>A avut loc o eroare, incercati din nou!</div>");
			}
		});
	});

	$('textarea').live('keydown', function(e) {
		if (e.ctrlKey && e.keyCode == 13) {
			var pmMessage = $(this).parents('table.pmMessage');
			$(pmMessage).find('#send').click();
		}
	});

	function returnToNorm(curElm) {
		$(curElm).find('textarea').parents('td:first').remove();
		//$(curElm).find('table td[class="text"]').show();
		$(curElm).find('a[href^="sendmessage.php"]').removeClass('clicked');
	}

})($j);
