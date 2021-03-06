<?php
require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
check_user_status();
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('editlog'));
$HTMLOUT = '';
$file_data = ROOT_DIR . 'dir_list' . DIRECTORY_SEPARATOR . 'data_' . $CURUSER['username'] . '.txt';
if (file_exists($file_data)) {
    $data = unserialize(file_get_contents($file_data));
    $exist = true;
} else {
    $exist = false;
}
$fetch_set = [];
$i = 0;
$directories = [ROOT_DIR];
global $site_config;
$included_extentions = explode(' ', $site_config['coders_log_allowed_ext']);
foreach ($directories as $path) {
    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach($objects as $name => $object) {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if (in_array($ext, $included_extentions)) {
            $fetch_set[$i]['modify'] = filemtime($name);
            $fetch_set[$i]['size'] = filesize($name);
            $fetch_set[$i]['hash'] = hash_file('sha256', $name);
            $fetch_set[$i]['name'] = $name;
            $fetch_set[$i]['key'] = $i;
            $i++;
        }
    }
}
if (!$exist or (isset($_POST['update']) and ($_POST['update'] == 'Update'))) {
    $data = serialize($fetch_set);
    file_put_contents($file_data, $data);
    setSessionVar('success', "Coder's Log updated for {$CURUSER['username']}");
    $data = $fetch_set;
    unset($_POST);
}
reset($fetch_set);
reset($data);
$current = $fetch_set;
$last = $data;
foreach ($current as $x) {
    foreach ($last as $y) {
        if ($x['name'] == $y['name']) {
            if (($x['hash'] === $y['hash'])) {
                unset($current[$x['key']]);
                unset($last[$y['key']]);
            } else {
                $current[$x['key']]['status'] = 'modified';
            }
        }
        if (isset($last[$y['key']])) {
            $last[$y['key']]['status'] = 'deleted';
        }
    }
    if (isset($current[$x['key']]['name']) and !isset($current[$x['key']]['status'])) {
        $current[$x['key']]['status'] = 'new';
    }
}
$current += $last;
unset($last);
unset($data);
unset($fetch_set);
$HTMLOUT .= "
    <div class='container-fluid portlet'>
        <h2 class='text-center top20'>Coder's Log</h2>
        <div class='bordered bottom20'>
            <div class='alt_bordered transparent'>
                <div class='text-center'>Tracking " . str_replace(' ', ', ', $site_config['coders_log_allowed_ext']) . " files only!</div>
                <div class='text-center'>" . number_format(count($current)) . " files have been added, modifed or deleted since your last update of the " . number_format($i) . " files being tracked.</div>
            </div>
        </div>

        <table class='table table-bordered table-striped'>
            <thead>
                <tr>
                    <th>{$lang['editlog_new']}</th>
                    <th class='rowhead'>{$lang['editlog_added']}</th>
                </tr>
            </thead>";
reset($current);
$count = 0;
$current = array_msort($current, array('name'=>SORT_ASC));
foreach ($current as $x) {
    if ($x['status'] == 'new') {
        $HTMLOUT .= "
                <tr>
                    <td>" .
                        htmlsafechars(str_replace(ROOT_DIR, '', $x['name'])) . "
                    </td>
                    <td>" .
                        get_date($x['modify'], 'DATE', 0, 1) . "
                    </td>
                </tr>";
        ++$count;
    }
}
if (!$count) {
    $HTMLOUT .= "
                <tr>
                    <td colspan='2' class='text-white'>{$lang['editlog_no_new']}</td>
                </tr>";
}
$HTMLOUT .= "
        </table>
        <table class='table table-bordered table-striped top20'>
            <thead>
                <tr>
                    <th>{$lang['editlog_modified']}</th>
                    <th class='rowhead'>{$lang['editlog_modified1']}</th>
                </tr>
            </thead>";
reset($current);
$count = 0;
foreach ($current as $x) {
    if ($x['status'] == 'modified') {
        $HTMLOUT .= "
                <tr>
                    <td>" .
                        htmlsafechars(str_replace(ROOT_DIR, '', $x['name'])) . "
                    </td>
                    <td>" .
                        get_date($x['modify'], 'DATE', 0, 1) . "
                    </td>
                </tr>";
        ++$count;
    }
}
if (!$count) {
    $HTMLOUT .= "
                <tr>
                    <td colspan='2' class='text-white'>{$lang['editlog_no_modified']}</td>
                </tr>";
}
$HTMLOUT .= "
        </table>
        <table class='table table-bordered table-striped top20'>
            <thead>
                <tr>
                    <th>{$lang['editlog_deleted']}</th>
                    <th class='rowhead'>{$lang['editlog_deleted1']}</th>
                </tr>
            </thead>";
reset($current);
$count = 0;
foreach ($current as $x) {
    if ($x['status'] == 'deleted') {
        $HTMLOUT .= "
                <tr>
                    <td>" .
                        htmlsafechars(str_replace(ROOT_DIR, '', $x['name'])) . "
                    </td>
                    <td>" .
                        get_date($x['modify'], 'DATE', 0, 1) . "
                    </td>
                </tr>";
        ++$count;
    }
}
if (!$count) {
    $HTMLOUT .= "
                <tr>
                    <td colspan='2' class='text-white'>{$lang['editlog_no_deleted']}</td>
                </tr>";
}
$HTMLOUT .= "
        </table>
        <form method='post' action='staffpanel.php?tool=editlog&amp;action=editlog'>
            <div class='text-center top20 bottom20'>
                <input name='update' type='submit' value='{$lang['editlog_update']}' class='btn' />
            </div>
        </form>
    </div>";
echo stdhead($lang['editlog_stdhead']) . $HTMLOUT . stdfoot();
