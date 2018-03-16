function details_ajaxator() {
	if ($('a_showpeers') == null) {
		_not_this_addEvent(window,'load',function(){details_ajaxator();});
		return;
	}

	var re = new RegExp('id=(\\d+)');
	var m = re.exec(window.location.href);
	if (m == null) { return; }
	torrent_id = m[1];
  
	$('a_showpeers').onclick=function(e){details_load_info(e);};
}

obj_a = new Array(); //References to clicked <a>
obj_tr = new Array(); //References to tr of clicked <a>
obj_new_tr = new Array(); //References to newly created trs

function details_load_info(e) {
	if (typeof(no_ajax) != 'undefined') return;
	_stop_e_Propagation(e);
	var tmp = get_object_from_event(e);
	var what = tmp.id
	obj_a[what] = get_object_from_event(e);
	obj_tr[what] = obj_a[what].parentNode.parentNode;
	//Store current action name
	if (typeof(current_job_name) == 'undefined') current_job_name = what;

	get_data('./details.php',what+'=1&ajax=1&id='+torrent_id+'&lsource='+what, function(a,b,c){details_info_onload(a,b,c);} );
	//alert('function(a,b,c){details_info_onload(a,b,c,\''+what+'\');}');
}
function details_info_onload(data, statusCode, statusMessage) {
	// format of data:
	// elm[0] = where
	// elm[1..] = array(td1,td2)
	if (data.search(/^\[(.+)\]$/) == 0) { //Check integrity
		var naturalizator = eval(data); //because data must be in json format
	} else {
		window.location.href=obj_a[current_job_name].href; //Data corupted, we can't use ajax, use the classic mode
		return;
	}
	where = naturalizator[0];
	if (obj_a[where].tagName != 'A') { //Some error occur.., use the classic mode
		window.location.href=obj_a[current_job_name].href;
		return;
	}
	obj_a[where].onclick=function(e){details_hide_info(e);};
	obj_a[where].innerHTML='[Hide list]';
	
	var details_tbody = obj_tr[where].parentNode;
	obj_new_tr[where] = new Array(); //Save newly created trs
	for(elm in naturalizator) {
		if (elm == 0) continue; //skip first element
		var _tr = document.createElement('tr');
		//Append first td
		var _td1 = document.createElement('td');
		_td1.setAttribute('align','right');
		_td1.innerHTML= naturalizator[elm][0];
		_tr.appendChild(_td1);
		//Append second td
		var _td2 = document.createElement('td');
		_td2.innerHTML = naturalizator[elm][1];
		_tr.appendChild(_td2);
		
		details_tbody.insertBefore(_tr,obj_tr[where].nextSibling);
		//Save references for futher manipulation
		obj_new_tr[where].push(_tr);
	}
}


//In this moment the details are showed, and we must hide them
function details_hide_info(e) {
	_stop_e_Propagation(e);
	var a = get_object_from_event(e);
	var what = a.id; //Id of clicked link are what
	for (var tr in obj_new_tr[where]) {
		obj_new_tr[where][tr].className='hideit';
	}
	a.onclick=function(e){details_show_info(e);};
	a.innerHTML='[See full list]';
}
// Info is already loaded, we just need to show it, easy as 2 cents ;D
function details_show_info(e) {
	_stop_e_Propagation(e);
	var a = get_object_from_event(e);
	var what = a.id; //Id of clicked link are what
	for (var tr in obj_new_tr[what]) {
		obj_new_tr[where][tr].className='showit_tr';
	}
	a.onclick=function(e){details_hide_info(e);};
	a.innerHTML='[Hide list]';
}

details_ajaxator();

function SmileIT(smile){
	var text=document.getElementById(SmileIT_id);
	text.value += " "+smile+" ";
    text.focus();
}

function PopMoreSmiles(name) {
	link='moresmiles.php?&text='+name;
    newWin=window.open(link,'moresmile','height=500,width=450,resizable=no,scrollbars=yes');
    if (window.focus) {
    	newWin.focus();
    }
}
function PopStamps(name,container,lang) {
	if ($('iframe_stamps') == null) {
		var _iframe = document.createElement('iframe');
		_iframe.src = './stamps_popup.php?&text='+name+'&container='+container+'&lang='+lang;
		_iframe.width=790;
		_iframe.height=200;
		_iframe.id='iframe_stamps';
		$('after_comment_box').appendChild(_iframe);
	}
	show_it('after_comment_box');
}

loading_img = new _loading_img();

//Thanks ajaxator
//Thank without page reload, on the button click, 
function thanks_ajax_obj() {
	//Set-up the events, on button click
	this.init = function() {
		if (!_ge_by_name('thank')) return;
		_not_this_addEvent(_ge_by_name('thank'),'click',this.click );
	}
	//On click
	this.click = function(e) {
		try {
			var button_obj = get_object_from_event(e);
			var parent_tr = button_obj.parentNode.parentNode; //1parentNode = form, 2parentNode = td
			$j('div.sp-body',$j(parent_tr)).each(function(){ // It's only one, but we need to use this as shortcut to our elm
				//var users_voted = $j(this).html().match('(.*?)(<br>|<BR>|<br/>|<BR/>)')[1];
				/*if (users_voted.length>7) {
					users_voted += ', ';
				}*/
				var last_a = $j('a:last',$j(this));
				var a = '<a href="./userdetails.php">' + torrents_md_nick + '</a>';
				if (last_a.length == 0) {
					$j(this).html(a);
				} else {
					last_a.after($j('<span>, </span>'+a));
					//last_a.after($j(a)).after($j(', '));
				}
				//$j(this).html(users_voted + '<a href="./userdetails.php">' + torrents_md_nick + '</a><br>');
				$j('div.sp-head:first',$j(this).parent()).not('.unfolded').click();
			});
			$('thanks_count').innerHTML = parseInt($('thanks_count').innerHTML) + 1;
			$j(button_obj).remove();
			post_data('./details.php','id='+torrents_md_torrent_id+'&thank=1&async=1',function(){});
			return false;
		} catch(e) {
			return;
		}
		_stop_e_Propagation(e); //Stop propagation only if no errors, if we are here, then we had no errors
	}
	this.init();
}
var thanks_ajax = new thanks_ajax_obj;

