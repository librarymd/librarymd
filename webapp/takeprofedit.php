<?php

require_once("include/bittorrent.php");

function bark($msg) {
  genbark($msg, __('Update nereuşit!'));
}

loggedinorreturn();

if (!mkglobal("password:chpassword:passagain")) {
  bark(__('Nu ați îndeplinit unele rînduri obligatorii.'));
}

$email = trim($_POST['email']);

$updateset = array();
$changedemail = 0;
$relogin_after_password_change = false;

if ($chpassword != "") {
  if ($CURUSER["passhash"] !== md5($CURUSER["secret"] . $password . $CURUSER["secret"])) {
    bark(__('Parola curentă este greşită.'));
  }

  if (validate_new_password($chpassword) !== true) {
    bark(validate_new_password($chpassword));
  }

  if ($chpassword != $passagain)
    bark(__('Parolele nu coincid'));

  // For logs only
  if (validate_new_password($password) !== true) {
    write_sysop_log("Utilizatorul $CURUSER[username] avea parola simpla, dar a schimbat-o");
  }

  $sec = mksecret();

  $passhash = md5($sec . $chpassword . $sec);

  $updateset[] = "secret = " . sqlesc($sec);
  $updateset[] = "passhash = " . sqlesc($passhash);
  $relogin_after_password_change = true;

  $body = <<<EOD
Va confirmam ca ati schimbat parola cu succes (utilizator {$CURUSER["username"]}).

Acest email are ca scop avertizarea voastra in caz ca nu dvs. ati schimbat parola.

In caz ca nu dvs. ati schimbat parola, va rugam sa intrati si s-o schimbati.

In caz contrat puteti ignora mesajul.

Va multumim anticipat.
EOD;

  email_to($CURUSER["email"], "parola a fost schimbata cu succes", $body);
}

if (@$_POST['ckb_change_email'] == 'yes' && strlen($email)) {
  if (!validemail($email)) bark(__('Adresa de poştă electronică pare a fi incorectă.'));
    $r = q("SELECT id FROM users WHERE email=" . sqlesc($email));
  if (mysql_num_rows($r) > 0)
    bark(__('Adresa de email ') . $email . __(' deja e ocupată de catre altcineva.'));
  $changedemail = 1;
}

$acceptpms = $_POST["acceptpms"];
//$deletepms = ($_POST["deletepms"] != "" ? "yes" : "no");
//$savepms = ($_POST["savepms"] != "" ? "yes" : "no");
$pmnotif = $_POST["pmnotif"];

$download_no_passkey = $_POST["download_no_passkey"];

$updateset[] = "download_no_passkey = " . ($download_no_passkey == 1 ? 1 : 0);

// Invisibility are accesible only to >=VIP
if (get_user_class() == UC_SYSOP) {
  $invisible = ($_POST["invisible"] == 'yes'?'yes':'no');
}
$emailnotif = $_POST["emailnotif"];
$notifs = ($pmnotif == 'yes' ? "[pm]" : "");
$notifs .= ($emailnotif == 'yes' ? "[email]" : "");
$r = q("SELECT id FROM categories");
$rows = mysql_num_rows($r);
for ($i = 0; $i < $rows; ++$i)
{
  $a = mysql_fetch_assoc($r);
  if (@$_POST["cat$a[id]"] == 'yes')
    $notifs .= "[cat$a[id]]";
}
###############
# Avatar handler
###############
if (isset($_POST['dltavatar']))
{
  @unlink( avatarWww($CURUSER['id'],$CURUSER['avatar_version'],false) );
  $avatar = 'no';
}

if (isset($_FILES['upload_avatar']) && $_FILES['upload_avatar']['name'] != "" && $CURUSER["warned"] != 'yes')
{
  $tmp_name = trim($_FILES['upload_avatar']['tmp_name']);
  $upload_name = trim($_FILES['upload']['name']);
  if (is_uploaded_file($tmp_name))
  {
    if ($imginfo = @getimagesize($tmp_name))
    {
      if ($imginfo[0] > 150 OR $imginfo[1] > 255)
      {
        @unlink($tmp_name);
        bark(__('Avatarul e prea mare, mărimea maximă este 150x255.'));
      }
        if ($imginfo[2] != 1 AND $imginfo[2] != 2 AND $imginfo[2] != 3)
         {
           @unlink($tmp_name);
           bark(__('Avatarul e într-un format necorespunzător. Formate permise sunt: JPG, PNG, GIF.'));
         }
     } else {
       @unlink($tmp_name);
       bark(__('Format incorect al imaginii.'));
      }
  } else {
    @unlink($tmp_name);
    bark(__('Avatarul nu a fost incarcat.'));
  }
  $filesize = @filesize($tmp_name);
  if (($imginfo[2] == 2 && $filesize > 256000) || ($filesize > 358400))
  {
    @unlink($tmp_name);
    bark(__('Imaginea pentru avatar este prea mare. Dimensiunea maximă a imaginii este de 150 pixeli în lăţime şi 255 pixeli în înălţime, mărimea maximă a unei imagini JPG nu trebuie să depăşească 25kb sau 350kb dacă e o animaţie GIF.'));
  }

    $avatar_version = $CURUSER['avatar_version'] + 1;

    if ($avatar_version > 250) $avatar_version = 0;

  $new_loc = avatarWww($CURUSER['id'],$avatar_version,false);
  if ($CURUSER['avatar'] == 'yes') @unlink( avatarWww($CURUSER['id'],$CURUSER['avatar_version'],false) );

  move_uploaded_file($tmp_name,$new_loc);
  if (is_file($new_loc)) {
    $avatar = 'yes';
    $updateset[] = "avatar_version = ".$avatar_version;
  }
  else bark(__('Încărcarea avatarului a eşuat, încercati vă rog altul.'));
  mem_delete(avatarWww($CURUSER['id'],$CURUSER['avatar_version']));
}

