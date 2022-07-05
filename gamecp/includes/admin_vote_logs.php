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
    $module[_l('Logs')][_l('Vote Logs')] = $file;
    return;
}

$lefttitle = _l('Vote for Game Points Logs');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    $max_pages = 10;
    $top_limit = 60;
    $enable_exit = false;
    $search_fun = (isset($_GET['search_fun'])) ? 1 : '';
    $page_gen = (isset($_GET['page_gen'])) ? $_GET['page_gen'] : '1';
    $enable_exit = false;
    $search_query = '';
    $query_p2 = '';

    if (hasPermissions($do)) {

        $gen = (isset($_GET['page_gen'])) ? $_GET['page_gen'] : '1';

        $out .= '<form method="GET">' . "\n";
        $out .= '<table class="table table-bordered">' . "\n";
        $out .= '	<tr>' . "\n";
        $out .= '		<td class="thead" colspan="2">Search for a user</td>' . "\n";
        $out .= '	</tr>' . "\n";
        $out .= '	<tr>' . "\n";
        $out .= '		<td class="alt2" width="1" nowrap>Account Name:</td>' . "\n";
        $out .= '		<td class="alt1"><input type="text" class="form-control" name="account_name"/></td>' . "\n";
        $out .= '	</tr>' . "\n";
        $out .= '	<tr>' . "\n";
        $out .= '		<td class="alt2" width="1" nowrap>Account Serial:</td>' . "\n";
        $out .= '		<td class="alt1"><input type="text" class="form-control" name="account_serial"/></td>' . "\n";
        $out .= '	</tr>' . "\n";
        $out .= '	<tr>' . "\n";
        $out .= '		<td class="alt2" colspan="2" nowrap><input type="submit"  class="btn btn-default" name="search_fun" value="Search"/></td>' . "\n";
        $out .= '	</tr>' . "\n";
        $out .= '</table>' . "\n";
        $out .= '<input type="hidden" name="do" value="' . $_GET['do'] . '"/>' . "\n";
        $out .= '</form>' . "\n";

        $out .= '<table class="table table-bordered">' . "\n";
        $out .= '	<tr>' . "\n";
        $out .= '		<td class="thead" style="text-align: center;" nowrap>#</td>' . "\n";
        $out .= '		<td class="thead" nowrap>Date</td>' . "\n";
        $out .= '		<td class="thead" nowrap>Account Name</td>' . "\n";
        $out .= '		<td class="thead" nowrap>Gained</td>' . "\n";
        $out .= '		<td class="thead" nowrap>Total after vote</td>' . "\n";
        $out .= '		<td class="thead" nowrap>IP Address</td>' . "\n";
        $out .= '	</tr>' . "\n";


        if ($search_fun != '') {
            $account_name = (isset($_GET['account_name'])) ? antiject(trim($_GET['account_name'])) : '';
            $account_serial = (isset($_GET['account_serial']) && is_int((int)$_GET['account_serial'])) ? antiject(trim((int)$_GET['account_serial'])) : '';

            if ($account_name == '' && $account_serial == '') {
                $enable_exit = true;
                $out .= '<p style="text-align: center; font-weight: bold;">You must enter either an account name or a account serial</p>';
            }

            if ($enable_exit != true) {

                $search_query = ' WHERE ';

                if ($account_name != "") {
                    connectuserdb();
                    $user_sql = "SELECT Serial FROM tbl_UserAccount WHERE id = convert(binary,'$account_name')";
                    if (!($user_result = mssql_query($user_sql, $user_dbconnect))) {
                        $enable_exit = true;
                        $out .= '<p style="text-align: center; font-weight: bold;">SQL Error while trying to obtain account information</p>';
                    } else {
                        if (mssql_num_rows($user_result) <= 0) {
                            $enable_exit = true;
                            $out .= '<p style="text-align: center; font-weight: bold;">No such account name found in the database</p>';
                        } else {
                            $user_info = mssql_fetch_array($user_result);
                            $account_serial = $user_info['Serial'];

                            $search_query .= " log_account_serial = '$account_serial' ";
                        }
                    }
                    // Free Result
                    @mssql_free_result($user_result);
                } else {
                    $search_query .= " log_account_serial = '$account_serial' ";
                }

                if ($enable_exit == true) {
                    $search_query = '';
                    $query_p2 .= ' WHERE ';
                } else {
                    $query_p2 .= ' AND ';

                    // Write Admin Log
                    gamecp_log(0, $userdata['username'], "ADMIN - VOTE SITES - SEARCH - Account Name: $account_name | Account Serial: $account_serial", 1);
                }

            } else {
                $query_p2 .= ' WHERE ';
            }
        } else {
            $query_p2 .= ' WHERE ';
        }

        connectgamecpdb();
        $query_p1 = "SELECT
		log_id, log_account_serial, log_time, log_ip, log_points_gained, log_total_points
		FROM 
			gamecp_vote_log
		$search_query";
        $query_p2 .= "log_id NOT IN ( SELECT TOP [OFFSET] log_id FROM gamecp_vote_log
			$search_query ORDER BY log_id DESC) ORDER BY log_id DESC";

        // Pageination
        include('./includes/pagination/ps_pagination.php');

        //Create a PS_Pagination object
        $url = str_replace("&page_gen=" . $page_gen, "", $_SERVER["REQUEST_URI"]);
        $filename = $_GET['do'] . "_" . md5('vote-logs');
        if (!$query_count = readCache($filename . ".cache", 5)) {
            $query_count = mssql_query("SELECT COUNT(log_id) AS Count FROM gamecp_vote_log $search_query");
            $query_count = mssql_fetch_array($query_count);
            $query_count = $query_count['Count'];
            writeCache($query_count, $filename . '.cache');
        }
        $pager = new PS_Pagination($gamecp_dbconnect, $query_p1, $query_p2, $top_limit, $max_pages, $url, $query_count);

        //The paginate() function returns a mysql
        //result set for the current page
        $rs = $pager->paginate();

        if ($gen == 1) {
            $i = 1;
        } else {
            $i += (($gen - 1) * $top_limit) + 1;
        }

        connectuserdb();
        while ($row = mssql_fetch_array($rs)) {

            $char_result = mssql_query("SELECT convert(varchar,id) AS Account FROM tbl_UserAccount WHERE Serial = '" . $row['log_account_serial'] . "'");
            $char = mssql_fetch_array($char_result);

            $out .= '	<tr>' . "\n";
            $out .= '		<td class="alt2" style="text-align: center;" nowrap>' . $i . '</td>' . "\n";
            $out .= '		<td class="alt1" nowrap>' . date("d/m/y h:i:s A", $row['log_time']) . '</td>' . "\n";
            $out .= '		<td class="alt1" nowrap>' . antiject($char['Account']) . '</td>' . "\n";
            $out .= '		<td class="alt1" nowrap>' . $row['log_points_gained'] . '</td>' . "\n";
            $out .= '		<td class="alt1" nowrap>' . $row['log_total_points'] . '</td>' . "\n";
            $out .= '		<td class="alt1" nowrap>' . $row['log_ip'] . '</td>' . "\n";
            $out .= '	</tr>' . "\n";

            // Free Result
            @mssql_free_result($char_result);

            $i++;
        }

        if (mssql_num_rows($rs) <= 0) {
            $out .= '		<tr>' . "\n";
            $out .= '			<td class="alt1" colspan="8" style="text-align: center; font-weight: bold;">No redeem logs found for your account.</td>' . "\n";
            $out .= '		</tr>' . "\n";
        } else {
            $out .= '		<tr>' . "\n";
            $out .= '			<td class="alt2" colspan="8" style="text-align: center; font-weight: bold;">' . $pager->renderFullNav() . '</td>' . "\n";
            $out .= '		</tr>' . "\n";
        }
        $out .= "</table>";

        // Free Result
        @mssql_free_result($rs);

    } else {
        $out .= _l('no_permission');
    }

} else {
    $out .= _l('invalid_page_load');
}
?>