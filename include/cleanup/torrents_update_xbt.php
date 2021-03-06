<?php
function torrents_update_xbt($data)
{
    global $site_config, $queries;
    set_time_limit(1200);
    ignore_user_abort(true);
    /** sync torrent counts - pdq **/
    $tsql = 'SELECT t.id, t.seeders, (
    SELECT COUNT(*)
    FROM xbt_files_users
    WHERE fid = t.id AND `left` = "0"
    ) AS seeders_num,
    t.leechers, (
    SELECT COUNT(*)
    FROM xbt_files_users
    WHERE fid = t.id AND `left` >= "1"
    ) AS leechers_num,
    t.comments, (
    SELECT COUNT(*)
    FROM comments
    WHERE torrent = t.id
    ) AS comments_num
    FROM torrents AS t
    ORDER BY t.id ASC';
    $updatetorrents = [];
    $tq = sql_query($tsql);
    while ($t = mysqli_fetch_assoc($tq)) {
        if ($t['seeders'] != $t['seeders_num'] || $t['leechers'] != $t['leechers_num'] || $t['comments'] != $t['comments_num']) {
            $updatetorrents[] = '(' . $t['id'] . ', ' . $t['seeders_num'] . ', ' . $t['leechers_num'] . ', ' . $t['comments_num'] . ')';
        }
    }
    ((mysqli_free_result($tq) || (is_object($tq) && (get_class($tq) == 'mysqli_result'))) ? true : false);
    if (count($updatetorrents)) {
        sql_query('INSERT INTO torrents (id, seeders, leechers, comments) VALUES ' . implode(', ', $updatetorrents) . ' ON DUPLICATE KEY UPDATE seeders = VALUES(seeders), leechers = VALUES(leechers), comments = VALUES(comments)');
    }
    unset($updatetorrents);
    if ($data['clean_log'] && $queries > 0) {
        write_log("XBT Torrent Cleanup: Completed using $queries queries");
    }
}
