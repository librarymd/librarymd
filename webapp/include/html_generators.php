<?php

function stdmsg($heading, $text) {
  print("<table class=main border=0 cellpadding=0 cellspacing=0><tr><td class=embedded>\n");
  if ($heading)
    print("<h2>$heading</h2>\n");
  print("<table width=100% border=1 cellspacing=0 cellpadding=10><tr><td class=text>\n");
  print($text . "</td></tr></table></td></tr></table>\n");
}

function stderr($heading, $text, $alreadyhead=false, $closetable=false, $axajAndJson=false)
{
  if($axajAndJson) echoJson($text, true);
  if ($alreadyhead == false) stdhead();
  if ($closetable == true) {
    end_table();
    end_frame();
  }
  stdmsg($heading, $text);
  stdfoot();
  die;
}

function stdok($text) {
  stderr(__("Succes"), $text);
}

function stderror($reason) {
  stderr(__('Eroare'), $reason);
}

//-------- Begins a main frame

function begin_main_frame()
{
  print('<table class="main " border=0 cellspacing=0 cellpadding=0>' .
    "<tr><td class=embedded>\n");
}

//-------- Ends a main frame

function end_main_frame()
{
  print("</td></tr></table>\n");
}

function begin_frame($caption = "", $center = false, $padding = 0, $dom_id = '')
{
  if ($caption)
    print("<h2>$caption</h2>\n");

  $tdextra = '';
  if ($center)
    $tdextra .= " align=center";

  //print("<table width=100% border=1 cellspacing=0 cellpadding=$padding><tr><td$tdextra>\n");
  print("<table width=100% border=1 ".(($dom_id!='')?"id=$dom_id ":' ')."cellspacing=0 cellpadding=$padding><tr><td$tdextra>\n");
}

function attach_frame($padding = 10)
{
  print("</td></tr><tr><td style='border-top: 0px'>\n");
}

function end_frame()
{
  print("</td></tr></table>\n");
}

function begin_table($fullwidth = false, $padding = 5)
{
  if ($fullwidth)
    $width = ' width=100%'; else $width ='';
  print("<table class=main{$width} border=1 cellspacing=0 cellpadding=$padding>\n");
}

function end_table()
{
  print("</td></tr></table>\n");
}


//-------- Inserts a smilies frame
//         (move to globals)

function insert_smilies_frame()
{
  include_once $GLOBALS['SETTINGS_PATH'] . 'sml_cache';
  global $smilies, $BASEURL;


  begin_frame(__('Smile-uri'), true);

  begin_table(false, 5);

  print("<tr><td class=colhead>". __('Scrie...') ."</td><td class=colhead>". __('Pentru a primi...') ."</td></tr>\n");

  while (list($code, $url) = each($smilies))
    print("<tr><td>$code</td><td><img src=./pic/smilies/$url></td>\n");

  end_table();

  end_frame();
}

function insert_stamps_frame() {
  include_once $GLOBALS['SETTINGS_PATH'] . 'sml_cache';
  global $smilies, $BASEURL;

  begin_frame("Smilies", true);

  begin_table(false, 5);

  print("<tr><td class=colhead>Type...</td><td class=colhead>To make a...</td></tr>\n");

  while (list($code, $url) = each($smilies))
    print("<tr><td>$code</td><td><img src=./pic/smilies/$url></td>\n");

  end_table();

  end_frame();
}

function tr($td1,$td2,$noesc=0,$trId='') {
    if (!$noesc) {
      $td1 = esc_html($td1);
      $td2 = esc_html($td2);
      $td2 = nl2br($td2);
    }
    $trId = ($trId != '')?' id="' . $trId.'"':'';;
    echo '<tr'.$trId.'><td class="heading" valign="top" align="right">'.$td1.'</td><td valign="top" align="left">'.$td2.'</td></tr>';
}



// Get the limits
// @return array(skip,limit)
function pagerSkip($rpp,$count, $opts = array()) {
    if (isset($_GET["page"])) {
        $page = 0 + (int)$_GET["page"];
        if ($page < 0)
            $page = 0;
    }
    else
        $page = 0;

    $start = $page * $rpp;
    return array($start,$rpp);
}

function pager($rpp, $count, $href, $opts = array()) {
    $pages = ceil($count / $rpp);

    $href = esc_html($href);
    if (!isset($opts["lastpagedefault"]))
        $pagedefault = 0;
    else {
        $pagedefault = floor(($count - 1) / $rpp);
        if ($pagedefault < 0)
            $pagedefault = 0;
    }

    if (isset($_GET["page"])) {
        $page = 0 + (int)$_GET["page"];
        if ($page < 0)
            $page = $pagedefault;
    }
    else
        $page = $pagedefault;

    $pager = "";

    $mp = $pages - 1;
    $as = '<b>&lt;&lt;&nbsp;'.__('Precedenta').'</b>';
    if ($page >= 1) {
        $pager .= "<a href=\"{$href}page=" . ($page - 1) . "\">";
        $pager .= $as;
        $pager .= "</a>";
    }
    else
        $pager .= $as;
    $pager .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $as = '<b>'.__('Următoarea').'&nbsp;&gt;&gt;</b>';
    if ($page < $mp && $mp >= 0) {
        $pager .= "<a href=\"{$href}page=" . ($page + 1) . "\">";
        $pager .= $as;
        $pager .= "</a>";
    }
    else
        $pager .= $as;

    if ($count) {
        $pagerarr = array();
        $dotted = 0;
        $dotspace = 3;
        $dotend = $pages - $dotspace;
        $curdotend = $page - $dotspace;
        $curdotstart = $page + $dotspace;
        for ($i = 0; $i < $pages; $i++) {
            if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
                if (!$dotted)
                    $pagerarr[] = "...";
                $dotted = 1;
                continue;
            }
            $dotted = 0;
            $start = $i * $rpp + 1;
            $end = $start + $rpp - 1;
            if ($end > $count)
                $end = $count;
            $text = "$start&nbsp;-&nbsp;$end";
            if ($i != $page)
                $pagerarr[] = "<a href=\"{$href}page=$i\"><b>$text</b></a>";
            else
                $pagerarr[] = "<b>$text</b>";
        }
        $pagerstr = join(" | ", $pagerarr);
        $pagertop = "<div align=\"center\" style=\"padding-top: 4px;padding-bottom: 7px;\">$pager<br />$pagerstr</div>\n";
        $pagerbottom = "<p align=\"center\">$pagerstr<br />$pager</p>\n";
    }
    else {
        $pagertop = "<p align=\"center\">$pager</p>\n";
        $pagerbottom = $pagertop;
    }

    $start = $page * $rpp;

    return array($pagertop, $pagerbottom, "LIMIT $start,$rpp");
}