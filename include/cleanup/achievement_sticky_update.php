<?php
function achievement_sticky_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    // *Updated* Sticky Torrents Achievements Mod by MelvinMeow
    $res = sql_query("SELECT userid, stickyup, stickyachiev FROM usersachiev WHERE stickyup >= 1") or sqlerr(__FILE__, __LINE__);
    $msg_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $subject = sqlesc('New Achievement Earned!');
        $points = random_int(1, 3);
        while ($arr = mysqli_fetch_assoc($res)) {
            $stickyup = (int)$arr['stickyup'];
            $lvl = (int)$arr['stickyachiev'];
            if ($stickyup >= 1 && $lvl == 0) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Stick Em Up LVL1[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/sticky1.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['userid'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . TIME_NOW . ', \'Stick Em Up LVL1\', \'sticky1.png\' , \'Uploading at least 1 sticky torrent to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',1, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['userid']);
                $mc1->delete_value('inbox_new_sb_' . $arr['userid']);
                $mc1->delete_value('user_achievement_points_' . $arr['userid']);
                $var1 = 'stickyachiev';
            }
            if ($stickyup >= 5 && $lvl == 1) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Stick Em Up LVL2[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/sticky2.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['userid'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . TIME_NOW . ', \'Stick Em Up LVL2\', \'sticky2.png\' , \'Uploading at least 5 sticky torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',2, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['userid']);
                $mc1->delete_value('inbox_new_sb_' . $arr['userid']);
                $mc1->delete_value('user_achievement_points_' . $arr['userid']);
                $var1 = 'stickyachiev';
            }
            if ($stickyup >= 10 && $lvl == 2) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Stick Em Up LVL3[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/sticky3.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['userid'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . TIME_NOW . ', \'Stick Em Up LVL3\', \'sticky3.png\' , \'Uploading at least 10 sticky torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',3, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['userid']);
                $mc1->delete_value('inbox_new_sb_' . $arr['userid']);
                $mc1->delete_value('user_achievement_points_' . $arr['userid']);
                $var1 = 'stickyachiev';
            }
            if ($stickyup >= 25 && $lvl == 3) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Stick Em Up LVL4[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/sticky4.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['userid'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . TIME_NOW . ', \'Stick Em Up LVL4\', \'sticky4.png\' , \'Uploading at least 25 sticky torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',4, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['userid']);
                $mc1->delete_value('inbox_new_sb_' . $arr['userid']);
                $mc1->delete_value('user_achievement_points_' . $arr['userid']);
                $var1 = 'stickyachiev';
            }
            if ($stickyup >= 50 && $lvl == 4) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Stick Em Up LVL5[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/sticky5.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['userid'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . TIME_NOW . ', \'Stick Em Up LVL5\', \'sticky5.png\' , \'Uploading at least 50 sticky torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',5, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['userid']);
                $mc1->delete_value('inbox_new_sb_' . $arr['userid']);
                $mc1->delete_value('user_achievement_points_' . $arr['userid']);
                $var1 = 'stickyachiev';
            }
        }
        $count = count($achievements_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES ' . implode(', ', $achievements_buffer) . ' ON DUPLICATE key UPDATE date=values(date),achievement=values(achievement),icon=values(icon),description=values(description)') or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO usersachiev (userid, $var1, achpoints) VALUES " . implode(', ', $usersachiev_buffer) . " ON DUPLICATE key UPDATE $var1=values($var1), achpoints=achpoints+values(achpoints)") or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log'] && $queries > 0) {
            write_log("Achievements Cleanup: Stickied Completed using $queries queries. Stickied Achievements awarded to - " . $count . ' Member(s)');
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
