<?php
require_once ROOT_DIR . 'tfreak.php';
$HTMLOUT .= "
    <a id='tfreak-hash'></a>
    <fieldset id='tfreak' class='header'>
        <legend class='flipper'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_torr_freak']}</legend>
        <div class='bordered padleft10 padright10 text-center'>" .
            rsstfreakinfo() . "
        </div>
    </fieldset>";
