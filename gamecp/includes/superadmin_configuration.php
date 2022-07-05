<?php
/**
 * Game Control Panel v2
 * Copyright (c) www.intrepid-web.net
 *
 * The use of this product is subject to a license agreement
 * which can be found at http://www.intrepid-web.net/rf-game-cp-v2/license-agreement/
 */

if (!defined('COMMON_INITIATED')) {
    die("Hacking attempt! Logged");
}

if (!empty($setmodules)) {
    $file = basename(__FILE__);
    $module[_l('Super Admin')][_l('<b>Configuration</b>')] = $file;
    return;
}

$lefttitle = _l('Super Admin - Game CP Configuration');
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    $super_admin = explode(",", $admin['super_admin']);
    $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : '';

    if ($isuser = true && in_array($userdata['username'], $super_admin)) {

        if (empty($page)) {

            $cfgs = array();

            $out .= '<form method="post">' . "\n";
            connectgamecpdb();
            $config_query = "SELECT * FROM gamecp_config";
            if (!($config_result = mssql_query($config_query, $gamecp_dbconnect))) {
                $out .= "Unable query the database";
            }
            while ($configx = mssql_fetch_array($config_result)) {
                $cfgs[$configx['config_cat']][$configx['config_name']] = array(
                    'config_name' => $configx['config_name'],
                    'config_description' => $configx['config_description'],
                    'config_title' => $configx['config_title'],
                    'config_fields' => $configx['config_fields'],
                    'config_default' => $configx['config_default'],
                    'config_type' => $configx['config_type'],
                    'config_value' => $configx['config_value'],
                );
            }

            foreach ($cfgs as $cat => $data) {
                $out .= '<table class="table table-bordered table-condensed table-hover">' . "\n";
                $out .= '   <tbody>';
                $out .= '	<tr>' . "\n";
                $out .= '		<th>' . strtoupper(str_replace('_', ' ', $cat)) . '</th>' . "\n";
                $out .= '	</tr>' . "\n";

                foreach ($data as $cfg) {
                    $out .= '	<tr>' . "\n";
                    $out .= '		<td class="alt2"><b>' . $cfg['config_title'] . '</b><br/><i>' . $cfg['config_description'] . '</i></td>' . "\n";
                    $out .= '       <td class="alt1">' . generate_field($cfg['config_name'], $cfg['config_value'], $cfg['config_type'], $cfg['config_fields']) . '</td>';
                    $out .= '	</tr>' . "\n";
                };
                $out .= '   </tbody>';

                $out .= "</table>";
                $out .= '<br/><br/>';
            }


            // Free Result
            @mssql_free_result($config_result);
            $out .= '<table width="100%" cellpadding="4" cellspacing="2" border="0">' . "\n";
            $out .= '	<tr>' . "\n";
            $out .= '		<td colspan="2" style="text-align: center;"><input type="hidden" name="page" value="update"/><input type="submit"  class="btn btn-primary" name="submit" value="Update Config"/></td>' . "\n";
            $out .= '	</tr>' . "\n";
            $out .= "</table>";
            $out .= '</form>';
        } elseif ($page == "update") {
            $configx = (isset($_POST['config'])) ? $_POST['config'] : '';

            if ($configx != '') {
                connectgamecpdb();
                $trigger_fail = false;
                foreach ($configx as $key => $value) {
                    $update_query = "UPDATE gamecp_config SET config_value = '" . antiject($value) . "' WHERE config_name = '" . antiject($key) . "'";
                    if (!($update_result = mssql_query($update_query))) {
                        $trigger_fail = true;
                        $out .= '<p style="text-align: center; font-weight: bold;">Unable to update the config, please contact a developer.</p>';
                        return;
                    }
                }
                if (!$trigger_fail) {
                    $out .= '<p style="text-align: center; font-weight: bold;">Configuration has been successfully updated.</p>';

                    // Writing an admin log :D
                    gamecp_log(2, $userdata['username'], "SUPER ADMIN - CONFIG - Updated the GameCP config", 1);
                }

            }
        } else {
            $out .= _l('page_not_found');
        }

    } else {
        $out .= _l('no_permission');
    }

} else {
    $out .= _l('invalid_page_load');
}

?>