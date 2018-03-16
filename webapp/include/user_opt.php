<?php
global $conf_user_opt;
$conf_user_opt['have_voted'] = 1;
$conf_user_opt['have_seen_vote'] = 2;
$conf_user_opt['have_seen_news'] = 4;
$conf_user_opt['postingban'] = 8;
//Mean that the user have played for football team (6 mai 2006)
$conf_user_opt['fotbalist'] = 16;
//User is binded to some ip
$conf_user_opt['ipbind'] = 32;
//User have seen annonce from first page
$conf_user_opt['have_seen_annonce'] = 64;
// Upload new torrents disabler
$conf_user_opt['torrentsUploadBan'] = 128;
$conf_user_opt['viewBanners'] = 256;
// Download torrents disabler
$conf_user_opt['torrentsDownloadBan'] = 512;
$conf_user_opt['spanked'] = 1024;
$conf_user_opt['invite_disabled'] = 2048;
$conf_user_opt['moderator_pe_tema_sa'] = 4096;
$conf_user_opt['copyrighter'] = 8192;
$conf_user_opt['velo_club'] = 16384;
$conf_user_opt['chess_club'] = 32768;
$conf_user_opt['drivers_club'] = 65536;
$conf_user_opt['volei_club'] = 131072;
$conf_user_opt['forum_moderator'] = 262144;

$user_icons['postingban'] = array(
	'name' => 'postingban',
	'big_img' => 'writebanbig.gif',
	'img' => 'writeban.gif',
	'alt' => 'Postban',
	'title' =>'Nu poate scrie mesaje pe forum şi comentarii la torrente',
	'canSet' => true,
	'award' => false
);

$user_icons['fotbalist'] = array(
	'name' => 'fotbalist',
	'big_img' => 'fotbalistbig.gif',
	'img' => 'fotbalist.gif',
	'alt' => 'Fotbalist',
	'title' =>'A jucat pentru echipa',
	'canSet' => true,
	'award' => true
);

$user_icons['torrentsUploadBan'] = array(
	'name' => 'torrentsUploadBan',
	'big_img' => 'notorrentsuploadbig.gif',
	'img' => 'notorrentsupload.gif',
	'alt' => 'UploadTorrentsBan',
	'title' =>'Nu poate încărca torrente noi',
	'canSet' => true,
	'award' => false
);

$user_icons['torrentsDownloadBan'] = array(
	'name' => 'torrentsDownloadBan',
	'big_img' => 'notorrentsdownloadbig.gif',
	'img' => 'notorrentsdownload.gif',
	'alt' => 'DownloadTorrentsBan',
	'title' =>'Nu poate copia torrente',
	'canSet' => true,
	'award' => false
);

$user_icons['drivers_club'] = array(
	'name' => 'drivers_club',
	'big_img' => 'drivers.png', /* TODO: big image */
	'img' => 'drivers.png',
	'alt' => 'Drivers Club',
	'title' =>'Face parte din Drivers Club',
	'canSet' => true,
	'award' => true
);

$user_icons['volei_club'] = array(
	'name' => 'volei_club',
	'big_img' => 'voleibig.png',
	'img' => 'volei.png',
	'alt' => 'Volei Club',
	'title' =>'Face parte din Volei Club',
	'canSet' => true,
	'award' => true
);

$user_icons['chess_club'] = array(
	'name' => 'chess_club',
	'big_img' => 'chessbig.png',
	'img' => 'chess.png',
	'alt' => 'Club de sah si dame',
	'title' =>'Membrul Clubului de Șah și Dame',
	'canSet' => true,
	'award' => true
);

$user_icons['velo_club'] = array(
	'name' => 'velo_club',
	'big_img' => 'velobig.png',
	'img' => 'velo.png',
	'alt' => 'Velo Club',
	'title' =>'Face parte din Velo Club',
	'canSet' => true,
	'award' => true
);

$user_icons['spanked'] = array(
	'name' => 'spanked',
	'big_img' => 'spankbig.gif',
	'img' => 'spank.gif',
	'alt' => 'Bataie',
	'title' =>'Utilizatorul s-a comportat urât, acum işi primeste pedeapsa',
	'canSet' => false,
	'award' => false
);

function get_user_opt_const($name) {
    global $conf_user_opt;

    if (!isset($conf_user_opt[$name])) {
        trigger_error("User opt $name doesn't exist", E_USER_ERROR);
    }

    return $conf_user_opt[$name];
}

?>