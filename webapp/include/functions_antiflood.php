<?php
class AntiFloodMessages {
    /**
     * This function should be when some public msg is wrote
     *
     * Returns false if no error
     * Returns String if error
     **/
    public static function check_flood($user_id, $msg) {
        if (get_user_class() >= UC_RELEASER) return false;

        $formatedMsg = format_comment($msg, true, true);

        $maybe_error1 = self::check_flood_links($user_id, $formatedMsg);
        $maybe_error2 = self::check_flood_quantity($user_id, $formatedMsg);

        if ($maybe_error1 !== false) {
            return $maybe_error1;
        }
        if ($maybe_error2 !== false) {
            return $maybe_error2;
        }
        return false;
    }

    public static function check_flood_current_user($msg) {
        global $CURUSER;

        return self::check_flood($CURUSER['id'], $msg);
    }

    public static function bark_if_current_user_is_flooding($postedUserMsg) {
        $is_flood_banned = self::check_flood_current_user($postedUserMsg);

        if ($is_flood_banned !== false) barkk($is_flood_banned);
    }

    public static function flood_link_key($user_id) {
        return 'linksantiflood_' . $user_id;
    }

    // Max 5 links per 15 min
    public static function check_flood_links($user_id, $formatedMsg) {
        $number_of_messages = 6;
        $time_interval = 15*60;
        $key = self::flood_link_key($user_id);
        $msg_already_posted = mem_get($key);

        $is_it_link = strpos($formatedMsg, '<a ') !== FALSE;
        if (!$is_it_link) return false;

        if ($msg_already_posted === false) {
            mem_set($key, 0, $time_interval);
        }

        if ($msg_already_posted >= $number_of_messages) {
            if ($msg_already_posted == $number_of_messages) {
                $report_str = " $user_id - [url=/userdetails.php?id={$user_id}]{$user_id}[/url] url flood attempt? " . substr($formatedMsg,0,100) . '.. post-banned ';
                write_moders_log($report_str);
                user_set_flag($user_id,'postingban','1 day');
                topic_post(88154156, $report_str, 3);
            }
            return 'Protectia anti-flood a fost activata, nu mai puteti posta mesaje, daca a avut loc eroare adresati-va la administratie.';
        }

        mem_increment($key);
        return false;
    }

    public static function flood_quantity_key($user_id) {
        return 'mesantiflood_' . $user_id;
    }

    // Max 15 mes. in 5 min
    public static function check_flood_quantity($user_id, $msg) {
        $number_of_messages = 15;
        $time_interval = 5*60;
        $key = self::flood_quantity_key($user_id);
        $msg_already_posted = mem_get($key);

        if ($msg_already_posted === false) {
            mem_set($key, 0, $time_interval);
            $msg_already_posted = 0;
        }

        if ($msg_already_posted >= $number_of_messages) {
            if ($msg_already_posted == $number_of_messages) {
                $report_str = " $user_id - [url=/userdetails.php?id={$user_id}]{$user_id}[/url] >15msg flood attempt? " . $msg . ' post-banned ';
                write_moders_log($report_str);
                user_set_flag($user_id,'postingban','1 day');
                topic_post(88154156, $report_str, 3);
            }
            return 'Protectia anti-flood a fost activata, nu puteti posta mesaje.';
        }
        mem_increment($key);
        return false;
    }

    public static function clean_all_counters($user_id) {
        mem_delete(self::flood_link_key($user_id));
        mem_delete(self::flood_quantity_key($user_id));
    }
}