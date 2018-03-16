//Preview stuff
_not_this_addEvent(_ge_by_name('sample'),'click',function(){ make_preview();} );
_not_this_addEvent(_ge_by_name('subtitles'),'click',function(){ make_preview();} );

make_preview = function() {
	if (_ge('preview') == null) return; //No div preview, no sense
	var name = $F('name') + ' ';
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
	name = name + ' [' + $F('sport_genre') + ']'

	_ge('preview').childNodes[0].nodeValue = name;
}

if (typeof(continue_category_onload) != 'undefined' && continue_category_onload == true) {
	continue_on_category_html_load();
	continue_category_onload=null;
}