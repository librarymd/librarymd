make_preview = function() {
	if (_ge('preview') == null) return; //No div preview, no sense
	//alert('zz');
	var name = $F('name') + ' ' + $F('appz_version');
	var license = _ge_by_name('appz_license').selectedIndex;
	var os = _ge_by_name('appz_os').selectedIndex;
	var lang = _ge_by_name('language').selectedIndex;
	if (license != 1 || os != 1 || lang != 2) {
		var ad = ' [';
		var after = '';
		if(license != 1) {
			ad = ad + _ge_by_name('appz_license').options[license].text;
			after = '/'
		}
		if(os != 1) {
			ad = ad + after + _ge_by_name('appz_os').options[os].text;
			after = '/';
		}
		if(lang != 2) {
			ad = ad + after + _ge_by_name('language').options[lang].text.substring(0,2);
			after = '/';
		}
		name = name + ad + ']';
	}
	_ge('preview').childNodes[0].nodeValue = name;
}

if(typeof(continue_category_onload) != 'undefined' && continue_category_onload == true) {
	continue_on_category_html_load();
	continue_category_onload=null;
}