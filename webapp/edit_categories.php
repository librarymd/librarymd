<?php
require_once("include/bittorrent.php");
require_once($INCLUDE . 'torrent_opt.php');
require_once($WWW_ROOT . 'moder/categ_sense/include.php');
require_once($WWW_ROOT . 'moder/categ_sense/global_conf.php');

require_once($WWW_ROOT . 'include/categtags/torrent_edit_inc.php');
require_once($WWW_ROOT . 'include/categtags/torrent_edit_tags_do.php'); // Will check for POST and will do any required actions

loggedinorreturn();

mkglobal2('id:req:int','get');
$table = 'torrents_catetags';

$torrent = fetchRow(
		 "SELECT torrents.name, torrents.owner, torrents.category, torrents.moder_status
          FROM torrents
          WHERE torrents.id = :id", array('id'=>$id)
);

if (!$torrent) {
	barkk('Torrent inexistent');
}

if (tags_allow_edit_torrent($torrent) !== true) {
	barkk(__('Nu sunteți proprietarul acestui torrent'));
}

stdhead(__('Editarea tag-urilor') . ' "' . $torrent["name"] . '"');

echo '<h2 style="display:inline;">'.__('Editarea tag-urilor').':</h2> ' . $torrent["name"] . '<br><br>';

echo '<a href="?id='.$id.'&regenerate=1">Pornește</a> detectorul automat de categorii (va reseta categoriile setate manual)<br/><br/>';

if (isset($_GET['regenerate'])) {
  torrentCategsAutodetect($id);
  echo __("Regenerarea a fost executată cu succes")."<br/><br/>";
}

begin_main_frame();
?>
<form method="POST">
<table width=990 align=center border=0>
<tr><td align=left>
<div style="float:left;width:220px">

