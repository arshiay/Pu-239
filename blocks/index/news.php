<?php
$adminbutton = '';
if ($CURUSER['class'] >= UC_STAFF) {
    $adminbutton = "
        <a class='pull-right' href='staffpanel.php?tool=news&amp;mode=news'>{$lang['index_news_title']}</a>";
}
$HTMLOUT .= "
    <a id='news-hash'></a>
    <fieldset id='news' class='header'>
        <legend class='flipper'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['news_title']}
            <span class='news'>{$adminbutton}</span>
        </legend>
        <div class='bordered padleft10 padright10'>
            <div class='alt_bordered transparent text-center'>";
$prefix = 'min5l3ss';
$news = $mc1->get_value('latest_news_');
if ($news === false) {
    $news = [];
    $res = sql_query('SELECT ' . $prefix . '.id AS nid, ' . $prefix . '.userid, ' . $prefix . '.added, ' . $prefix . '.title, ' . $prefix . '.body, ' . $prefix . '.sticky, ' . $prefix . '.anonymous, u.username, u.id, u.class, u.warned, u.chatpost, u.pirate, u.king, u.leechwarn, u.enabled, u.donor FROM news AS ' . $prefix . ' LEFT JOIN users AS u ON u.id = ' . $prefix . '.userid WHERE ' . $prefix . '.added + ( 3600 *24 *45 ) > ' . TIME_NOW . ' ORDER BY sticky, ' . $prefix . '.added DESC LIMIT 10') or sqlerr(__FILE__, __LINE__);
    while ($array = mysqli_fetch_assoc($res)) {
        $news[] = $array;
    }
    $mc1->cache_value('latest_news_', $news, $site_config['expires']['latest_news']);
}
$news_flag = 0;
if ($news) {
    foreach ($news as $array) {
        $button = '';
        if ($CURUSER['class'] >= UC_STAFF) {
            $hash = md5('the@@saltto66??' . $array['nid'] . 'add' . '@##mu55y==');
            $button = "
                <div class='pull-right'>
                    <a href='staffpanel.php?tool=news&amp;mode=edit&amp;newsid=" . (int)$array['nid'] . "'>
                        <i class='icon-edit' title='{$lang['index_news_ed']}'></i>
                    </a>
                    <a href='staffpanel.php?tool=news&amp;mode=delete&amp;newsid=" . (int)$array['nid'] . "&amp;h={$hash}'>
                        <i class='icon-remove' title='{$lang['index_news_del']}'></i>
                    </a>
                </div>";
        }
        $HTMLOUT .= "
            <div class='article'>";
        if ($news_flag < 2) {
            $HTMLOUT .= "
                    <div class='section'>
                        <a href=\"javascript: klappe_news('a" . (int)$array['nid'] . "')\">
                            <img border='0' src='./images/plus.png' id='pica" . (int)$array['nid'] . "' alt='{$lang['index_hide_show']}' /> " . get_date($array['added'], 'DATE') . "{$lang['index_news_txt']}" . '' . htmlsafechars($array['title']) . "
                        </a>
                        {$lang['index_news_added']}<b>" . (($array['anonymous'] == 'yes' && $CURUSER['class'] < UC_STAFF && $array['userid'] != $CURUSER['id']) ? "<i>{$lang['index_news_anon']}</i>" : format_username($array['userid'])) . "</b>
                        {$button}
                    </div>
                </div>";
            $HTMLOUT .= '
                <div id="ka' . (int)$array['nid'] . '" style="display:' . ($array['sticky'] == 'yes' ? '' : 'none') . ';margin-left:20px;margin-top:10px;"> ' . format_comment($array['body'], 0) . '
        </div><br> ';
            $news_flag = ($news_flag + 1);
        } else {
            $HTMLOUT .= "
                <div class='section'>
                    <a href=\"javascript: klappe_news('a" . (int)$array['nid'] . "')\">
                        <img border=\"0\" src='./images/plus.png' id='pica" . (int)$array['nid'] . "' alt='{$lang['index_news_title']}' /> " . get_date($array['added'], 'DATE') . "{$lang['index_news_txt']}" . '' . htmlsafechars($array['title']) . "
                    </a>
                    {$lang['index_news_added']}<b>" . (($array['anonymous'] == 'yes' && $CURUSER['class'] < UC_STAFF && $array['userid'] != $CURUSER['id']) ? "<i>{$lang['index_news_anon']}</i>" : format_username($array['userid'])) . "</b>
                    {$button}
                </div>
            </div>";
            $HTMLOUT .= '
            <div id="ka' . (int)$array['nid'] . '" style="display:' . ($array['sticky'] == 'yes' ? '' : 'none') . ';margin-left:20px;margin-top:10px;"> ' . format_comment($array['body'], 0) . '
            </div>';
        }
    }
    $HTMLOUT .= "
            </div>
        </div>
    </fieldset>";
}
if (empty($news)) {
    $HTMLOUT .= "
        " . format_comment($lang['index_news_not']) . "
            </div>
        </div>
    </fieldset>";
}