$info = $_POST["info"];
$language = intval($_POST["language"]);
$region = intval($_POST["region"]);
//Check if region exist
  $r = q("SELECT * FROM regions WHERE id=$region");
  // mysql_num_rows return FALSE on failure
  $rows = mysql_num_rows($r);
  if (!$rows && $region != 0) { bark("Hm.. Wrong region!??!"); }

$gender = intval($_POST["gender"]);
if ($gender == 1) $gender = 'masc';
elseif ($gender == 2) $gender = 'fem';
else $gender = 'none';

$updateset[] = "torrentsperpage = " . min(100, 0 + $_POST["torrentsperpage"]);
$updateset[] = "topicsperpage = " . min(100, 0 + $_POST["topicsperpage"]);
$updateset[] = "postsperpage = " . min(100, 0 + $_POST["postsperpage"]);

$updateset[] = "region = " . sqlesc($region);
$updateset[] = "language = " . sqlesc($language);

$updateset[] = "gender = '$gender'";

Q("UPDATE users_username SET gender = '$gender' WHERE id=".$CURUSER["id"]);

$updateset[] = "notifs = '$notifs'";

if (isset($invisible)) {
  $updateset[] = "invisible = '$invisible'";
}

$updateset[] = "info = " . sqlesc($info);

$changeCT = (@$_POST["changeCT"] != "");
if ( isCTenabled() && $changeCT)  //CT
{
  $title = $_POST["title"];
  if(mb_strlen(esc_html($title))<$CTmaxLen)
    $updateset[] = "title = " . sqlesc(esc_html($title));
}

$updateset[] = "acceptpms = " . sqlesc($acceptpms);

$avatars = ($_POST["avatars"] != "" ? "yes" : "no");
$updateset[] = "avatars = '$avatars'";
if (isset($avatar)) $updateset[] = "avatar = '$avatar'";

if ($_POST['resetpasskey']) {
  $passkey = md5($CURUSER['username'].get_date_time().$CURUSER['passhash']);
  $updateset[] = "passkey=" . _esc($passkey);

}


/* ****** */

$urladd = "";

if ($changedemail) {

    if (!validemail($email)) {
      barkk(__('Adresa email nu este validă.'));
    }

  $currentEmailIsInvalid = fetchOne('SELECT email_is_invalid FROM users WHERE id = :id',
  		array('id' => $CURUSER["id"])
  	) == 'yes';

  if ($currentEmailIsInvalid) {
    q('UPDATE users SET email=:newEmail, email_is_invalid = "no" WHERE id= :userId',
      array('newEmail' => $email, 'userId' => $CURUSER["id"])
    );
    write_user_modcomment($CURUSER['id'], 'User has changed his email, had previously an invalid one.');
    write_sysop_log("User $CURUSER[id] with an invalid email has changed his email.");
    $urladd .= "&emailch=1";

  } else {
    $sec = mksecret();
    $hash = md5($sec . $email . $sec);
    $obemail = urlencode($email);
    $updateset[] = "editsecret = " . sqlesc($sec);
    $thishost = $_SERVER["HTTP_HOST"];
    $thisdomain = preg_replace('/^www\./is', "", $thishost);
    $body = <<<EOD
You have requested that your user profile (username {$CURUSER["username"]})
on $thisdomain should be updated with this email address ($email) as
user contact.

If you did not do this, please ignore this email. Please do not reply.

To complete the update of your user profile, please follow this link:

http://$thishost/confirmemail.php?id={$CURUSER["id"]}&hash=$hash&mail=$obemail

Your new email address will appear in your profile after you do this. Otherwise
your profile will remain unchanged.
EOD;

    email_to($CURUSER["email"], "profile change confirmation", $body);
    $urladd .= "&mailsent=1";
  }

}

q("UPDATE users SET " . implode(",", $updateset) . " WHERE id = " . $CURUSER["id"]) or sqlerr(__FILE__,__LINE__);
cache_user_expire();

if ($relogin_after_password_change) {
  $updated_user_row = fetchRow("select * from users WHERE id=:id", array('id' => $CURUSER["id"]));
  logincookie($updated_user_row);
  $urladd .= "&password_changed=1";
}

redirect('./my.php?edited=1'.$urladd);
?>
