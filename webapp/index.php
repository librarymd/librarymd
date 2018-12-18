<?php
require "include/bittorrent.php";
require "include/index_inc.php";

stdhead();

$strLang = (get_lang()=='ro')?'ro':'ru';

/* News */

$limit = 'LIMIT 3';
if (isset($_GET['newsShowAll'])) $limit = '';

$column_body_name = (get_lang()=='ro')?'body_ro':'body_ru';

$news = mem_get('news4_'.$limit.get_lang());

if (!$news) {
    $news = fetchAll("SELECT id,userid,added,$column_body_name AS body FROM news ORDER BY added DESC ".$limit);
    mem_set('news4_'.$limit.get_lang(),serialize($news),86400);
} else {
     $news = unserialize($news);
}

if ($limit == '') {
    $news = fetchAll("SELECT id,userid,added,$column_body_name AS body FROM news ORDER BY added DESC ".$limit);
}
/* End news */

?>

<div class="section">
  <div class="group">
    <div class="col full_with_for_small" style="width: 100%;">
      <h2 style="margin:0;">
          <span id="last_news" style="cursor:pointer"><?=$GLOBALS['lang']['index_recent_news']?></span>
          <?php if (!isset($_GET['newsShowAll'])): ?>
            [<a href="./index.php?newsShowAll=1" style="font-size:11px;"><?=$lang['index_news_show_all']?></a>]
          <?php endif; ?>
      </h2>
      <br/>

        <div class="generic_box_default">
        <?php
            $firstLi = true;
            foreach($news AS $array) {
                $firstLiStr = '';
                if ($firstLi) $firstLiStr = ' style="margin-top:4px;" ';
                print('<li class=newslistli '.$firstLiStr.'>' . date("Y-m-d",strtotime($array['added'])) . " - " . $array['body']);
                $firstLi = false;
                    if (get_user_class() >= UC_ADMINISTRATOR)
                    {
                  print(" <font size=\"-2\">[<a class=altlink href=news.php?action=edit&newsid=" . $array['id'] . "&returnto=index.php><b>E</b></a>]</font>");
                  print(" <font size=\"-2\">[<a class=altlink href=news.php?action=delete&newsid=" . $array['id'] . "&returnto=index.php><b>D</b></a>]</font>");
                    }
                print("</li>");
            }
        ?>
        </div>

    </div>
  </div>
</div>

<div style="max-width:990px; width:100%;">
<?php
//Anunt
$anunt = get_config_var('anunt');
if ($anunt || get_user_class() >= UC_MODERATOR) {
    echo '<h2 align=left>',$lang['index_anunt_label'], ' ';
    if (get_user_class() >= UC_MODERATOR) {
        echo '[<a href="./firstpage_annonce.php">Annonce page</a>] ';
        if ($anunt) echo '[<a href=./firstpage_annonce.php?turnannonce=off>Turn Off</a>] (acum e <font color=green>On</font>, se arata tuturor)';
        else echo '[<a href=./firstpage_annonce.php?turnannonce=on>Turn On</a>] (acum e <font color=#CC3200>Off</font>, se arata doar la staff)';
    }
    echo '</h2>';
?>
<table width="100%" align=center border=0>
<tr><td align=center>

<?php
    $column_body_name = (get_lang()=='ro')?'body_ro':'body_ru';
    $annonce = mem_get('annonce_'.$column_body_name);
    if ($annonce == null) {
        $annonce = q_firstrow("SELECT $column_body_name AS body, until FROM annonces ORDER BY added DESC LIMIT 1");
        mem_set('annonce_'.$column_body_name, serialize($annonce), 0);
    } else {
        $annonce = unserialize($annonce);
    }
    //Check if annonce is not expired
    if ($anunt && $annonce['until'] < time()) {
        set_config_var('anunt',0);
        write_admins_log('Pagina de anunt s-a facut oFf deoarece ' . $annonce['until'] . ' < ' . time());
    }
    if ($anunt) echo $annonce['body'];
?>

</td></tr></table><br>

<?php
}

?>



<?php
    showMostActiveUploaders();
    showActiveUsersOnWebsite();
    include('include/index_stats.php');
    include('include/index_top_forum.php');
?>

<br />
<div align="center">
  <p>
    <a href="./forum.php?action=viewtopic&topicid=1"><?=__('Ultimile modificări pe tracker');?></a>
  </p>
</div>
</div>
<?php
stdfoot();
?>
<noscript>
      <div style="position: absolute; top:150px; left:2px; width:260px; height:auto; background-color:#FFFFCC; border: solid 2px black; z-index: 100; padding: 10px; font-family: Arial, Helvetica, sans-serif; font-size: 12pt; line-height: 18pt;"><center><span style="font-family: Arial, Helvetica, sans-serif; font-size: 18pt; line-height: 22pt; font-weight: bold; color: #AC2D0D"><i>WHOAH!</i></span></center>Vede&#355;i aceasta din cauza c&#259; <u>javascript din browserul vostru este dezactivat</u>. Aceast&#259; pagin&#259; &#351;i &icirc;ntregul site folose&#351;te activ javascript. Situl nu va putea fi vizualizat adecvat far&#259; javascript, noi v&#259; rug&#259;m s&#259;-l activa&#355;i. Ap&#259;sa&#355;i <a href="http://www.tookaa.com/js_turn/on.htm">&nbsp;AICI&nbsp;</a> pentru a g&#259;si cum s&#259; activa&#355;i pe browserul vostru. V&#259; mul&#355;umim.</div>
</noscript>
