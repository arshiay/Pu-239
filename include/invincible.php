<?php
function invincible($id, $invincible = true, $bypass_bans = true)
{
    global $CURUSER, $mc1, $site_config;
    $ip = '127.0.0.1';
    $setbits = $clrbits = 0;
    if ($invincible) {
        $display = 'now';
        $setbits |= bt_options::PERMS_NO_IP; // don't log IPs
        if ($bypass_bans) {
            $setbits |= bt_options::PERMS_BYPASS_BAN;
        } // bypass ban on
        else {
            $clrbits |= bt_options::PERMS_BYPASS_BAN; // bypass ban off
            $display = 'now bypass bans off and';
        }
    } else {
        $display = 'no longer';
        $clrbits |= bt_options::PERMS_NO_IP; // log IPs
        $clrbits |= bt_options::PERMS_BYPASS_BAN; // bypass ban off
    }
    // update perms
    if ($setbits || $clrbits) {
        sql_query('UPDATE users SET perms = ((perms | ' . $setbits . ') & ~' . $clrbits . ') 
                 WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    }
    // grab current data
    $res = sql_query('SELECT username, torrent_pass, ip, perms, modcomment FROM users 
                     WHERE id = ' . sqlesc($id) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_assoc($res);
    $row['perms'] = (int)$row['perms'];
    // delete from iplog current ip
    sql_query('DELETE FROM `ips` WHERE userid = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    // delete any iplog caches
    $mc1->delete_value('ip_history_' . $id);
    $mc1->delete_value('u_passkey_' . $row['torrent_pass']);
    // update ip in db
    $modcomment = get_date(TIME_NOW, '', 1) . ' - ' . $display . ' invincible thanks to ' . $CURUSER['username'] . "\n" . $row['modcomment'];
    //ipf = '.sqlesc($ip).',
    sql_query('UPDATE users SET ip = ' . sqlesc($ip) . ', modcomment = ' . sqlesc($modcomment) . '
              WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    //'ipf'   => $ip,
    // update ip in caches
    //$mc1->delete_value('user'.$id);
    $mc1->begin_transaction('user' . $id);
    $mc1->update_row(false, [
        'ip'    => $ip,
        'perms' => $row['perms'],
    ]);
    $mc1->commit_transaction($site_config['expires']['user_cache']);
    $mc1->begin_transaction('MyUser_' . $id);
    $mc1->update_row(false, [
        'ip'    => $ip,
        'perms' => $row['perms'],
    ]);
    $mc1->commit_transaction($site_config['expires']['curuser']);
    $mc1->begin_transaction('user_stats_' . $id);
    $mc1->update_row(false, [
        'modcomment' => $modcomment,
    ]);
    $mc1->commit_transaction($site_config['expires']['user_stats']);
    //'ipf'   => $ip,
    if ($id == $CURUSER['id']) {
        $mc1->begin_transaction('user' . $CURUSER['id']);
        $mc1->update_row(false, [
            'ip'    => $ip,
            'perms' => $row['perms'],
        ]);
        $mc1->commit_transaction($site_config['expires']['user_cache']);
        $mc1->begin_transaction('MyUser_' . $CURUSER['id']);
        $mc1->update_row(false, [
            'ip'    => $ip,
            'perms' => $row['perms'],
        ]);
        $mc1->commit_transaction($site_config['expires']['curuser']);
        $mc1->begin_transaction('user_stats_' . $CURUSER['id']);
        $mc1->update_row(false, [
            'modcomment' => $modcomment,
        ]);
        $mc1->commit_transaction($site_config['expires']['user_stats']);
    }
    write_log('Member [b][url=userdetails.php?id=' . $id . ']' . (htmlsafechars($row['username'])) . '[/url][/b] is ' . $display . ' invincible thanks to [b]' . $CURUSER['username'] . '[/b]');
    // header ouput
    $mc1->cache_value('display_' . $CURUSER['id'], $display, 5);
    header('Location: userdetails.php?id=' . $id);
    exit();
}
