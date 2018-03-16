/*
 * 	all.ourJS.js
 * 		bagam aici toate scripturile/functiile
 * */

/*
 * Simple Console logging v.3
 * (c) flienteen.com
 *
 * use:
 * 		l(bla, bla, ...);
 *
 * 		l.logging	= true  -> turn on logging, default false
 * 		l.debugging = true  -> turn on debugging, default false
 *
*/
var l = function l() {
	try {
		var logging = l.logging||false, debugging = l.debugging||false;

		if(logging && typeof(console) != 'undefined') {
			if(debugging) {
				console.groupCollapsed(l.caller.name||'anonymous');
				console.log.apply(console, arguments);
				console.trace();
				console.groupEnd();
			} else
			console.log.apply(console, arguments);
		}
	}catch(e){}
};

/*
	spoiler, aici caci jquery.js e global, probabil tre de creat un fisier gen global.js
*/
function initPostImages(context) {
	var done_anything = false;
	var $in_spoilers = $j('div.sp-body var.postImg', context);
	$j('var.postImg', context).not($in_spoilers).each(function(){
		var $v = $j(this);
		var src = $v.attr('title');
		var $img = $j('<img class="'+ $v.attr('className') +'" alt="pic" />').attr('src',src);
		$v.before($img).remove();
		done_anything = true;
	});
	return done_anything;
}

function initSpoilers()
{
	lang_click_here = message('lang_click_here');
	$j('div.sp-head').not('.jsClickEvAttached').click(function(e){
			var $sp_body = $j('div.sp-body:first', $j(this).parents('div.sp-wrap')[0]);

			if (!$sp_body.hasClass('inited')) {
				var any_image = initPostImages($sp_body);

				$sp_body.addClass('inited');

				if ($sp_body.height()>300 || any_image)
					$sp_body.find('.sp-foot:last').show();
			}

			if (e.shiftKey) {
				e.stopPropagation();
				e.shiftKey = false;
				var fold = $j(this).hasClass('unfolded');
				$j('div.sp-head', $j($sp_body.parents('td')[0])).filter( function(){ return $j(this).hasClass('unfolded') ? fold : !fold } ).click();
			}
			else {
				$j(this).toggleClass('unfolded');
				$sp_body.slideToggle('fast');
			}
	}).addClass('jsClickEvAttached').each(function(){
		this.innerHTML += ' ('+lang_click_here+')';
	});
	$j('div.sp-foot').not('.jsClickEvAttached').click(function () {
		var $sp_head = $j(this).parents('div.sp-wrap:first');
        // Only if our viewpoint is below the top of the spoiler
        if ( $j(window).scrollTop() > $sp_head.offset().top )
		    $j('html, body').animate({scrollTop:$sp_head.offset().top-1}, 80);
		$sp_head.find('div.sp-head:first').click();
	}).addClass('jsClickEvAttached');
}

function initIurl() {
	$j("a.lbimg").not('.initIurl').each(function(){
		if ($j(this).hasClass('initIurl')) return; // Putea fi adaugat mai jos, cind deja acest ciclu era pornit
		$j(this).parents('td:first').not('.initIurl').each(function(){
			$j("a.lbimg",$j(this)).lightBox().css("color","green").css("font-weight","bold").addClass('initIurl');
		}).addClass('initIurl');
	});
}

