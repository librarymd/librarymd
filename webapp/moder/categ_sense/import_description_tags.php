<?php
	include dirname(__FILE__).'/conf.php';
$conv_bookz_genre[1] = 'Biography';
$conv_bookz_genre[2] = 'Business & Money';
$conv_bookz_genre[3] = 'Children';
$conv_bookz_genre[4] = 'Computing & Internet';
$conv_bookz_genre[5] = 'Cooking, Food & Wine';
$conv_bookz_genre[6] = 'Diet & Health';
$conv_bookz_genre[7] = 'Education';
$conv_bookz_genre[8] = 'Fiction & Literature';
$conv_bookz_genre[9] = 'History';
$conv_bookz_genre[10] = 'Medicine';
$conv_bookz_genre[11] = 'Mystery & Crime';
$conv_bookz_genre[12] = 'Reference';
$conv_bookz_genre[13] = 'Religion';
$conv_bookz_genre[14] = 'Self-Improvement';
$conv_bookz_genre[15] = 'Science & Technics';
$conv_bookz_genre[100] = 'Other';

foreach($conv_bookz_genre AS $id=>$genre) {
	q("INSERT INTO torrents_catetags SET name_ro = :ro,name_ru = :ru,name_en =:en,desc_ro = '',desc_ru = '',
	orderi = '0',visible = 'yes',checkable = 'yes',father = '139'",
		array('ro'=>$genre,'ru'=>$genre,'en'=>$genre,)
	);
	$last = mysql_insert_id();
	echo '"'.$last.'" => "'.$id.'", // '.$genre.'<br>';	
}