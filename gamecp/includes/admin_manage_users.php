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
    $module[_l('Item Shop Admin')][_l('ADD Game Point')] = $file;
    return;
}

$lefttitle = _l('Item Shop Admin - Game Point');;
$time = date('F j Y G:i');

if ($this_script == $script_name) {

    if (hasPermissions($do)) {

        # Main variables
        $update = (isset($_POST['update'])) ? $_POST['update'] : "";
        $page_gen = (isset($_GET['page_gen'])) ? $_GET['page_gen'] : "1";
        $search_fun = (isset($_POST['search_fun'])) ? $_POST['search_fun'] : "";
        $query_p2 = "";
        $search_query = "";
        $exit_process = 0;
        $account_name = (isset($_POST['account_name'])) ? antiject($_POST['account_name']) : '';

        # Draw the search page
        $out .= '<form method="post">' . "\n";
        $out .= '<table class="table table-bordered" align="center">' . "\n";
        $out .= '	<tr>' . "\n";
        $out .= '		<td class="thead" colspan="2">Look up a user</td>' . "\n";
        $out .= '	</tr>' . "\n";
        $out .= '	<tr>' . "\n";
        $out .= '		<td class="alt2">Account Name</td>' . "\n";
        $out .= '		<td class="alt1"><input type="text" class="form-control" name="account_name" value="' . $account_name . '"/></td>' . "\n";
        $out .= '	</tr>' . "\n";
        $out .= '	<tr>' . "\n";
        $out .= '		<td class="alt1" colspan="2"><input type="submit"  class="btn btn-default" name="search_fun" value="Look up"/></td>' . "\n";
        $out .= '	</tr>' . "\n";
        $out .= '</table>' . "\n";
        $out .= '</form>' . "\n";

        $out .= '<br/>' . "\n";

        # Do update?
        if ($update != '') {
            $user_id = (isset($_POST['user_id']) && is_array($_POST['user_id'])) ? $_POST['user_id'] : '';
            $user_point = (isset($_POST['user_points']) && is_array($_POST['user_points'])) ? $_POST['user_points'] : '';

            if ($user_id != '' && $user_point != '') {
                $count = count($user_id);

                for ($i = 0; $i < $count; $i++) {
                    $userid = (is_int((int)$user_id[$i])) ? antiject((int)$user_id[$i]) : '';
                    $userpoint = (is_int((int)$user_point[$i])) ? antiject((int)$user_point[$i]) : '';

                    if ($userid != '' && $userpoint != '') {
                        connectgamecpdb();
                        $select_user = "SELECT user_points,user_account_id FROM gamecp_gamepoints WHERE user_id = '$userid'";
                        $select_result = mssql_query($select_user);
                        $user = mssql_fetch_array($select_result);

                        if ($user['user_points'] != $userpoint) {

                            $update_points = "UPDATE gamecp_gamepoints SET user_points = '" . $userpoint . "' WHERE user_id = '" . $userid . "'";
                            if (!($update_points_result = mssql_query($update_points))) {
                                $exit_process = 1;
                                $out .= '<p style="text-align: center; font-weight: bold;">Unable to user game points!</p>';
                            }

                            // Writing an admin log :D
                            gamecp_log(0, $userdata['username'], "ADMIN - ADD GAME POINTS - UPDATED - Account ID: " . $user['user_account_id'] . " | New Points: $userpoint | Old Points: " . $user['user_points'], 0);

                        }
                        // Free Result
                        @mssql_free_result($select_result);
                    }
                }

                if ($exit_process != 1) {
                    $out .= '<table class="table table-bordered" align="center">' . "\n";
                    $out .= '	<tr>' . "\n";
                    $out .= '		<td class="alt1" style="text-align: center;">Updated user game points</td>' . "\n";
                    $out .= '	</tr>' . "\n";
                    $out .= '</table>' . "\n";
                    $out .= '<br/>' . "\n";
                }
            }
        }

        # Searched?
        if ($search_fun != "") {

            if ($account_name == '') {
                $exit_process = 1;
                $out .= '<p style="text-align: center; font-weight: bold;">Please fill in a account name</p>';
            } else {
                connectuserdb();
                $sql = "SELECT Serial FROM tbl_UserAccount WHERE id = convert(binary,'" . $account_name . "')";
                $result = mssql_query($sql);
                $user_info = mssql_fetch_array($result);
                $account_id = $user_info['Serial'];
                // Free Result
                @mssql_free_result($result);
            }

            if ($exit_process == 0) {

                if ($account_name != "") {
                    $search_query = " WHERE ";
                    $query_p2 .= " AND ";
                } else {
                    $query_p2 .= "WHERE ";
                }

                if ($account_name != "") {
                    $search_query .= " user_account_id = '$account_id'";
                }

            }

        } else {
            $query_p2 .= " WHERE ";
        }

        # May we proceed with displaying the data?
        if ($exit_process == 0) {

            # Draw the rest of the page
            $out .= '<form method="post">' . "\n";
            $out .= '<table class="table table-bordered" align="center">' . "\n";
            $out .= '	<tr>' . "\n";
            $out .= '		<td class="thead" style="text-align: center;">ID</td>' . "\n";
            $out .= '		<td class="thead">Account Name</td>' . "\n";
            $out .= '		<td class="thead">Game Points</td>' . "\n";
            $out .= '	</tr>' . "\n";

            # Pageination
            include('./includes/pagination/ps_pagination.php');

            # Connect
            connectgamecpdb();

            # SQL Statements
            $query_p1 = "SELECT user_id, user_points, user_account_id FROM gamecp_gamepoints" . $search_query;
            $query_p2 .= "user_id NOT IN ( SELECT TOP [OFFSET] user_id FROM gamecp_gamepoints$search_query ORDER BY user_id DESC) ORDER BY user_id DESC";

            # Create a PS_Pagination object
            $url = str_replace("&page_gen=" . $page_gen, "", $_SERVER["REQUEST_URI"]);
            $pager = new PS_Pagination($gamecp_dbconnect, $query_p1, $query_p2, 20, 10, $url);

            # The paginate() function returns a mysql
            # result set for the current page
            $rs = $pager->paginate();

            # user db
            connectuserdb();
            while ($row = mssql_fetch_array($rs)) {

                $sql = "SELECT convert(varchar,id) AS Name FROM tbl_UserAccount WHERE Serial = '" . $row['user_account_id'] . "'";
                $result = mssql_query($sql);
                $user_info = mssql_fetch_array($result);

                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2" width="1" style="text-align: center;"><input type="hidden" name="user_id[]" value="' . $row['user_id'] . '" />' . $row['user_id'] . '</td>' . "\n";
                $out .= '		<td class="alt1">' . antiject($user_info['Name']) . '</td>' . "\n";
                $out .= '		<td class="alt1"><input type="text" class="form-control" name="user_points[]" value="' . $row['user_points'] . '" /></td>' . "\n";
                $out .= '	</tr>' . "\n";

                // Free Result
                @mssql_free_result($result);
            }

            if (mssql_num_rows($rs) > 0) {
                $out .= '<tr>' . "\n";
                $out .= '	<td class="alt2" colspan="3" style="text-align: center;">' . $pager->renderFullNav() . '</td>' . "\n";
                $out .= '</tr>' . "\n";
                $out .= '<tr>' . "\n";
                $out .= '	<td class="alt2" colspan="3" style="text-align: center;"><input type="hidden" name="account_name" value="' . $account_name . '"/><input type="submit"  class="btn btn-default" name="update" value="Update Points" /></td>' . "\n";
                $out .= '</tr>' . "\n";
            } else {
                $out .= '<tr>' . "\n";
                $out .= '	<td class="alt2" colspan="3" style="text-align: center;">No users found</td>' . "\n";
                $out .= '</tr>' . "\n";
            }

            $out .= '</table>' . "\n";
            $out .= '</form>' . "\n";

            // Free Result
            @mssql_free_result($rs);
        }

    } else {
        $out .= _l('no_permission');
    }

} else {
    $out .= _l('invalid_page_load');
}

?>