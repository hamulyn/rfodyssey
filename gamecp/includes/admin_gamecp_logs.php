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
    $module[_l('Logs')][_l('Game CP Logs')] = $file;
    return;
}
// Just incase its not defined
if (!isset($logTypes_array)) {
    $logTypes_array = array("--- N/A ---",
        "GAMECP - CHANGE PASSWORD",
        "GAMECP - CHANGE EMAIL",		
        "GAMECP - DONATE",
        "GAMECP - DELETE CHARACTER",
        "SUPPORT - USER INFO",
        "SUPPORT - LOG OUT LOGS",
        "ADMIN - MAIL LOGS",
        "ADMIN - CHAR LOOK UP",
        'ADMIN - DELETE CHARACTER',
        "PAYPAL - <b>REVERSED</b>",
        "PAYPAL - <b>CANCELED REVERSAL</b>",
        "PAYPAL - SUCCESSFUL PAYMENT",
        "PAYPAL - ADDED CREDITS",
        "PAYPAL - DUPLICATE TXN ID",
        "PAYPAL - INVALID BUSINESS",
        "PAYPAL - <b>INCOMPLETE</b>",
        "PAYPAL - PAYMENT INVALID",
        "PAYPAL - PAYMENT FAILED",
        "PAYPAL - Unable to connect to www.paypal.com",
        "GAMECP - PASSWORD RECOVERY",
        "GAMECP - ACCOUNT INFO",
        "ADMIN - ITEM EDIT",
        "ADMIN - BANK EDIT",
        "ADMIN - ITEM SEARCH",
        "ADMIN - ITEM LIST",
        'ADMIN - GIVE ITEM',
        'ADMIN - DELETE ITEM',
        "ADMIN - MANAGE BANS - ADDED",
        "ADMIN - MANAGE BANS - UPDATED",
        "ADMIN - MANAGE BANS - UNBAN",
        "ADMIN - CHARACTER EDIT",
        "SUPER ADMIN - PERMISSIONS",
        'SUPER ADMIN - CONFIG',
        'ADMIN - MANAGE USERS',
        'ADMIN - MANAGE ITEMS - UPDATED',
        'ADMIN - MANAGE ITEMS - ADDED',
        'ADMIN - MANAGE ITEMS - DELETED',
        'ADMIN - MANAGE CATEGORIES',
        'GAMECP - CHANGE CHAR NAME',
        'ADMIN - MANAGE REDEEM',
        'ADMIN - VOTE SITES',
        'GAMECP - ACCOUNT INFO - UPDATED SIG - PHP CODE FOUND'
    );
    sort($logTypes_array);
}

