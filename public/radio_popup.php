<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
//require_once INCL_DIR . 'html_functions.php';
check_user_status();
$lang = array_merge(load_language('global'));
require_once ROOT_DIR . 'radio.php';
global $CURUSER, $site_config;

$body_class = 'background-15 h-style-1 text-1 skin-2';
$HTMLOUT = '';
$HTMLOUT = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
    <meta name='MSSmartTagsPreventParsing' content='TRUE' />
	<title>{$site_config['site_name']} Radio</title>
    <link rel='stylesheet' href='" . get_file('css') . "' />
</head>
<body class='$body_class'>
    <script>
        var theme = localStorage.getItem('theme');
        if (theme) {
            document.body.className = theme;
        }
        function roll_over(img_name, img_src) {
            document[img_name].src=img_src;
        }
    </script>
    <h2>{$site_config['site_name']} Site Radio</h2>
    <div>
        <a href='http://{$radio['host']}:{$radio['port']}/listen.pls' onmouseover=\"roll_over('winamp', './images/winamp_over.png')\" onmouseout=\"roll_over('winamp', './images/winamp.png')\">
            <img src='./images/winamp.png' name='winamp' alt='Click here to listen with Winamp' title='Click here to listen with Winamp' />
        </a>
        <a href='http://{$radio['host']}:{$radio['port']}/listen.asx' onmouseover=\"roll_over('wmp', './images/wmp_over.png')\" onmouseout=\"roll_over('wmp', './images/wmp.png')\">
            <img src='./images/wmp.png' name='wmp' alt='Click here to listen with Windows Media Player' title='Click here to listen with Windows Media Player' />
        </a>
    </div>
    {radioinfo($radio)}
    <div class='text-center'>
        <a class='altlink' href='javascript: window.close()'><b>[ Close window ]</b></a>
    </div>
</body>
</html>";
echo $HTMLOUT;
