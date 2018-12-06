<?php
  if (!isset($_GET['container']) || !isset($_GET['text']) || !isset($_GET['lang'])) { die('params missed'); }
  $l = $_GET['lang'];
  if ($l != 'ro' && $l != 'ru' && $l != 'en') die('Bad lang');

  require_once("include/variables.php");

  include $SETTINGS_PATH . '/sml_cache';
  include $SETTINGS_PATH . '/sml_cache_sizes';

  $smilies = array_unique($smilies);

  $l_smilies_count = count($smilies); //Get total stamps in the first lang

  $smilies_html = '<tr>';
  $row_template = "<td align=center><a href='javascript:StampIT(\"{code}\");'><img src=\"pic/smilies/{url}\" width={width} height={height}></a></td>";

  $row = 0;
  $i = 0;

  $smilies_size = array();
  foreach ($smilies as $smilieTag => $smilieImg) {
  	  //list($width, $height) = getimagesize('pic/smilies/'.$smilieImg);
  	  //$smilies_size[$smilieImg] = array($width, $height);
  	  if ($row == 3 || $i == $l_smilies_count) {
  	  	  if ($i == $l_smilies_count) { //That mean we separate 2 language, but we need also to complete missing with tr
  	  	  	  $smilies_html .= '<td colspan="'.(3-$row).'">&nbsp;</td>';
  	  	  }
  	  	  $smilies_html .= "</tr>\n<tr>";
  	  	  $row = 0;
  	  }
  	  list($width, $height) = $smilies_sizes[$smilieImg];
  	  $smilies_html .= str_replace( array('{code}', '{url}', '{width}', '{height}'), array($smilieTag, $smilieImg, $width, $height), $row_template);
  	  $row++;
  	  $i++;
  }
  if ($row != 0 && $row != 3) { //That mean we need to put the /tr and also some tr to complete missing..
  	  $smilies_html .= '<td colspan="'.(3-$row).'">&nbsp;</td></tr>';
  }
  //$abc = var_export($smilies_size,true);
  //file_put_contents('sml_cache_sizes',$abc);
  unset($lang);
  include './lang/details.php_'.$l.'.php';

  $content = file_get_contents("templates/smilies.tpl");
  $tpl_vars = array( '{text}', '{container}', '{stamps_label}', '{smilies_html}');
  $tpl_values = array( esc_html($_GET['text']), esc_html($_GET['container']), $lang['comment_smilies'], $smilies_html);
  $content = str_replace($tpl_vars, $tpl_values, $content);
  echo $content;

function esc_html($t) {
	return htmlspecialchars($t,ENT_QUOTES,'UTF-8');
}
?>