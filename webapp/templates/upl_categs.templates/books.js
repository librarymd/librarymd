if(typeof(continue_category_onload) != 'undefined' && continue_category_onload == true) {
	continue_on_category_html_load();
	continue_category_onload=null;
}

make_preview = function() {
	if (_ge('preview') == null) return; //No div preview, no sense
	if (_ge_by_name('name') == null || _ge_by_name('year') == null) { return; }
	var name = $F('name') + ' ' + ' [' + $F('year');
	if (_ge_by_name('language') != null) {
		name = name + '/' + $F('language').substring(0,2);
	}
	name = name + '] [' + $F('bookz_genre') + ']';
	_ge('preview').childNodes[0].nodeValue = name;
}