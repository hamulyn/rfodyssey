<?php
//////////////////////////////////////////////////////////////
//    Game CP: RF Online Game Control Panel                    //
//    Module: admin_donations_google.php                        //
//    Copyright (C) www.AaronDM.com                            //
//////////////////////////////////////////////////////////////

# Write Module to Menu
if (!defined('COMMON_INITIATED')) {
    die("Hacking attempt! Logged");
}

if (!empty($setmodules)) {
    $file = basename(__FILE__);
    $module[_l('Server Admin')][_l('Manage Concil')] = $file;
    return;
}

$lefttitle = _l('Support Desk - Manage Concil');;
$time = date('F j Y G:i');

# Build the rest of the site
if ($this_script == $script_name) {

    $exit_stage = 0;

    if (isset($isuser) && $isuser == true) {


            $out .= '<center>'."\n";
            $out .= '</iframe><iframe  src="includes/Concil/Concil.php"   align="middle" height="1100" width="800" border="0" frameborder="0" scrolling="no"></iframe>';
    } else {
        $out .= $lang['no_permission'];
    }
} else {
    $out .= $lang['invalid_page_load'];
}
?>