<input type="hidden" name="id" value="<?=esc_html($id)?>">
<?php
//var_dump(_get_all_catetags());die();
$selected_tags = fetchColumn(
	"SELECT torrents_catetags_index.catetag
	FROM torrents_catetags_index
	LEFT JOIN torrents_catetags ON torrents_catetags.id = torrents_catetags_index.catetag
	WHERE torrent=:id AND torrents_catetags.visible='yes'",array('id'=>$id));
echo form_element('categtag_list','selectMultiple','',$selected_tags,array('id','name',_get_all_categories_sense('yes')), array('size'=>10) );
?>
</div>
<div style="margin-left:250px;" id="tagsGolf" class="lottext">
<div>
	<?=__('După ce selectați toate tag-urile, apăsați pe acest buton')?>: <input type="submit" value="<?=__('Salvează')?>">
</div>
<div>
	<?=__('Taguri')?>:<br><br>
</div>
</div>
</td></tr></table>
</form>
<?php
end_main_frame();
stdfoot();


function form_element_adapterSelectMultiple($name,$type,$label,$value='',$select='',$opt='') {

	$last_tree_count = 0;
	foreach($select[2] AS $row) {
		if ($row[$select[0]] == 0) continue;

		$is_checked = ($value!=''&&in_array($row[$select[0]],$value))?' checked ':'';

		$current_tree_count = substr_count($row[$select[1]],'--');
		$delta = $last_tree_count - $current_tree_count;

		$row[$select[1]] = substr($row[$select[1]],2);
		$row[$select[1]] = str_replace('--','',$row[$select[1]]);

		if ($row['checkable'] == 'yes') {
			$context = '<label><input type="checkbox" class="checkbox" name="'. $name .'[]" value="'. $row[$select[0]] .'"'. $is_checked .'>'. $row[$select[1]] .'</label>';
		} else
			$context = '<span>'. $row[$select[1]] .'</span>';


		if ( $current_tree_count > $last_tree_count ) {
			echo '<ul><li>'. $context;
		} elseif ( $current_tree_count == $last_tree_count ) {
			echo '</li><li>' . $context;
		} else {
			echo str_repeat('</li></ul>', $delta) . '</li><li>'.$context;
		}

		$last_tree_count = $current_tree_count;
	}

	echo str_repeat('</li></ul>', $last_tree_count);

}

?>

	<script type="text/javascript">
(function(a){a.extend(a.fn,{swapClass:function(a,b){var c=this.filter("."+a);this.filter("."+b).removeClass(b).addClass(a);c.removeClass(a).addClass(b);return this},replaceClass:function(a,b){return this.filter("."+a).removeClass(a).addClass(b).end()},hoverClass:function(b){b=b||"hover";return this.hover(function(){a(this).addClass(b)},function(){a(this).removeClass(b)})},heightToggle:function(a,b){a?this.animate({height:"toggle"},a,b):this.each(function(){jQuery(this)[jQuery(this).is(":hidden")?"show":"hide"]();if(b)b.apply(this,arguments)})},heightHide:function(a,b){if(a){this.animate({height:"hide"},a,b)}else{this.hide();if(b)this.each(b)}},prepareBranches:function(a){if(!a.prerendered){this.filter(":last-child:not(ul)").addClass(b.last);this.filter((a.collapsed?"":"."+b.closed)+":not(."+b.open+")").find(">ul").hide()}return this.filter(":has(>ul)")},applyClasses:function(c,d){this.filter(":has(>ul):not(:has(>a))").find(">span").unbind("click.treeview").bind("click.treeview",function(b){if(this==b.target)d.apply(a(this).next())}).add(a("a",this)).hoverClass();if(!c.prerendered){this.filter(":has(>ul:hidden)").addClass(b.expandable).replaceClass(b.last,b.lastExpandable);this.not(":has(>ul:hidden)").addClass(b.collapsable).replaceClass(b.last,b.lastCollapsable);var e=this.find("div."+b.hitarea);if(!e.length)e=this.prepend('<div class="'+b.hitarea+'"/>').find("div."+b.hitarea);e.removeClass().addClass(b.hitarea).each(function(){var b="";a.each(a(this).parent().attr("class").split(" "),function(){b+=this+"-hitarea "});a(this).addClass(b)})}this.find("div."+b.hitarea).click(d)},treeview:function(c){function h(){var b=a.cookie(c.cookieId);if(b){var d=b.split("");i.each(function(b,c){a(c).find(">ul")[parseInt(d[b])?"show":"hide"]()})}}function g(){function b(a){return a?1:0}var d=[];i.each(function(b,c){d[b]=a(c).is(":has(>ul:visible)")?1:0});a.cookie(c.cookieId,d.join(""),c.cookieOptions)}function f(){a(this).parent().find(">.hitarea").swapClass(b.collapsableHitarea,b.expandableHitarea).swapClass(b.lastCollapsableHitarea,b.lastExpandableHitarea).end().swapClass(b.collapsable,b.expandable).swapClass(b.lastCollapsable,b.lastExpandable).find(">ul").heightToggle(c.animated,c.toggle);if(c.unique){a(this).parent().siblings().find(">.hitarea").replaceClass(b.collapsableHitarea,b.expandableHitarea).replaceClass(b.lastCollapsableHitarea,b.lastExpandableHitarea).end().replaceClass(b.collapsable,b.expandable).replaceClass(b.lastCollapsable,b.lastExpandable).find(">ul").heightHide(c.animated,c.toggle)}}function e(c,d){function e(d){return function(){f.apply(a("div."+b.hitarea,c).filter(function(){return d?a(this).parent("."+d).length:true}));return false}}a("a:eq(0)",d).click(e(b.collapsable));a("a:eq(1)",d).click(e(b.expandable));a("a:eq(2)",d).click(e())}c=a.extend({cookieId:"treeview"},c);if(c.toggle){var d=c.toggle;c.toggle=function(){return d.apply(a(this).parent()[0],arguments)}}this.data("toggler",f);this.addClass("treeview");var i=this.find("li").prepareBranches(c);switch(c.persist){case"cookie":var j=c.toggle;c.toggle=function(){g();if(j){j.apply(this,arguments)}};h();break;case"location":var k=this.find("a").filter(function(){return this.href.toLowerCase()==location.href.toLowerCase()});if(k.length){var l=k.addClass("selected").parents("ul, li").add(k.next()).show();if(c.prerendered){l.filter("li").swapClass(b.collapsable,b.expandable).swapClass(b.lastCollapsable,b.lastExpandable).find(">.hitarea").swapClass(b.collapsableHitarea,b.expandableHitarea).swapClass(b.lastCollapsableHitarea,b.lastExpandableHitarea)}}break}i.applyClasses(c,f);if(c.control){e(this,c.control);a(c.control).show()}return this}});a.treeview={};var b=a.treeview.classes={open:"open",closed:"closed",expandable:"expandable",expandableHitarea:"expandable-hitarea",lastExpandableHitarea:"lastExpandable-hitarea",collapsable:"collapsable",collapsableHitarea:"collapsable-hitarea",lastCollapsableHitarea:"lastCollapsable-hitarea",lastCollapsable:"lastCollapsable",lastExpandable:"lastExpandable",last:"last",hitarea:"hitarea"}})(jQuery);

	(function($){
		$(document).ready(function() {

			// make sure labels are drawn in the correct state

			$('ul.treeview label').each(function()
			{
				// credits: http://www.joelanman.com/archives/11
				if ($(this).find(':checkbox').attr('checked'))
					$(this).addClass('selected').each(function(){
						tagsAdd(this);
					});
			});

			// toggle label css when checkbox is clicked

			$('ul.treeview').click(function(e)
			{
				var c = $(e.target);
				if (c.filter('input').length == 0) return;
				var checked = c.attr('checked');
				c.parents('label').toggleClass('selected', checked);
				if (checked) tagsAdd(c.parents('label')[0]);
				else tagsRemove(c.parents('label')[0]);
			});

			function getTagHierarcy(start,text) {
				if (text == '')	text = start.text();
				else text = start.text()+'->'+text;

				if (start.parents('ul').length == 1) return text;
				return getTagHierarcy( start.parents('ul:first').prev('label,span'), text );
			}
			// elm is a label
			function tagsAdd(elm) {
				var trace = getTagHierarcy($(elm), '');
				$('#tagsGolf').append( '<div id="selected_tag_'+$(elm).children('input').val()+'">' + trace + '</div>' );
			}
			function tagsRemove(elm) {
				$('#selected_tag_'+$(elm).children('input').val(),'#tagsGolf').remove();
			}
		});

		$('ul:first').treeview({
			collapsed: true,
			animated: "fast",
			persist: "location"
		});;
	})(jQuery);
	</script>


<style>
#multipleSelect li{
	margin-bottom:0pt;
	margin-top:0pt;
}
ul#multipleSelect{
	width:				220px;
	height:				400px;
	overflow-y:			auto;
	overflow-x:			hidden;
	list-style:			none;
	padding: 			0;
}

