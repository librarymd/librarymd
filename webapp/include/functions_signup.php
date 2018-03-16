<?php
function assert_user_signup() {
    $allow_only_in_weekends = false;
    $invitation_only = false;
    $block_clones = false;

    $error_message_header = __('Ne pare rău');
    $error_message_weekend = __('Înregistrarea la moment este închisă, <b>dar este deschisă în fiecare sâmbătă</b>. Sau dacă ai vreun prieten deja înregistrat, roagă-l pe el să te invite. <br>Pagina de pe care te poate invita: invite.php');
    $error_message_invitation = __('Înregistrarea la moment este închisă. Sau dacă ai vreun prieten deja înregistrat, roagă-l pe el să te invite. <br>Pagina de pe care te poate invita: /invite.php');

    if ($block_clones && strlen(@$_COOKIE['phpsessid2'])) {
        $clonedUsers = fetchOne('SELECT COUNT(id) FROM users WHERE browserHash=:bs', array('bs'=>$_COOKIE['phpsessid2']) );

        if ($clonedUsers >= 4) {
            stderr($error_message_header, $error_message_weekend);
            die();
        }
    }

    if ($invitation_only) {
      $error_message_header = __('Ne pare rău');
      stderr($error_message_header, $error_message_invitation);
    }

    if ($allow_only_in_weekends && date("w") != 6) {
        stderr($error_message_header, $error_message_weekend);
    }

}
