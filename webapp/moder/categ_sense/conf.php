<?php
include dirname(__FILE__).'/../../include/bittorrent.php';

if (get_user_class() < UC_SYSOP) {
  die();
}

include dirname(__FILE__).'/global_conf.php';
$_script_conf_form = array(
	'name_ro'=> array('l'=>'Nume român','t'=>'text'),
	'name_ru'=> array('l'=>'Nume rus','t'=>'text'),
	'name_en'=> array('l'=>'Nume engleză','t'=>'text'),
	'desc_ro'=> array('l'=>'Desc român','t'=>'text'),
	'desc_ru'=> array('l'=>'Desc rus','t'=>'text'),
	'orderi'=> array('l'=>'Order','t'=>'text','default_value'=>0, 'default'=>0),
	'visible'=> array('l'=>'Visible','t'=>'enum','values'=>array( array('yes','Da'),array('no','Nu') ), 'default'=>'yes'),
	'checkable'=> array('l'=>'Flag checkable','t'=>'enum','values'=>array( array('yes','Da'),array('no','Nu') ), 'default'=>'yes'),
	//TODO: de băgat query-le într-o funcție cu mem_set, să micșorăm nr iterețiilor.. (dependendOnCategTagCSV)
	'dependendOnCategTagCSV'=> array('l'=>'Dependent de', 't'=>'select', 'val_join'=>true, 'source'=>array('id','name_ro','sql'=>'SELECT id, name_ro FROM `torrents_catetags` WHERE `visible`="yes" AND `father` = '.$browseGCatVariable.';'), 'html_opt'=>array('size'=>10, 'multiple'=>'multiple')),
	'father'=> array('l'=>'Părinte','t'=>'select', 'source'=>array('id','name','function'=>'_get_all_categories_sense'), 'use_incomming_arg_as_value'=>true,'html_opt'=>array('size'=>10))
);

$_script_conf = array(
	'title'=>'Categorii după sensul torrentelor',
	'table'=>'torrents_catetags',
	'architecture'=>'tree',
	'pk'=>'id',
	'name'=>'name_'.get_lang(),
	'form_default_text_html_opt'=>array('size'=>60),
	);

include dirname(__FILE__).'/include.php';