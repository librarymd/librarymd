_not_this_addEvent(_ge_by_name('vbr'),'click',function(){ make_preview();} );

make_preview = function() {
	if (_ge('preview') == null) return; //No div preview, no sense
	var name = $F('artist') + ' - ' + $F('title') + ' ';
	//Artist - Title [1997/MP3/225 (VBR)]
	format_obj = _ge_by_name('music_format');
	format = format_obj.options[format_obj.selectedIndex].text;
	name = name + '[' + $F('year') + '/' + format + '/' + $F('music_bitrate');
	if(_ge_by_name('vbr').checked == true) {
		name = name + ' (VBR)';
	}
	name = name + '] [' + $F('music_genre') + ']';
	_ge('preview').childNodes[0].nodeValue = name;
}

if(typeof(continue_category_onload) != 'undefined' && continue_category_onload == true) {
	continue_on_category_html_load();
	continue_category_onload=null;
}