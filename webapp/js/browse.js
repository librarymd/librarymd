//2006Y

//Show top functions
function show_top(type,aobj) {
	if (type.length > 5) return; //security check for who the hell know for what ? lol, anyway, we are more secure now, right ? :)

	//Save lastest torrents
	if (typeof(lastest_torrents) == 'undefined') {
		lastest_torrents = $('torrents').innerHTML;
	}
	top_header = aobj.childNodes[0].data;
	//Check if not already loaded
	if (eval('window.show_menu_' + type) != null) {
		eval('window.show_menu_' + type + '();');
	} else {
		loading_img.show();
		xLoadScript('./cache/tops/'+topVerGen+'_' + type + '.js');
	}

	change_active_link(aobj);
}

function change_active_link(aobj) {
	//Make all top menu links normal links
	as = $('topmenu').getElementsByTagName('a');
	for(i=0;i<as.length;i++) {
		cur_a = as[i];
		cur_a.className='a_inactive';
	}

	aobj.className='a_cur_selected';
	a_current_active_topmenu = aobj;
}

function show_lastest(aobj) {
	change_active_link(aobj);
	if (typeof(lastest_torrents) == 'undefined') return;
	$('torrents').innerHTML = lastest_torrents;
	$('torrents_header').innerHTML = '';
}

function change_topmenu(move) {
	if (move == 'next') {
		hide_it('menu_1');
		show_it('menu_2');
	} else if (move == 'back') {
		hide_it('menu_2');
		show_it('menu_1');
	}
}

loading_img = new _loading_img();
