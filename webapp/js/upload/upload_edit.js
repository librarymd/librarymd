// Code what upload.js and edit.js share

/* Events */

function on_category_load() {
    // For the lang
	/*$j('.lang_hide').removeClass('lang_hide').filter('select').each(function(){
		if ($j(this.options[this.selectedIndex]).hasClass('lang_ro')) {
			this.selectedIndex = 1;
		} else this.selectedIndex = 0;
	});*/
    if (user_lang == 'ro') $j('option.lang-ru-hide,option.lang-ru-hide-all').remove();
	else $j('option.lang-ro-hide,option.lang-ro-hide-all').remove();

	// Checkboxes
	$j('#imdb_doesnt_exist').unbind('mousedown').mousedown(function() {
		if (this.checked == false) {
			var r = confirm('Sunteți siguri că nu este pe IMDB ? 99% din filme sunt în baza IMDB.');
			if (r == false) return r;
			this.checked = true;
			upload_check_imdb_link_enable = false;
		} else {
			upload_check_imdb_link_enable = true;
		}
		check_mandatory();
	});
	$j('#trailer_doesnt_exist').unbind('mousedown').mousedown(function() {
		if (this.checked == false) {
			var r= confirm('Sunteți siguri că nu este pe youtube ? 99% din filme au trailer pe youtube.');
			if (r == false) return r;
			this.checked = true;
			upload_check_youtube_link_enable = false;
		} else {
			upload_check_youtube_link_enable = true;
		}
		check_mandatory();
	});

	// The buttons
	$j('#search_youtube').unbind().click(function() {
		var n = '"' + $j('#movie_original_name').val() + '" trailer';
		window.open('https://www.youtube.com/results?search_type=videos&search_query=' + encodeURIComponent(n));
	});

	$j('#search_imdb').unbind().click(function() {
		var n = $j('#movie_original_name').val();
		window.open('https://www.imdb.com/find?s=tt&q=' + encodeURIComponent(n) + '&x=0&y=0');
	});

	$j('#search_ambele').unbind().click(function() {
		$j('#search_imdb,#search_youtube').click();
	});

	upload_check_imdb_link_enable = true;
	upload_check_youtube_link_enable = true;
}

var upload_check_imdb_link_enable;
var upload_check_youtube_link_enable;

function upload_check_imdb_link(value) {
	if (upload_check_imdb_link_enable == false) return true;
	var reg = new RegExp('^http(?:s|)://www.imdb.com/title/tt(\\d{7}).*');
	if (value.match(reg) != null) return true;
	return false;
}

function upload_check_youtube_link(value) {
	if (upload_check_youtube_link_enable == false) return true;
	var reg = new RegExp('^http(?:s|)://www.youtube.com/watch\\?v=([\\w\\d\-]){11}.*?$');
	var reg2 = new RegExp('^http(?:s|)://www.youtube.com/watch\\?v=([\\w\\d\-]){11}#t=(\\d)+.*?$');

	if (value.match(reg) != null || value.match(reg2) != null) return true;
	return false;
}

var desc_one_time_screenshot_img=false;
var desc_one_time_screenshot_spoiler=false;
var desc_one_time_screenshot_thumb=false;

function upload_check_descr(value) {
	if ($j('#tr_description').hasClass('oldBehaiveiors')) {
		if(value.length>=15){return true;}
		return false;
	}
	if (value.length < 50) { return false; }

	return true;
}

function substr_count( haystack, needle, offset, length ) {
  if(!isNaN(offset)){
    if(!isNaN(length)){
      haystack=haystack.substr(offset,length);
    }else haystack = haystack.substr(offset)
  }
  haystack = haystack.split(needle).length-1;
  return haystack<0?false:haystack;
}

var dethumbnail_undo_mem;
function dethumbnail_descr(check_only) {
	var descr = $j('#descr');
	if (descr.size() == 0) return false;
	var e = '\\[url=(.+?)(jpg|jpeg|png|gif)\\]\\[img\\](.+?)\\[/img\]\\[/url\\]';
	var re = new RegExp(e,'gi');
	if (descr.val().match(re)) {
		if (check_only) return true;
		dethumbnail_undo_mem = descr.val();
		$j('#dethumbnail_undo').removeClass('hideit');

		descr.val( descr.val().replace(re,'[img]$1$2[/img]') );
	} else {
		if (check_only) return false;
		alert('Thumbnail-uri nu au fost depistate, doar formatul de genul [url=http://..jpg][img]http://..jpg[/img][/url] la moment este mentinut');

	}
}

function dethumbnail_descr_undo() {
	var descr = $j('#descr');
	descr.val( dethumbnail_undo_mem );
}
