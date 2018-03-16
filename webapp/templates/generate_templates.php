<?php
if (php_sapi_name() != "cli") die();

// -- conf --
$html = './upl_categs.templates/parts.htm';
$templates_dir = './upl_categs.templates/';
$output_dir = './upl_categs/';

// -- code --

$lines = file($html);
/*
	Format of a section is:
-- section_name --
..
..
-- another_section --
*/

$parts = array();
$separator = '';

foreach($lines AS $line) {
	// $line
	if (preg_match('/^--(.+)--$/',trim($line),$matches)) {
		$separator = trim($matches[1]);
		$parts[$separator] = '';
		continue;
	}
	$parts[$separator] .= $line;
}

foreach(glob($templates_dir.'*.html') AS $template) {
	$html = file_get_contents($template);

	foreach($parts AS $separator=>$content) {
		$html = str_replace("#{$separator}#",$content,$html);
	}

	file_put_contents($output_dir.basename($template),$html);
	echo basename($template) . ' Done !<br>',"\n";
}