<?php
require "include/bittorrent.php";


loggedinorreturn();

$search = trim($_GET['search']);
$class = $_GET['class'];
if ($class == '-' || !is_valid_id($class))
  $class = '';

if ($search != '' || $class) {
  // Filter wildcard chars
  if (strstr( $search, '_') !== FALSE || strstr( $search, '%') !== FALSE) {
  	  $search = str_replace(array('_','%'),array("\_",''),$search	);
  }

  $query = "username LIKE " . sqlesc("$search%") . " AND status='confirmed'";
	if ($search)
		  $q = "search=" . esc_html($search);
} else {
	$letter = trim($_GET["letter"]);
  if (strlen($letter) > 1)
    die;

  if ($letter == "" || strpos("abcdefghijklmnopqrstuvwxyz", $letter) === false)  $letter = '';
  $query = "username LIKE '$letter%' AND status='confirmed'";
  $q = "letter=$letter";
}

$browser = get('browser');
$ip = get('ip');

if (get_user_class() >= UC_MODERATOR && strlen($browser) == 32) {
	$query = "browserHash = "._esc($browser)." AND status='confirmed'";
	$q = "browser=".$browser;
}

if (get_user_class() == UC_SYSOP && strpos($search,'@') !== FALSE) {
	$query = "email = "._esc($search)." AND status='confirmed'";
	$q = "search=".$search;
}

if (get_user_class() >= UC_MODERATOR && strlen($browser) == 32) {
	$query = "browserHash = "._esc($browser)." AND status='confirmed'";
	$q = "browser=".$browser;
}

if (get_user_class() >= UC_MODERATOR && strlen($ip) == 64) {
  $query = "ip = "._esc($ip)." ";
  $q = "ip=".$ip;
}

// Show invited users by ..
if ( (get_user_class() >= UC_MODERATOR && get('inviter') > 0) || get('inviter') == $CURUSER['id'] ) {
	$inviter = get('inviter');
	$query = "inviter = "._esc($inviter);
	$q = "inviter=".$inviter;
}

if ($class) {
  if ($class > 7) $class = 7;
  $query .= " AND class=$class";

  $q .= ($q ? "&amp;" : "") . "class=$class";
}

stdhead(__('Utilizatori'));

print("<h1>". __('Utilizatori') ."</h1>\n");

echo '<div class="center">';
print("<form method=get action=?>\n");
print(__('Caută:') ." <input type=text size=30 name=search>\n");
print("<select name=class>\n");
print("<option value='-'>(". __('toate clasele') .")</option>\n");
for ($i = 0;;++$i)
{
	if ($c = get_user_class_name($i))
	  print("<option value=$i" . ($class && $class == $i ? " selected" : "") . ">$c</option>\n");
	else
	  break;
}
print("</select>\n");
print("<input type=submit value='Okay'>\n");
print("</form>\n");
echo '</div>';

$page = $_GET['page'];
$perpage = 100;
if (!$letter && (!$search && $class < UC_UPLOADER ) && !$browser && !$inviter && !$ip) {
  stdfoot();
  die;
}
$res = q("SELECT COUNT(*) FROM users WHERE $query");
$arr = mysql_fetch_row($res);


$pages = floor($arr[0] / $perpage);
if ($pages * $perpage < $arr[0])
  ++$pages;

if ($page < 1)
  $page = 1;
else
  if ($page > $pages) {
  	  $page = $pages;
  	  if ($page == 0) $page = 1;
  }


for ($i = 1; $i <= $pages; ++$i)
  if ($i == $page)
    $pagemenu .= "<b>$i</b>\n";
  else
    $pagemenu .= "<a href=?$q&page=$i><b>$i</b></a>\n";

if ($page == 1)
  $browsemenu .= "<b>&lt;&lt; ". __('Precedenta') ."</b>";
else
  $browsemenu .= "<a href=?$q&page=" . ($page - 1) . "><b>&lt;&lt; ". __('Precedenta') ."</b></a>";

$browsemenu .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

if ($page == $pages)
  $browsemenu .= "<b>". __('Următoarea') ." &gt;&gt;</b>";
else
  $browsemenu .= "<a href=?$q&page=" . ($page + 1) . "><b>". __('Următoarea') ." &gt;&gt;</b></a>";

print("<p class='center'>$browsemenu<br>$pagemenu</p>");

$offset = ($page * $perpage) - $perpage;


$res = q("SELECT users.*, u_du.last_access
	FROM users
  LEFT JOIN users_down_up AS u_du ON users.id = u_du.id
	WHERE $query ORDER BY username LIMIT $offset,$perpage");

$num = mysql_num_rows($res);

print("<table class='mCenter' border=1 cellspacing=0 cellpadding=5>\n");
print("<tr><td class=colhead align=left>". __('Nume utilizator') ."</td><td class=colhead>". __('Înregistrat') ."</td><td class=colhead>". __('Ultima vizită') ."</td><td class=colhead align=left>". __('Statut') ."</td><td class=colhead>". __('Regiune') ."</td></tr>\n");
for ($i = 0; $i < $num; ++$i)
{
  $arr = mysql_fetch_assoc($res);
  if ($arr['id'] == 26) $arr['class'] = 7;
  //print_r($arr);
  if ($arr['region'] > 0)
  {
  	//echo $arr[region]; //exit();
    $cres = q("SELECT name FROM regions WHERE id=$arr[region]");
    if (mysql_num_rows($cres) == 1)
    {
      $carr = mysql_fetch_assoc($cres);
      $country = "<td align=left>$carr[name]</td>";
    }
  }
  else
    $country = "<td align=left>---</td>";
  if ($arr['added'] == '0000-00-00 00:00:00')
    $arr['added'] = '-';
  if ($arr['last_access'] == '0000-00-00 00:00:00')
    $arr['last_access'] = '-';

  $userIcons = get_user_icons($arr, false);
  print("<tr><td align=left><a href=userdetails.php?id=$arr[id]><b>$arr[username]</b></a><span class=userIcons>$userIcons</span></td>" .
  "<td>$arr[added]</td><td>$arr[last_access]</td>".
    "<td align=left>" . get_user_class_name($arr["class"]) . "</td>$country</tr>\n");
}
print("</table>\n");

print("<p class='center'>$pagemenu<br>$browsemenu</p>");



stdfoot();
die;

?>