#multipleSelect li label{
	display:			block;
	//border-bottom:		1px solid transparent;
	margin-bottom:1px;
	padding:			4px 2px 2px 0px;
	color:				#000;
	outline: 			none;
	position:			relative;
}

#multipleSelect li label:hover {
	color:				#000;
	background-color:	#D4D1AC;
}

#multipleSelect li label.selected{
	color:				#FFF;
	background-color:	#A79F72;
}

#multipleSelect li label .checkbox{

}

.treeview, .treeview ul {
	padding: 0;
	margin: 0;
	list-style: none;
}

.treeview ul {
	margin-top: 4px;
}

.treeview .hitarea {
	background: url(./pic/treeview-default.gif) -64px -25px no-repeat;
	height: 16px;
	width: 16px;
	margin-left: -16px;
	float: left;
	cursor: pointer;
}
/* fix for IE6 */
* html .hitarea {
	display: inline;
	float:none;
}

.treeview li {
	margin: 0;
	padding: 3px 0pt 3px 16px;
}

.treeview a.selected {
	background-color: #eee;
}

#treecontrol { margin: 1em 0; display: none; }

.treeview .hover { color: red; cursor: pointer; }

.treeview li { background: url(./pic/treeview-default-line.gif) 0 0 no-repeat; }
.treeview li.collapsable, .treeview li.expandable { background-position: 0 -176px; }

.treeview .expandable-hitarea { background-position: -80px -3px; }

.treeview li.last { background-position: 0 -1766px }
.treeview li.lastCollapsable, .treeview li.lastExpandable { background-image: url(./pic/treeview-default.gif); }
.treeview li.lastCollapsable { background-position: 0 -111px }
.treeview li.lastExpandable { background-position: -32px -67px }

.treeview div.lastCollapsable-hitarea, .treeview div.lastExpandable-hitarea { background-position: 0; }

ul.treeview {margin-left: 10px}
</style>