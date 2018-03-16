//Drow down DIV with movie genres
movie_genres_input_id = 'movie_genres_input';
downdrop_div_ = 'movie_genres_all_list'; //The div id what should appear on click downdrop_div_father
downdrop_div_father = 'movie_genres_input'; //Appear at the bottom of this element
downdrop_div_excit = 'movie_genres_onclick_show'; //On mousedown on downdrop_div_excit, downdrop_div under downdrop_div_father will appear
categ_dropdiv = new _dropdown_div(downdrop_div_,downdrop_div_father,new Array('movie_genres_input',downdrop_div_excit),'categ_dropdiv');

movie_genres_list = new Array(); //The categorys names
movie_genres_list_ids = new Array(); //The categorys ids

//On click on some genre checkbox
function movie_genres_checkbox(checkbox) {
	var genre = checkbox.nextSibling.nodeValue;
	var status = checkbox.checked; //True or False
	var input = _ge(movie_genres_input_id);
	var checkbox_name = checkbox.id; //example: genre4
	var checkbox_id = checkbox_name.substring(5,7);
	
    if (status == true) { //Add new genre
    	movie_genres_list.push(genre);
    	movie_genres_list_ids.push(checkbox_id);
    } else { //Remove a genre from the list
        var i=0;
        for(i=0;i<movie_genres_list.length;i++) { //Search to remove
        	if (movie_genres_list[i] == genre) { //Yeap, we have found the genre what we should remove from genre array
        		movie_genres_list.splice(i,1); //Remove 1 element at the i position
        		movie_genres_list_ids.splice(i,1);
        		break;
        	}
        }
    }
    movie_genres_update_input(); //Update input what show the movie genres
}
function movie_genres_update_input() { //Update input what show the movie genres
	var input = _ge(movie_genres_input_id);
	var list_ids = _ge_by_name('movie_genres_list_ids');
	var i=0;
	var output='';
	for (i=0;i<movie_genres_list.length;i++) {
	    output = output+movie_genres_list[i];
		output=output+' / ';
	}
	output=output.substring(0,output.length - 3); //Remove last 3 char, we don't need ' / ' at the end of string
	input.value=output;
	list_ids.value=movie_genres_list_ids.toString();
	//Excite mandatory checker, if exist
	if (check_mandatory != '') check_mandatory();
}
//When movie_genres_list is null, because we have movie_genres_list_ids only
function movie_generate_genres_list(ids) {
	movie_genres_list = new Array();
	movie_genres_list_ids = ids;
	for(var i=0;i<movie_genres_list_ids.length;i++) {
		var v = movie_genres_list_ids[i];
		if($('genre'+v)) {
			var checkbox = $('genre'+v);
			var genre = checkbox.nextSibling.nodeValue;
			movie_genres_list.push(genre);
			checkbox.checked=true;
		}
	}
	movie_genres_update_input();
}

//Serial stuff
_not_this_addEvent(_ge_by_name('serial'),'click',function(e) { serial_change(e); } );

serial_change_prevent_multiple_times = false;

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
_not_this_addEvent(_ge_by_name('sample'),'click',function(){ make_preview();} );
_not_this_addEvent(_ge_by_name('subtitles'),'click',function(){ make_preview();} );

make_preview = function() {
	if (_ge('preview') == null) return; //No div preview, no sense
	
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
	//Add genre to the end
	name = name + ' [' + $('movie_genres_input').value + ']'
	_ge('preview').childNodes[0].nodeValue = name;
}
if (typeof(continue_category_onload) != 'undefined' && continue_category_onload == true) {
	continue_on_category_html_load();
	continue_category_onload=null;
}