//Register events for checkbox
if (_ge_by_name('catall') != null) {
	_not_this_addEvent(_ge_by_name('catall'),'click',function(){categ_all_ckbox();});
}

function categ_all_ckbox() {
	var i;
	var categs = $('categs').getElementsByTagName('input');
	if (_ge_by_name('catall').checked == true) {
		//Make all categs checkbox checked and disabled
		for(i = 1;i<categs.length;i++) { //start from 1, skip first
			if (categs[i].type.toLowerCase() == 'checkbox') {
				categs[i].checked = true;
				categs[i].setAttribute('disabled',true);
			}
		}
	} else {
		//Travers all elements and unset the disabled attribute
		for(i = 1;i<categs.length;i++) {
			if (categs[i].type.toLowerCase() == 'checkbox') {
				categs[i].removeAttribute('disabled');
			}
		}
	}
}