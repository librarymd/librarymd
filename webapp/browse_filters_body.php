<?php 
require_once $WWW_ROOT.'moder/categ_sense/global_conf.php';

require_once $WWW_ROOT.'moder/categ_sense/include.php';


function search_show_sense_categories($id,$categs,$array_key='id',$array_value='name')
{
	global $categs_lang;
	if (!isset($categs[$id])) return;

	if($id !=='root')
	    echo '<ul data-id="'.$id.'">';

	foreach($categs[$id] AS $categ)
	{
		if(isset($categ['visible']) && $categ['visible']=='no')
			continue;


		if($categ['id'] !== 0)
		    echoe('<li data-name="%s" data-id="%s" data-checkable="%s" data-dep=",%s,"><span>%s</span>', $categ[$categs_lang], $categ['id'], $categ['checkable'], $categ['dependendOnCategTagCSV'], $categ[$categs_lang]);
			
		search_show_sense_categories($categ[$array_key],$categs);
			
		if($categ['id'] !== 0)
		    echo '</li>';
	}
	if($id !=='root')
	    echo '</ul>';
}

?>
<script>
jQuery(document).ready(function()
{
	//l.logging=true;
	browseGCatVariable = '<?php echo esc_html($browseGCatVariable); ?>';
	browseRequirebBySearch = <?php echo (@$useSearchInFiltersBody?@$useSearchInFiltersBody:'false'); ?>;
	browseSearchVal = '<?php echo esc_html(@$_GET['search_str']); ?>';
	browseCategTags = '<?php echo esc_html(@$_GET['categtags']); ?>';
	browseSortBy = '<?php echo esc_html(@$_GET['sort']); ?>';
	browseOrderBy = '<?php echo esc_html(@$_GET['order']); ?>';
	jsForBrowseFilters(jQuery);
});
</script>

<div id="browse_filters" class="<?php echo (@$useSearchInFiltersBody?'insearch':''); ?>">
<!--  
  <div class="pagePurpose boxes">(<b>beta</b>) <?php echo __('Filtrarea torrentelor după mai multe criterii')?>.</div>
-->
	<div class="ins"></div>
	<div class="hideFilters boxes bxShadow"><?php echo __('Ascunde');?></div>
	<div class="addFilters boxes bxShadow"><?php echo __('Arată filtrarea avansată');?></div>
	<div class="prnt">
		<div class="options unselectable boxes">
			<label class="tran"><input type="checkbox" id="isAnimationEnabled" /><div class="checkbox"><div class="checked">√</div></div> <?php echo __('Folosește animații');?></label>
			<label class="tran"><input type="checkbox" id="keepCategoriesExpandable" /><div class="checkbox"><div class="checked">√</div></div> <?php echo __('Ține categoriile deschise');?></label>
			<label class="tran"><input type="checkbox" id="useSmartFilters" /><div class="checkbox"><div class="checked">√</div></div> <?php echo __('Filtrarea deșteaptă');?></label>
			<!-- <label class="tran"><input type="checkbox" id="hideShareURL" /><div class="checkbox"><div class="checked">√</div></div> <?php echo __('Ascunde tabelul de "Împărtășire"');?></label>-->
		</div>
		<div align="left" class="ourMegaCat unselectable boxes">
			<div class="mesg"><?=__('Categorii')?>:<img class="options tran" src="/pic/options.png" /></div>
<?php	
	$rows = _get_all_catetags();
	

	$rows = array_merge($default_categs,$rows);
		
	// Search for father 0, and then recursive..
		
	$categs_org = array_set_index($rows,'father');
	
	$categs_lang = 'name_'.get_lang();
	
	//print_r($categs_org);
	//încercăm să rescriem așa cum ne-ar trebuie nouă..
	search_show_sense_categories('root',$categs_org);		
?>
		</div>
		<div class="titlesearch boxes rightbox">
			<div class="mesg"><?=__('Denumirea (opțional)')?>:</div>
			<div>
				<input class="input tran" type="search" id="searchByTitle" placeholder="<?php echo __('Nume torrent');?>...">
			</div>
		</div>
<!--		<div class="shareUrl boxes rightbox onAnyTrigger">
			<div class="mesg"><?/*=__('Adresa pentru a împărtăși rezultatul generat de Dumneavoastre')*/?>:</div>
			<div  class="input">
				<input type="input" id="url" class="tran" readonly="readonly" value="">
			</div>
		</div>-->
		<div class="informer boxes rightbox onAnyTrigger">
			<div class="mesg"><?=__('Căutăm torentele ce satisfac următoarele cerințe')?>:</div>
			<div class="filters"></div>
			<div class="mesg resultEcho"><?php echo __('După criteriile selectate am găsit <span>X</span> torrente, <i>vezi aici</i>');?></div>
			<div class="mesg sort"><?=__('Sortează după')?>
				<select id="sortby" data-url="sort" class="sortby" data-placeholder="sort by">
					<option value="date"><?php echo __('dată') ?></option>
					<option value="name"><?php echo __('denumire') ?></option>
					<option value="peers"><?php echo __('peers') ?></option>
				</select>

				<select id="orderby" data-url="order" class="sortby" data-placeholder="order by">
					<option value="dsc"><?php echo __('descrescător') ?></option>
					<option value="asc"><?php echo __('crescător') ?></option>
				</select>
			</div>
		</div>
		<img class="picResultLoader" src="/pic/loading.gif" />
		<div style="clear: both;"></div>
	</div>
  <div style="clear:both"></div>
	
	<div class="result" id="result"><?php if ($useSearchInFiltersBody && $count)
	{
		print($pagertop);
		torrenttable($res,'','search',$addparam);
		print($pagerbottom);
	}?></div>
	<div class="tmpresultload" style="display:none;"></div>
</div>