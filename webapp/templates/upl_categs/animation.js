//Serial stuff
_not_this_addEvent(_ge_by_name('serial'),'click',function(e) { serial_change(e); } );

serial_change_prevent_multiple_times = false; //1 time assign keyup to inputs season&episode

function serial_change(e) {
	var checkbox = get_object_from_event(e);
	if(checkbox.checked == true) {
		enable_input(_ge_by_name('season'),_ge_by_name('episode'));
		if(serial_change_prevent_multiple_times == false) {
			_not_this_addEvent(_ge_by_name('season'),'keyup',function(){check_value(_ge_by_name('season'),'ranges'); make_preview(); } );
			_not_this_addEvent(_ge_by_name('episode'),'keyup',function(){check_value(_ge_by_name('episode'),'ranges'); make_preview(); } );
			serial_change_prevent_multiple_times=true;
		}
	} else {
		disable_input(_ge_by_name('season'),_ge_by_name('episode'));
	}
}

/*
  Title translated - same as original
*/
_not_this_addEvent(_ge_by_name('translated_same_original'),'click',function(e) { transltated_as_original(e); } );

function transltated_as_original(e) {
	var checkbox = get_object_from_event(e);
	var translated = _ge_by_name('movie_translated_name');
	if (checkbox.checked == true) {
		translated.value = $F('movie_original_name');
		translated.setAttribute('readonly',true);
		_not_this_addEvent(_ge_by_name('movie_original_name'),'keyup', check_transltated_as_original);
	} else {
		_not_this_removeEvent(_ge_by_name('movie_original_name'),'keyup', check_transltated_as_original);
		translated.removeAttribute('readonly');
	}
}

function check_transltated_as_original(e) { //This will be called at original input onkeyup
	var origina_input = get_object_from_event(e);
	_ge_by_name('movie_translated_name').value = origina_input.value;
}

//Preview stuff
//In fact make_preview are called for all mandatory fields, but sample&subtitles are optional, but participe in preview
_not_this_addEvent(_ge_by_name('sample'),'click',function(){ make_preview();} );
_not_this_addEvent(_ge_by_name('subtitles'),'click',function(){ make_preview();} );

make_preview = function() {
	if (_ge('preview') == null) return; //No div preview, no sense
	
	var name;
	
	//If translated same as original, show the original only
	if (_ge_by_name('translated_same_original').checked == true) name = $F('movie_translated_name') + ' ';
	else name = $F('movie_translated_name') + '/' + $F('movie_original_name') + ' ';
	
	//If it's a serial
	if (_ge_by_name('serial').checked == true) {
		var serial = '[';
		if ($F('season') != '') serial = serial + 'Season ' + $F('season') + iif($F('episode') == '',']','/');
		if ($F('episode') != '') serial = serial + 'Episode ' + $F('episode') + ']';
		name = name + serial + ' ';
	}
	name = name + '[' + $F('year') + '/' + $F('movie_quality') + ']';
	//Check for sample and subtitles
	if (_ge_by_name('sample').checked == true || _ge_by_name('subtitles').checked == true) {
		var other = '[';
		if (_ge_by_name('sample').checked == true) {
			other = other + 'Sample';
		}
		if (_ge_by_name('subtitles').checked == true) {
			other = other + '/Sub';
		}
		var other = other + ']';
		name = name + ' ' + other;
	}
	_ge('preview').childNodes[0].nodeValue = name;
}

if(typeof(continue_category_onload) != 'undefined' && continue_category_onload == true) {
	continue_on_category_html_load();
	continue_category_onload=null;
}