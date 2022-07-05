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
    $module[_l('Item Shop Admin')][_l('ADD Premium')] = $file;
    return;
}

$lefttitle = _l('Item Shop Admin - ADD Premium');;
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
            $serial = (isset($_POST['serial']) && is_array($_POST['serial'])) ? $_POST['serial'] : '';
            $DTEndPrem = (isset($_POST['DTEndPrem']) && is_array($_POST['DTEndPrem'])) ? $_POST['DTEndPrem'] : '';

            if ($serial != '' && $DTEndPrem != '') {
                $count = count($serial);

                for ($i = 0; $i < $count; $i++) {
                    $userid = (is_int((int)$serial[$i])) ? antiject((int)$serial[$i]) : '';
                    $userpoint = (is_int((int)$DTEndPrem[$i])) ? antiject((int)$DTEndPrem[$i]) : '';

                    if ($userid != '' && $userpoint != '') {
                        connectcashdb();
                        $select_user = "SELECT DTEndPrem,id FROM BILLING.dbo.BILLING.dbo.tbl_UserStatus WHERE serial = '$userid'";
                        $select_result = mssql_query($select_user);
                        $user = mssql_fetch_array($select_result);

                        if ($user['DTEndPrem'] != $userpoint) {

                            $update_points = "UPDATE BILLING.dbo.BILLING.dbo.tbl_UserStatus SET Status = '2' , DTStartPrem = Getdate(), DTEndPrem = Getdate()+" . $userpoint . " WHERE serial = '" . $userid . "'";
                            if (!($update_points_result = mssql_query($update_points))) {
                                $exit_process = 1;
                                $out .= '<p style="text-align: center; font-weight: bold;">Unable to update premium!</p>';
                            }

                            // Writing an admin log :D
                            gamecp_log(0, $userdata['username'], "ADMIN - ADD PREMIUM - UPDATED - Account ID: " . $user['id'] . " | Days: $userpoint | Old Premium: " . $user['DTEndPrem'], 0);

                        }
                        // Free Result
                        @mssql_free_result($select_result);
                    }
                }

                if ($exit_process != 1) {
                    $out .= '<table class="table table-bordered" align="center">' . "\n";
                    $out .= '	<tr>' . "\n";
                    $out .= '		<td class="alt1" style="text-align: center;">Updated user Premium!</td>' . "\n";
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
                connectcashdb();
                $sql = "SELECT Serial FROM BILLING.dbo.BILLING.dbo.tbl_UserStatus WHERE id = '" . $account_name . "'";
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
                    $search_query .= " serial = '$account_id'";
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
            $out .= '		<td class="thead">Premium Days [ Insert 1 for one day / 2 for two days... ]</td>' . "\n";
            $out .= '	</tr>' . "\n";

            # Pageination
            include('./includes/pagination/ps_pagination.php');

            # Connect
            connectcashdb();

            # SQL Statements
            $query_p1 = "SELECT serial,DTEndPrem, id FROM BILLING.dbo.tbl_UserStatus" . $search_query;
            $query_p2 .= "serial NOT IN ( SELECT TOP [OFFSET] serial FROM BILLING.dbo.tbl_UserStatus$search_query ORDER BY serial DESC) ORDER BY serial DESC";

            # Create a PS_Pagination object
            $url = str_replace("&page_gen=" . $page_gen, "", $_SERVER["REQUEST_URI"]);
            $pager = new PS_Pagination($gamecp_dbconnect, $query_p1, $query_p2, 20, 10, $url);

            # The paginate() function returns a mysql
            # result set for the current page
            $rs = $pager->paginate();

            # user db
            connectcashdb();
            while ($row = mssql_fetch_array($rs)) {

                $sql = "SELECT id AS Name FROM BILLING.dbo.tbl_UserStatus WHERE Serial = '" . $row['serial'] . "'";
                $result = mssql_query($sql);
                $user_info = mssql_fetch_array($result);

                $out .= '	<tr>' . "\n";
                $out .= '		<td class="alt2" width="1" style="text-align: center;"><input type="hidden" name="serial[]" value="' . $row['serial'] . '" />' . $row['serial'] . '</td>' . "\n";
                $out .= '		<td class="alt1">' . antiject($user_info['Name']) . '</td>' . "\n";
                $out .= '		<td class="alt1"><input type="text" class="form-control" name="DTEndPrem[]" value="' . $row['DTEndPrem'] . '" /></td>' . "\n";
                $out .= '	</tr>' . "\n";

                // Free Result
                @mssql_free_result($result);
            }

            if (mssql_num_rows($rs) > 0) {
                $out .= '<tr>' . "\n";
                $out .= '	<td class="alt2" colspan="3" style="text-align: center;">' . $pager->renderFullNav() . '</td>' . "\n";
                $out .= '</tr>' . "\n";
                $out .= '<tr>' . "\n";
                $out .= '	<td class="alt2" colspan="3" style="text-align: center;"><input type="hidden" name="account_name" value="' . $account_name . '"/><input type="submit"  class="btn btn-default" name="update" value="Update Premium" /></td>' . "\n";
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