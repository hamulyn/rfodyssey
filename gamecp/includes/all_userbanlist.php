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
    $module[_l('Server Stats')][_l('Banned Users')] = $file;
    return;
}

$lefttitle = _l('Latest Banned Users');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : "";
    $page_gen = (isset($_GET['page_gen'])) ? $_GET['page_gen'] : '1';
    $search_fun = (isset($_GET['search_fun'])) ? $_GET['search_fun'] : "";
    $account_name = (isset($_GET['account_name'])) ? $_GET['account_name'] : '';
    $account_name = preg_replace(sql_regcase("/(from|select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/"), "", $account_name);
    $search_query = '';
    $query_p2 = '';
    $enable_exit = false;
    $top_limit = 50;
    $max_pages = 10;
    $enable_search = false;

    connectuserdb();

    $out .= '<form method="GET">' . "\n";
    $out .= '<p style="text-align: right; font-weight: bold; margin: 0; padding: 0 2px 4px 0;">Look up a banned account: <input type="text" class="form-control" name="account_name" value="' . $account_name . '"/> <input type="submit"  class="btn btn-default" name="search_fun" value="Search" /></p>' . "\n";
    $out .= '<input type="hidden" name="do" value="' . $_GET['do'] . '"/>' . "\n";
    $out .= '</form>' . "\n";

    if ($search_fun != "") {

        if ($account_name == "") {
            $enable_exit = true;
            $out .= "<p align='center'><b>Enter a valid account name</b></p>";
        }

        if (preg_match("/[^a-zA-Z0-9_-]/i", $account_name)) {
            $enable_exit = true;
            $out .= "<p align='center'><b>Enter a valid account name (2)</b></p>";
        }

        if ($enable_exit != true) {

            $search_query = ' WHERE ';

            if ($account_name != "") {
                $user_sql = "SELECT Serial FROM RF_User.dbo.tbl_UserAccount WHERE id = convert(binary,'$account_name')";
                if (!($user_result = mssql_query($user_sql, $user_dbconnect))) {
                    $enable_exit = true;
                    $out .= '<p style="text-align: center; font-weight: bold;">SQL Error while trying to obtain account information</p>';
                } else {
                    if (mssql_num_rows($user_result) <= 0) {
                        $enable_exit = true;
                    } else {
                        $user_info = mssql_fetch_array($user_result);
                        $account_serial = $user_info['Serial'];

                        $search_query .= " B.nAccountSerial = '$account_serial' ";

                        $enable_search = true;
                    }
                }
                // Free Result
                @mssql_free_result($user_result);

            }

            if ($enable_exit == true) {
                $search_query = '';
                $query_p2 .= ' WHERE ';
            } else {
                $query_p2 .= ' AND ';
            }

        } else {
            $query_p2 .= ' WHERE ';
        }

    } else {
        $query_p2 .= ' WHERE ';
    }
    $out .= '<table class="table">' . "\n";
    $out .= '<tr>';
    $out .= '<th>#</th>';
    $out .= '<th>Start Date</th>';
    $out .= '<th>End Date</th>';
    $out .= '<th>Account Name</th>';
    $out .= '<th>Banned By</th>';
    $out .= '<th>Reason for ban</th>';
    $out .= '</tr>';

    $query_p1 = "SELECT
	CONVERT(varchar, U.id) AS username, B.nAccountSerial, B.dtStartDate AS startdate, B.nPeriod, B.nKind, B.szReason, B.GMWriter
	FROM 
		tbl_UserBan AS B
	INNER JOIN
		tbl_UserAccount AS U
	ON U.serial = B.nAccountSerial
	$search_query";

    $query_p2 .= "B.nAccountSerial NOT IN ( SELECT TOP [OFFSET] B.nAccountSerial
	FROM 
		tbl_UserBan AS B
	INNER JOIN
		tbl_UserAccount AS U
	ON U.serial = B.nAccountSerial
	$search_query
	ORDER BY B.dtStartDate DESC) ORDER BY B.dtStartDate DESC";

    // Pageination
    if (!$enable_search) {
        include('./includes/pagination/ps_pagination.php');
    }

    if ($page_gen == 1) {
        $i = 1;
    } else {
        $i = ($page_gen * $top_limit) - ($top_limit - 1);
    }

    if (!$enable_search) {
        $url = str_replace("&page_gen=" . $page_gen, "", $_SERVER["REQUEST_URI"]);
        $pager = new PS_Pagination($user_dbconnect, $query_p1, $query_p2, $top_limit, $max_pages, $url);

        //The paginate() function returns a mysql
        //result set for the current page
        $rs = $pager->paginate();
    } else {
        $rs = mssql_query($query_p1) or die("Seems we got a problem with the query portion");
    }

    connectdatadb();
    while ($row = mssql_fetch_array($rs)) {

        $username = ereg_replace(";$", "", $row['username']);
        $username = ereg_replace("\\\\", "", $username);

        $startdate = preg_replace('/:[0-9][0-9][0-9]/', '', $row['startdate']);
        $startdate = strtotime($startdate);
        $enddate = $row['nPeriod'] * 3600;
        $enddate = $startdate + $enddate;
        $enddate = date("d/m/Y h:i A", $enddate);
        $startdate = date("d/m/Y h:i A", $startdate);

        if ($row['GMWriter'] == 'WS0') {
            $row['szReason'] = 'Auto-banned by FireGuard';
        }

        $gm_banned = $row['GMWriter'];
        if ($gm_banned == 'WS0') {
            $gm_banned = '-';
        }

        if (!preg_match("/TEMP/", $row['szReason'])) {
            $out .= '<tr>';
            $out .= '<td>' . $i . '</td>';
            $out .= '<td>' . $startdate . '</td>';
            $out .= '<td>' . $enddate . '</td>';
            $out .= '<td>' . $username . '</td>';
            $out .= '<td>' . $gm_banned . '</td>';
            $out .= '<td>' . $row['szReason'] . '</td>';
            $out .= '</td>';

            $char_query = "SELECT Serial, Name, DeleteName FROM RF_World.dbo.tbl_base WHERE DCK = '0' AND AccountSerial = '" . $row['nAccountSerial'] . "' ORDER BY Serial DESC";
            $char_result = mssql_query($char_query) or die("A user might not exist? (" . $row['nAccountSerial'] . ") ERROR");

            while ($char = mssql_fetch_array($char_result)) {
                $out .= '<tr>';
                $out .= '<td>&raquo;</td>';
                $out .= '<td colspan="5">' . $char['Name'] . '</td>';
                $out .= '</tr>';
            }

            // Free Result
            @mssql_free_result($char_result);

        }

        $i++;

    }

    if (mssql_num_rows($rs) <= 0) {
        $out .= '<tr>' . "\n";
        $out .= '<td colspan="6" style="text-align: center;">No banned user(s) found</td>' . "\n";
        $out .= '</tr>' . "\n";
    } else {
        if (!$enable_search) {
            $out .= '<tr>';
            $out .= '<td colspan="6" style="text-align: center;">' . $pager->renderFullNav() . '</td>';
            $out .= '</tr>';
        }
    }

    $out .= "</table>";

    // Free Result
    @mssql_free_result($rs);


} else {
    $out .= _l('invalid_page_load');
}
?>