$lefttitle = _l('Game CP - Logs');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    if (hasPermissions($do)) {

        $page = "";
        $search_fun = (isset($_GET['search_fun'])) ? $_GET['search_fun'] : "";
        $page_gen = (isset($_GET['page_gen'])) ? $_GET['page_gen'] : '1';
        $search_query = " ";
        $query_p2 = "";

        if (empty($page)) {

            $out .= '<table class="table table-bordered table-condensed">' . "\n";
            $out .= '  <tr>';
            $out .= '	<td class="level_0" nowrap>Level 0</td>';
            $out .= '	<td class="level_1" nowrap>Level 1</td>';
            $out .= '	<td class="level_2" nowrap>Level 2</td>';
            $out .= '	<td class="level_3" nowrap>Level 3</td>';
            $out .= '	<td class="level_4" nowrap>Level 4</td>';
            $out .= '	<td class="level_5" nowrap>Level 5</td>';
            $out .= '  </tr>';
            $out .= '</table>';

            $out .= '<form method="get" action="' . $script_name . '">';
            $out .= '<table class="table table-bordered table-condensed">' . "\n";
            $out .= '		<tr>';
            $out .= '			<th>Search the Logs</th>';
            $out .= '		</tr>';
            $out .= '		<tr>';
            $out .= '			<td class="alt1">Account Name:</td>';
            $out .= '			<td class="alt2"><input type="text" class="form-control" name="account_name"/></td>';
            $out .= '		</tr>';
            $out .= '		<tr>';
            $out .= '			<td class="alt1">Search:</td>';
            $out .= '			<td class="alt2"><input type="text" class="form-control" name="text"/></td>';
            $out .= '		</tr>';
            $out .= '		<tr>';
            $out .= '			<td class="alt1">Log Type:</td>';
            $out .= '			<td class="alt2">';
            $out .= '				<select class="form-control"name="log_type">';
            if (($logType_count = count($logTypes_array)) > 0) {
                foreach ($logTypes_array as $key => $value) {
                    $out .= '					<option value="' . $key . '">' . $value . '</option>';
                }
            }
            $out .= '				</select>';
            $out .= '			</td>';
            $out .= '		</tr>';
            $out .= '		<tr>';
            $out .= '			<td colspan="2"><input type="hidden" name="do" value="admin_gamecp_logs"/><input type="submit"  class="btn btn-default" value="Look Up" name="search_fun" /></td>';
            $out .= '		</tr>';
            $out .= '	</table>';
            $out .= '</form>';
            $out .= '<br/>';

            $out .= '<table class="table table-bordered table-condensed">' . "\n";
            $out .= '<tr>';
            $out .= '<td class="thead"style="padding: 4px;"><b>ID</b></td>';
            $out .= '<td class="thead"style="padding: 4px;"><b>Time</b></td>';
            $out .= '<td class="thead"style="padding: 4px;"><b>Account</b></td>';
            $out .= '<td class="thead"style="padding: 4px;"><b>Message</b></td>';
            $out .= '<td class="thead"style="padding: 4px;"><b>IP Address</b></td>';
            #$out .= '<td class="thead"style="padding: 4px;"><b>Page</b></td>';
            #$out .= '<td class="thead"style="padding: 4px;" nowarp><b>Browser</b></td>';
            $out .= '</tr>';

            # Write search query statements if applicable
            if ($search_fun != "") {
                $account_name = (isset($_GET['account_name'])) ? antiject($_GET['account_name']) : '';
                $text = (isset($_GET['text'])) ? antiject($_GET['text']) : '';
                $log_type = (isset($_GET['log_type']) && ($_GET['log_type'] != 0)) ? antiject($_GET['log_type']) : '';

                if ($account_name != "" OR $log_type != "") {
                    $search_query = " WHERE ";
                    if ($account_name != "") {
                        $account_name = trim($account_name);
                        $search_query .= "log_account = '" . $account_name . "'";
                    }

                    if ($log_type != "") {
                        if ($account_name != "") {
                            $search_query .= " AND ";
                        }
                        if ($text != '') {
                            $app = ' %' . $text . '%';
                        } else {
                            $app = '';
                        }
                        $search_query .= "log_message LIKE '" . $logTypes_array[$log_type] . "%" . $app . "'";

                    }

                    $query_p2 = " AND ";
                } else {
                    $query_p2 = "WHERE ";
                }

            } else {
                $query_p2 = "WHERE ";
            }

            // Pageination
            include('./includes/pagination/ps_pagination.php');

            connectgamecpdb();
            $query_text = 'SELECT id, log_level, log_time, log_account, log_message, log_ip, log_page, log_browser FROM gamecp_log' . $search_query;

            $query_p2 .= 'id NOT IN ( SELECT TOP [OFFSET] id FROM gamecp_log ' . $search_query . ' ORDER BY id DESC) ORDER BY id DESC';

            #$out .= $query_text.$query_p2."<br/>";

            //Create a PS_Pagination object
            $filename = $_GET['do'] . "_" . md5($query_text);
            if (!$query_count = readCache($filename . ".cache", 5)) {
                $query_count_result = mssql_query("SELECT COUNT(id) AS Count FROM gamecp_log $search_query");
                $query_count = mssql_fetch_array($query_count_result);
                $query_count = $query_count['Count'];
                writeCache($query_count, $filename . '.cache');
                // Free Result
                @mssql_free_result($query_count_result);
            }
            $url = str_replace("&page_gen=" . $page_gen, "", $_SERVER["REQUEST_URI"]);
            $pager = new PS_Pagination($gamecp_dbconnect, $query_text, $query_p2, 50, 20, $url, $query_count);

            //The paginate() function returns a mysql
            //result set for the current page
            $rs = $pager->paginate();

            $out .= "Total number of logs found: " . number_format($pager->totalResults());

            while ($row = mssql_fetch_array($rs)) {

                if ($row['log_level'] == 5) {
                    $class = "level_5";
                } elseif ($row['log_level'] == 4) {
                    $class = "level_4";
                } elseif ($row['log_level'] == 3) {
                    $class = "level_3";
                } elseif ($row['log_level'] == 2) {
                    $class = "level_2";
                } elseif ($row['log_level'] == 1) {
                    $class = "level_1";
                } else {
                    $class = "level_0";
                }

                if ($row['log_ip'] == '66.211.170.66') {
                    $ip = 'PayPal Notify';
                } elseif ($row['log_ip'] == '74.125.64.136') {
                    $ip = 'Google Notify';
                } else {
                    $ip = $row['log_ip'];
                }

                $out .= '<tr>';
                $out .= '<td class="' . $class . '" style="text-align: center; font-size: 10px;" nowrap>' . $row['id'] . '</td>';
                $out .= '<td class="' . $class . '" style="text-align: center; font-size: 10px;" nowrap>' . date("d/m/y - h:i:s A", $row['log_time']) . '</td>';
                $out .= '<td class="' . $class . '" style="font-size: 10px;" nowrap>' . $row['log_account'] . '</td>';
                $out .= '<td class="' . $class . '" style="font-size: 10px;" nowrap>' . $row['log_message'] . '</td>';
                $out .= '<td class="' . $class . '" style="text-align: center; font-size: 10px;" nowrap>' . $ip . '</td>';
                #$out .= '<td class="alt1" width="5%" style="font-size: 10px;">'.$row['log_page'].'</td>';
                #$out .= '<td class="'.$class.'" style="font-size: 10px;" nowrap>'.$row['log_browser'].'</td>';
                $out .= '</td>';
            }

            $out .= "</table>";
            $out .= "<br/>";
            $out .= $pager->renderFullNav();

            // Free Result
            @mssql_free_result($rs);

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