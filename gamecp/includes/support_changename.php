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
    $module[_l('Logs')][_l('Name Changes Logs')] = $file;
    return;
}

$lefttitle = _l('Support Desk - Name Changes Logs');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    if (hasPermissions($do)) {

        $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : "";
        $search_fun = (isset($_POST['search_fun'])) ? $_POST['search_fun'] : "";

        if (empty($page)) {

            connectgamecpdb();
            $query_text = 'SELECT
			id, username_charid, username_oldname, username_newname, username_ip
			FROM gamecp_username_log
			ORDER BY id DESC';
            $result = mssql_query($query_text);


            $out .= '<table class="table table-bordered">' . "\n";
            $out .= '<tr>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>Char ID</b></td>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>OLD NAME</b></td>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>NEW NAME</b></td>';
            $out .= '<td class="thead"style="padding: 4px;" nowrap><b>IP Address</b></td>';
            $out .= '</tr>';

            while ($row = mssql_fetch_array($result)) {

                $out .= '<tr>';
                $out .= '<td class="alt2" width="1%" nowrap>' . $row['username_charid'] . '</td>';
                $out .= '<td class="alt1" width="10%" nowrap>' . $row['username_oldname'] . '</td>';
                $out .= '<td class="alt1" width="10%" nowrap>' . $row['username_newname'] . '</td>';
                $out .= '<td class="alt1" width="15%" nowarp>' . $row['username_ip'] . '</td>';
                $out .= '</td>';
            }

            $out .= "</table>";

            // Free Result
            @mssql_free_result($result);

        } else {

            $out .= _l('invalid_page_id');

        }

    } else {

        $out .= _l('no_permission');

    }

} else {
    $out .= _l('invalid_page_load');
}
?>