<?php
function sitepot_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    //== sitepot
    sql_query("UPDATE avps SET value_i = 0, value_s = '0' WHERE arg = 'sitepot' AND value_u < " . TIME_NOW . " AND value_s = '1'") or sqlerr(__FILE__, __LINE__);
    $mc1->delete_value('Sitepot_');
    if ($data['clean_log'] && $queries > 0) {
        write_log("Sitepot Cleanup: Completed using $queries queries");
    }
}