//Love ajaxator
//Love - original, da ? ;D
function love_torrent_ajaxator() {
	if ($('love_icon')) {
		//Animator, on mouse over if off->on, mouse out on->off
		$('love_icon').onmouseover = function(e){love_icon_mouseover(e);};
		$('love_icon').onmouseout = function(e){love_icon_mouseout(e);};
		$('love_icon').onclick = function(e){love_icon_click(e);};
	}
}

function love_icon_mouseover(e) {
	love_obj = get_object_from_event(e);
	//If off, then we can show at mouse over the On image
	if (torrents_md_bookmarked == 'yes') {
		love_obj.src = './pic/stars/love_off.gif';
	} else {
		love_obj.src = './pic/stars/love_on.gif';
	}
}

function love_icon_mouseout(e) {
	if (torrents_md_bookmarked == 'yes') {
		love_obj.src = './pic/stars/love_on.gif';
	} else {
		love_obj.src = './pic/stars/love_off.gif';
	}
}

function love_icon_click(e) {
	love_obj = get_object_from_event(e);

	if (typeof(torrents_md_bookmarked) == 'undefined') torrents_md_bookmarked = 'no';
	
	if (torrents_md_bookmarked == 'no') {
		love_obj.src = './pic/stars/love_on.gif';
		post_data('./bookmarks.php','action=add&torrentid='+torrents_md_torrent_id+'&ajax=1',function(){});
		torrents_md_bookmarked = 'yes';
	} else {
		love_obj.src = './pic/stars/love_off.gif';
		post_data('./bookmarks.php','action=del&torrentid='+torrents_md_torrent_id+'&ajax=1',function(){});
		torrents_md_bookmarked = 'no';
	}
	_stop_e_Propagation(e);
}

love_torrent_ajaxator();

/*
	Watcher section
*/

function Watcher() {
	if (watchStatut) {
		$('watcherText').innerHTML = langWatchOn;
		$('watcherText').style.backgroundColor = '#ECE9D8';
		watchStatut = false;
		var action = 'del';
	} else {
		$('watcherText').innerHTML = langWatchOff;
		$('watcherText').style.backgroundColor = '#D3F1E2';
		watchStatut = true;
		var action = 'add';
	}
	
	sendStr = 'ajax=1&type=torrent&action='+action+'&thread='+topicId;

	var callback = function(data) {
		if (naturalizator != 1) {
			document.location = './watcher.php?'+sendStr;
		}
	}
	
	post_data('./watcher.php', sendStr, callback, true);
}

/*
	Cenzurator
**/

function initCens() {
	var censSpan = getElementsByClass('censlnk',null,'span');

	for (var i=0,l=censSpan.length;i<l;i++) {
		var curElm = censSpan[i];
		addEvent(curElm,'click',function(e){cens(e);});
	}
}

function cens(e) {

	var lnk = get_object_from_event(e);
	
	lnk = lnk;
	
	var cId = lnk.getAttribute('customCid');
	var tId = torrents_md_torrent_id;
	
	sendStr = 'ajax=1&action=censor&cid='+cId+'&tid='+tId;
	post_data('./comment.php', sendStr,function(){}, true);
	
	var cenzText = document.createTextNode('Cenzurat')
	lnk.parentNode.insertBefore(cenzText, lnk);

	_removeNode(lnk);
}





function initRaport() {
	var censSpan = getElementsByClass('raportareLnk',$('forumPosts'),'span');
	censSpan[censSpan.length] = getElementsByClass('raportareLnk',$('forumPosts_first'),'span')[0];
	
	for (var i=0,l=censSpan.length;i<l;i++) {
		var curElm = censSpan[i];
		if (!curElm) continue;
		addEvent(curElm,'click',function(e){raport(e);});
	}
}

function raport(e) {
	var lnk = get_object_from_event(e);
	
	lnk.innerHTML = lang_raport_ok;
	lnk.className = '';
	
	var callback = function(a,b,c) {};
	
	var cId = lnk.getAttribute('customCid');
	var tId = torrents_md_torrent_id;
	
	sendStr = 'ajax=1&action=postRaport&cid='+cId+'&tid='+tId;
	post_data('./comment.php', sendStr,function(a,b,c){callback(a,b,c);}, true);
}


function onDetailsLoad() {
	initCens();
	initRaport();
}

$j(document).ready(function(){ onDetailsLoad(); });

initSpoilers();

(function ($)
{
    $('.showAllCategtags span').click(function ()
    {
		$(this).hide();
		$("#categtagInactivList .hiddenCategtags").show();
	});
})($j);