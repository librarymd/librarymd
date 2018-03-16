//2006Y

/**
   Register events for login link
**/
menu_item_login_id = 'menu_item_login';
function init_menu_item_login() {
	var m = _ge(menu_item_login_id);
	if (m == null) {
		_not_this_addEvent(window,'load',init_menu_item_login,false);
		return;
	}
	_not_this_addEvent(m,'click',show_login_box,false);
}
/**
   Show the login box in center of screen
**/
login_box_id = 'navbar_login_menu';
function show_login_box(e) {
	if (cap_login) {
		login_redirect_to_cap_login();
		return;
	}
	var box = _ge(login_box_id);
	box.style.display='block'; //First we need to make this, without this offsetWidth&offsetHeight will be 0
	width = (document.body.clientWidth / 2) - (box.offsetWidth / 2);
	//height = ( (document.body.clientHeight / 2) - (box.offsetHeight / 2) ) / 2;
	height = 220;
	box.style.left=width + 'px';
	box.style.top=height + 'px';
	box.style.visibility='visible';

	if (!browser.isGecko) {
		turnOnTransparency();
	}

	var input = _ge(login_box_id+'_input_to_focus_on'); //Now set the focus to the login input
	input.focus();
	//Preload loading img
	loading_img = new _loading_img();
	loading_img.preload();
	_stop_e_Propagation(e); //Stop bulbulator :)
}
function close_login_box() {
	var box = _ge(login_box_id);
	box.style.display='none';
	box.style.visibility='hidden';

	if (!browser.isGecko) {
		turnOffTransparency();
	}
}

function turnOnTransparency() {
	var overlay = _ge('overlay');
    overlay.style.opacity = 0.4;
    overlay.style.filter="alpha(opacity=40)";
	overlay.style.display='block';
	overlay.style.visibility='visible';

}

function turnOffTransparency() {
	var overlay = _ge('overlay');
	overlay.style.display='none';
	overlay.style.visibility='hidden';
}


//Register events for login link
init_menu_item_login();

function startLoginVerify() {
	_ge_by_name('username').style.bordercolor = 'black';
	_ge_by_name('username').style.bordercolor = 'black';
	if (login_preProcess() == false) return false;

	var sendstr = 'username=' + escape($F('username')) + '&password=' + escape($F('password'));
	post_data('./login_check.php', sendstr ,function(a,b,c){login_getData(a,b,c);});
	return false;
}

function login_preProcess() {
	err_enter_username = message('err_enter_username');
	err_enter_password = message('err_enter_password');

	if($F('username') == "") {
	 alert(err_enter_username);
	 return false;
	}
	if($F('password') == "") {
	 alert(err_enter_password);
	 return false;
	}
	return true;
}

function trimString(str) {
	return str.replace(/^\s+|\s+$/g, '');
}

function login_redirect_to_cap_login() {
	window.location.href = "/login.php?c=1";
}

function login_getData(data, statusCode, statusMessage) {
	err_wrong_username = message('err_wrong_username');
	err_wrong_password = message('err_wrong_password');
	ok_logined_successfully = message('ok_logined_successfully');
	info_captcha_login = message('info_captcha_login');

	if (!$('login_form')) {
		alert('Error. Can\'t find my mama ;(');
		return;
	}
	var msg;
	data = trimString(data);
	if (data == '0') {
		msg = '<span style="color:green;">' + ok_logined_successfully + "</span>";
	    login_send_timer = setTimeout("login_send()", 500);
	} else if (data == '1') {
		msg = '<span style="color:red;">' + err_wrong_password + "</span>";
		_ge_by_name('password').style.bordercolor ='red';
	} else if (data == '2') {
		msg = '<span style="color:red;">' + err_wrong_username + "</span>";
		_ge_by_name('username').style.bordercolor ='red';
	} else if (data == '3') {
		msg = '<span style="color: #a9720f">' + info_captcha_login + "</span>";
		login_redirect_to_cap_login();
	} else if (statusCode == 200) {
		msg = '<span style="color:red;">' + data + "</span>";
	} else if (statusCode != 200) {
		msg = '<span style="color:red;">Error! Can\'t send data: ' + statusCode + ' Msg: ' + statusMessage + '</span>';
	}
	$('login_status').innerHTML = msg;
}

function login_send() {
	clearTimeout(login_send_timer);
	document.login_form.submit();
}