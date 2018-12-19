function ajax_delete_bookmark() {
	var deletes = getElementsByClass('delete_bookmark',$('bookmark_table'));
	for (var i=0; i<deletes.length; i++) {
		_not_this_addEvent(deletes[i],'click',function(e){delete_bookmark(e);} );
	}
}

ajax_delete_bookmark();

function delete_bookmark(e) {
	_stop_e_Propagation(e);
	var del_img = get_object_from_event(e);
	var torrent_id = del_img.id.substr(1); //Omit 1 character, and get the string until the end
	post_data('./bookmarks.php','action=del&torrentid='+torrent_id+'&ajax=1',function(){});
	delete_row(del_img);
}

/**
    This function will search to the tree up, until will find a row,
    at row find, it will be deleted, the search will stop.
    (Short) Delete first parrent row element
**/
function delete_row(child_point) {
	if (typeof(child_point) == 'undefined') return;
	pos = child_point;
	var max_safer = 0;
	while (pos.tagName.toUpperCase() != 'HTML') {
		if (pos.tagName.toUpperCase() == 'TR') { //We found the row, delete it, and get out of here
			pos.className='hideit';
			return;
		}
		pos = pos.parentNode;
		//Prevent infinite loop
		max_safer++;
		if (max_safer > 100) return 0;
	}
}