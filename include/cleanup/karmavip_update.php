<?php
function karmavip_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    $res = sql_query("SELECT id, modcomment FROM users WHERE vip_added='yes' AND donoruntil < " . TIME_NOW . " AND vip_until < " . TIME_NOW . '') or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $subject = 'VIP status expired.';
        $msg = "Your VIP status has timed out and has been auto-removed by the system. Become a VIP again by donating to {$site_config['site_name']} , or exchanging some Karma Bonus Points. Cheers !\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = $arr['modcomment'];
            $modcomment = get_date(TIME_NOW, 'DATE', 1) . " - Vip status Automatically Removed By System.\n" . $modcomment;
            $modcom = sqlesc($modcomment);
            $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
            $users_buffer[] = '(' . $arr['id'] . ',1, \'no\', \'0\' , ' . $modcom . ')';
            $mc1->begin_transaction('user' . $arr['id']);
            $mc1->update_row(false, [
                'class'     => 1,
                'vip_added' => 'no',
                'vip_until' => 0,
            ]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('user_stats' . $arr['id']);
            $mc1->update_row(false, [
                'modcomment' => $modcomment,
            ]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            $mc1->begin_transaction('MyUser_' . $arr['id']);
            $mc1->update_row(false, [
                'class'     => 1,
                'vip_added' => 'no',
                'vip_until' => 0,
            ]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->delete_value('inbox_new_' . $arr['id']);
            $mc1->delete_value('inbox_new_sb_' . $arr['id']);
        }
        $count = count($users_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO users (id, class, vip_added, vip_until, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE key UPDATE class=values(class),vip_added=values(vip_added),vip_until=values(vip_until),modcomment=values(modcomment)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup - Karma Vip status expired on - ' . $count . ' Member(s)');
        }
        unset($users_buffer, $msgs_buffer, $count);
        status_change($arr['id']); //== For Retros announcement mod
    }
    //==
    if ($data['clean_log'] && $queries > 0) {
        write_log("Karma Vip Cleanup: Completed using $queries queries");
    }
}
