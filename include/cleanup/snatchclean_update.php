<?php
function snatchclean_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    //== Delete snatched
    $days = 30;
    $dt = (TIME_NOW - ($days * 86400));
    sql_query('DELETE FROM snatched WHERE complete_date < ' . sqlesc($dt)) or sqlerr(__FILE__, __LINE__);
    if ($data['clean_log'] && $queries > 0) {
        write_log("Snatch List Cleanup: Removed snatches not seeded for $days days. Completed using $queries queries");
    }
}
