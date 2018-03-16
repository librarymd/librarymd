<?php
include_once $GLOBALS['INCLUDE'].'functions_additional.php';
include_once $GLOBALS['INCLUDE'].'classes/categtag.php';
require_once($WWW_ROOT . 'moder/categ_sense/global_conf.php');

function title_header($name, $same_page_title=false) {
	global $_script_conf;
	if (isset($_script_conf['title'])) {
		stdhead($_script_conf['title'].' ::'.$name);
	}
	if ($same_page_title) {
		echoe('<div align="center"><h2><a href="index.php">%s</a> :: %s</h2></div>',$_script_conf['title'],$name);
	}
}

function script_get_id() {
	
}

function get_table_name() {
	global $_script_conf;
	return $_script_conf['table'];
}

function get_pk_name() {
	global $_script_conf;
	return $_script_conf['pk'];
}

// Column "name"
function get_column_name() {
	global $_script_conf;
	return $_script_conf['name'];
}

function _get_all_catetags_sql($visible='') {
  return CategTag::getAllCategTagsSql($visible);
}
function _get_all_catetags($visible='') {
	return CategTag::getAllCategTags($visible);
}
function _categtags_modified() {
	$sql = _get_all_catetags_sql();
	mem2_force_delete($sql);
	$sql = _get_all_catetags_sql('yes');
	mem2_force_delete($sql);
}
function _get_all_categories_sense_0($visible = '') {
	global $default_categs;
	
	$rows = _get_all_catetags();
	
	$rows = array_merge($default_categs,$rows);
	
	return array_set_index($rows,'father');
}
function _get_all_categories_sense($visible = '') {
	$categs_org = _get_all_categories_sense_0($visible);
	
	return _decorize_tree_names('root',$categs_org);
}

/**
* @param int tag_id
* @result
*/
function categtag_get_path($tagid) {

    

}

/*
	_decorize_tree_names
	Add -- to childrens
	It will generate a new array with keys $array_key and $array_value
	@param $father - id to start
	@param $categs - 
*/

function _decorize_tree_names($father,$categs,$level='',$array_key='id',$array_value='name') {
	/*
		<ul>
	<li>Index <></i>*/
	$res = array();
	if (!isset($categs[$father])) {
		return $res;
	}
	
	//if ($level != '') $level=substr($level,2); // ugly hack
	
	foreach($categs[$father] AS $categI=>$categV) {
		$res[] = array( $array_key => $categV[$array_key], $array_value => $level . $categV[$array_value], 'checkable'=>@$categV['checkable'] );
		
		$res_level = _decorize_tree_names($categV['id'],$categs,$level.'--');
		$res = array_merge($res,$res_level);
	}
	return $res;
}

function tree_show_sense_categories($id,$categs,$array_key='id',$array_value='name') {
	/*
		<ul>
	<li>Index <></i>*/
	if (!isset($categs[$id])) return;
	echo '<ul>';
	foreach($categs[$id] AS $categ) {
		echoe('<li>%s [<a href="new.php?id=%d">New</a>] [<a href="edit.php?id=%d">Edit</a>] [<a href="delete.php?id=%d">Delete</a>]',$categ['name_ro'] . ' / ' . $categ['name_ru'],$categ['id'],$categ['id'],$categ['id']);
		tree_show_sense_categories($categ[$array_key],$categs);
		echo '</li>',"\n";
	}
	echo '</ul>';
}