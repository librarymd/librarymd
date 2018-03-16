<?php
require_once("./include/bittorrent.php");
require_once($INCLUDE . 'torrent_opt.php');
include_once($INCLUDE . 'classes/categtag.php');
require_once($INCLUDE . 'classes/users.php');
require_once($INCLUDE . 'classes/torrents.php');

loggedinorreturn();

if (get_user_class() < UC_MODERATOR) {
  die();
}

stdhead("Torrente semnalate ", true);

if (isset($_GET['resolved']) && is_numeric($_GET['id'])) {
  $id = (int)$_GET['id'];
  Q('UPDATE torrent_reports SET solvedby=:admin, solved="yes" WHERE id=:id',
    array('admin'=>$CURUSER['id'], 'id'=>$id)
  );
  Torrents_Reports::cleanCounters($_GET['id']);
  echo "Cazul a fost tratat cu succes. Iti multumesc stimate admin pentru ca ajuti utilizatorii si ca faci siteul mai bun.";
}

$reasons = Torrents_Reports::$signal_reasons;
$levels = Torrents_Reports::$signal_levels;

$solved_status_to_show = "no";
if (isset($_GET['solved'])) {
  $solved_status_to_show = "yes";
}

$reports = fetchAll(
  "SELECT torrent_reports.id,
          torrent_reports.torrent, torrent_reports.added, torrent_reports.addedby,
          torrent_reports.reason, torrent_reports.level, torrent_reports.comment,
          torrent_reports.solvedby,
          torrents.name as torrent_name,
          addedby_users.username AS addedby_username, addedby_users.class AS addedby_class,
          solvedby_users.username AS solvedby_username
   FROM torrent_reports
   LEFT JOIN torrents ON torrent_reports.torrent = torrents.id
   LEFT JOIN users AS addedby_users ON torrent_reports.addedby = addedby_users.id
   LEFT JOIN users AS solvedby_users ON torrent_reports.solvedby = solvedby_users.id
   WHERE solved='$solved_status_to_show'
   ORDER BY id DESC
   LIMIT 25
  ");
?>
<h1>Torrente semnalate</h1>
<a href="?solved=true">Afiseaza cazurile rezolvate</a><br/><br/>
<table width="100%" cellpadding="10">
  <tr>
    <td width="30">Ora</td>
    <td width="200">Torrent</td>
    <td width="50">Nivel - Motiv</td>
    <td>Detalii</td>
    <td width="30">Action</td>
    <td width="30">Rezolvat de</td>
  </tr>
  <?php foreach ($reports as $report) {
    $torrent = '<a href="/details.php?id='.$report['torrent'].'">'.$report['torrent_name'].'</a>';
  ?>
    <tr>
      <td>
        <?=get_elapsed_time(sql_timestamp_to_unix_timestamp($report["added"]))?> de
        <a href="/userdetails.php?id=<?=$report["addedby"]?>"><?=$report["addedby_username"]?></a>
        (<?=get_user_class_name($report["addedby_class"])?>)
      </td>
      <td>
        <?=$torrent?>
      </td>
      <td>
        <?=$levels[$report["level"]]?> -
        <?=$reasons[$report["reason"]]?>
      </td>
      <td>
        <?=esc_html($report["comment"])?>
      </td>
      <td>
        <a href="?resolved=ok&id=<?=$report['id']?>">Ok</a>
      </td>
      <td>
        <?php
          if ($report["solvedby"] > 0) {
            echo $report["solvedby_username"];
          } else {
            echo "Nesolutionat";
          } ?>
      </td>
    </tr>
  <?php
    }
  ?>
</table>
<?php
  stdfoot();
?>
