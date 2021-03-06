<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'torrenttable_functions.php';
require_once INCL_DIR . 'pager_functions.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('torrenttable_functions'), load_language('bookmark'));
$htmlout = '';
function bookmarktable($res, $variant = 'index')
{
    global $site_config, $CURUSER, $lang;
    $htmlout = "
    <span>
        {$lang['bookmarks_icon']}
        <img src='{$site_config['pic_base_url']}aff_cross.gif' alt='{$lang['bookmarks_del']}' border='none' />{$lang['bookmarks_del1']}
        <img src='{$site_config['pic_base_url']}zip.gif' alt='{$lang['bookmarks_down']}' border='none' />{$lang['bookmarks_down1']}
        <img alt='{$lang['bookmarks_private']}' src='{$site_config['pic_base_url']}key.gif' border='none'  /> {$lang['bookmarks_private1']}
        <img src='{$site_config['pic_base_url']}public.gif' alt='{$lang['bookmarks_public']}' border='none'  />{$lang['bookmarks_public1']}
    </span>
    <div class='table-wrapper'>
        <div class='container-fluid portlet'>
            <table class='table table-bordered table-striped top20 bottom20''>
                <thead>
                    <tr>
                        <th class='text-center'>{$lang['torrenttable_type']}</th>
                        <th class='text-left'>{$lang['torrenttable_name']}</th>";
    $htmlout .= ($variant == 'index' ? '
                        <th class="text-center">' . $lang['bookmarks_del2'] . '</th>
                        <th class="text-right">' : '') . '' . $lang['bookmarks_down2'] . '</th>
                        <th class="text-right">' . $lang['bookmarks_share'] . '</th>';
    if ($variant == 'mytorrents') {
        $htmlout .= "
                        <th class='text-center'>{$lang['torrenttable_edit']}</th>
                        <th class='text-center'>{$lang['torrenttable_visible']}</th>";
    }
    $htmlout .= "
                        <th class='text-right'>{$lang['torrenttable_files']}</th>
                        <th class='text-right'>{$lang['torrenttable_comments']}</th>
                        <th class='text-center'>{$lang['torrenttable_added']}</th>
                        <th class='text-center'>{$lang['torrenttable_size']}</th>
                        <th class='text-center'>{$lang['torrenttable_snatched']}</th>
                        <th class='text-right'>{$lang['torrenttable_seeders']}</th>
                        <th class='text-right'>{$lang['torrenttable_leechers']}</th>";
    if ($variant == 'index') {
        $htmlout .= "
                        <th class='text-center'>{$lang['torrenttable_uppedby']}</th>";
    }
    $htmlout .= "
                    </tr>
                </thead>
                <tbody>";
    $categories = genrelist();
    foreach ($categories as $key => $value) {
        $change[$value['id']] = [
            'id'    => $value['id'],
            'name'  => $value['name'],
            'image' => $value['image'],
        ];
    }
    while ($row = mysqli_fetch_assoc($res)) {
        $row['cat_name'] = htmlsafechars($change[$row['category']]['name']);
        $row['cat_pic'] = htmlsafechars($change[$row['category']]['image']);
        $id = (int)$row['id'];
        $htmlout .= "
                    <tr>
                        <td class='text-center'>";
        if (isset($row['cat_name'])) {
            $htmlout .= '<a href="./browse.php?cat=' . (int)$row['category'] . '">';
            if (isset($row['cat_pic']) && $row['cat_pic'] != '') {
                $htmlout .= "<img src='{$site_config['pic_base_url']}caticons/". get_categorie_icons() . "/" . htmlsafechars($row['cat_pic']) . "' alt='" . htmlsafechars($row['cat_name']) . "' class='tooltipper' title='" . htmlsafechars($row['cat_name']) . "' />";
            } else {
                $htmlout .= htmlsafechars($row['cat_name']);
            }
            $htmlout .= '</a>';
        } else {
            $htmlout .= '-';
        }
        $htmlout .= "
                        </td>";
        $dispname = htmlsafechars($row['name']);
        $htmlout .= "
                        <td class='text-left'>
                            <a href='./details.php?";
        if ($variant == 'mytorrents') {
            $htmlout .= 'returnto=' . urlencode($_SERVER['REQUEST_URI']) . '&amp;';
        }
        $htmlout .= "id=$id";
        if ($variant == 'index') {
            $htmlout .= '&amp;hit=1';
        }
        $htmlout .= "'><b>$dispname</b></a>&#160;
                        </td>";
        $htmlout .= ($variant == 'index' ? "
                        <td class='text-center'>
                            <a href='./bookmark.php?torrent={$id}&amp;action=delete'>
                                <img src='{$site_config['pic_base_url']}aff_cross.gif' border='0' alt='{$lang['bookmarks_del3']}' class='tooltipper' title='{$lang['bookmarks_del3']}' />
                            </a>
                        </td>" : '');
        $htmlout .= ($variant == 'index' ? "
                        <td class='text-center'>
                            <a href='./download.php?torrent={$id}'>
                                <img src='{$site_config['pic_base_url']}zip.gif' border='0' alt='{$lang['bookmarks_down3']}' class='tooltipper' title='{$lang['bookmarks_down3']}' />
                            </a>
                        </td>" : '');
        $bm = sql_query('SELECT * FROM bookmarks WHERE torrentid=' . sqlesc($id) . ' && userid=' . sqlesc($CURUSER['id']));
        $bms = mysqli_fetch_assoc($bm);
        if ($bms['private'] == 'yes' && $bms['userid'] == $CURUSER['id']) {
            $makepriv = "<a href='./bookmark.php?torrent={$id}&amp;action=public'>
                                <img src='{$site_config['pic_base_url']}key.gif' alt='{$lang['bookmarks_public2']}' class='tooltipper' title='{$lang['bookmarks_public2']}' />
                            </a>";
            $htmlout .= '' . ($variant == 'index' ? "
                        <td class='text-center'>
                            {$makepriv}
                        </td>" : '');
        } elseif ($bms['private'] == 'no' && $bms['userid'] == $CURUSER['id']) {
            $makepriv = "<a href='./bookmark.php?torrent=" . $id . "&amp;action=private'>
                                <img src='{$site_config['pic_base_url']}public.gif' border='0' alt='{$lang['bookmarks_private2']}' class='tooltipper' title='{$lang['bookmarks_private2']}' />
                            </a>";
            $htmlout .= '' . ($variant == 'index' ? "
                        <td class='text-center'>
                            {$makepriv}
                        </td>" : '');
        }
        if ($variant == 'mytorrents') {
            $htmlout .= "
                        </td>
                        <td class='text-center'>
                            <a href='./edit.php?returnto=" . urlencode($_SERVER['REQUEST_URI']) . '&amp;id=' . (int)$row['id'] . "'>{$lang['torrenttable_edit']}</a>";
        }
        if ($variant == 'mytorrents') {
            $htmlout .= "
                        <td class='text-right'>";
            if ($row['visible'] == 'no') {
                $htmlout .= '<b>' . $lang['torrenttable_not_visible'] . '</b>';
            } else {
                $htmlout .= '' . $lang['torrenttable_visible'] . '';
            }
            $htmlout .= "
                        </td>";
        }
        if ($variant == 'index') {
            $htmlout .= "
                        <td class='text-right'><b><a href='./filelist.php?id=$id'>" . (int)$row['numfiles'] . "</a></b></td>";
        } else {
            $htmlout .= "
                        <td class='text-right'><b><a href='./filelist.php?id=$id'>" . (int)$row['numfiles'] . "</a></b></td>";
        }
        if (!$row['comments']) {
            $htmlout .= "
                        <td class='text-right'>" . (int)$row['comments'] . "</td>";
        } else {
            if ($variant == 'index') {
                $htmlout .= "
                        <td class='text-right'><b><a href='./details.php?id=$id&amp;hit=1&amp;tocomm=1'>" . (int)$row['comments'] . "</a></b></td>";
            } else {
                $htmlout .= "
                        <td class='text-right'><b><a href='./details.php?id=$id&amp;page=0#startcomments'>" . (int)$row['comments'] . "</a></b></td>";
            }
        }
        $htmlout .= "
                        <td class='text-center'><span>" . str_replace(',', '<br>', get_date($row['added'], '')) . "</span></td>
                        <td class='text-center'>" . str_replace(' ', '<br>', mksize($row['size'])) . "</td>";
        if ($row['times_completed'] != 1) {
            $_s = '' . $lang['torrenttable_time_plural'] . '';
        } else {
            $_s = '' . $lang['torrenttable_time_singular'] . '';
        }
        $htmlout .= "
                        <td class='text-center'><a href='./snatches.php?id=$id'>" . number_format($row['times_completed']) . "<br>$_s</a></td>";
        if ((int)$row['seeders']) {
            if ($variant == 'index') {
                if ($row['leechers']) {
                    $ratio = (int)$row['seeders'] / (int)$row['leechers'];
                } else {
                    $ratio = 1;
                }
                $htmlout .= "
                        <td class='text-right'><b><a href='./peerlist.php?id=$id#seeders'><font color='" . get_slr_color($ratio) . "'>" . (int)$row['seeders'] . "</font></a></b></td>";
            } else {
                $htmlout .= "
                        <td class='text-right'><b><a class='" . linkcolor($row['seeders']) . "' href='./peerlist.php?id=$id#seeders'>" . (int)$row['seeders'] . "</a></b></td>";
            }
        } else {
            $htmlout .= "
                        <td class='text-right'><span class='" . linkcolor($row['seeders']) . "'>" . (int)$row['seeders'] . "</span></td>";
        }
        if ((int)$row['leechers']) {
            if ($variant == 'index') {
                $htmlout .= "
                        <td class='text-right'><b><a href='./peerlist.php?id=$id#leechers'>" . number_format($row['leechers']) . "</a></b></td>";
            } else {
                $htmlout .= "
                        <td class='text-right'><b><a class='" . linkcolor($row['leechers']) . "' href='./peerlist.php?id=$id#leechers'>" . (int)$row['leechers'] . "</a></b></td>";
            }
        } else {
            $htmlout .= "
                        <td class='text-right'>0</td>";
        }
        if ($variant == 'index') {
            $htmlout .= "
                        <td class='text-center'>" . (isset($row['owner']) ? format_username($row['owner']) : '<i>(' . $lang['torrenttable_unknown_uploader'] . ')</i>') . "</td>";
        }
        $htmlout .= "
                    </tr>";
    }
    $htmlout .= "
                </tbody>
            </table>
        </div>
    </div>";

    return $htmlout;
}

//==Bookmarks
$userid = isset($_GET['id']) ? (int)$_GET['id'] : $CURUSER['id'];
if (!is_valid_id($userid)) {
    stderr($lang['bookmarks_err'], $lang['bookmark_invalidid']);
}
if ($userid != $CURUSER['id']) {
    stderr($lang['bookmarks_err'], "{$lang['bookmarks_denied']}<a href='./sharemarks.php?id={$userid}'>{$lang['bookmarks_here']}</a>");
}
$res = sql_query('SELECT id, username FROM users WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_array($res);
$htmlout .= "
        <h1 class='text-center'>{$lang['bookmarks_my']}</h1>
        <div class='text-center'>
            <b><a href='./sharemarks.php?id=" . $CURUSER['id'] . "'>{$lang['bookmarks_my_share']}</a></b>
        </div>";
$res = sql_query('SELECT COUNT(id) FROM bookmarks WHERE userid = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_array($res);
$count = $row[0];
$torrentsperpage = $CURUSER['torrentsperpage'];
if (!$torrentsperpage) {
    $torrentsperpage = 25;
}
if ($count) {
    $pager = pager($torrentsperpage, $count, 'bookmarks.php?&amp;');
    $query1 = 'SELECT b.id as bookmarkid, t.owner, t.id, t.name, t.comments, t.leechers, t.seeders, t.save_as, t.numfiles, t.added, t.filename, t.size, t.views, t.visible, t.hits, t.times_completed, t.category
                FROM bookmarks AS b
                LEFT JOIN torrents AS t ON b.torrentid = t.id
                WHERE b.userid =' . sqlesc($userid) . "
                ORDER BY t.id DESC {$pager['limit']}" or sqlerr(__FILE__, __LINE__);
    $res = sql_query($query1) or sqlerr(__FILE__, __LINE__);
}
if ($count) {
    $htmlout .= $pager['pagertop'];
    $htmlout .= bookmarktable($res, 'index', true);
    $htmlout .= $pager['pagerbottom'];
}
echo stdhead($lang['bookmarks_stdhead']) . $htmlout . stdfoot();
