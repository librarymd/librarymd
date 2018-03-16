//Cookies functions
//Parms: name=var_name value=var_value days=after x days cookies will expire
function createCookie(name,value,days)
{
	if (!days) days = 100; //default 100 days
	if (days)
	{
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}
function readCookie(name)
{
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++)
	{
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

/**
  Get Element, return element object pointer
**/
function _ge(id) {
    return document.getElementById(id);
}
function $(id) {
    return document.getElementById(id);
}

function _ge_by_name(name) {
    return document.getElementsByName(name)[0];
}

//This function will change ids Classes to hideit
function hide_it() {
	arg=hide_it.arguments;
	for(i=0;i<arg.length;i++) {
		_z = _ge(arg[i]);
        if (_z) _z.className='hideit';
	}
}

//This function will change ids Classes to showit
function show_it() {
	arg=show_it.arguments;
	for(i=0;i<arg.length;i++) {
		_z = _ge(arg[i]);
		if (_z) _z.className='showit';
	}
}

/**
  This function will disable a button
  @arg1,@arg2,argN - must be id of the buttons
**/
function disable_input() {
	arg=disable_input.arguments;
	for(i=0;i<arg.length;i++) {
		if(typeof(arg[i]) == 'string') _z = _ge(arg[i]);
		else if (typeof(arg[i]) != null) _z = arg[i];
		     else return;
		if (_z && typeof(_z.disabled) == 'boolean') {
			if (_z.type && _z.type == 'text') _z.style.backgroundColor='#D4D0C8';
			_z.disabled=true;
		}
	}
}
/**
  This function will enable a button
  @arg1,@arg2,argN - must be id of the buttons
**/
function enable_input() {
	arg=enable_input.arguments;
	for(i=0;i<arg.length;i++) {
		if(typeof(arg[i]) == 'string') _z = _ge(arg[i]);
		else if (typeof(arg[i]) != null) _z = arg[i];
		     else return;
		if (_z && typeof(_z.disabled) == 'boolean') {
			if (_z.type && _z.type == 'text') _z.style.backgroundColor='white';
			_z.disabled=false;
		}
	}
}
/**
SECTION FOR LIST DOWN DIV
**/
//Determine Heightoffset..etc.., borrowed from Marco
//Functions.. Lib
function getOffsetHeight(layer)
{
	var value = 0;
	value = _ge(layer).offsetHeight;
	return (value);
}

function getOffsetTop(layer)
{
	var value = 0;
	object = _ge(layer);
	value = object.offsetTop;
	while (object.tagName != 'BODY' && object.offsetParent) {
		object = object.offsetParent;
		value += object.offsetTop;
	}
	return (value);
}

function getOffsetLeft(layer)
{
	var value = 0;
		object = _ge(layer);
		value = object.offsetLeft;
		while (object.tagName != 'BODY' && object.offsetParent) {
			object = object.offsetParent;
			value += object.offsetLeft;
		}
	return (value);
}

function setLeftTop(layer, x,y)
{
	_ge(layer).style.left = x + 'px';
	_ge(layer).style.top = y + 'px';
}

function addEvent(target,eventName,handlerName) { 
  //if ( target.addEventListener ) { 
    //target.addEventListener(eventName, eval(handlerName), false);
  //} else if ( target.attachEvent ) { 
  	var oldevent = eval('target.on'+eventName+'='+handlerName);
  	//alert(oldevent);
  //}
}

//On IE this function dosen't not support "this", it's harmful, use carefully
function _not_this_addEvent(obj, evType, fn, useCapture){
  if (obj.addEventListener){
    obj.addEventListener(evType, fn, useCapture);
    return true;
  } else if (obj.attachEvent){
    var r = obj.attachEvent("on"+evType, fn);
    return r;
  }
}

function _not_this_removeEvent(obj, evType, fn, useCapture){
  if (obj.removeEventListener){
    obj.removeEventListener(evType, fn, useCapture);
    return true;
  } else if (obj.detachEvent){
    var r = obj.detachEvent("on"+evType, fn);
    return r;
  }
}


function _stop_e_Propagation(eventobj)
{
	if (!eventobj || browser.isIE)
	{
		window.event.returnValue = false;
		window.event.cancelBubble = true;
		return window.event;
	}
	else
	{
		eventobj.stopPropagation();
		eventobj.preventDefault();
		return eventobj;
	}
}

// Browser Detect  v2.1.6
// code by Chris Nott (chris[at]dithered[dot]com)

function BrowserDetect() {
   var ua = navigator.userAgent.toLowerCase(); 
   // browser engine name
   this.isGecko       = (ua.indexOf('gecko') != -1 && ua.indexOf('safari') == -1);
   this.isAppleWebKit = (ua.indexOf('applewebkit') != -1);
   // browser name
   this.isOpera       = (ua.indexOf('opera') != -1); 
   this.isIE          = (ua.indexOf('msie') != -1 && !this.isOpera && (ua.indexOf('webtv') == -1) ); 
}
var browser = new BrowserDetect();

/**
   This function will return function name
   @f - function what's name we need
**/
function get_function_name(f) {
	var str = f.toString();
    var name = str.split ('(')[0];
    name = name.split (/[' '{1,}]/)[1];
    return(name);
}

/**
   Down menu div by Adrenalin..2005..
   This is mine, don't copy
**/
function _dropdown_div(divid,father,excit,var_name) {
	//Define the vars
	this.id = divid;
	this.parrent = father;
	this.excitator = excit; //On mouse down at this element, id under parrent should appear
    this.react_on_focus = true; //On focus, excite it too
	this.var_obj_name = var_name; //You think i'm crazy ? Bwhaha, maybe :)
	                              //Some times we need pass the object var to can use "this"..
	                              //If we'll get just copy of this.function, we'll can't acces this.some_var or this.func..
    
	this.currentactive = false;
	this.hide = function() {
	  if (this.currentactive == false) return;
	  _ge(this.id).style.visibility='hidden';
	  this.currentactive = false;
	};
	this.show = function() {
	  _ge(this.id).style.visibility='visible';
	  this.currentactive = true;
	};
	this.onclick = function(e) {
	   e = _stop_e_Propagation(e);
	   if (this.currentactive == true) { //On click to show, if is current active, hide
	     this.hide();
	     return;
	   } else {
	     this.show();
	   }
	};
	this.on_document_click = function(e) {
		if (browser.isIE) {
			event_id = window.event.srcElement.id;
			event_obj = window.event.srcElement;
		} else {
			event_id = e.target.id;
			event_obj = e.target;
		}
		//Cases when div shouldn't be hidded
		//Verify all family tree ids, if someNode.id=this.id, then div shouldn't be hidded
		t = event_obj;
		while(typeof(t.parentNode.id) == 'string') {
			if (t.parentNode.id == this.id) {
				return;
			}
			t = t.parentNode;
		}
		if (event_id == 'null' || event_id == this.id) {
			return;
		}
		this.hide();
	};
	this.init = function() {
		if (_ge(this.id) == null) return;
	    this.apply_styles();
	    this.align();
        this.set_onclick_document_handler();
	    this.set_onclick_excitator_handler();
	};
	//On click the parent button to show the div
	this.set_onclick_excitator_handler = function() {
		var p = _ge(this.excitator);
		if (p == 'null') { 
		  return;
		}
		var f = 'function(e) { ' + this.var_obj_name + '.onclick(e); }';
		if (typeof(this.excitator) != 'string') { //If it's array
		  var i = 0;
		  if(this.react_on_focus)addEvent($(this.excitator[0]),'focus',f);
		  for(i=0;i<this.excitator.length;i++) {
		  	  addEvent($(this.excitator[i]),'mousedown',f);
		  }
	    } else {
		  addEvent(p,'mousedown',f);
		  if(this.react_on_focus && this.excitator != this.parrent)addEvent($(this.excitator),'focus',f);
		}
	};
	//On click anywhere on the document
	//Take away the div at any document clicks
	this.set_onclick_document_handler = function() {
		//You ask why don't just use function..blabla instead of this.. ?
		//Just's because we can't write function.. this.var..+'.hide..
		var f = 'function(e) { ' + this.var_obj_name + '.on_document_click(e); }';
		addEvent(document,'mousedown', f );
	};
    //Set styles for child: invisible,absolute,zindex..
	this.apply_styles = function() {
		_ge(this.id).style.position = 'absolute';
		_ge(this.id).style.visibility = 'hidden';
		_ge(this.id).style.zindex = 100;
	};
	//Align at the bottom of father
	this.align = function() {
	  var fatherTOP = getOffsetTop(this.parrent);
	  var fatherLEFT = getOffsetLeft(this.parrent);
	  var fatherHEIGHT = getOffsetHeight(this.parrent);
	  setLeftTop(this.id,fatherLEFT,fatherTOP + fatherHEIGHT);
	};
	
	//Zavodim :)
	this.init();
}

// xLoadScript, Copyright 2001-2005 Michael Foster (Cross-Browser.com)
function xLoadScript() {
  var arg=xLoadScript.arguments;
  var url=arg[0];
  if(arg.length>1)var onload = arg[1];
  if (document.createElement && document.getElementsByTagName) {
    var s = document.createElement('script');
    var h = document.getElementsByTagName('head');
    if (s && h.length) {
      s.src = url;
      //Unfortunately this work only under FF.. FCK OTHER BROWSERS
      //if(typeof(onload)) _not_this_addEvent(s,'load',onload);
      h[0].appendChild(s);
    }
  }
}

//In depenedence of browser return the event source object
function get_object_from_event(e) {
	var event_obj;
	if (browser.isIE) {
		event_obj = window.event.srcElement;
	} else {
		event_obj = e.target;
	}
	return event_obj;
}

/*
  $F
  returns the value of any field input control
  the idea/name borrowed from prototype
  
  @name - name, (not id!)
*/
function $F(name) {
	o = _ge_by_name(name);
	if(o.nodeName.toLowerCase() == 'select') return o.options[o.selectedIndex].text;
	return o.value;
}

function node_getElementById(node,to_find_id) {
	tags = node.getElementsByTagName('a');
	for(var i=0;i<tags.length;i++) {
		if(tags[i].id && tags[i].id == to_find_id) return tags[i];
	}
	return null;
}

//v.1
function _loading_img() {
	this.loading = 0;
	this.show = function() {
		this.preload(); //preload if need
		$('loading_pic').style.top = (7 + document.body.scrollTop) + 'px';
		$('loading_pic').style.left = (document.body.clientWidth - 25) + 'px';
		show_it('loading_pic');
		this.loading = 1;
	};
	this.hide = function() {
		hide_it('loading_pic');
		this.loading = 0;
	};
	this.preload = function() {
		if ($('loading_pic') != null) {
			return;
		}
		this.load();
	};
	this.load = function() {
		var b = document.getElementsByTagName('body');
		var i = document.createElement('img');
		i.id = 'loading_pic';
		i.src = './pic/loading_.gif';
		i.className = 'hideit';
		i.style.position = 'absolute';
		b[0].insertBefore(i,b[0].childNodes[0]);
	};
	this.is_loading = function() {
		return this.loading;
	};
}

function ctrlEnter_sensor(_1){try{if((_1.ctrlKey)&&((_1.keyCode==10)||(_1.keyCode==13))){var o=get_object_from_event(_1);while(o!=null){if(o.tagName.toLowerCase()=="form"){if(o.id=="noenter"){return;}if (o.onsubmit && o.onsubmit() == false) {return;}o.submit();return;}o=o.parentNode;}return;}}catch(e){}}
_not_this_addEvent(document,'keypress',function(e){ctrlEnter_sensor(e);});

function fireEvent(obj, eventType) {
	// Must be a IE client
	if (!document.createEvent) {
		obj.fireEvent('on'+eventType);
		return;
	}
	var e = document.createEvent('HTMLEvents');
	e.initEvent(eventType, true, true);
	obj.dispatchEvent(e);
}

//ajax
function getXMLobj(){req=false;if(window.XMLHttpRequest){try{req=new XMLHttpRequest();}catch(e){req=false;}}else{if(window.ActiveXObject){try{req=new ActiveXObject("Msxml2.XMLHTTP");}catch(e){try{req=new ActiveXObject("Microsoft.XMLHTTP");}catch(e){req=false;}}}}if(req){return req;}else{return false;}}

//This function will load dest url and will send the result to handler
//Example of use loadurl(url,handler), handler must take 
function post_data(dest,string,handler,noLoadingImg) {
	user_f_loadurl = handler;
    xmlhttp = getXMLobj();
    xmlhttp.onreadystatechange = function(){loadurl_response();};
    xmlhttp.open("POST", dest,true);
    xmlhttp.setRequestHeader( "Content-Type" , "application/x-www-form-urlencoded; charset=UTF-8" );
    xmlhttp.send(string);
    //Make loading icon visible
    if (typeof(loading_img) == 'undefined' && noLoadingImg != true) {
    	loading_img = new _loading_img();
    	loading_img.preload(); 
    }
    if (noLoadingImg != true) {
		loading_img.show();
	}
}

function get_data(dest,string,handler) {
	user_f_loadurl = handler;
    xmlhttp = getXMLobj();
    xmlhttp.onreadystatechange = function(){loadurl_response();};
    xmlhttp.open('GET', dest + '?' + string, true);
    xmlhttp.setRequestHeader("Content-Type" , "application/x-www-form-urlencoded; charset=UTF-8" );
    xmlhttp.send(null);
	
    //Make loading icon visible
    if (typeof loading_img == 'undefined' || loading_img == null) {
    	loading_img = new _loading_img();
    	loading_img.preload(); 
    }
	loading_img.show();
}


function loadurl_response() {
    if (xmlhttp.readyState == 4) {
    	if (typeof loading_img != 'undefined') loading_img.hide();
    	try {
    		user_f_loadurl(xmlhttp.responseText,xmlhttp.status,xmlhttp.statusText);
    	} catch(e) {}
    }
}

function getSel(){var txt='';if(window.getSelection){txt=window.getSelection();foundIn='window.getSelection()';}else if(document.getSelection){txt=document.getSelection();}else if(document.selection){txt=document.selection.createRange().text;}else return;return txt;}

function citeaza(obj) {
	var to = $('posttext');
	var nick = obj.nextSibling.nextSibling.childNodes[0].childNodes[0].data;
	var id = obj.childNodes[0].data + ' ' + nick + ', ';
	var quote = getSel();
	if (quote && quote != '') id = id + '"... ' + quote + ' ..." - ';
	
	if (to.value!='') {
		var id = "\n\n" + id;
	}
	to.focus();
	to.value += id;
}

function iif(bool_expression,str_true,str_false) {
	if (bool_expression) return str_true;
	else return str_false;
}

function getElementsByClass(searchClass,node,tag) {
	var classElements = new Array();
	if ( node == null )
		node = document;
	if ( tag == null )
		tag = '*';
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
	for (i = 0, j = 0; i < elsLen; i++) {
		if ( pattern.test(els[i].className) ) {
			classElements[j] = els[i];
			j++;
		}
	}
	return classElements;
}

function _removeNode(node) {
	if (node == undefined) return false;
	return node.parentNode.removeChild(node);
}