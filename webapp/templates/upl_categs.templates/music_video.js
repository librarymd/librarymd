_not_this_addEvent(_ge_by_name('sample'),'click',function(){ make_preview();} );

make_preview = function() {
	if (_ge('preview') == null) return; //No div preview, no sense
	var name = $F('artist') + ' - ' + $F('title') + ' ';
	name = name + '[' + $F('year');
	if(_ge_by_name('sample').checked == true) {
		name = name + '/Sample';
	}
	name = name + ']';
	_ge('preview').childNodes[0].nodeValue = name;
}

if(typeof(continue_category_onload) != 'undefined' && continue_category_onload == true) {
	continue_on_category_html_load();
	continue_category_onload=null;
}