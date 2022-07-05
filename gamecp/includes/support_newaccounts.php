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
    $module[_l('Support')][_l('New Accounts')] = $file;
    return;
}

$lefttitle = _l('Support Desk - Lates Registered Accounts');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    if (hasPermissions($do)) {

        $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : "";
        $page_gen = (isset($_GET['page_gen'])) ? $_GET['page_gen'] : '1';
        $search_fun = (isset($_GET['search_fun'])) ? $_GET['search_fun'] : "";
        $search_query = " ";
        $query_p2 = "";

        if (empty($page)) {

            $out .= '<form method="get" action="' . $script_name . '?do=support_newaccounts">';
            $out .= '<table class="table table-bordered">' . "\n";
            $out .= '<tr>';
            $out .= '<td class="thead" colspan="2" style="padding: 4px;"><b>Look up an account/ip</b></td>';
            $out .= '</tr>';
            $out .= '<tr>';
            $out .= '<td class="alt1">Account Name:</td>';
            $out .= '<td class="alt2"><input type="text" class="form-control" name="account_name" /></td>';
            $out .= '</tr>';
            $out .= '<tr>';
            $out .= '<td class="alt1">Last Logged Ip:</td>';
            $out .= '<td class="alt2"><input type="text" class="form-control" name="account_ip" /></td>';
            $out .= '</tr>';
            $out .= '<tr>';
            $out .= '<td colspan="2"><input type="hidden" name="do" value="support_newaccounts" /><input type="submit"  class="btn btn-default" value="Search" name="search_fun" /></td>';
            $out .= '</tr>';
            $out .= '</table>';
            $out .= '</form>';

            $out .= '<br/>' . "\n";

            $out .= '<table class="table table-bordered">' . "\n";
            $out .= '<tr>';
            $out .= '<td class="thead"style="padding: 4px;"><b>Account</b></td>';
            $out .= '<td class="thead"style="padding: 4px;"><b>Time</b></td>';
            $out .= '<td class="thead"style="padding: 4px;"><b>IP Address</b></td>';
            $out .= '<td class="thead"style="padding: 4px;"><b>Browser</b></td>';
            $out .= '</tr>';

            connectgamecpdb();
            if ($search_fun != "") {
                $account_name = (isset($_GET['account_name'])) ? $_GET['account_name'] : "";
                $account_ip = (isset($_GET['account_ip'])) ? $_GET['account_ip'] : "";

                if ($account_name != "" or $account_ip != "") {
                    $search_query = " WHERE ";
                    if ($account_name != "") {
                        $account_name = antiject($account_name);
                        $search_query .= "reg_account = '" . $account_name . "' ";

                    }

                    if ($account_ip != "") {

                        $account_ip = antiject($account_ip);

                        if (!preg_match("/%/", $account_ip)) {
                            if ($account_name != "") {
                                $search_query .= " AND ";
                            }
                            $search_query .= "reg_ip LIKE '%" . $account_ip . "%' ";
                        } else {
                            $search_query .= "reg_ip = '" . $account_ip . "' ";
                        }

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

            $query_p1 = 'SELECT id, reg_account, reg_ip, reg_time, reg_browser FROM gamecp_registration_log' . $search_query;
            $query_p2 .= 'id NOT IN ( SELECT TOP [OFFSET] id FROM gamecp_registration_log ORDER BY id DESC) ORDER BY id DESC';

            //Create a PS_Pagination object
            $url = str_replace("&page_gen=" . $page_gen, "", $_SERVER["REQUEST_URI"]);
            $pager = new PS_Pagination($gamecp_dbconnect, $query_p1, $query_p2, 50, 10, $url);

            //The paginate() function returns a mysql
            //result set for the current page
            $rs = $pager->paginate();

            while ($row = mssql_fetch_array($rs)) {

                $out .= '<tr>';
                $out .= '<td class="alt2" style="font-size: 10px;" width="2%" nowrap>' . $row['reg_account'] . '</td>';
                $out .= '<td class="alt1" style="font-size: 10px;" width="10%" nowrap>' . date("d/m/y h:i:sA", $row['reg_time']) . '</td>';
                $out .= '<td class="alt1" style="font-size: 10px;" width="8%" nowrap>' . $row['reg_ip'] . '</td>';
                $out .= '<td class="alt1" style="font-size: 10px;" width="20%" nowrap>' . $row['reg_browser'] . '</td>';
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