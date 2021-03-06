<?php
function peer_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    require_once INCL_DIR . 'ann_functions.php';
    $torrent_seeds = $torrent_leeches = [];
    $deadtime = TIME_NOW - floor($site_config['announce_interval'] * 1.3);
    $dead_peers = sql_query('SELECT torrent, userid, peer_id, seeder FROM peers WHERE last_action < ' . $deadtime);
    while ($dead_peer = mysqli_fetch_assoc($dead_peers)) {
        $torrentid = (int)$dead_peer['torrent'];
        $userid = (int)$dead_peer['userid'];
        $seed = $dead_peer['seeder'] === 'yes'; // you use 'yes' i thinks :P
        sql_query('DELETE FROM peers WHERE torrent = ' . $torrentid . ' AND peer_id = ' . sqlesc($dead_peer['peer_id']));
        if (!isset($torrent_seeds[$torrentid])) {
            $torrent_seeds[$torrentid] = $torrent_leeches[$torrentid] = 0;
        }
        if ($seed) {
            ++$torrent_seeds[$torrentid];
        } else {
            ++$torrent_leeches[$torrentid];
        }
    }
    foreach (array_keys($torrent_seeds) as $tid) {
        $update = [];
        adjust_torrent_peers($tid, -$torrent_seeds[$tid], -$torrent_leeches[$tid], 0);
        if ($torrent_seeds[$tid]) {
            $update[] = 'seeders = (seeders - ' . $torrent_seeds[$tid] . ')';
        }
        if ($torrent_leeches[$tid]) {
            $update[] = 'leechers = (leechers - ' . $torrent_leeches[$tid] . ')';
        }
        sql_query('UPDATE torrents SET ' . implode(', ', $update) . ' WHERE id = ' . $tid);
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Peers Cleanup: Completed using $queries queries");
    }
}
