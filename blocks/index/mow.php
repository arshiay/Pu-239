<?php
$categorie = genrelist();
foreach ($categorie as $key => $value) {
    $change[$value['id']] = [
        'id'    => $value['id'],
        'name'  => $value['name'],
        'image' => $value['image'],
    ];
}
if (($motw_cached = $mc1->get_value('top_movie_2')) === false) {
    $motw = sql_query("SELECT t.added, t.checked_by, t.id, t.seeders, t.poster, t.leechers, t.name, t.size, u.username, t.category, c.name AS cat, c.image, t.free, t.silver, t.subs, t.times_completed, t.added, t.size
                        FROM torrents AS t
                        INNER JOIN users AS u ON t.owner = u.id
                        INNER JOIN categories AS c ON t.category = c.id
                        INNER JOIN avps AS a ON t.id = a.value_u WHERE a.arg = 'bestfilmofweek'
                        LIMIT 1") or sqlerr(__FILE__, __LINE__);
    while ($motw_cache = mysqli_fetch_assoc($motw)) {
        $motw_cached[] = $motw_cache;
    }
    $mc1->cache_value('top_movie_2', $motw_cached, 0);
}

if (count($motw_cached) > 0) {
    $HTMLOUT .= "
    <a id='mow-hash'></a>
    <fieldset id='mow' class='header'>
        <legend class='flipper'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_mow_title']}</legend>
        <div class='text-center'>
            <div class='module'><div class='badge badge-hot'></div>
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th class='span1 text-center'>{$lang['index_mow_type']}</th>
                            <th class='span8'>{$lang['index_mow_name']}</th>
                            <th class='span1 text-center'>{$lang['index_mow_snatched']}</th>
                            <th class='span1 text-center'>{$lang['index_mow_seeder']}</th>
                            <th class='span1 text-center'>{$lang['index_mow_leecher']}</th>
                        </tr>
                    </thead>
                    <tbody>";
    if ($motw_cached) {
        foreach ($motw_cached as $m_w) {
            $poster = empty($m_w['poster']) ? "<img src='{$site_config['pic_base_url']}noposter.png' width='150' height='220' />" : "<img src='" . htmlsafechars($m_w['poster']) . "' width='150' height='220' />";
            $title = "
                <div class='flex'>
                    <span class='margin10'>
                        $poster
                    </span>
                    <span class='margin10'>
                        <b>{$lang['index_ltst_name']} " . htmlsafechars($m_w['name']) . "</b><br>
                        <b>Added: " . get_date($m_w['added'], 'DATE', 0, 1) . "</b><br>
                        <b>Size: " . mksize(htmlsafechars($m_w['size'])) . "</b><br>
                        <b>{$lang['index_ltst_seeder']} " . (int)$m_w['seeders'] . "</b><br>
                        <b>{$lang['index_ltst_leecher']} " . (int)$m_w['leechers'] . "</b><br>
                    </span>
                </div>";
            $mw['cat_name'] = htmlsafechars($change[$m_w['category']]['name']);
            $mw['cat_pic'] = htmlsafechars($change[$m_w['category']]['image']);

            $HTMLOUT .= "
                        <tr>
                            <td class='span1 text-center'><img src='./images/caticons/" . get_categorie_icons() . "/" . htmlsafechars($mw['cat_pic']) . "' class='tooltipper' alt='" . htmlsafechars($mw['cat_name']) . "' title='" . htmlsafechars($mw['cat_name']) . "' /></td>
                            <td class='span8'><a href='{$site_config['baseurl']}/details.php?id=" . (int)$m_w['id'] . "' class='tooltipper' title=\"$title\"><b>" . htmlsafechars($m_w['name']) . "</b></a></td>
                            <td class='span1 text-center'>" . (int)$m_w['times_completed'] . "</td>
                            <td class='span1 text-center'>" . (int)$m_w['seeders'] . "</td>
                            <td class='span1 text-center'>" . (int)$m_w['leechers'] . "</td>
                        </tr>";
        }
        $HTMLOUT .= "
                    </tbody>
                </table>
            </div>
        </div>
    </fieldset>";
    } else {
        if (empty($motw_cached)) {
            $HTMLOUT .= "
                        <tr>
                            <td colspan='5'>{$lang['index_mow_no']}!</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </fieldset>";
        }
    }
}
