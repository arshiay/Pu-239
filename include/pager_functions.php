<?php
function pager($rpp, $count, $href, $opts = []) // thx yuna or whoever wrote it
{
    $pages = ceil($count / $rpp);
    if (!isset($opts['lastpagedefault'])) {
        $pagedefault = 0;
    } else {
        $pagedefault = floor(($count - 1) / $rpp);
        if ($pagedefault < 0) {
            $pagedefault = 0;
        }
    }
    if (isset($_GET['page'])) {
        $page = (int)$_GET['page'];
        if ($page < 0) {
            $page = $pagedefault;
        }
    } else {
        $page = $pagedefault;
    }
    $mp = $pages - 1;
    $as = '<i class="fa fa-nomargin fa-angle-double-left" aria-hidden="true"></i>';
    if ($page >= 1) {
        $pager .= "
                        <span>
                            <a href=''{$href}page=" . ($page - 1) . "' class='pager'>$as</a>
                        </span>";
    }
    $as = '<i class="fa fa-nomargin fa-angle-double-right" aria-hidden="true"></i>';
    $pager2 = $bregs = '';
    if ($page < $mp && $mp >= 0) {
        $pager2 .= "
                        <span>
                            <a href='{$href}page=" . ($page + 1) . "' class='pager'>$as</a>
                        </span>$bregs";
    } else {
        $pager2 .= $bregs;
    }
    if ($count) {
        $pagerarr = [];
        $dotted = 0;
        $dotspace = 3;
        $dotend = $pages - $dotspace;
        $curdotend = $page - $dotspace;
        $curdotstart = $page + $dotspace;
        for ($i = 0; $i < $pages; ++$i) {
            if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
                if (!$dotted) {
                    $pagerarr[] = '
                        <span class="pager">...</span>';
                }
                $dotted = 1;
                continue;
            }
            $dotted = 0;
            $start = $i * $rpp + 1;
            $end = $start + $rpp - 1;
            if ($end > $count) {
                $end = $count;
            }
            $text = $i + 1;
            if ($i != $page) {
                $pagerarr[] = "
                        <span>
                            <a title='$start - $end' href='{$href}page=$i' class='pager tooltipper'>
                                $text
                            </a>
                        </span>";
            } else {
                $pagerarr[] = "
                        <span class='highlight margin10'>
                            $text
                        </span>";
            }
        }
        $pagerstr = join('', $pagerarr);
        $pagertop = "<div class='text-center top20 bottom20'>
                        $pager $pagerstr $pager2
                    </div>";
        $pagerbottom = "
                    <div class='text-center'>Overall $count items in " . ($i) . ' page' . ($i > 1 ? '\'s' : '') . ", showing $rpp per page.</div>
                    <div class='margin10 text-center'>
                        $pager $pagerstr $pager2
                    </div>";
    } else {
        $pagertop = $pager;
        $pagerbottom = $pagertop;
    }
    $start = $page * $rpp;

    return [
        'pagertop'    => $pagertop,
        'pagerbottom' => $pagerbottom,
        'limit'       => "LIMIT $start,$rpp",
    ];
}

function pager_rep($data)
{
    $pager = [
        'pages'     => 0,
        'page_span' => '',
        'start'     => '',
        'end'       => '',
    ];
    $section = $data['span'] = isset($data['span']) ? $data['span'] : 2;
    $parameter = isset($data['parameter']) ? $data['parameter'] : 'page';
    $mini = isset($data['mini']) ? 'mini' : '';
    if ($data['count'] > 0) {
        $pager['pages'] = ceil($data['count'] / $data['perpage']);
    }
    $pager['pages'] = $pager['pages'] ? $pager['pages'] : 1;
    $pager['total_page'] = $pager['pages'];
    $pager['current_page'] = $data['start_value'] > 0 ? ($data['start_value'] / $data['perpage']) + 1 : 1;
    $previous_link = '';
    $next_link = '';
    if ($pager['current_page'] > 1) {
        $start = $data['start_value'] - $data['perpage'];
        $previous_link = "<a href='{$data['url']}&amp;$parameter=$start' class='tooltipper' title='Previous'><span class='{$mini}pagelink'>&lt;</span></a>";
    }
    if ($pager['current_page'] < $pager['pages']) {
        $start = $data['start_value'] + $data['perpage'];
        $next_link = "&#160;<a href='{$data['url']}&amp;$parameter=$start' class='tooltipper' title='Next'><span class='{$mini}pagelink'>&gt;</span></a>";
    }
    if ($pager['pages'] > 1) {
        if (isset($data['mini'])) {
            $pager['first_page'] = "<img src='./images/multipage.gif' alt='' title='' />";
        } else {
            $pager['first_page'] = "<span style='background: #F0F5FA; border: 1px solid #072A66;padding: 1px 3px 1px 3px;'>{$pager['pages']} Pages</span>&#160;";
        }
        for ($i = 0; $i <= $pager['pages'] - 1; ++$i) {
            $RealNo = $i * $data['perpage'];
            $PageNo = $i + 1;
            if ($RealNo == $data['start_value']) {
                $pager['page_span'] .= $mini ? "&#160;<a href='{$data['url']}&amp;$parameter={$RealNo}' class='tooltipper' title='$PageNo'><span  class='{$mini}pagelink'>$PageNo</span></a>" : "&#160;<span class='pagecurrent'>{$PageNo}</span>";
            } else {
                if ($PageNo < ($pager['current_page'] - $section)) {
                    $pager['start'] = "<a href='{$data['url']}' class='tooltipper' title='Goto First'><span class='{$mini}pagelinklast'>&laquo;</span></a>&#160;";
                    continue;
                }
                if ($PageNo > ($pager['current_page'] + $section)) {
                    $pager['end'] = "&#160;<a href='{$data['url']}&amp;$parameter=" . (($pager['pages'] - 1) * $data['perpage']) . "' class='tooltipper' title='Go To Last'><span class='{$mini}pagelinklast'>&raquo;</span></a>&#160;";
                    break;
                }
                $pager['page_span'] .= "&#160;<a href='{$data['url']}&amp;$parameter={$RealNo}' class='tooltipper' title='$PageNo'><span  class='{$mini}pagelink'>$PageNo</span></a>";
            }
        }
        $float = $mini ? '' : ' fleft';
        $pager['return'] = "<div class='pager{$float}'>{$pager['first_page']}{$pager['start']}{$previous_link}{$pager['page_span']}{$next_link}{$pager['end']}
            </div>";
    } else {
        $pager['return'] = '';
    }

    return $pager['return'];
}