function jsForBrowseFilters($) {
	//bagat init direct cu json
	store.init = function(k,v) {
		var get = function() {
			return JSON.parse(store.get(k));
		};
		var set = function()
		{
			store.set(k, JSON.stringify(v));
		};

		if(!store.get(k) || !get().version || (get().version < v.version))
			set();

		return get();
	};

	$.fn.dHide = function() {
		this.addClass('dHide').hide();
		return this;
	};

	$.fn.dShow = function() {
		this.removeClass('dHide').show();
		return this;
	};

	$.fn.cShow = function() {
		$ourMegaCat.find('.limit').removeClass('limit');
		this.addClass('clicked').next('ul').addClass('show limit').hide().slideDown(options.animationSpeed);
		return this;
	};

	$.fn.cHide = function() {
		this.removeClass('clicked').next('ul').removeClass('show').slideUp(options.animationSpeed);
		return this;
	};

	$.fn.dep = function() {
		var dep = [];
		for(var i=0; i<this.length; i++)
		{
			var t = $(this[i]).data('dep').split(',');
			t.pop();
			t.shift();
			dep = dep.concat(t);
		}

		return dep;
	};


	//------

	var optionsDefault = {
		version: 1.1,
		autoCloseOnSelectNode : false,
		keepCategoriesExpandable : true,
		isAnimationEnabled : false,
		useSmartFilters : true,
		hideShareURL: true, //deprecated!
		showOnlygCat: true
	};
	var options = store.init('browseFilters', optionsDefault);

	var filterCats = {};

	var $parent = $('#browse_filters');
	var $ourMegaCat = $parent.find('.ourMegaCat');
	var $informer = $parent.find('.informer');
	var $loading = $parent.find('.picResultLoader').hide();
	var $result = $parent.find("#result");
	var $tmp = $parent.find('.tmpresultload');
	var $resEcho = $parent.find('.resultEcho>span');

	var $onAnyTrigger;

	var WL=window.location, WH=window.history;
	var html5history = typeof(WH.pushState) == 'function';
	var globalLocation = {q:{}};

	globalLocation.url = function() {
		var url='';
		$.each(globalLocation.q, function(i,v)
		{
			url+=i+'='+v+'&';
		});
		url = (url)?'?'+url.replace(/&$/,''):'';

		return globalLocation.page + url;
	};
	globalLocation.init = function() {
		var url = WL.search;
		if(url)
		{
			url = url.replace(/^\?/,'');
			var arr = url.split('&');
			$.each(arr, function(i,v)
			{
				var t=v.split('=');
				globalLocation.q[t[0]] = t[1];
			});
		}
		globalLocation.page = WL.pathname;
	};


	//===
	var o={};
	o.browseRequirebBySearch = browseRequirebBySearch;
	o.browseCategTags = browseCategTags;

	options.check = function()
	{
		options.animationSpeed = (options.isAnimationEnabled)?500:1;
		//hideShareDiv(options.hideShareURL);

		showOnlygCat(options.showOnlygCat);
	};
	options.check();

	options.update = function()
	{
		options.animationSpeed = (options.isAnimationEnabled)?500:1;

		store.set('browseFilters', JSON.stringify(options));

		options.check();

		doDepTest();
	};

	isAnyTrigger(false);
	var $gCat = $ourMegaCat.find('ul[data-id="'+browseGCatVariable+'"]').addClass('gCat');
	$gCat.prev().addClass('gCat');
	$gCat.parent().addClass('gCat');



	//var gCatArray=[];
	$gCat.find('>li').each(function makeDepForAllLi(i,v)
	{
		$(v).attr('data-dep', ','+$(v).data('id')+',');
		//gCatArray.push($(v).data('id'));
	});
	//l('gCatArray',gCatArray);

	$ourMegaCat.find('li[data-dep=",,"]').addClass('skip');


	//markam nodurile finale
	$ourMegaCat.find('span').each(function markAllFinalNode()
	{
		var $v = $(this);
		var $next = $v.next('ul');
		if(!$next.length)
		{
			$v.addClass('final');
			$v.parent().addClass('final').removeAttr('data-checkable');
		} else {
			$v = $(this);
			if($v.parent().data('checkable') === 'yes')
			{
				$('<div>', {'class':'checkbox'}).html('<div class="checked">√</div>').appendTo($v).click(selectThisSubCat);
			}
		}
	}).addClass('tran');



	$ourMegaCat.find('span:not(".clicked, .final")').live('click', showNextNode);
	$ourMegaCat.find('span.clicked:not(".final")').live('click', hideNextNode);

	$ourMegaCat.find('span.final:not(.selected)').live('click', selectThisNode);
	$ourMegaCat.find('span.final.selected').live('click', deselectThisNode);
	$informer.find('.filters .rowRm').live('click', rmRow);


	function selectThisSubCat()
	{
		var $v=$(this);
		$v.toggleClass('checked');

		//$v.parent().toggleClass('selected');
		if(!$v.hasClass('checked'))
		{
			$v.parent().each(deselectThisNode);
		} else {
			$v.parent().each(selectThisNode);
		}


		l($v);
	}

	function rmRow()
	{
		var $v = $(this);
		var id = $v.parent().data('id');

		if(id=='title')
		{
			$('#searchByTitle').val('').change();
			return;
		}
		filterCats['p'+id].ref.click();
	}


	function showNextNode(e)
	{
		var $v = $(this);
		l("Clicked on",$v, e, e.target.nodeName, e.target, e.target.className, e.target.className=='checkbox', e.target.nodeName=='DIV');

		if(e.target.nodeName=='DIV')
			return;


		var $hide = $('span.clicked');
		if(!options.keepCategoriesExpandable)
		{
			$hide.each(function()
			{
				if(!$(this).parent().has($v).length)
				{
					$(this).cHide();
				}
			});
		}

		$v.cShow();
	}

	function hideNextNode(e)
	{
		var $v = $(this);
		l("Clicked on",$v, e.target.nodeName=='DIV');

		if(e.target.nodeName=='DIV')
			return;

		$v.cHide();
	}


	function deselectThisNode()
	{
		var $v = $(this);
		l("Deselect this item", $v, $v.parent('li'));

		//$v.each(doDepTest);

		$v.removeClass('selected');

		filtersRm($v.parent('li').data('id'));
	}

	function selectThisNode()
	{
		var $v = $(this);
		l("Select this item", $v,  $v.parent('li'), arguments, arguments[0], typeof(arguments[0]));

		//$v.each(doDepTest);

		var isSubCat = (typeof(arguments[0]) == 'number');

		$v.addClass('selected');

		if(options.autoCloseOnSelectNode)
			$v.parent('li').parent('ul').prev().click();

		var p1;
		var $parents = $v.parents('li').map(function(i, e)
		{
			var p = $(e).data();
			p.ref = $(e).children('span:first');
			if(isSubCat) p.ref = p.ref.find('.checkbox');
			if(!p1) p1 = p;

			if(p.id>0)
				return {id:p.id, name:p.name, ref:p.ref};
		}).get().reverse();

		l('my parents', $parents, p1);

		//filterCats.push($parents);
		//l('tipa>', p1, p1.id);

		filtersAdd(p1, $parents);
	}

	function filtersAdd(p, arr)
	{
		filterCats['p'+p.id] = arr;
		filterCats['p'+p.id].ref = p.ref;
		buildFilterList();
	}
	function filtersRm(id)
	{
		//$(filterCats['p'+id].ref).each(deselectThisNode);
		delete filterCats['p'+id];
		buildFilterList();
	}


	var $fildiv = $informer.find(".filters");
	var lastNb=0;
	function buildFilterList()
	{
		$fildiv.html('');
		var url=[], k=0;


		//
		var findNb = $result.children().length > 0, nb=0;
		if (findNb)
		{
			var $atag = $result.find('p:first a:last').prevAll('a:first');
			if ($atag.length>0)
				nb = $atag.find('b').html().replace(/.*-&nbsp;/,'');
		}
		$resEcho.fadeOut(0).html(nb).fadeIn(50).parent().addClass('show');



		//search by title
		if(searchVal)
			$row = $('<div/>',{'class':'catRow', 'data-id':'title'}).appendTo($fildiv).html('<span class="rowRm">x</span><span>'+message('Denumirea')+'</span>><span>'+searchVal.replace(/</g, '&lt;').replace(/>/g, '&gt;')+'</span>');


		$.each(filterCats, function(i,row)
		{
			var rspans='<span class="rowRm">x</span>', v;
			$.each(row, function(j,c)
			{
				v=c;
				//l(i, ":", j, v, v.id, v.name, v.e);

				rspans += (j>0)?">":'';
				rspans += "<span>"+v.name+"</span>";
			});
			//rspans = '<span class="rowRm">x</span>'+ rspans;

			url.push(v.id);
			$row = $('<div/>',{'class':'catRow', 'data-id':v.id}).appendTo($fildiv).html(rspans);
			k++;
		});


		//hack urat..
		$fildiv.find('.rowRm').css({'opacity':'0'}).addClass('nopa').css({'opacity':''});
		if(lastNb < k)
		{
			var $last = $fildiv.find('.rowRm:last').removeClass('nopa');
			$.wait(300).then(function()
			{
				$last.addClass('nopa');
			});
		}

		lastNb=k;

		//l(9, r);
		url = url.join(',');
		if((url || searchVal) && !browseRequirebBySearch)
		{
			applyFilters(url);
		}
		browseRequirebBySearch = false;

		l("avem urm lista", filterCats);

		doDepTest();
	}


	function changeUrl(url)
	{
		//un fel de fallback))
		//daca aveti o varianta mai buna, foc :)
		if(!html5history)
			WH.pushState = function(i,t,u)
			{
				//l.search = '';
				WL.hash = u.replace(/.*\?/,'');
			};
		else
			bindPopState();

		WH.pushState(null, null, url);
		//$shareInput.val(WL.origin+url);
	}


	/*
	 îi cam buggy.. nu pre pot testa pe local,
	 și nici nu mai gândesc la ora asta))
	 */
	var bindPopState = function()
	{
		if(!bindPopState.binded && !browseCategTags)
		{
			window.onpopstate = function()
			{
				var url = WL.search.replace(/.*=/g,'');
				l('window.onpopstate => trigged =>', url);
				/*
				 TODO:
				 //ar trebui un mega reset..
				 //pana cand facem refresh..

				 reBuildCategs(url);
				 applyFilters(url);
				 */
				WL.reload();
			};

			bindPopState.binded = true;
		}
	};


	function applyFilters(url)
	{
		isAnyTrigger(true);
		//prevenim intrarea în funcție

		var fromBrowsePhp = window.location.pathname == '/browse_filters.php';
		if(browseCategTags && !fromBrowsePhp) return;


		var lPage = '/browse.php';
		var lPageQuery = '?categtags='+url;
		var torrentsPage;


		if(searchVal)
		{
			var s = encodeURIComponent(searchVal).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
			lPage = '/search.php';
			//lPageQuery = '?search_str='+s + ((url)?'&categtags='+url:'');

			globalLocation.q['search_str'] = s;
		}



		globalLocation.page = lPage;
		if(url) globalLocation.q['categtags'] = url;

		torrentsPage = globalLocation.url();
		l('Mod normal avem urmatorul request\t',torrentsPage);
		//torrentsPage = 'br2.html';
		//torrentsPage = 'br1.html';

		//
		changeUrl(torrentsPage);
		$loading.show();
		//$result.html("loading..");


		var tmpLoad = $tmp.load(torrentsPage+" .pageContainer:first", function(response, status, xhr)
		{
			if (status == "error")
			{
				var msg = "A avut loc o erroare: ";
				$result.html(msg + xhr.status + " " + xhr.statusText);

				return;
			}
			var $v=$(this);
			var $table = $v.find('.tableTorrents');
			var $p = $table.next('p');

			//number of torrents
			var nb = 0;
			var $atag = $p.find('a:last').prevAll('a:first');
			if($atag.length>0)
			{
				nb = $atag.find('b').html().replace(/.*-&nbsp;/,'');
			} else
				nb = $table.find('tr:not(:first)').length;

			if(false)
			{
				$table.find('a[href]').each(function(i,v)
				{
					$v = $(v);
					$v.attr('href', '/'+$v.attr('href'));
				});
			}

			$table = ($table.length<1)?$v.find('h2'):$table;

			$result.html('');
			$resEcho.fadeOut(0).html(nb).fadeIn(200).parent().addClass('show');
			$result.append($p.clone(), $table, $p);
			$loading.hide();
		});

	}


	var curDep=[];
	function doDepTest()
	{
		if(!options.useSmartFilters)
		{
			$ourMegaCat.find('.hideSlow').show(options.animationSpeed).removeClass('hideSlow');
			return;
		}
		//$v = $(this);

		//selectam toate span-urile selectate
		var $v = $ourMegaCat.find('.selected');

		l('dep>>>>', $v, $v.parents('li:not(.skip)'), $v.hasClass('selected'));

		//dep list..
		//cautam parintii LI care au data-dep
		var dep = $v.parents('li:not(.skip)').dep();

		//var show = $v.hasClass('selected');

		curDep = curDep.concat(dep)._unique();

		//reset if no dep selected
		if(!dep.length) curDep = [];


		l('Dep>>>>>>', dep, curDep);

		var $gCatSelected = $gCat.find('.selected');
		var isAnygCatSelected = $gCatSelected.length;
		var gCatSelectedArray = $gCatSelected.parent().dep();

		l('Testing gCat', isAnygCatSelected, gCatSelectedArray);

		//$ourMegaCat.find('li:not(.skip)').each(function(i,v)
		$ourMegaCat.find('li').each(function(i,v)
		{
			//l(i,v);
			//.not('[data-dep*=",'+v+',"]')
			if($(v).hasClass('skip'))
				return;

			var arr = $(v).dep();
			//l(i,v);
			var found = false;

			if(isAnygCatSelected)
				$.each(gCatSelectedArray, function(ii,vv)
				{
					found = $.inArray(vv,  arr)>-1;
					return !found;
				});
			else
				$.each(curDep, function(ii,vv)
				{
					//l('curDepF=>',v, ii, vv, $.inArray(vv,  arr)>-1);
					found = $.inArray(vv,  arr)>-1;
					return !found;
				});
			//l(i,v, found);


			if(!found && curDep.length)
				$(v).hide(options.animationSpeed, function(){$(this).addClass('hideSlow');});
			else
				$(v).show(options.animationSpeed).removeClass('hideSlow');
		});


	}


	$informer.find('.mesg.resultEcho > i').click(function scrollToResultTable()
	{
		var scrollTop = $result.offset().top;
		$('html:not(:animated),body:not(:animated)').animate({scrollTop : scrollTop-80}, options.animationSpeed).animate({scrollTop : scrollTop-10},1000);
	});

	//#searchByTitle
	//buildFilterList();
	var searchVal=browseSearchVal;
	function initSearchVal()
	{
		$('#searchByTitle').val(searchVal).change(function()
		{
			searchVal = $(this).val();
			l('#searchByTitle=>', searchVal);
			buildFilterList();
		}).change();
	};
	initSearchVal();




	/*
	 options function
	 */
	l('op',store.get('browseFilters'), options);
	var $optionImg = $ourMegaCat.find('img.options');
	var $optionDiv = $parent.find('div.options').fadeOut();

	$optionImg.click(function clickOnOptionImg() {
		var $v=$(this).toggleClass('active');
		$optionDiv.fadeToggle(200, function() {
			function bindDocumentHideOpt() {
				$(document).one('click', function hideOptionDiv(e) {
					if($optionDiv.is(':visible') && !$(e.target).hasClass('options')) {
						l(e, $(e.target), $(e.target).attr('class'), $(e.target).hasClass('options'), $(e.target).parents('div.options'), $(e.target).parents());
						$v.click();
					} else bindDocumentHideOpt();
				});
			}
		});
	});

	$optionDiv.find('input').each(function(i,v) {
		var id = $(v).attr('id');

		if(options[id]==true)
			$(v).next('.checkbox').addClass('checked');

		$(v).attr('checked', options[id]).change(function()
		{
			$(v).next('.checkbox').toggleClass('checked');

			options[id] = ($(v).attr('checked'))?true:false;

			options.update();
		});
	});

	function isAnyTrigger(show)
	{
		$onAnyTrigger = $parent.find('.onAnyTrigger:not(.dHide)');

		if(show)
			$onAnyTrigger.fadeIn(1);
		else
			$onAnyTrigger.fadeOut(1);
	}

	$('.addFilters').click(function() {
		$('h1.search_str').hide();
		var $v = $(this).hide();
		var $filters = $v.next().show();


		var $hide = $v.prev('.hideFilters').show().click(function clickedOnHideFilters()
		{
			$v.show();
			$filters.hide();
			$(this).hide();
		});

		var c = $ourMegaCat.offset();
		var lineTop = c.top;

		$hide.css({left: (c.left-$hide.width()*1.5), top: (lineTop+20)  });

	});

	if(browseCategTags) {
		reBuildCategs(browseCategTags);
		browseCategTags=null;
	}

	//parser pentru cei ce nu stiu de html5
	if(WL.hash && WL.hash.search(/(search_str)|(categtags)/)>-1) {
		window.location = WL.pathname + WL.hash.replace(/#/,'?');
	}

	function reBuildCategs(c) {
		var cats = c.split(',');
		l('aveam categorii', cats);
		$.each(cats, function(i,v)
		{
			var $span = $ourMegaCat.find('li[data-id="'+v+'"]').children('span');
			var $chBox = $span.find('.checkbox');

			if($chBox.length)
				$chBox.click();
			else
				$span.click();
		});

	}

	function showOnlygCat(s)
	{
		return;
		l("showOnlygCat=> val:", s, '+browseCategTags', o.browseCategTags==true);
		if(!o.browseCategTags)
			return;

		if(s)
			$ourMegaCat.find('>ul> li:not(.gCat)').addClass('onAnyTrigger');
		else
			$ourMegaCat.find('>ul> li:not(.gCat)').removeClass('onAnyTrigger');
	}

	globalLocation.init();

	function sortOrderBy() {
		var $v = $(this);
		var c = $v.data('url');

		globalLocation.q[c] = $v.val();
		var url = globalLocation.url();

		l($v.data('url'), $v.attr('id'), $v.val(), url, WL);

		buildFilterList();

		changeUrl(url);
	}
	$sort = $('#sortby').change(sortOrderBy);
	$order = $('#orderby').change(sortOrderBy);

	if(browseSortBy)
	{
		$sort.val(browseSortBy);
	}

	if(browseOrderBy)
	{
		$order.val(browseOrderBy);
	}
};

function fixFlashOnChrome() {
	var $ = jQuery;
	if(/chrome/.test(navigator.userAgent.toLowerCase())) {
		$('object').each(function() {
			$(this).css('display','block');
		});
	}
}

jQuery(document).ready(function()
{
    initSpoilers();
    initIurl();

    jQuery.tmdBBCodes();
    fixFlashOnChrome();
});